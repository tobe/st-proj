#!/usr/bin/env php
<?php

require_once 'vendor/autoload.php';
use lsolesen\pel;

class Parser {
    private $data;
    private $parsed;

    private $regexes = array(
        'id_camera'       => 'id_camera\s:\s(\d+)', # camera ID
        'time'            => 'time\s:\s(\d{2}:\d{2}:\d{2}\.\d+)', # HH:MM:SS.<miliseconds>
        'date'            => 'date\s:\s(\d{4}-\d{2}-\d{2})', # YYYY-MM-DD
        'status'          => 'status\s:\s([t|f])', # t or f
        'id_alarm'        => 'id_alarm\s:\s(\d+)', # alarm ID
        'id_logic_camera' => 'id_logic_camera\s:\s(\d+)', # logic camera ID
        'fire_index'      => 'fire_index\s:\s(\d+\.?\d*)', # fire index, 0 to 1, floating point!
        'confirmed'       => 'confirmed\s:\s([t|f])', # t or f
        'dismissed'       => 'dismissed\s:\s([t|f])', # t or f
        'img_number'      => 'img_number\s:\s(\d+)', # img number, integer
        # vrijeme: date + \s + time
        'img_number'      => 'min_elongation\s:\s(\d+\.?\d*)', # min_elongation -> decimal (?!)
        # koordinate ???
        'preset_name'     => 'preset_name\s:\s(preset\d+)' # preset
    );

    public function __construct($argv, $argc) {
        // Load up the file
        $filename = './image_meta.php.html';
        $fp = fopen($filename, 'rb');
        $this->data = fread($fp, filesize($filename));

        // Parse into an array
        $this->parsed = $this->parse();

        // Process images
        $this->processImages();
    }

    private function parse() {
        // Prepare the output
        $out = [];

        // Prepare all file names and push them into the output array
        preg_match_all('/(image\d+)\.jpg/', $this->data, $files);
        foreach($files[1] as $file) {
            $out[$file] = [];
        }

        // Get all the array keys so they corresponds numerically
        $keys = array_keys($out);

        // Loop through each regex and parse
        foreach($this->regexes as $key => $regex) {
            preg_match_all(sprintf('/%s/', $regex), $this->data, $res);

            $i = 0;
            foreach($res[1] as $data) {
                $image_id = $keys[$i];

                // Add normal tags
                $out[$image_id][$key] = $data;

                $i++;
            }
        }

        // Add special tags and json encode them
        foreach($out as $image => $data) {
            $out[$image]['vrijeme'] = sprintf('%s %s', $data['date'], $data['time']);

            // JSON way (comment/uncomment either one of these)
            //$out[$image] = json_encode($data);
            // Human readable way
            $out[$image] = implode("\r\n", array_map(
                function ($v, $k) { return sprintf('%s: %s', $k, $v); },
                $data,
                array_keys($data)
            ));
        }

        return $out;
    }

    private function processImages() {
        foreach($this->parsed as $file => $data) {
            // That it?
            $this->writeExif(
                $file,
                $data
            );
        }
    }

    private function writeExif($file, $description) {
        /* Load the given image into a PelJpeg object */
        $jpeg = new pel\PelJpeg('./image_meta.php_files/' . $file . '.jpg');

        /*
         * Create and add empty Exif data to the image (this throws away any
         * old Exif data in the image).
         */
        $exif = new pel\PelExif();
        $jpeg->setExif($exif);

        /*
         * Create and add TIFF data to the Exif data (Exif data is actually
         * stored in a TIFF format).
         */
        $tiff = new pel\PelTiff();
        $exif->setTiff($tiff);

        $ifd0 = new pel\PelIfd(pel\PelIfd::IFD0);
        $tiff->setIfd($ifd0);

        $inter_ifd = new pel\PelIfd(pel\PelIfd::INTEROPERABILITY);
        $ifd0->addSubIfd($inter_ifd);
        $ifd0->addEntry(new pel\PelEntryAscii(pel\PelTag::IMAGE_DESCRIPTION, $description));

        $exif_ifd = new pel\PelIfd(pel\PelIfd::EXIF);
        $exif_ifd->addEntry(new pel\PelEntryUserComment($description));
        $ifd0->addSubIfd($exif_ifd);

        $jpeg->saveFile(sprintf('./output/%s.jpg', $file));
    }
}

new Parser($argv, $argc);

?>

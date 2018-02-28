# st-proj
1. Postaviti *image_meta.php_files/* direktorij i *image_meta.php.html* datoteku unutar direktorija zajedno s ovim projektom
    * U radnom direktoriju sada se trebaju nalaziti dva direktorija, *image_meta.php_files* i *output* i `parser.php` datoteka
    * Osim toga su tu i sporedne datoteke, ovaj `README.md` i `composer.json` i `composer.lock`
2. Instalirati [Composer](https://getcomposer.org/), PHP-ov package manager
3. [Instalirati dependencies](https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies), odnosno pokrenuti
    * ``` php composer.phar install ``` ili bez *.phar* dijela
4. Pokrenuti parser, ```php parser.php```. Eventualno promijeniti način generiranja izlaznih EXIF tagova (JSON/human-readable)
5. U `output/` direktoriju pojave se generirane slike s EXIF tagovima.

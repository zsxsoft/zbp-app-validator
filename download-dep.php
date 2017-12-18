<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 12/18/2017
 * Time: 16:02
 */
echo "Downloading PHP7CC...\n";
file_put_contents('./vendor/php7cc.phar', file_get_contents('https://github.com/sstalle/php7cc/releases/download/1.2.1/php7cc.phar'));
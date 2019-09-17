<?php defined('SYSPATH') OR exit();
/*
 * Для работы с CSS
 */

class Core_Css {
    static function path($file){
        $a = explode(DOCROOT,$file);
        return $a[1];
    }
}
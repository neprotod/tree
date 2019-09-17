<?php
/*
 * Подключение конфигурационных файлов и создание запросов к БД
 * @package   Tree
 * @category  Base
 */
class Core_Config extends Core{
    /*
     * Получение конфигураций с файла
     * @param   string   имя файла без расширения
     * @return  array | false
     */
    static function get_global($file){
        //Проверяем на заполненость
        if(!$file){
            return FALSE;
        }
        //Сокращаем код
        $path = 'config'.DIRECTORY_SEPARATOR.$file.EXT;
        //Ищем путь
        foreach(Core::$_paths as $dir){
            if(is_file($dir.$path)){
                //Проверяем получили ли массив
                if(is_array($arr = require($dir.$path))){
                    return $arr;
                }else{
                    return FALSE;
                }
            }
        }
    }
}
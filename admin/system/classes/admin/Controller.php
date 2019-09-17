<?php
/*
 * Распределение и подключение модулей
 *
 * @package   Tree
 * @category  Base
 */
class Admin_Controller{
    public static function factory($name, $module, $settings = NULL,  $bool = TRUE){
        // Добавить префикс модель
        $class = 'Controller_'.$name;
        
        // создаем путь к файлу
        $file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($class));
        
        // Дополняем имя
        $class .= "_".Admin_Module::name($module);
        
        // Абсолютный путь к файлу
        $path = Admin_Module::mod_path($module).'classes'.DIRECTORY_SEPARATOR.$file.EXT;

        // Проверка счуществут ли данный Контроллер
        if(is_file($path)){
            if(!class_exists($class))
                require $path;
        }else{
            throw new Core_Exception('Контроллер  '.$name.' не найден');
        }
        if($bool === TRUE){
            if(!empty($settings))
                return new $class($settings);
            return new $class;
        }
    }
}

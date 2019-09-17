<?php

/*
 * Модель базового класса. Все модели должны расширить этот класс.
 * 
 * @package    Tree
 * @category   Models
 */
class Admin_Model{
    
    /*********methods***********/
    /**
     * Создайте новый экземпляр модели.
     *
     *     $model = Model::factory($name);
     *
     * @param   string   model name
     * @return  Model
     */
     
     static function factory($name, $module, $settings = NULL, $bool = TRUE){
        // класс модели
        $class = 'Model_'.$name;
        
        // создаем путь к файлу
        $file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($class));
        
        $class .= "_".Admin_Module::name($module);
        // Абсолютный путь к файлу
        $path = Admin_Module::mod_path($module).'classes'.DIRECTORY_SEPARATOR.$file.EXT;

        // Проверка счуществут ли данная Модель
        if(is_file($path)){
            if(!class_exists($class))
                require $path;
        }else{
            throw new Core_Exception('Модель  '.$name.' не найдена');
        }
        if($bool === TRUE){
            if(!empty($settings))
                return new $class($settings);
            return new $class;
        }
        if(strtolower($bool) === 'get'){
                return $class;
        }
    }
}
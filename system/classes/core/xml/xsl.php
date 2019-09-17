<?php defined('SYSPATH') OR exit();

class Core_Xml_Xsl extends View{
    /*Перенастройки View*/
    public static function factory($file = NULL, $module = NULL, array $data = NULL){
        $view = new View($file, $module, $data);
        
        try{
            return $view->render();
        }catch (Exception $e){
            // Отображение сообщение об исключении
            Core_Exception::handler($e);

            return '';
        }
    }
    
    public function __construct($file = NULL, $module, array $data = NULL){
        if ($file !== NULL AND !is_object($file)){
            $this->set_filename($file, $module);
        }else{
            if($file instanceof Template)
                $this->_file = $file->template();
        }

        if ($data !== NULL){
            // Добавьте значения в текущих данных
            $this->_data = $data + $this->_data;
        }
    }
    
    function set_filename($file, $module){
        $file = 'views_'.$file;
        
        // создаем путь к файлу
        $file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($file));
        
        
        // Абсолютный путь к файлу
        if(isset($module))
            $path = Module::mod_path($module).$file.EXT;
        else 
            $path = $file.EXT;
        // Храните путь к файлу локально
        $this->_file = $path;

        return $this;
    }

}
<?php
/**
 * Abstract controller class for automatic templating.
 *
 * @package    Kohana
 * @category   Controller
 */
class Admin_Template{
    public $template;
    public $file;
    
    public $view;
    /*Что бы можно было создать темплате*/
    static function factory($template, $file, $date = NULL){
        $view = new Admin_Template($template, $file, $date);
        return $view->view;
    }
    
    protected function __construct($template, $file, $date){
        $this->template = $template;

        $this->file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($file));
        
        $this->view = Admin_View::factory($this, NULL, $date);
    }
    
    function template(){
        $file = Admin::find_file("template_{$this->template}", $this->file);
        return $file;
    }
}

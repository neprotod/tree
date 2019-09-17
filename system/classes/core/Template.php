<?php defined('SYSPATH') OR exit();
/**
 * Abstract controller class for automatic templating.
 *
 * @package    Kohana
 * @category   Controller
 */
class Core_Template{
    public $template;
    public $file;
    
    public $view;
    /*Что бы можно было создать темплате*/
    static function factory($template, $file, $date = NULL){
        $view = new Template($template, $file, $date);
        return $view->view;
    }
    
    protected function __construct($template, $file, $date){
        $this->template = $template;

        $this->file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($file));
        
        $this->view = View::factory($this, NULL, $date);
    }
    
    function template(){
        $file = Core::find_file("template_{$this->template}", $this->file);
        return $file;
    }
}

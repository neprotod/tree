<?php defined('SYSPATH') OR exit();
    
/*
 * Возвращает URL и делает всевозможные проверки
 * 
 * @package   Tree
 * @category  Base
 */
class Core_Url{
    /**
     * @var  string  Содержит в себе путь адреса
     */
    public $url;
    
    /**
     * @var  string  Содержит в "?query string"
     */
    public $query;
    /**
     * @var  string  без начального слеша
     */
    public $out;
    
    private static $_instance;
    /*********methods***********/
    
    
    /*
     * Создаем класс и заполняем переменные
     * @return  void
     */
    private function __construct(){
        $url = $_SERVER['REQUEST_URI'];
        $query = $_SERVER['QUERY_STRING'];
        // Если есть строка подзапроса 
        if(!empty($query)){
            $str = explode('?' . $query,$url);
            $url = $str[0];
            //Заполняем если есть строка подзапроса
            $this->query = $query;
        }

        // Убераем последний слеш
        $url = '/' . $out = trim($url, '/');
        
        //Заполняем
        $this->url = $url;
        
        $this->out = (empty($out))? $url : $out;
    }
    static function instance(){
        if(isset(self::$_instance)){
            return self::$_instance;
        }
        return self::$_instance = new Url();
    }
    
    /*
     * Выводит всю строку
     *
     * @return  string
     */
    function __toString(){
        if($this->query)
            $q = '?';
        return $this->url.$q.$this->query;
    }
}

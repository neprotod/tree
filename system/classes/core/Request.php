<?php defined('SYSPATH') OR exit();
/*
 * Выполненире всего кода
 */
class Core_Request{
    
    // Для хранения роутера
    public $router;
    
    // Для хранения роутера
    static public $design;
    // Здесь хранится URL для запроса
    //private $url;
    
    // Здесь хранится модуль для заполнения контента
    //private $module;
    /*********Method***********/
    
    /*Static*/
    /*
    * Создает образец объектка
    *
    * @return this
    */
    static function factory(){
        $request = new Request();
        return $request;
    }
    
    /*no static*/
    /*
    * Конструктор класса, заполняем начальные параметры, вызывает rout, 
    * определят текущую тему, возможно в будущем будет кешировать станицу.
    *
    * @return void
    */
    function __construct(){
        if($_SERVER['HTTP_TYPE'] == 'socket' AND $_SERVER['HTTP_INIT'] == '0000'){
            ob_start();
                Module::load('socket',NULL,'fetch');
            $buffer = ob_get_clean();
            echo $buffer;
            exit();
        }
            
        $this->router = new Route();
        $this->router->init();
        
        Request::$design = Module::factory('design', TRUE);
    }
    
    function render(){
        // Количество товара в корзине
        Request::$design->cart = Module::load('cart',NULL,'cart_num');
        
        // Категории товаров
        Module::load('categories',NULL,'init_categories');
        
        // Меню
        Request::$design->menu_top = Module::load('menu',NULL,'top');

        // Основной блок страницы
        if (!Request::$design->content = Module::load(Registry::i()->module,NULL,'fetch')){
            // Если блок пустой
            Module::load('system',NULL,'error');
        }
        // Делаем меню категорий ниже
        Request::$design->menu_bottom = Module::load('menu',NULL,'bottom');
        
        echo Template::factory(Registry::i()->settings['theme'],'index',array('design' => Request::$design));
    }
    
    function execute(){
        
        // Буфиринизируем все данные
        ob_start();
        $this->render();
        $buffer = ob_get_clean();
        
        // Наченаем строить вывод
        //header('Content-Type: text/html; charset=utf-8');
        echo $buffer;
    }
    /*************************/
    /*Вспомогательные функции*/
    /*************************/
    /**
    * Возвращает переменную _POST, отфильтрованную по заданному типу, если во втором параметре указан тип фильтра
    * Второй параметр $type может иметь такие значения: integer, string, boolean
    * Если $type не задан, возвращает переменную в чистом виде
    */
    static function post($name = null, $type = null){
        $val = null;
        if(!empty($name) && isset($_POST[$name]))
            $val = $_POST[$name];
        elseif(empty($name))
            $val = file_get_contents('php://input');
            
        if($type == 'string')
            return strval(preg_replace('/[^\p{L}\p{Nd}\d\s_\-\.\%\s]/ui', '', $val));
            
        if($type == 'integer')
            return intval($val);

        if($type == 'boolean')
            return !empty($val);

        return $val;
    }
    
    /**
    * Возвращает переменную _GET, отфильтрованную по заданному типу, если во втором параметре указан тип фильтра
    * Второй параметр $type может иметь такие значения: integer, string, boolean
    * Если $type не задан, возвращает переменную в чистом виде
    */
    
    static function get($name, $type = null){
        $val = null;
        if(isset($_GET[$name]))
            $val = $_GET[$name];
        if(!empty($type) && is_array($val))
            $val = reset($val);
        
        if($type == 'string')
            return strval(preg_replace('/[^\p{L}\p{Nd}\d\s_\-\.\%\s]/ui', '', $val));
            
        if($type == 'integer')
            return intval($val);

        if($type == 'boolean')
            return !empty($val);
            
        return $val;
    }
    
    /**
    * Проверка сессии
    */
    static function check_session(){
        if(!empty($_POST)){
            if(empty($_POST['session_id']) || $_POST['session_id'] != session_id()){
                unset($_POST);
                return false;
            }
        }
        return true;
    }
    
    static function method($method = null){
        if(!empty($method))
            return strtolower($_SERVER['REQUEST_METHOD']) == strtolower($method);
        return $_SERVER['REQUEST_METHOD'];
    }
    
   /**
    * Возвращает переменную _FILES
    * Обычно переменные _FILES являются двухмерными массивами, поэтому можно указать второй параметр,
    * например, чтобы получить имя загруженного файла: $filename = Request::files('myfile', 'name');
    */
    static function files($name, $name2 = null){
        if(!empty($name2) && !empty($_FILES[$name][$name2]))
            return $_FILES[$name][$name2];
        elseif(empty($name2) && !empty($_FILES[$name]))
            return $_FILES[$name];
        else
            return null;
    }
    
    /*Выдача параметров*/
    static function param($param, $escape = NULL,$return = NULL, $charset = 'UTF-8'){
        if(isset($param) AND !empty($param)){
            if($escape === TRUE AND is_string($param))
                return htmlentities(strip_tags($param),ENT_QUOTES,$charset);

            return $param;
        }else{
            return $return;
        }
    }
    /*Удаляет значение и возвращает результат*/
    static function unsets(&$param){
        if(!empty($param)){
            $get = $param;
            unset($param);
            return $get;
        }
    }
    
}
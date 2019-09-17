<?php
/*
 * @package   Tree
 * @category  Base
 */
class Admin extends Core{
    
    /**
     * @var  string  Базовый URL
     */
    public static $base_url = '/';
    
    public $default_modul = 'products';
    
    public static $url;
    
    public static $design;
    
    protected static $_paths = array(SYSPATH_ADMIN, APPPATH, SYSPATH);
    
    
    ////////////////////////
    ///// Запуск CMS
    ////////////////////////
    function __construct(){
        Core::$selected_mode = Core::DEVELOPMENT;
        Core::$_paths = array(SYSPATH_ADMIN,DESIGN_ADMIN, APPPATH, SYSPATH);
        Core::init();
        Core::$sample = get_class($this);
        /*Узнаем корневое УРЛ*/
        $url = explode('/', URL::instance()->url);
        
        Core::$base_url = array_shift($url);
        
        unset($url);
        /*
         * Запускаем пути подключения путей
         */
        Core::$config->attach(new Config_File);
        // Загружаем модули

        Module::module_path(TRUE);
        
        Admin_Module::module_path(TRUE);
        
        // Конфигурации
        $this->initialization();
        
        /********
        *Определение пользователя
        **********/
        $user = Admin_Module::load('user');
        
        // Проверка сессии для защиты от xss
        if(!Request::check_session()){
            unset($_POST);
            exit('Session expired');
        }

        /*Загрузка необходимых модулей*/
        Request::$design = Module::factory('design', TRUE);
        
        //echo Admin_Template::factory('default','index');

        // Подключаем основной модуль отображения
        if(!empty(Registry::i()->module))
            $this->module = Admin_Module::factory(Registry::i()->module,TRUE);
        
    }
    
    function fetch(){

        if($method = Request::get('method')){
            $content = $this->module->$method();
        }else{
            $content = $this->module->fetch();
        }
        return $content;
        
    }
    ////////////////////////
    //// Функции администрирования
    ///////////////////////
    
    function initialization(){
        $route = new Route;
        // Настройки
        Registry::i()->settings = $route->settings();
        Registry::i()->currency = $route->currencies();
        $url = URL::instance()->url;
        
        Registry::i()->module = Request::get('module','string');
        if(empty(Registry::i()->module)){
            Registry::i()->module = $this->default_modul;
        }
        Registry::i()->page_url = URL::instance()->url;
    }
    
    ////////////////////////
    //// Автозагрузка классов
    ///////////////////////

    
    /*Классы как переменные*/
    function __get($name){
        return Module::factory($name, TRUE);
    }

}

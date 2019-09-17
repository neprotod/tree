<?php defined('SYSPATH') OR exit();
/*
 * Маршрузитатор
 */
class Core_Route{
    
    public $module;
    
    public $page_url;
    /*********method*********/
    function __construct(){
        if(Core::$check_host !== TRUE){
            throw new Core_Exception_Include(NULL,'domen');
        }
    }
    function init(){
        $settings = Core::config('settings')->page;
        
        $url = URL::instance()->url;
        
        // Проверяем ссылку
        if(($char = UTF8::substr($_SERVER['REQUEST_URI'],-1,1) AND $_SERVER['REQUEST_URI'] != '/') AND ($char == '?' OR  $char == '/')){
        
            header("HTTP/1.1 302 Found");
            
            if($char == '?'){
                header("Location: ".rtrim(URL::root(NULL),'?/'));
            }else{
                header("Location: ".URL::root(NULL));
            }
            
            exit();
            
        }elseif($_SERVER['REQUEST_URI'] == '/index.php'){
            header("Location: /",TRUE,301);
            exit();
        }
        if($url == '')
            Registry::i()->home = TRUE;
        else
            Registry::i()->home = FALSE;
        // Разбиваем на куски
        $pieces = explode('/',$url);
        if(isset($settings[$pieces[0]]))
            $check = $settings[$pieces[0]];
        Registry::i()->type = $pieces[0];
        if(!empty($check)){
            $this->module = $check;
            unset($pieces[0]);
            $this->page_url = strtolower(implode('/',$pieces));
        }else{
            $this->module = 'page';
            $this->page_url = strtolower($url);
        }
        
        // Дополнительно сохраняем в глобальную область
        Registry::i()->module = & $this->module;
        Registry::i()->page_url = & $this->page_url;
        Registry::i()->settings = $this->settings();
        Registry::i()->currencies = $this->currencies();

        /*Отключаем сайт если надо*/
        if(Request::param(Registry::i()->settings['lock']) == 'lock'){
            header("Cache-control: no-store,max-age=0");
            exit('Сайт временно отключен');
        }
        // Сессия если есть
        
        $this->session(2592000);
    }
    
    function settings(){
        $sql = DB::placehold("SELECT name, value FROM __settings");
        $query = DB::query(Database::SELECT, $sql);

        $result = $query->execute();
        $setting = array();
        foreach($result as $res){
            $setting[$res['name']] = $res['value'];
        }
        return $setting;
    }
    function currencies(){
        $sql = DB::placehold("SELECT name, sign, code, rate_from, rate_to, position FROM __currencies WHERE enabled = 1");
        $query = DB::query(Database::SELECT, $sql);

        $result = $query->execute();
        
        return $result[0];
    }
    
    function session($cookie_life = 0, $session_id = 'session_id'){
        $cookie_life = intval($cookie_life);
        
        if(empty($_COOKIE[$session_id])){
            session_start();
            session_id();
            setcookie($session_id, session_id(), time()+$cookie_life);
            $_SESSION['life'] = TRUE;
            return;
        }
        session_id($_COOKIE[$session_id]);
        session_start();
        if(isset($_SESSION['life']) AND $_SESSION['life'] !== TRUE){
            setcookie($session_id, session_id(), time()+$cookie_life);
            $_SESSION['life'] = TRUE;
        }
        
    }
    
}
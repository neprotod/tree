<?php defined('MODPATH') OR exit();

class Model_Error_System{
    function error(){
        ob_clean();
        
        header('HTTP/1.1 404 Not Found');
        header('Content-Type: text/html; charset=UTF-8');
        header("Cache-control: no-store,max-age=0");
        
        echo View::factory('error404','system');
        exit();
    }
}
<?php defined('MODPATH') OR exit();

class News_Module implements I_Module{

    const VERSION = '0.0.1';
    
    function __construct(){
        
    }
    
    function index($setting = null){
        
    }
    
    function fetch(){
        echo 'Модуль подключен';
    }

}
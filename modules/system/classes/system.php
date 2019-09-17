<?php defined('MODPATH') OR exit();

class System_Module implements I_Module{

    const VERSION = '0.0.1';
    
    // Модно ли проходить по нодам?
    const NODE = 1;
    
    // Для модели работы с url
    private $url;
    
    function index($setting = null){
        //echo "Начнем же<br>";
        $this->url = Model::factory('url','system');
        
        Registry::i()->map = $this->start();
        
        $block = Module::load_module('block');
        
        Registry::i()->root = $this->url->root();

        return Module::load_module('page',$block);
    }
    
    private function start(){
        // Определение страницы
        // массив для данных
        $map = array();
        
        // Получаем рабочую ссылку
        $map['sourse'] = $this->url->node();
        
        // проверяем можно ли работать с этой ссылкой
        if(!empty($map['sourse']))
            $map['transfer'] = $this->url->router($map['sourse']);
        
        if(empty($map['transfer'])){
            $error = Model::factory('error','system');
            $error->error();
        }
        
        return $map;
    }    
    /*Ошибка 404*/
    function error(){
        $error = Model::factory('error','system');
            $error->error();
    }
}
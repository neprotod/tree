<?php defined('MODPATH') OR exit();
// Клиентское приложение
class Criptograf_Module implements I_Module{
    function __construct(){
    
    }
    
    function index($setting = null){
        
    }
    /*Создает*/
    function set($string){
        if(is_string($string)){
            $bin = unpack("H*",$string);
            $bin = $this->coder($bin[1]);
        }
        return $bin;
    }
    /*возвращет*/
    function get($string){
        if(is_string($string)){
            $bin = $this->coder($string);
            $bin = pack('H*',$bin);
        }
        return $bin;
    }
    
    function coder($arr = array()){
        if(!is_array($arr)){
            $leng = strlen($arr);
            $fond = array();
            for($i = 0; $i < $leng; $i++){
                $fond[] = $arr{$i};
            }
            $arr = $fond;
        }
        
        $newArr = array();
        
        for(;;){
            $first = '';
            $two = '';
            if(empty($arr))
                break;
            $first = array_shift($arr);
            if(!empty($arr))
                $two = array_shift($arr);
            if($two != '')
                $newArr[] = $two;
            $newArr[] = $first;
        }
        
        return implode($newArr);
    }
    
}
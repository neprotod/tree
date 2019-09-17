<?php defined('SYSPATH') OR exit();

/*
 * Создает коталог файлов и сохраняет их.
 *
 * @package   Tree
 * @category  Base
 */
 class Core_Direct{
    
    public static $dir = 'direct';
    
    /*********methods***********/
    
    /*
     * @param  array Абсолютные пути где нужно построить каталоги
     * @param  bool  Перезаписать каталог?
     */
    static function directory_out(array $directory,$cache = FALSE){
        $found = array();
        // Закешировано ли?
        if($cache !== TRUE){
            foreach($directory as $dir){
                // Определеяем есть ли в конце слеш
                $int_str = mb_strlen($dir);
                if(mb_substr($dir,$int_str - 1,$int_str) == DIRECTORY_SEPARATOR)
                    $dir = mb_substr($dir,0,-1);
                    
                // Открываем директорию
                if($d = @opendir($dir)){
                    while($file = readdir($d)){
                        // Уберем корневые каталоги
                        if($file == '.' OR $file == '..')
                            continue;
                        if(is_dir($dir.DIRECTORY_SEPARATOR.$file)){
                            $found[] = $dir.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR;
                        }
                    }
                }
            }
            /*Решить проблему кеширования*/
            Direct::cache_direct($found,$directory);
        }else{
            $found = Direct::get_cache($directory);
        }
        
        
        return $found;
    }
    
    /*
     * Отдает закешированый массив
     * 
     * @param array Массив который нужно закешировать.
     * @param array Массив которому нужно сделать хеш.
     */
    protected static function cache_direct(array $array,$hesh = FALSE ){
        if($hesh != FALSE){
            $array_string = serialize($array);
            $hesh = md5(serialize($hesh));
        }else{
            $array_string = serialize($array);
            $hesh = md5($array_string);
        }
        file_put_contents(SYSPATH.'config/'.Direct::$dir.'/'.$hesh.'.cach',$array_string);
    }
    /*
     * Отдает закешированый массив
     * 
     * @param array Массив который нужно вернуть.
     */
    protected static function get_cache($array){
        $array_string = serialize($array);
        $hesh = md5($array_string);
        if($string = @file_get_contents(SYSPATH.'config/'.Direct::$dir.'/'.$hesh.'.cach')){
            return unserialize($string);
        }else{
            return Direct::directory_out($array);
        }
    }
 }
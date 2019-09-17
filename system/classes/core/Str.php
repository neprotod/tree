<?php defined('SYSPATH') OR exit();
/*
 * empty class
 */
class Core_Str{
    static function separator($string,$char = '.',$separator = '/'){
        if($result = str_replace($char, $separator, $string)){
            return $result;
        }
        return FALSE;
    }
    static function __($string, array $values = NULL){

        return empty($values) ? $string : strtr($string, $values);
    }
    
    static function money($price){
        $r = fmod($price, 1);
        if($r == 0){
            $price = $price - $r;
            $price = number_format($price, 0, '.', ' ');
        }else{
            $price = number_format($price, 2, '.', ' ');
        }
        return $price;
    }
    
    static function key_value($fonds = array()){
        if(!empty($fonds) AND is_array($fonds)){
            foreach($fonds as $key => $value){
                if(!is_null($value)){
                    $value = DB::escape($value);
                }else{
                    $value = 'NULL';
                }
                $fond .= "$key = $value,";
            }
            return trim($fond,',');
        }
        return FALSE;
    }
    /*
     * Обрезает строку
     * $int = на сколько обрезать
     * $bool = обрезать до пробельного символа?
     */
    static function crop($string,$int,$bool = TRUE){
        $int = intval($int);
        $char = '';
        $length = UTF8::strlen($string);
        if($length > $int){
            $ofset = UTF8::substr($string,$int);
            preg_match("/(^.[^\W\s]*)(\.{3}|\W|\s)/u",$ofset,$result);
            if(!empty($result)){
                $int += UTF8::strlen($result[1]);
                if($bool === TRUE AND !empty($result[2])){
                    $char = preg_replace("/[^\.]/u", '...', $result[2]);
                }
            }
            $string = UTF8::substr($string,0,$int);
        }
        return $string.$char;
    }
    
    // Определяем кодировку Windows-1251 или UTF-8
    static function charset($string){
        
        $string = (string)$string;
        if(!preg_match('/(.)/u',$string,$char_map)){
            $charset = 'ASCII';
        }else{
            $charset = 'UTF-8';
        }
        
        return $charset;
    }
    
    // Функция конвертации даты и времени с mysql
    static function sql_date($format,$date){
        $date_two = explode(' ',$date);
        if(count($date_two) != 2)
            return FALSE;

        $date = explode('-',$date_two[0]);
        $time = explode(':',$date_two[1]);
        
        for($i = 0;$i<3;$i++){
            if(isset($date[$i]) AND isset($time[$i])){
                $date[$i] = intval($date[$i]);
                $time[$i] = intval($time[$i]);
            }
        }
        return date($format, mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]));
    }
}
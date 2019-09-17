<?php defined('SYSPATH') OR exit();

class Core_Translit{
    // Перевод строки
    static function cyrillicy($stroka){
        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '',  'ы' => 'y',   'ъ' => '',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
           
            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '',  'Ы' => 'Y',   'Ъ' => '',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
     
        );
     
        return strtr($stroka, $converter);
    }
    
    
    static function declension_words($integer, $word) {
        $keisi = array (2, 0, 1, 1, 1, 2); 
        return $word[ ($integer%100 > 4 && $integer %100 < 20) ? 2 : $keisi[min($integer%10, 5)] ]; 
    }
    
    static function url($str) {
        $url = strtolower(Translit::cyrillicy($str));
        $url = trim($url, '/');
        $url = preg_replace("/[^0-9a-zа-я\/-_]/ui", '-', $url);
        $url = preg_replace("/-{2,}|_-{1,}|-{1,}_/", '-', $url);
        $url = trim($url, '-_');
        return $url;
    }
}

<?php

class Dir{

    public $dir;
    
    function __construct(){
        $this->dir = ($_GET['dir'])? utf8_decode($_GET['dir']) : '.';
        Registry::i()->file = array();
        Registry::i()->directory = array();
    }
    
    // Для отображения страници файлов и директорий
    function scan(){
        $direct = dir($this->dir);
        // Сохраняем путь к папке
        $path = $direct->path;
        
        // Отбераем каталоги и файлы
        Registry::i()->size = array();
        while (false !== ($entry = $direct->read())){
            if($entry == '.' OR $entry == '..')
                continue;
            
            if(is_file($direct->path.'/'.$entry)){
                $file = $entry;
                $tmp_file = array();
                //Str::charset($entry);
                if(Str::charset($entry) === 'ASCII'){
                    $tmp_file['name'] = iconv('cp1251','UTF-8',$entry);
                }else{
                    $tmp_file['name'] = $entry;
                }
                $tmp_file['file'] = utf8_encode($file);
                
                Registry::i()->file[] = $tmp_file;
                Registry::i()->size[$tmp_file['name']] = $this->FBytes(filesize($path.'/'.$file));
            }else{
                $tmp_dir = array();
                if(Str::charset($entry) === 'ASCII'){
                    $tmp_dir['name'] = iconv('cp1251','UTF-8',$entry);
                }else{
                    $tmp_dir['name'] = $entry;
                }
                $tmp_dir['dir'] = utf8_encode($entry);
                Registry::i()->directory[] = $tmp_dir;
            }
        }
        asort(Registry::i()->file);
        asort(Registry::i()->directory);
        // Для кнопки назад
        $back = explode('/',$path);
        if(!empty($back) AND is_array($back)){
            array_pop($back);
            $back = implode('/',$back);
            if(empty($back))
                $back = NULL;
        }else{
            $back = NULL;
        }
        $direct->close();
        
        // Массив для передачи в шаблон

            $directories = Registry::i()->directory;
            $files = Registry::i()->file;
            $path = utf8_encode($path);
            $back = utf8_encode($back);

        include Registry::i()->root.'/view/scan.php';
    }
    
    // Вставка фала или директории
    function past(){
        $newDir = $this->dir.'/';
        $oldDir = utf8_decode(key($_SESSION['directory']['save'])).'/';
        foreach($_SESSION['directory']['save'] as $save){
            $save = reset($save);
            $save = utf8_decode($save);
            if(is_file($oldDir.$save))
                $this->past_file($oldDir.$save,$newDir.$save);
            else{
                $scan = $this->deep_scan($oldDir.$save,$newDir.$save);
                $this->past_dir($scan);
            }
        }
        unset($_SESSION['directory']['save']);
    }
    
    // Переименовать файл или директорию
    function rename(){
        $old = utf8_decode($_POST['old']);
        $new = $_POST['new'];
        $dir = utf8_decode($_GET['dir']).'/';
        // Кодировка для windows
        rename($dir.$old,$dir.$new);
    }
    // Создать файл
    function create_file(){
        $file = $_POST['create'];
        $dir = utf8_decode($_GET['dir']).'/';
        fopen($dir.$file,'a');
    }
    // Создать директорию
    function create_dir(){
        $direcorty = $_POST['create'];
        $dir = utf8_decode($_GET['dir']).'/';
        mkdir($dir.$direcorty);
    }
    
    // Простая проверка и вставка файла
    private function past_file($old,$new){
        if(is_file($old))
            copy($old,$new);
    }
    
    // Вставляет директорию на основе скана
    private function past_dir($dirs){
        foreach($dirs as $old => $new){
            if(is_file($old)){
                $this->past_file($old,$new);
            }else{
                if(!is_dir($new)){
                    mkdir($new);
                }
            }
        }
    }
        // Архиватор
    function archivator(){
            set_time_limit(0);
            include Registry::i()->root."/classes/pclzip.lib.php";
            
            $lookup = array("{time}"=>date("Y.m.d"));
            
            $files_dir = utf8_decode(key($_POST['selected']));
            $name_arch_input = Str::__($_POST['archiv_name'],$lookup);

            $files_to_arch = array();
            $chdir = getcwd();
            chdir($files_dir);
            
            foreach(reset($_POST['selected']) as $select){
                // Разкодируем
                $select = utf8_decode($select);
                
                if(empty($name_arch))
                    $name_arch = $select;
                if(is_file($select)){
                    $files_to_arch[$select] = $select;
                }else{
                    $this->deep_scan($select,$select,$files_to_arch);
                }
            }
            
            // Создаем имя
            if(empty($name_arch))
                $name_arch = "_";
            $name_arch .= '.zip';
            $base = pathinfo($name_arch);
            $ext = $base['extension'];
            while(file_exists($name_arch)){
                $new_base = pathinfo($name_arch, PATHINFO_FILENAME);
                if(preg_match('/_([0-9]+)$/', $new_base, $parts))
                    $name_arch = $base['filename'].'_'.($parts[1]+1).'.'.$ext;
                else
                    $name_arch = $base['filename'].'_1.'.$ext;
            }
            
            $archive = new PclZip($name_arch);
            $v_list = $archive->create(implode(',', $files_to_arch));
            
            chdir($chdir);
            if($v_list == 0){
               exit("Error : ".$archive->errorInfo(true));
            }
    }
    // Деархиватор
    function de_archivator(){
        set_time_limit(0);
        include Registry::i()->root."/classes/pclzip.lib.php";
        
        $files_dir = $this->dir;
        
        $chdir = getcwd();
        chdir($files_dir);
        
        foreach(reset($_POST['selected']) as $select){
            $archive = new PclZip(utf8_decode($select));
            if(($list = $archive->listContent()) == 0) {
                echo "<p>Error : ".$archive->errorInfo(true)."</p>";
                continue;
            }
            $archive->extract();
        }
        
        chdir($chdir);
        
    }
    // при большом массиве
    function big_zip(){
        $extension = 'tmp';
        $file_name = $_POST['big_zip']['file'];
        
        if(!$dir = utf8_decode($_GET['dir'])){
            return;
        }
        
        if(isset($_POST['big_zip']['end'])){
            $extension_old = $extension;
            $extension = $_POST['big_zip']['end'];
            $old = $dir.'/'.$file_name.'.'.$extension_old;
            $new = $dir.'/'.$file_name.'.'.$extension;
            rename($old,$new);
            return;
        }
        $file = $dir.'/'.$file_name.'.'.$extension;
        
        if($_POST['big_zip']['content']){
            $handle = fopen($file, 'a');
            fwrite($handle, $_POST['big_zip']['content']);
        }
        exit();
    }
    // Удаляет все файлы и саму директорию 
    function unlink(){
        $dir = utf8_decode(key($_POST['selected'])).'/';
        foreach(reset($_POST['selected']) as $unlink){
            $unlink = utf8_decode($unlink);
            // Кодировка для windows
            if(is_file($dir.$unlink)){
                unlink($dir.$unlink);
            }else{
                $this->unlink_directory($dir.$unlink);
            }
        }
    }
    
    // Для удаления файлов и директорий внутри директории
    private function unlink_directory($dir){
        if ($objs = glob($dir."/*")){
            foreach($objs as $obj) {
                is_dir($obj) ? $this->unlink_directory($obj) : unlink($obj);
            }
        }
        rmdir($dir);

    }
    
    // Вырезает из одного места и вставляет в другое
    function cut(){
        $newDir = $this->dir.'/';
        $oldDir = utf8_decode(key($_SESSION['directory']['save'])).'/';
        foreach($_SESSION['directory']['save'] as $save){
            $save = reset($save);
            $save = utf8_decode($save);    
            rename($oldDir.$save,$newDir.$save);
        }
        unset($_SESSION['directory']['save']);
    }
    
    // Загружает файл из строки
    function upload(){
        $uploads_dir = $this->dir;
        $files = $_POST['_FILES']['file'];
        unset($_POST['_FILES']['file']);
        foreach($files as $name => $file){
            file_put_contents($uploads_dir.'/'.$name,$file);
        }

    }
    
    // Для глубокого скана директорий
    private function deep_scan($old,$new,&$fonds = NULL,$no_root = FALSE){
        if(empty($fonds))
            $fonds = array();
        if($no_root === FALSE)
            $fonds[$old] = $new;

        $scans = scandir($old);
        
        foreach($scans as $fond){
            if($fond == '.' OR $fond == '..')
                continue;
            if(is_file($old.'/'.$fond)){
                $fonds[$old.'/'.$fond] = $new.'/'.$fond;
            }else{
                if($no_root === FALSE)
                    $fonds[$old.'/'.$fond] = $new.'/'.$fond;
                $fonds += $this->deep_scan($old.'/'.$fond,$new.'/'.$fond,$fonds,$no_root);
            }
        }
        return $fonds;
    }
    
    // Переводит байты в мегабайты
    /*
     * @param int/string количество байт
     * @param int сколько цифр после запятой
     * @return преобразованое значение вплоть до терабайта
     */
    function FBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes?log($bytes):0)/log(1024));
        $pow = min($pow, count($units)-1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision).' '.$units[$pow];
    }
    // Конвертирует все папки и файлы в UTF-8
    function convert_utf8() {
        foreach(reset($_POST['selected']) as $select){
            // Разкодируем
            $select = utf8_decode($select);
        
            $cp1251 = $select;
            $utf8 = iconv('cp1251','UTF-8',$select);
            rename($this->dir.'/'.$cp1251,$this->dir.'/'.$utf8);
        }
        
    }
    // Конвертирует все папки и файлы в UTF-8
    function convert_cp1251() {
        foreach(reset($_POST['selected']) as $select){
            // Разкодируем
            $select = utf8_decode($select);
            $utf8 = $select;
            $cp1251 = iconv('UTF-8','cp1251',$select);
            rename($this->dir.'/'.$utf8,$this->dir.'/'.$cp1251);
        }
        
    }
}
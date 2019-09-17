<?php defined('MODPATH') OR exit();

class Image_Module implements I_Module{

    const VERSION = '0.0.1';
    
    private    $allowed_extentions = array('png', 'gif', 'jpg', 'jpeg', 'ico');
    
    function __construct(){}
    
    function index($setting = null){}
    
    public function upload_image($filename, $name, $original = NULL){
        // Имя оригинального файла
        $name = $name;
        $name = $this->correct_filename($name);

        $uploaded_file = $new_name = pathinfo($name, PATHINFO_BASENAME);
        $base = pathinfo($uploaded_file, PATHINFO_FILENAME);
        $ext = pathinfo($uploaded_file, PATHINFO_EXTENSION);
        $category_dir = '';
        $separator = '/';
        if(!empty(Registry::i()->category_image)){
            $category_dir = Registry::i()->category_image.'/';
            $separator = '/';
        }

        if($original === NULL)
            $original = $settings['original'];
            
        $original .= $separator . $category_dir;

        if(in_array(strtolower($ext), $this->allowed_extentions)){
                
            while(file_exists($original.$new_name)){
                $new_base = pathinfo($new_name, PATHINFO_FILENAME);
                if(preg_match('/_([0-9]+)$/', $new_base, $parts))
                    $new_name = $base.'_'.($parts[1]+1).'.'.$ext;
                else
                    $new_name = $base.'_1.'.$ext;
            }
            if(move_uploaded_file($filename, $original.$new_name)){
                return $category_dir.$new_name;
            }
        }

        return false;
    }
    
    private function correct_filename($filename){
         $res = Translit::cyrillicy($filename);
        $res = preg_replace("/[\s]+/ui", '-', $res);
        $res = preg_replace("/.+\//ui", '', $res);
        $res = preg_replace("/\(/ui", '-', $res);
        $res = preg_replace("/[^a-zA-Z0-9\.\/\-\_]+/ui", '', $res);
         $res = strtolower($res);
        return $res;  
    }
    
    function resizeimage($img,$params = array(),$settings = NULL){
        $width = NULL;
        $height = NULL;
        $resizeWidth = NULL;
        $resizeHeight = NULL; 
        $offSetX = NULL;
        $offSetY = NULL;
        $path = NULL;
        $resizeDir = NULL;
        if(empty($settings) OR !is_array($settings)){
            $settings = Registry::i()->settings;
        }
        try{
            if(!is_array($params))
                throw new Exception('Не массив');
            // Заполняем переменные
            foreach($params as $variable => $param){
                switch(strtolower($variable)){
                    case 'width':
                        $width = $param;
                    break;
                    case 'height':
                        $height = $param;
                    break;
                    case 'resizewidth':
                        $resizeWidth = $param;
                    break;
                    case 'resizeheight':
                        $resizeHeight = $param;
                    break;
                    case 'offsetx':
                        $offSetX = $param;
                    break;
                    case 'offsety':
                        $offSetY = $param;
                    break;
                    case 'path':
                        $path = $param;
                    break;
                    case 'resizedir':
                        $resizeDir = $param;
                    break;
                }
            }
            
            if($img === NULL OR $img == ''){
                $img = $settings['no-image'];
                $original = $settings['no-image'];
            }
            elseif($path !== NULL){
                $originalPath = $path;
                $original = $originalPath . '/' . $img;
            }
            else{
                $originalPath = $settings['original'];
                $original = $originalPath.'/'.$img;
            }
            
            if(!is_file($original)){
                $img = $settings['no-image'];
                unset($originalPath);
                $original = $settings['no-image'];
            }else{
                
            }
            if($resizeDir === NULL){
                $resizeDir = $settings['resize'] . '/';
            }else{
                $resizeDir .= '/';
                if(!is_dir($resizeDir)){
                    return FALSE;
                }
                
            }
            
            if(!empty($width) OR !empty($height)){
                
                $expImg = explode('.', $img);
                $imgName = $expImg[0];
                $imgExp = $expImg[1];    

                $imgName = str_replace('/','-', $imgName);

                if(isset($originalPath))
                    $originalPath = str_replace('/','-', $originalPath).'-';

                $widthRes = '-'.$width;
                $heightRes = '-'.$height;
                if(!empty($resizeWidth) OR !empty($resizeHeight)){
                    $resizeRes = '-resize';
                    
                    $resizeRes .= '-' . $resizeWidth . '-' . $resizeHeight;
                }
                if((!empty($offSetX) OR $offSetX === 0) OR (!empty($offSetY) OR $offSetX === 0)){
                    $offSetRes = '-offset';
                    
                    $offSetRes .= '-' . $offSetX . '-' . $offSetY;
                }else{
                    $offSetRes = '';
                }
                $resizeName = $originalPath . $widthRes . $heightRes. $resizeRes . $offSetRes .$imgName. '.'.$imgExp;
                $resizeDir .= $resizeName;
                // подключаем если такой файл есть
                if(is_file($resizeDir)){
                    return '/'.$resizeDir;
                }
                
                
                $imgCore = Image::factory($original);

                // изменяем размер
                $imgCore->resize($width, $height);
                
                // заполняем недостающие значения
                $resizeWidth = empty($resizeWidth)? $imgCore->width : $resizeWidth;
                $resizeHeight = empty($resizeHeight)? $imgCore->height : $resizeHeight;
                
                $imgCore->crop($resizeWidth, $resizeHeight, $offSetX, $offSetY);
                if($imgCore->errorImage !== TRUE){
                    // сохраняем
                    $imgCore->save($resizeDir);
                    // сохраняем в базу данных
                    $this->save_image_db($original, $resizeDir);
                    
                    $fond = '/' . $resizeDir;
                }else{
                    return "data:image/png;base64," . chunk_split(base64_encode($imgCore));
                }
            }else{
                $fond = '/' . $original;
            }

            return $fond;
        }catch(Exception $e){
            //return '/system/error/image/error.png';
            return '';
        }
    }
    
    function get_image($img, $path = NULL){
        if($path === NULL){
            $path = Registry::i()->settings['original'];
        }
        $path = trim($path,'/').'/'.$img;
        
        if(!is_file($path)){
            $path = Registry::i()->settings['no-image'];
        }
        return '/'.$path;
    }
    
    function save_image_db($original,$resize){
    
        $sql = "INSERT IGNORE INTO __resize_image SET original=:original, resize=:resize";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::INSERT, $sql);
        
        //Параметры
        $query->param(':original',$original);
        $query->param(':resize',$resize);
        
        $query->execute();
    }
    
    function get_image_db($original){
        $sql = "SELECT original, resize FROM __resize_image 
                    WHERE original=:original";
            
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        
        //Параметры
        $query->param(':original',$original);
        
        // На выдачу
        $result = $query->execute();
        //Удаляем записи
        if(!empty($result)){
            $sql = "DELETE FROM __resize_image 
                        WHERE original=:original";
            $sql = DB::placehold($sql);
        
            $query = DB::query(Database::DELETE, $sql);
            
            //Параметры
            $query->param(':original',$original);
            
            $query->execute();
            return $result;
        }
        
        return array();
    }
    
    function delete_image($image){
        if(is_file($image)){
            @unlink($image);    
            return TRUE;
        }
        return FALSE;
    }
    
}
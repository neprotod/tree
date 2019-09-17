<?php defined('MODPATH') OR exit();

class Design_Module extends ArrayObject implements I_Module{
    
    const VERSION = '0.0.1';
    
    // Корневой коталог темы
    public $root;
    static $design;
    
    public $error = array();
    
    public $massage = array();
    
    public $tabs;
    
    //Набор пользовательских функций
    public $user;
    
    function __construct(){
        if(Core::$sample == 'Core')
            $this->root = Url::root().'/application/template/'.Registry::i()->settings['theme'];
        else
            $this->root = Url::root().'/design/template/'.Registry::i()->settings['admin_theme'];
        
        $this->user = Model::factory('user','design');
    }
    
    function index($setting = null){}
    
    // Для получения миникартинок
    function light($features = array()){
        if(is_array($features)){
            foreach($features as $feature){
                if($feature['name'] == 'Освещение'){
                    $light = Utf8::strtolower($feature['value']);
                    $option = $feature;
                    break;
                }
            }
        }
        if(isset($light)){    
            $light = explode(',', $light);
            $lignts = array();
            foreach($light as $l){
                $l = trim($l);
                switch($l){
                    case 'солнце':
                        $lig['image'] = "/".$option['img'];
                        $lig['height'] = '28';
                        $lig['width'] = '28';
                        $lig['position_left'] = '0';
                    break;
                    
                    case 'полутень':
                        $lig['image'] = "/".$option['img'];
                        $lig['height'] = '28';
                        $lig['width'] = '26';
                        $lig['position_left'] = '31';
                    break;
                    
                    case 'тень':
                        $lig['image'] = "/".$option['img'];
                        $lig['height'] = '28';
                        $lig['width'] = '19';
                        $lig['position_left'] = '58';
                    break;
                }
                if(!empty($lig)){
                    $lignts[] = $lig;
                    unset($lig);
                }
            }
            return $lignts;
        }else{
            return FALSE;
        }
    }
    
    ////////////////////////
    // Изменение и выдача изображениея
    ///////////////////////
    function resizeimage($img, $width = NULL, $height = NULL, $resizeWidth = NULL, $resizeHeight = NULL, $offSetX = NULL, $offSetY = NULL, $path = NULL, $resizeDir = NULL){
        try{
            if($img === NULL OR $img == ''){
                $img = Registry::i()->settings['no-image'];
                $original = Registry::i()->settings['no-image'];
            }
            elseif($path !== NULL){
                $originalPath = $path;
                $original = $originalPath . '/' . $img;
            }
            else{
                $originalPath = Registry::i()->settings['original'];
                $original = $originalPath.'/'.$img;
            }

            if(!is_file($original)){
                $img = Registry::i()->settings['no-image'];
                unset($originalPath);
                $original = Registry::i()->settings['no-image'];
            }else{
                
            }
            
            if($resizeDir === NULL){
                $resizeDir = Registry::i()->settings['resize'] . '/';
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
    
    /*Работа с категориями*/
    function category_select($categories, $separator = '',$catName = FALSE, $catId = FALSE, $stap = '&nbsp;&nbsp;&nbsp;&nbsp;'){
        if(!is_array($categories))
            $categories = (array)$categories;
        // для результата

        $fond;
        /*
            $catName = FALSE, $catId = FALSE,
        */
        foreach($categories as $category){
        
            $catName = ($catName === TRUE)? '"category_name='.$category['name'].'"' : '' ;
            
            $selected = ($catId == $category['id'])? 'selected="selected"' : '' ;
            
            if(isset($category['id'])){
                $fond .= "<option $catName $selected value='{$category['id']}'>$separator{$category['name']}</option>"; 
            }
            if(isset($category['subcategories'])){
                $fond .= $this->category_select($category['subcategories'],$new .= $stap, $catName, $catId);
                $new = $separator;
            }
        }
        return $fond;
    }
    
    /*Ошибки*/
    function error($errors = NULL,$massage = NULL){
        if(!empty($massage))
            $this->error[$errors] = $massage;
    }
    /*Сообщения*/
    function massage($name = NULL,$massage = NULL){
        if(!empty($massage))
            $this->massage[$name] = $massage;
    }
    
    /*Для выдачи табов*/
    public $start;
    function tabs($value = NULL){
        switch(strtolower($value)){
            case 'start':
                if($this->start !== TRUE){
                    ob_start();
                    $this->start = TRUE;
                }
            break;
            case 'end':
                if($this->start === TRUE){
                    $this->start = FALSE;
                    $this->tabs = ob_get_clean();
                }
            break;
            default:
                if(!empty($this->tabs) AND $this->start === FALSE)
                    return $this->tabs;
                else
                    return '';
        }
    }
}
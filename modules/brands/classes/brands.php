<?php defined('MODPATH') OR exit();

class Brands_Module implements I_Module{

    const VERSION = '0.0.1';
    
    function __construct(){}
    
    function index($setting = null){}
    
    function fetch(){
        
    }
    
    /*
     *
     * Функция возвращает бренд по его id или url
     * (в зависимости от типа аргумента, int - id, string - url)
     * @param $id id или url поста
     *
     */
    
    function get_brand($id){
        if(is_int($id))            
            $filter = "id = '$id'";
        else
            $filter = "url = '$id'";
        $sql = "SELECT id, name, url, meta_title, meta_description, description, image
                        FROM __brands WHERE $filter ORDER BY name LIMIT 1";
        
        $sql = DB::placehold($sql);
        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute();
        return $result[0];
    }
    
    /*
     *
     * Функция возвращает массив брендов, удовлетворяющих фильтру
     * @param $filter
     *
     */
    function get_brands($filter = array()){
        $brands = array();
        $category_id_filter = '';
        
        if(!empty($filter['category_id']))
            $category_id_filter = Str::__("LEFT JOIN __products p ON p.brand_id=b.id LEFT JOIN __products_categories pc ON p.id = pc.products_id WHERE pc.categories_id in(:category_id)", array(':category_id' => implode(',', (array)$filter['category_id'])));
            
            $sql = "SELECT id, name, url, meta_title, meta_description, description, image
                        FROM __brands WHERE $filter ORDER BY name LIMIT 1";
        
        $sql ="SELECT DISTINCT b.id, b.name, b.url, b.meta_title, b.meta_description, b.description, b.image
                                         FROM __brands b $category_id_filter ORDER BY b.name";

        $sql = DB::placehold($sql);
        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute();
        
        return $result;
    }
    
    /*
    *
    * Добавление бренда
    * @param $brand
    *
    */
    public function add_brand($brand){
        $brand = (array)$brand;
        if(empty($brand['url'])){
            $brand['url'] = preg_replace("/[\s]+/ui", '_', $brand['name']);
            $brand['url'] = Translit::url($brand['url']);
        }else{
            $brand['url'] = Translit::url($brand['url']);
        }
        
        $brands = Str::key_value($brand);
        
        $sql = Str::__("INSERT INTO __brands SET :brands",array(':brands'=>$brands));
        $sql = DB::placehold($sql);
            
        $query = DB::query(Database::INSERT, $sql);
        //$query->param(':brands',$brands);
        
        $result = $query->execute();
        
        return $result[0];
    }
    /*
    *
    * Обновление бренда(ов)
    * @param $brand
    *
    */        
    public function update_brand($id, $brand){
        if(!$brands = Str::key_value($brand)){
            return FALSE;
        }
        
        $sql = Str::__("UPDATE __brands SET :brands WHERE id=:id LIMIT 1",array(':brands'=>$brands));
        $sql = DB::placehold($sql);
            
        $query = DB::query(Database::UPDATE, $sql);
        $query->param(':id',$id);
        
        $query->execute();

        return $id;
    }
    /*
    *
    * Удаление бренда
    * @param $id
    *
    */    
    public function delete_brand($id){
        if(!empty($id)){
            
            $this->delete_image($id);
            
            $sql = DB::placehold("DELETE FROM __brands WHERE id=:id LIMIT 1");
            
            $query = DB::query(Database::DELETE, $sql);
            $query->param(':id',$id);
            
            $query->execute();
            
            // Обнавляем товар
            $sql = DB::placehold("UPDATE __products SET brand_id=NULL WHERE brand_id=:brand_id", $id);
            
            $query = DB::query(Database::UPDATE, $sql);
            $query->param(':brand_id',$id);
            
            $query->execute();    
        }
    }
    
    /*
    *
    * Удаление изображения бренда
    * @param $id
    *
    */
    public function delete_image($brand_id){
        $sql = DB::placehold("SELECT image FROM __brands WHERE id=:id");
        
        $query = DB::query(Database::SELECT, $sql);
        $query->param(':id',intval($brand_id));
            
        $result = $query->execute();
        
        $filename = $result[0];
        
        if(!empty($filename)){
            $sql = DB::placehold("UPDATE __brands SET image=NULL WHERE id=:id");
        
            $query = DB::query(Database::UPDATE, $sql);
            $query->param(':id',intval($brand_id));
            
            $query->execute();
            
            // Проверяем, есть ли такой файл
            $sql = DB::placehold("SELECT count(*) as count FROM __brands WHERE image=:image LIMIT 1");
        
            $query = DB::query(Database::SELECT, $sql);
            $query->param(':image',$filename);
            
            $result = $query->execute();
            $count = $result[0];
            if($count == 0){        
                @unlink('/'.trim(Registry::i()->settings['brands_images_dir'],'.').'/'.$filename);        
            }
        }
    }
}
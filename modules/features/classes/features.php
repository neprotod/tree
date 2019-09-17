<?php defined('MODPATH') OR exit();

class Features_Module implements I_Module{

    const VERSION = '0.0.1';
    
    function __construct(){}
    
    function index($setting = null){}
    
    function get_product_options($product_id){
    
        $sql = Str::__("SELECT f.id as feature_id, f.name, po.value, po.product_id, f.img FROM __options po LEFT JOIN __features f ON f.id=po.feature_id
                                        WHERE po.product_id in(:product_id) ORDER BY f.position", array(':product_id' => implode(',', (array)$product_id)));
        
        $sql = DB::placehold($sql);

        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();
        return $result;
    }
    
    
    function get_features($filter = array()){
        $category_id_filter = '';
        $in_filter_filter = '';    
        $id_filter = '';
        
        if(isset($filter['category_id']))
            $category_id_filter = Str::__("AND id in(SELECT feature_id FROM __categories_features AS cf WHERE cf.category_id in(:category_id))", array(':category_id' => implode(',', (array)$filter['category_id'])));
            
        if(isset($filter['in_filter']))
            $in_filter_filter =  Str::__('AND f.in_filter=:in_filter', array(':in_filter' => $filter['in_filter']));
            
        if(!empty($filter['id']))
            $id_filter = Str::__("AND f.id in(:id)", array(':id' => implode(',', (array)$filter['id'])));
        
        $sql = "SELECT f.id, f.name, f.position, f.in_filter FROM __features AS f
                                    WHERE 1
                                    $category_id_filter $in_filter_filter $id_filter ORDER BY f.position";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();
        return $result;
    }
    
    function get_feature($id){
        // Выбираем свойство
        $sql = DB::placehold("SELECT id, name, position, in_filter FROM __features WHERE id=:id LIMIT 1", array(':id'=>$id));
        
        $query = DB::query(Database::SELECT, $sql);
        
        $feature = $query->execute();

        return $feature[0];
    }
    
    function get_options($filter = array()){
        $feature_id_filter = '';
        $product_id_filter = '';
        $category_id_filter = '';
        $visible_filter = '';
        $brand_id_filter = '';
        $features_filter = '';
        
        $group_by = 'GROUP BY po.feature_id, po.value ORDER BY value=0, -value DESC, value';
        
        $count = ', count(po.product_id) as count';
        if(empty($filter['feature_id']) && empty($filter['product_id']))
            return array();
        
        if(isset($filter['feature_id']))
            $group_by = 'GROUP BY feature_id, value';
        
        if(isset($filter['feature_id']))
            $feature_id_filter = Str::__("AND po.feature_id in(:feature_id)", array(':feature_id' => implode(',', (array)$filter['feature_id'])));
        
        if(isset($filter['product_id'])){
            $product_id_filter = Str::__("AND po.product_id in(:product_id)", array(':product_id' => implode(',', (array)$filter['product_id'])));
            
            $group_by = 'ORDER BY po.product_id';
            $count = '';
        }
        
        if(isset($filter['category_id'])){
            $category_id_filter = Str::__("INNER JOIN __products_categories pc ON pc.products_id = p.id AND pc.categories_id IN(:category_id)", array(':category_id' => implode(',', (array)$filter['category_id'])));
            
        }
        if(isset($filter['visible']))
            $visible_filter = Str::__("INNER JOIN __products p ON p.id=po.product_id AND visible=:visible", array(':visible' => intval($filter['visible'])));

        if(isset($filter['brand_id']))
            $brand_id_filter = Str::__("AND po.product_id in(SELECT id FROM __products WHERE brand_id in(:brand_id))", array(':brand_id' => implode(',', (array)$filter['brand_id'])));
        
        if(isset($filter['features']))
            foreach($filter['features'] as $feature=>$value){
                $features_filter .= Str::__("AND (po.feature_id=:feature_id OR po.product_id in (SELECT product_id FROM __options WHERE feature_id=:feature_id AND value=:value ))", array(':feature_id' => $feature, ':value' => $value));
            }

        if(isset($filter['in_filter'])){
            $f_name = 'f.name, f.img,';
            $in_filter_filter_inner =  "INNER JOIN __features f ON f.id = po.feature_id";
            
            $in_filter_filter =  Str::__('AND f.in_filter=:in_filter', array(':in_filter' => $filter['in_filter']));
        }

        $sql = "SELECT po.product_id, $f_name po.feature_id, po.value $count
            FROM __options po
            $in_filter_filter_inner
            $visible_filter
            $category_id_filter
            WHERE 1 $in_filter_filter $feature_id_filter $product_id_filter $brand_id_filter $features_filter $group_by";
        
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();

        return $result;
    }
    
    function add_feature($feature){
        $feature = Str::key_value($feature);
        
        $sql = Str::__("INSERT INTO __features SET :feature",array(':feature' => implode(',', (array)$feature)));
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::INSERT, $sql);
        
        $result = $query->execute();
        
        $id = $result[0];
        /*Возможно сделать работу с позициями*/
        return $id;
    }
    /*Удаление и изменение*/
    function delete_option($product_id, $feature_id){
        //
        $sql = "DELETE FROM __options WHERE product_id=:product_id AND feature_id=:feature_id LIMIT 1";
        $sql = DB::placehold($sql);
            
        $query = DB::query(Database::DELETE, $sql);
        // Параметры
        $query->param(':product_id',intval($product_id));
        $query->param(':feature_id',intval($feature_id));
        
        $result = $query->execute();
        
        return $result[0];
    }
    function delete_feature($id){
        if(!empty($id)){
            // Удаляем характиристику
            $sql = "DELETE FROM __features WHERE id=:ud LIMIT 1";
            $sql = DB::placehold($sql);
                
            $query = DB::query(Database::DELETE, $sql);
            // Параметры
            $query->param(':id',intval($id));
            
            $query->execute();
            
            // Удаляем опции
            $sql = "DELETE FROM __options WHERE feature_id=:feature_id";
            $sql = DB::placehold($sql);
                
            $query = DB::query(Database::DELETE, $sql);
            // Параметры
            $query->param(':feature_id',intval($id));
            
            $query->execute();
            
            // Удаляем характиристики в категориях
            $sql = "DELETE FROM __categories_features WHERE feature_id=:feature_id";
            $sql = DB::placehold($sql);
                
            $query = DB::query(Database::DELETE, $sql);
            // Параметры
            $query->param(':feature_id',intval($id));
            
            $query->execute();
        }
    }
    
    function update_option($product_id, $feature_id, $value){
            /*
                ДЛЯ ПРОВЕРКИ
            */
            /*// Проверяем существует ли
            $sql = "SELECT id FROM __options WHERE product_id=:product_id AND feature_id=:feature_id";
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::INSERT, $sql);
            // Параметры
            $query->param(':product_id',intval($product_id));
            $query->param(':feature_id',intval($feature_id));
            
            $result = $query->execute();*/
        
        if($value != ''){
            $sql = "REPLACE INTO __options SET value=:value, product_id=:product_id, feature_id=:feature_id";
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::INSERT, $sql);
            // Параметры
            $query->param(':value',$value);
            $query->param(':product_id',intval($product_id));
            $query->param(':feature_id',intval($feature_id));
            
            $result = $query->execute();
        }else
            $result = $this->delete_option($product_id, $feature_id);
            
        return $result[0];
    }
    
    function update_feature($id, $feature){
        
        $feature = Str::key_value($feature);
    
        $sql = Str::__("UPDATE __features SET :feature WHERE id in(:id) LIMIT :count", array(':feature' => $feature,':id' => implode(',', (array)$id), ':count'=>count((array)$id)));
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::UPDATE, $sql);
        
        $query->execute();
        
        return $id;
    }
    
    function add_feature_category($id, $category_id){
        $sql = "INSERT IGNORE INTO __categories_features SET feature_id=:id, category_id=:category_id";
        $sql = DB::placehold($sql);
            
        $query = DB::query(Database::INSERT, $sql);
        // Параметры
        $query->param(':id',intval($id));
        $query->param(':category_id',intval($category_id));
            
        $query->execute();
    }
            /**********************/
    public function update_feature_categories($id, $categories){
        $id = intval($id);
        $sql = "DELETE FROM __categories_features WHERE feature_id=:feature_id";
        $sql = DB::placehold($sql);
            
        $query = DB::query(Database::DELETE, $sql);
        // Параметры
        $query->param(':feature_id',intval($id));
            
        $query->execute();
        
        
        if(is_array($categories)){
            $values = array();
            foreach($categories as $category)
                $values[] = "($id , ".intval($category).")";
    
            $sql = Str::__("INSERT INTO __categories_features (feature_id, category_id) VALUES :values", array(':values' => implode(', ', (array)$values)));
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::INSERT, $sql);
            $query->execute();
            
            // Удалим значения из options 
            $sql = Str::__("DELETE o FROM __options o
                                           LEFT JOIN __products_categories pc ON pc.products_id=o.product_id
                                           WHERE o.feature_id=:feature_id AND pc.categories_id not in(:categories)", array(':feature_id'=>intval($id), ':categories' => implode(',', (array)$categories)));
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::DELETE, $sql);
            $query->execute();
            
        }else{
            // Удалим значения из options 
            $sql = "DELETE o FROM __options o WHERE o.feature_id=:feature_id";
            $sql = DB::placehold($sql);
                
            $query = DB::query(Database::DELETE, $sql);
            // Параметры
            $query->param(':feature_id',intval($id));
                
            $query->execute();
        }
    }
    
    function get_feature_categories($id,$reset = TRUE){
        $sql = DB::placehold("SELECT cf.category_id as category_id FROM __categories_features cf
                                        WHERE cf.feature_id = :id", array(':id'=>$id));
        
        $query = DB::query(Database::SELECT, $sql);
            
        $result = $query->execute();
        if($reset AND is_array($result)){
            foreach($result as $key => $r){
                $result[$key] = reset($r);
            }
        }
        return $result;    
    }
}
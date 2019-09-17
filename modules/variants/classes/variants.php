<?php defined('MODPATH') OR exit();

class Variants_Module implements I_Module{

    const VERSION = '0.0.1';
    
    function __construct(){

    }
    
    function index($setting = null){}
    
    
    function get_variants($filter = array()){
        $product_id_filter = '';
        $variant_id_filter = '';
        $instock_filter = '';
        $sort = 'v.position';
        if(!empty($filter['product_id']))
            $product_id_filter = Str::__("AND v.product_id in(:product_id)", array(':product_id' => implode(',', (array)$filter['product_id'])));
        
        if(!empty($filter['id']))
            $variant_id_filter = Str::__("AND v.id IN (:product_id)", array(':product_id' => implode(',', (array)$filter['id'])));

        if(!empty($filter['in_stock']) && $filter['in_stock']){
            $variant_id_filter = 'AND (v.stock>0 OR v.stock IS NULL)';
        }
            
        if(!empty($filter['sort'])){
            switch(strtolower($filter['sort'])){
                case 'price':
                    $sort = 'v.price';
                break;
                default:
                    $sort = 'v.position';
            }
        }
        
        if(!$product_id_filter && !$variant_id_filter)
            return array();
        $str = "SELECT v.id, v.product_id , v.price, NULLIF(v.compare_price, 0) as compare_price, v.sku, IFNULL(v.stock, :stock) as stock, (v.stock IS NULL) as infinity, v.name, v.position
                FROM __variants AS v
                WHERE 
                1
                $product_id_filter          
                $variant_id_filter   
                ORDER BY $sort      
                ";
        $str = Str::__($str, array(':stock' => Registry::i()->settings['max_order_amount']));
        
        $sql = DB::placehold($str);
        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute();
        
        return $result;
    }
    
    function get_variant($id){
        if(empty($id))
            return false;
        if(empty(Registry::i()->settings['max_order_amount']))
            Registry::i()->settings['max_order_amount'] = 'NULL';
        
        $str = "SELECT v.id, v.product_id , v.price, NULLIF(v.compare_price, 0) as compare_price, v.sku, IFNULL(v.stock, :stock) as stock, (v.stock IS NULL) as infinity, v.name
                    FROM __variants v WHERE id=:id
                    LIMIT 1";

        $str = Str::__($str, array(':stock' => Registry::i()->settings['max_order_amount'], ':id'=>$id));
        
        $sql = DB::placehold($str);
        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute();
        return $result[0];
    }
    
    function update_variant($id, $variant){

        $variant = Str::key_value($variant);

        $sql = Str::__("UPDATE __variants SET :variant WHERE id=:id LIMIT 1",array(':variant' =>$variant, ':id' => intval($id)));
        
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::UPDATE, $sql);
        $query->execute();
        
        return $id;
    }
    
    function add_variant($variant){
        $variant = Str::key_value($variant);
        
        $sql = Str::__("INSERT INTO __variants SET :variant",array(':variant' =>$variant));
        
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::INSERT, $sql);
        $result = $query->execute();
        
        return $result[0];
    }
    
    function delete_variant($id){
        if(!empty($id)){
            // Удаляем вариант
            $sql = Str::__("DELETE FROM __variants WHERE id = :id LIMIT 1",array(':id' =>intval($id)));
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::DELETE, $sql);
            $query->execute();
            
            // Удаляем вариант из покупок
            $sql = Str::__("UPDATE __purchases SET variant_id=NULL WHERE variant_id=:id",array(':id' =>intval($id)));
            $sql = DB::placehold($sql);
            
            $query = DB::query(Database::UPDATE, $sql);
            $query->execute();
        }
    }
    
}
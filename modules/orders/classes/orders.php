<?php defined('MODPATH') OR exit();

class Orders_Module implements I_Module{

    const VERSION = '0.0.1';
    
    function __construct(){
        $this->variants = Module::factory('variants', TRUE);
        $this->products = Module::factory('products', TRUE);
    }
    
    function index($setting = null){}
    
    function fetch(){
        $order = $this->get_order(Registry::i()->page_url);
        return Template::factory(Registry::i()->settings['theme'],'content_orders_order',array("order"=>$order));
    }
    
    public function get_order($id){
        if(is_int($id))
            $where = Str::__(" WHERE o.id=:id ",array(':id' =>intval($id)));
        else
            $where = Str::__(" WHERE o.url=':url' ",array(':url' =>$id));
        
        $sql = "SELECT  o.id, o.delivery_id, o.delivery_price, o.separate_delivery,
                                        o.payment_method_id, o.paid, o.payment_date, o.closed, o.discount,
                                        o.date, o.customer_id, o.name, o.address, o.phone, o.email, o.comment, o.status,
                                        o.url, o.total_price, o.note, o.ip
                                        FROM __orders o $where LIMIT 1";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        
        
        if($result = $query->execute())
            return reset($result);
        else
            return false; 
    }
    
    function add_order($order){
    
        $order['url'] = md5(uniqid('', true));
        
        $set_curr_date = '';
        if(empty($order['date']))
            $set_curr_date = ', date=now()';
            
        $orders = Str::key_value($order);
        
        $sql = Str::__("INSERT INTO __orders SET :orders $set_curr_date",array(':orders' =>$orders));
        
        $sql = DB::placehold($sql);

        $query = DB::query(Database::INSERT, $sql);
        $result = $query->execute();

        return $result[0];
    }
    
    function count_orders($filter = array()){
        /*$keyword_filter = '';    */
        $label_filter = '';    
        $status_filter = '';
        $user_filter = '';    
        
        if(isset($filter['status']))
            $status_filter = DB::placehold('AND o.status = :status', array(':status'=>intval($filter['status'])));
        
        if(isset($filter['customer_id']))
            $user_filter = DB::placehold('AND o.customer_id = :customer_id', array(':customer_id'=>intval($filter['customer_id'])));

        if(isset($filter['label']))
            $label_filter = DB::placehold('AND ol.label_id = :label_id', array(':label_id'=>$filter['label']) );
        
        /*if(!empty($filter['keyword']))
        {
            $keywords = explode(' ', $filter['keyword']);
            foreach($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (o.name LIKE "%'.$this->db->escape(trim($keyword)).'%" OR REPLACE(o.phone, "-", "")  LIKE "%'.$this->db->escape(str_replace('-', '', trim($keyword))).'%" OR o.address LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
        }*/
        
        // Выбираем заказы
        $sql = DB::placehold("SELECT COUNT(DISTINCT id) as count
                                    FROM __orders AS o 
                                    LEFT JOIN __orders_labels AS ol ON o.id=ol.order_id 
                                    WHERE 1
                                    $status_filter $user_filter $label_filter");
                                    
        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();
        return (isset($result[0]))? reset($result[0]) : NULL;
    }
    
    public function update_order($id, $order){
        $sql = DB::placehold("UPDATE __orders SET :order, modified=now() WHERE id=:id LIMIT 1", array(':order'=>Str::key_value($order),':id'=>intval($id)));
        
        $query = DB::query(Database::UPDATE, $sql);
        
        $result = $query->execute();
        
        return $id;
    }
    
    public function delete_order($id){
        if(!empty($id)){
            $sql = DB::placehold("DELETE FROM __purchases WHERE order_id=:order_id", array(':order_id'=>$id));
            
            $query = DB::query(Database::DELETE, $sql);
            
            $query->execute();
            // удаляем заказ
            $sql = $this->db->placehold("DELETE FROM __orders WHERE id=:id LIMIT 1", array(':id'=>$id));
            
            $query = DB::query(Database::DELETE, $sql);
            
            $query->execute();
        }
    }
    
    function get_orders($filter = array()){
        // По умолчанию
        $limit = 100;
        $page = 1;
        $keyword_filter = ''; // OFF
        $label_filter = '';    
        $status_filter = '';
        $user_filter = '';    
        $modified_from_filter = '';    
        $id_filter = '';
        
        if(isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if(isset($filter['page']))
            $page = max(1, intval($filter['page']));

        $sql_limit = DB::placehold(' LIMIT :limit1, :limit2 ', array(':limit1'=>($page-1)*$limit,':limit2'=>$limit));
        
            
        if(isset($filter['status']))
            $status_filter = DB::placehold('AND o.status = :status', array(':status'=>intval($filter['status'])));
        
        if(isset($filter['id']))
            $id_filter = DB::placehold('AND o.id in(:id)', array(':id'=>implode(',',(array)$filter['id'])));
        
        if(isset($filter['customer_id']))
            $user_filter = DB::placehold('AND o.customer_id = :customer_id', array(':customer_id'=>intval($filter['customer_id'])));
        
        if(isset($filter['modified_from']))
            $modified_from_filter = DB::placehold('AND o.modified > :modified', array(':modified'=>$filter['modified_from']));
        
        if(isset($filter['label']))
            $label_filter = DB::placehold('AND ol.label_id = :label_id', array(':label_id'=>$filter['label']));
        
        /*if(!empty($filter['keyword']))
        {
            $keywords = explode(' ', $filter['keyword']);
            foreach($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (o.name LIKE "%'.$this->db->escape(trim($keyword)).'%" OR REPLACE(o.phone, "-", "")  LIKE "%'.$this->db->escape(str_replace('-', '', trim($keyword))).'%" OR o.address LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
        }*/
        
        // Выбираем заказы
        $sql = DB::placehold("SELECT o.id, o.delivery_id, o.delivery_price, o.separate_delivery,
                                        o.payment_method_id, o.paid, o.payment_date, o.closed, o.discount,
                                        o.date, o.customer_id, o.name, o.address, o.phone, o.email, o.comment, o.status,
                                        o.url, o.total_price, o.note
                                    FROM __orders AS o 
                                    LEFT JOIN __orders_labels AS ol ON o.id=ol.order_id 
                                    WHERE 1
                                    $id_filter $status_filter $user_filter $keyword_filter $label_filter $modified_from_filter GROUP BY o.id ORDER BY status, id DESC $sql_limit", "%Y-%m-%d");
        
        $query = DB::query(Database::SELECT, $sql);

        $orders = array();
        foreach($query->execute() as $order)
            $orders[$order['id']] = $order;
        return $orders;
    }
    
    public function add_purchase($purchase){

        if(!empty($purchase['variant_id'])){
            $variant = $this->variants->get_variant($purchase['variant_id']);
            if(empty($variant))
                return false;
            $product = $this->products->get_product(intval($variant['product_id']));
            if(empty($product))
                return false;
        }
                
        $order = $this->get_order(intval($purchase['order_id']));
        if(empty($order))
            return false;

        if(!isset($purchase['product_id']) && isset($variant))
            $purchase['product_id'] = $variant['product_id'];
                
        if(!isset($purchase['product_name'])  && !empty($product))
            $purchase['product_name'] = $product['name'];
            
        if(!isset($purchase['sku']) && !empty($variant))
            $purchase['sku'] = $variant['sku'];
            
        if(!isset($purchase['variant_name']) && !empty($variant))
            $purchase['variant_name'] = $variant['name'];
            
        if(!isset($purchase['price']) && !empty($variant))
            $purchase['price'] = $variant['price'];
            
        if(!isset($purchase['amount']))
            $purchase['amount'] = 1;
        
        // Если заказ закрыт, нужно обновить склад при добавлении покупки
        if($order['closed'] && !empty($purchase['amount']) && !empty($variant['id'])){
            $stock_diff = $purchase['amount'];
            
            $sql = Str::__("UPDATE __variants SET stock=stock-:stock WHERE id=:id AND stock IS NOT NULL LIMIT 1",array(':stock' =>$stock_diff,':id' =>$variant['id']));
            
            $sql = DB::placehold($sql);
        
            $query = DB::query(Database::UPDATE, $sql);
            $query->execute();
        }

        $purchase = Str::key_value($purchase);
        $sql = Str::__("INSERT INTO __purchases SET :purchase",array(':purchase' =>$purchase));
        
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::INSERT, $sql);
        $result = $query->execute();

        $purchase_id = $result[0];
        
        $this->update_total_price($order['id']);

        return $purchase_id;
    }
    
    private function update_total_price($order_id){
        $order = $this->get_order(intval($order_id));
        if(empty($order))
            return false;
        
        $sql = Str::__("UPDATE __orders o SET o.total_price=IFNULL((SELECT SUM(p.price*p.amount)*(100-o.discount)/100 FROM __purchases p WHERE p.order_id=o.id), 0)+o.delivery_price*(1-o.separate_delivery), modified=NOW() WHERE o.id=:id LIMIT 1",array(':id' =>$order['id']));
        $sql = DB::placehold($sql);

        $query = DB::query(Database::UPDATE, $sql);
        
        $result = $query->execute();
        
        return $result[0];
    }
    
    public function get_purchase($id){
        $sql = Str::__("SELECT * FROM __purchases WHERE id=:id LIMIT 1",array(':id' =>$id));
        $sql = DB::placehold($sql);

        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();
        
        return $result[0];
    }
    
    public function get_purchases($filter = array()){
        $order_id_filter = '';
        if(!empty($filter['order_id']))
            $order_id_filter = Str::__("AND order_id in(:order_id)",array(':order_id' =>implode(',',(array)$filter['order_id'])));

        $sql = Str::__("SELECT * FROM __purchases WHERE 1 $order_id_filter ORDER BY id");
        $sql = DB::placehold($sql);

        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();
        
        return $result;
    }
    
    public function delete_purchase($id){
        $purchase = $this->get_purchase($id);
        if(!$purchase)
            return false;
            
        $order = $this->get_order(intval($purchase['order_id']));
        if(!$order)
            return false;

        // Если заказ закрыт, нужно обновить склад при изменении покупки
        if($order['closed'] && !empty($purchase['amount'])){
            $stock_diff = $purchase['amount'];
            $sql = DB::placehold("UPDATE __variants SET stock=stock+:stock WHERE id=:id AND stock IS NOT NULL LIMIT 1", array(':stock'=>$stock_diff,':id'=>$purchase['variant_id']));
            
            $query = DB::query(Database::UPDATE, $sql);
        
            $query->execute();
        }
        
        $sql = DB::placehold("DELETE FROM __purchases WHERE id=:id LIMIT 1", array(':id'=>intval($id)));
        
        $query = DB::query(Database::DELETE, $sql);
        
        $query->execute();
        
        $this->update_total_price($order['id']);                
        return true;
    }
    
    public function update_purchase($id, $purchase){    
        $old_purchase = $this->get_purchase($id);
        if(!$old_purchase)
            return false;
            
        $order = $this->get_order(intval($old_purchase['order_id']));
        if(!$order)
            return false;
            
        // Если заказ закрыт, нужно обновить склад при изменении покупки
        if($order['closed'] && !empty($purchase['amount'])){
            if($old_purchase['variant_id'] != $purchase['variant_id']){
                if(!empty($old_purchase['variant_id'])){
                    $sql = DB::placehold("UPDATE __variants SET stock=stock+:stock WHERE id=:id AND stock IS NOT NULL LIMIT 1", array(':stock'=>$purchase['amount'],':id'=>$purchase['variant_id']));
                    
                    $query = DB::query(Database::UPDATE, $sql);
        
                    $query->execute();
                }
                if(!empty($purchase['variant_id'])){
                    $sql = DB::placehold("UPDATE __variants SET stock=stock-:stock WHERE id=:id AND stock IS NOT NULL LIMIT 1", array(':stock'=>$purchase['amount'],':id'=>$purchase['variant_id']));
                    
                    $query = DB::query(Database::UPDATE, $sql);
        
                    $query->execute();
                }
            }
            elseif(!empty($purchase['variant_id'])){
                $sql = DB::placehold("UPDATE __variants SET stock=stock+(:stock) WHERE id=:id AND stock IS NOT NULL LIMIT 1", array(':stock'=>$old_purchase['amount'] - $purchase['amount'],':id'=>$purchase['variant_id']));
                
                $query = DB::query(Database::UPDATE, $sql);
        
                $query->execute();
            }
        }
        
        $sql = DB::placehold("UPDATE __purchases SET :purchases WHERE id=:id LIMIT 1", array(':purchases'=>Str::key_value($purchase),':id'=>intval($id)));
        
        $query = DB::query(Database::UPDATE, $sql);
        
        $query->execute();
        
        $this->update_total_price($order['id']);        
        return $id;
    }
    
    public function get_label($id){
        $sql = DB::placehold("SELECT * FROM __labels WHERE id=:id LIMIT 1", array(':id'=>intval($id)));
        
        $query = DB::query(Database::SELECT, $sql);
            
        $result = $query->execute();
        
        return reset($result);
    }

    public function get_labels(){
        $sql = DB::placehold("SELECT * FROM __labels ORDER BY position");
        
        $query = DB::query(Database::SELECT, $sql);
            
        $result = $query->execute();
        
        return $result;
    }
    
    /*
    *
    * Создание метки заказов
    * @param $label
    *
    */    
    public function add_label($label){
        $sql = DB::placehold('INSERT INTO __labels SET :labels', array(':labels'=>Str::key_value($label)));
        
        $query = DB::query(Database::INSERT, $sql);
        
        if(!$result = $query->execute())
            return false;
        
        $id = $result[0];
        
        $sql = DB::placehold('UPDATE __labels SET position=id WHERE id=:id', array(':id'=>$id));
        
        $query = DB::query(Database::UPDATE, $sql);
        
        $query->execute();
        
        return $id;
    }
    
    /*
    *
    * Обновить метку
    * @param $id, $label
    *
    */    
    public function update_label($id, $label){
        $sql = DB::placehold("UPDATE __labels SET :labels WHERE id in(:id) LIMIT :limit", array(':labels'=>Str::key_value($label),':id'=>implode(',',(array)$id),':limit'=>count((array)$id)));
        
        $query = DB::query(Database::UPDATE, $sql);
        
        $query->execute();
        
        return $id;
    }
    
    /*
    *
    * Удалить метку
    * @param $id
    *
    */    
    public function delete_label($id){
        if(!empty($id)){
            $sql = DB::placehold("DELETE FROM __orders_labels WHERE label_id=:label_id", array(':label_id'=>intval($id)));
            $query = DB::query(Database::DELETE, $sql);
            if($result = $query->execute()){
                $sql = DB::placehold("DELETE FROM __labels WHERE id=:id LIMIT 1", array(':id'=>intval($id)));
                $query = DB::query(Database::DELETE, $sql);
                return $query->execute();
            }else{
                return false;
            }
        }
    }
    
    function get_order_labels($order_id = array()){
        if(empty($order_id))
            return array();

        $label_id_filter = DB::placehold('AND order_id in(:order_id)', array(':order_id'=>implode(',',(array)$order_id)));
                
        $sql = DB::placehold("SELECT ol.order_id, l.id, l.name, l.color, l.position
                    FROM __labels l LEFT JOIN __orders_labels ol ON ol.label_id = l.id
                    WHERE 
                    1
                    $label_id_filter   
                    ORDER BY position       
                    ");
        
        $query = DB::query(Database::SELECT, $sql);
        return $query->execute();
    }
    public function update_order_labels($id, $labels_ids){
        $labels_ids = (array)$labels_ids;
        $sql = DB::placehold("DELETE FROM __orders_labels WHERE order_id=:order_id", array(':order_id'=>intval($id)));
        
        $query = DB::query(Database::DELETE, $sql);
        
        $query->execute();
        
        if(is_array($labels_ids))
        foreach($labels_ids as $l_id){
            $sql = DB::placehold("INSERT INTO __orders_labels SET order_id=:order_id, label_id=:label_id", array(':order_id'=>intval($id),':label_id'=>$l_id));
            
            $query = DB::query(Database::INSERT, $sql);
            
            $query->execute();
        }
    }
    
    public function add_order_labels($id, $labels_ids){
        $labels_ids = (array)$labels_ids;
        if(is_array($labels_ids))
        foreach($labels_ids as $l_id){
            $sql = DB::placehold("INSERT IGNORE INTO __orders_labels SET order_id=:order_id, label_id=:label_id", array(':order_id'=>intval($id),':label_id'=>$l_id));
            
            $query = DB::query(Database::INSERT, $sql);
            
            $query->execute();
        }
    }
    
    public function delete_order_labels($id, $labels_ids){
        $labels_ids = (array)$labels_ids;
        if(is_array($labels_ids))
        foreach($labels_ids as $l_id){
            $sql = DB::placehold("DELETE FROM __orders_labels WHERE order_id=:order_id AND label_id=:label_id", array(':order_id'=>intval($id),':label_id'=>$l_id));
            
            $query = DB::query(Database::DELETE, $sql);
            
            $query->execute();
        }
    }
    
    
    public function close($order_id){
        $order = $this->get_order(intval($order_id));
        if(empty($order))
            return false;
        
        if(!$order['closed']){
            $variants_amounts = array();
            $purchases = $this->get_purchases(array('order_id'=>$order['id']));
            foreach($purchases as $purchase){
                if(isset($variants_amounts[$purchase['variant_id']]))
                    $variants_amounts[$purchase['variant_id']] += $purchase['amount'];
                else
                    $variants_amounts[$purchase['variant_id']] = $purchase['amount'];
            }

            foreach($variants_amounts as $id=>$amount){
                $variant = $this->variants->get_variant($id);
                if(empty($variant) || ($variant['stock']<$amount))
                    return false;
            }
            foreach($purchases as $purchase){    
                $variant = $this->variants->get_variant($purchase['variant_id']);
                if(!$variant['infinity']){
                    $new_stock = $variant['stock']-$purchase['amount'];
                    $this->variants->update_variant($variant['id'], array('stock'=>$new_stock));
                }
            }                
            $sql = DB::placehold("UPDATE __orders SET closed=1, modified=NOW() WHERE id=:id LIMIT 1", array(':id'=>$order['id']));
            
            $query = DB::query(Database::UPDATE, $sql);
        
            $query->execute();
        }
        return $order['id'];
    }
    
    public function open($order_id){
        $order = $this->get_order(intval($order_id));
        if(empty($order))
            return false;
        
        if($order['closed']){
            $purchases = $this->get_purchases(array('order_id'=>$order['id']));
            foreach($purchases as $purchase){
                $variant = $this->variants->get_variant($purchase['variant_id']);                
                if($variant && !$variant['infinity']){
                    $new_stock = $variant['stock']+$purchase['amount'];
                    $this->variants->update_variant($variant['id'], array('stock'=>$new_stock));
                }
            }                
            $sql = DB::placehold("UPDATE __orders SET closed=0, modified=NOW() WHERE id=:id LIMIT 1", array(':id'=>$order['id']));
            
            $query = DB::query(Database::UPDATE, $sql);
        
            $query->execute();
        }
        return $order['id'];
    }
    
    public function pay($order_id){
        $order = $this->get_order(intval($order_id));
        if(empty($order))
            return false;
        
        if(!$this->close($order['id'])){
            return false;
        }
        $sql = DB::placehold("UPDATE __orders SET payment_status=1, payment_date=NOW(), modified=NOW() WHERE id=:id LIMIT 1", array(':id'=>$order['id']));
        
        $query = DB::query(Database::UPDATE, $sql);
        
        $query->execute();
        
        return $order['id'];
    }
    
    public function get_next_order($id, $status = null){
        $f = '';
        if($status!==null AND $status!== '')
            $f = DB::placehold("AND status=':status'", array(':status'=>$status));
        
        $sql = DB::placehold("SELECT MIN(id) as id FROM __orders WHERE id>:id $f LIMIT 1", array(':id'=>$id));
        
        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();
        $next_id = (isset($result[0]))? reset($result[0]) : NULL;
        if($next_id)
            return $this->get_order(intval($next_id));
        else
            return false; 
    }
    
    public function get_prev_order($id, $status = null){
        $f = '';
        if($status!==null AND $status!== '')
            $f = DB::placehold('AND status=:status', array(':status'=>$status));
        $sql = DB::placehold("SELECT MAX(id) as id FROM __orders WHERE id<:id $f LIMIT 1", array(':id'=>$id));
        
        $query = DB::query(Database::SELECT, $sql);
        
        $result = $query->execute();
        
        $prev_id = (isset($result[0]))? reset($result[0]) : NULL;
        if($prev_id)
            return $this->get_order(intval($prev_id));
        else
            return false; 
    }
}
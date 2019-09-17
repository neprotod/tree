<?php defined('MODPATH') OR exit();

class Model_Mail_CART{

    function __construct(){
        $this->variants = Module::factory('variants', TRUE);
        $this->products = Module::factory('products', TRUE);
        $this->orders = Module::factory('orders', TRUE);
        $this->mail = Module::factory('mail', TRUE);
    }
    
    function email_order_user($order_id){
        if(!($order = $this->orders->get_order(intval($order_id))) || empty($order['email']))
            return false;
        
        $purchases = $this->orders->get_purchases(array('order_id'=>$order['id']));
        //$this->design->assign('purchases', $purchases);            

        $products_ids = array();
        $variants_ids = array();
        foreach($purchases as $purchase){
            $products_ids[] = $purchase['product_id'];
            $variants_ids[] = $purchase['variant_id'];
        }
        
        $products = array();
        foreach($this->products->get_products(array('id'=>$products_ids)) as $p)
            $products[$p['id']] = $p;
            
        $images = $this->products->get_images(array('product_id'=>$products_ids));
        foreach($images as $image)
            $products[$image['product_id']]['images'][] = $image;
        
        $variants = array();
        foreach($this->variants->get_variants(array('id'=>$variants_ids)) as $v){
            $variants[$v['id']] = $v;
            $products[$v['product_id']]['variants'][] = $v;
        }

        foreach($purchases as &$purchase){
            if(!empty($products[$purchase['product_id']]))
                $purchase['product'] = $products[$purchase['product_id']];
            if(!empty($variants[$purchase['variant_id']]))
                $purchase['variant'] = $variants[$purchase['variant_id']];
        }
        // отпровляем письмо.
        $massage = View::factory('mail_customers','orders',array('purchases'=>$purchases,'order'=>$order));
        
        $this->mail->email($order['email'], 'Ваша заявка №'.$order['id'].' принята',$massage, Registry::i()->settings['order_email']);
    }
    
    function email_order_admin($order_id){
        if(!($order = $this->orders->get_order(intval($order_id))))
            return false;
        
        $purchases = $this->orders->get_purchases(array('order_id'=>$order['id']));
        //$this->design->assign('purchases', $purchases);            

        $products_ids = array();
        $variants_ids = array();
        foreach($purchases as $purchase){
            $products_ids[] = $purchase['product_id'];
            $variants_ids[] = $purchase['variant_id'];
        }
        
        $products = array();
        foreach($this->products->get_products(array('id'=>$products_ids)) as $p)
            $products[$p['id']] = $p;
            
        $images = $this->products->get_images(array('product_id'=>$products_ids));
        foreach($images as $image)
            $products[$image['product_id']]['images'][] = $image;
        
        $variants = array();
        foreach($this->variants->get_variants(array('id'=>$variants_ids)) as $v){
            $variants[$v['id']] = $v;
            $products[$v['product_id']]['variants'][] = $v;
        }

        foreach($purchases as &$purchase){
            if(!empty($products[$purchase['product_id']]))
                $purchase['product'] = $products[$purchase['product_id']];
            if(!empty($variants[$purchase['variant_id']]))
                $purchase['variant'] = $variants[$purchase['variant_id']];
        }
        // отпровляем письмо.
        $massage = View::factory('mail_admin','orders',array('purchases'=>$purchases,'order'=>$order));
        
        $cistomer_email = (!empty($order['email']))? $order['email'] : '' ;
        
        $this->mail->email(Registry::i()->settings['order_email'], 'Заказ №'.$order['id'].' с '.Registry::i()->settings['site_name'],$massage, $order['email']);
    }
    
}
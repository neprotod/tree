<?php

class Order_Admin{

    function index(){}
    
    function __construct(){
        $this->orders = Module::factory('orders',TRUE);
        $this->products = Module::factory('products',TRUE);
        $this->variants = Module::factory('variants',TRUE);
    }
    
    function fetch(){

        $order = array();
        if(Request::method('post')){
            $order['id'] = Request::post('id', 'integer');
            $order['name'] = Request::post('name');
            $order['email'] = Request::post('email');
            $order['phone'] = Request::post('phone');
            $order['address'] = Request::post('address');
            $order['comment'] = Request::post('comment');
            $order['note'] = Request::post('note');
            $order['discount'] = Request::post('discount', 'floatr');
            $order['delivery_id'] = Request::param(Request::post('delivery_id', 'integer'),NULL,1);
            $order['delivery_price'] = Request::param(Request::post('delivery_price', 'float'),NULL,0);
            $order['payment_method_id'] = Request::post('payment_method_id', 'integer');
            $order['paid'] = Request::post('paid', 'integer');
            $order['customer_id'] = Request::post('customer_id', 'integer');
            $order['separate_delivery'] = Request::post('separate_delivery', 'integer');
            
             if(!$order_labels = Request::post('order_labels'))
                 $order_labels = array();

            if(empty($order['id'])){
                  $order['id'] = $this->orders->add_order($order);
                Request::$design->massage('message_success','Заказ добавлен');
              }else{
                $this->orders->update_order($order['id'], $order);
                Request::$design->massage('message_success','Заказ обнавлен');
            }    

            $this->orders->update_order_labels($order['id'], $order_labels);
            
            if($order['id']){
                // Покупки
                $purchases = array();
                if(Request::post('purchases')){
                    foreach(Request::post('purchases') as $n=>$va) foreach($va as $i=>$v){
                        if(empty($purchases[$i]))
                            $purchases[$i] = array();
                        $purchases[$i][$n] = $v;
                    }
                }
                
                $posted_purchases_ids = array();
                
                foreach($purchases as $purchase){

                    $variant = $this->variants->get_variant(intval($purchase['variant_id']));
                    
                    
                    if(!empty($purchase['id'])){
                        if(!empty($variant)){
                            $this->orders->update_purchase($purchase['id'], array('variant_id'=>$purchase['variant_id'], 'variant_name'=>$variant['name'], 'price'=>$purchase['price'], 'amount'=>$purchase['amount']));
                        }else{
                            $this->orders->update_purchase($purchase['id'], array('price'=>$purchase['price'], 'amount'=>$purchase['amount']));
                        }
                    }else{
                        $purchase['id'] = $this->orders->add_purchase(array('order_id'=>$order['id'], 'variant_id'=>$purchase['variant_id'], 'variant_name'=>$variant['name'], 'price'=>$purchase['price'], 'amount'=>$purchase['amount']));
                    }

                    $posted_purchases_ids[] = $purchase['id'];            
                }

                // Удалить непереданные товары
                foreach($this->orders->get_purchases(array('order_id'=>$order['id'])) as $p)
                    if(!in_array($p['id'], $posted_purchases_ids))
                        $this->orders->delete_purchase($p['id']);
                    
                // Принять?
                if(Request::post('status_new'))
                    $new_status = 0;
                elseif(Request::post('status_accept'))
                    $new_status = 1;
                elseif(Request::post('status_done'))
                    $new_status = 2;
                elseif(Request::post('status_deleted'))
                    $new_status = 3;
                else
                    $new_status = Request::post('status', 'string');
    
                if($new_status == 0){
                    if(!$this->orders->open(intval($order['id'])))
                        Request::$design->error('error_name','Ошибка открытия');
                    else
                        $this->orders->update_order($order['id'], array('status'=>0));
                }
                elseif($new_status == 1){
                    if(!$this->orders->close(intval($order['id'])))
                        Request::$design->error('error_name','Ошибка закрытия');
                    else
                        $this->orders->update_order($order['id'], array('status'=>1));
                }
                elseif($new_status == 2){
                    if(!$this->orders->close(intval($order['id'])))
                        Request::$design->error('error_name','Ошибка закрытия');
                    else
                        $this->orders->update_order($order['id'], array('status'=>2));
                }
                elseif($new_status == 3){
                    if(!$this->orders->open(intval($order['id'])))
                        Request::$design->error('error_name','Ошибка открытия');
                    else
                        $this->orders->update_order($order['id'], array('status'=>3));
                    header('Location: '.Request::get('return'));
                }
                $order = $this->orders->get_order($order['id']);
    
                // Отправляем письмо пользователю
                /*if(Request::post('notify_user'))
                    $this->notify->email_order_user($order['id']);*/
            }

        }else{
            $order['id'] = Request::get('id', 'integer');
            $order = $this->orders->get_order(intval($order['id']));
            // Метки заказа
            $order_labels = array();
            if(isset($order['id']))
            foreach($this->orders->get_order_labels($order['id']) as $ol)
                $order_labels[] = $ol['id'];            
        }


        $subtotal = 0;
        $purchases_count = 0;
        if($order && $purchases = $this->orders->get_purchases(array('order_id'=>$order['id']))){
            // Покупки
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
            foreach($this->variants->get_variants(array('product_id'=>$products_ids)) as $v)
                $variants[$v['id']] = $v;
            
            foreach($variants as $variant)
                if(!empty($products[$variant['product_id']]))
                    $products[$variant['product_id']]['variants'][] = $variant;
                
            
            foreach($purchases as &$purchase){
                if(!empty($products[$purchase['product_id']]))
                    $purchase['product'] = $products[$purchase['product_id']];
                if(!empty($variants[$purchase['variant_id']]))
                    $purchase['variant'] = $variants[$purchase['variant_id']];
                $subtotal += $purchase['price']*$purchase['amount'];
                $purchases_count += $purchase['amount'];                
            }            
        }else{
            $purchases = array();
        }
        
        // Если новый заказ и передали get параметры
        if(empty($order['id'])){
            $order = array();
            if(empty($order['phone']))
                $order['phone'] = Request::get('phone', 'string');
            if(empty($order['name']))
                $order['name'] = Request::get('name', 'string');
            if(empty($order['address']))
                $order['address'] = Request::get('address', 'string');
            if(empty($order['email']))
                $order['email'] = Request::get('email', 'string');
        }
        
        $assign = array();
        
        $assign['purchases'] = $purchases;

        $assign['purchases_count'] = $purchases_count;

        $assign['subtotal'] = $subtotal;

        $assign['order'] = $order;

        if(!empty($order['id'])){
            /*// Способ доставки
            $delivery = $this->delivery->get_delivery($order['delivery_id']);
            $assign['delivery'] = $delivery;
            // Способ оплаты
            $payment_method = $this->payment->get_payment_method($order['payment_method_id']);
            
            if(!empty($payment_method)){
                $assign['payment_method'] = $payment_method;
            }
            */
            // Соседние заказы
            $assign['next_order'] = $this->orders->get_next_order($order['id'], Request::get('status', 'string'));
            $assign['prev_order'] = $this->orders->get_prev_order($order['id'], Request::get('status', 'string'));
        }

        // Все способы доставки
        /*$deliveries = $this->delivery->get_deliveries();
        $this->design->assign('deliveries', $deliveries);*/

        // Все способы оплаты
        /*$payment_methods = $this->payment->get_payment_methods();
        $this->design->assign('payment_methods', $payment_methods);*/

        // Метки заказов
          $labels = $this->orders->get_labels();
          $assign['labels'] = $labels;
             
        $assign['order_labels'] = $order_labels;
        
        if(Request::get('view') == 'print')
               return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_order-print',$assign);
           else
               return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_order',$assign);
    }
    
    
}
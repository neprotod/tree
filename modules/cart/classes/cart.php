<?php defined('MODPATH') OR exit();

class Cart_Module implements I_Module{

    const VERSION = '0.0.1';
    
    private $cart_url;
    
    function __construct(){
        $this->variants = Module::factory('variants', TRUE);
        $this->products = Module::factory('products', TRUE);
        $this->customers = Module::factory('customers', TRUE);
        $this->features = Module::factory('features', TRUE);
        $this->orders = Module::factory('orders', TRUE);
        
    }
    
    function index($setting = null){}
    
    function fetch(){
        $this->cart_url = Registry::i()->page_url;
        
        // Если пришли данные пост загружаем аналог конструктора
        if(strtolower($_SERVER['REQUEST_METHOD']) == 'post'){
            return $this->construct($this->cart_url);
        }

        if($action = $this->action()){
            return $action;
        } 
        
        $cart = $this->get_cart('Корзина');
        
        return Template::factory(Registry::i()->settings['theme'],'content_cart',array('cart' => $cart));
    }
    
    function cart_num(){
        // Массив основных значений
        $cart = array();
        
        // Если он не пустой, shopping_cart содержит key - индитификатор варианта, value - количество    
        if(!empty($_SESSION['shopping_cart'])){
            $cart['page'] = '/cart';
            foreach($_SESSION['shopping_cart'] as $shopping_cart){
                $cart['num'] += $shopping_cart;
            }
        }else{
            $cart['page'] = '';
            $cart['num'] = 0;
        }
        return $cart;
        
    }
    
    function construct($cart_url){
        if($cart_url == 'add'){
            if($variant_id = Request::post('variant', 'integer')){
                $url = $this->add_item($variant_id, Request::post('amount','integer'));
                header("Location: /products/$url");
            }
        }
        // Оформление
        elseif($cart_url == 'making'){
        
            $order = array();
            //Доставка
            $order['delivery_id'] = '1';
            
            $order['name'] = trim(Request::post('name','string'));
            $order['email'] = Request::post('email');
            $order['address'] = Request::post('city','string');
            $order['phone'] = trim(Request::post('phone'));
            $order['comment'] = Request::post('massage');
            // ip адресс
            $order['ip'] = $_SERVER['REMOTE_ADDR'];

            $errors = array();
            if(empty($order['name'])){
                $errors['name'] = 'Не заполнено имя';
            }
            if(empty($order['phone'])){
                $errors['phone'] = 'Не заполнен телефон';
            }
            
            if(!empty($errors)){
                return $this->action($errors);
            }else{
                $order_id = $this->orders->add_order($order);
                $_SESSION['order_id'] = $order_id;
                
                // Добавляем товары к заказу
                foreach($_SESSION['shopping_cart'] as $variant_id=>$amount){
                    $this->orders->add_purchase(array('order_id'=>$order_id, 'variant_id'=>intval($variant_id), 'amount'=>intval($amount)));
                }
                
                // Если заказ сделан
                if($order = $this->orders->get_order(intval($order_id))){
                    $mail = Model::factory('mail','cart',array());
                    
                    $mail->email_order_user($order['id']);
                    
                    $mail->email_order_admin($order['id']);
                    
                    unset($_SESSION['shopping_cart']);
                    header("Location: /orders/{$order['url']}");
                }
            }
        }
        exit();
    }
    /*
    *
    * Добавление варианта товара в корзину
    *
    */
    function add_item($variant_id, $amount = 1){
        $amount = max(1, $amount);

        if(isset($_SESSION['shopping_cart'][$variant_id]))
              $amount = max(1, $amount+$_SESSION['shopping_cart'][$variant_id]);
            
        // Выберем товар из базы, заодно убедившись в его существовании
        $variant = $this->variants->get_variant($variant_id);
            // Если товар существует, добавим его в корзину
        if(!empty($variant) && ($variant['stock'] > 0) ){
            // Не дадим больше чем на складе
            $amount = min($amount, $variant['stock']);
            $_SESSION['shopping_cart'][$variant_id] = intval($amount); 
        }

        $product = $this->products->get_product(intval($variant['product_id']));

        return $product['url'];
    }
    
    /*
     *
     * Функция возвращает корзину
     *
     */
    function get_cart($title){
        
        $cart['purchases'] = array();
        $cart['total_price'] = 0;
        $cart['total_products'] = 0;
        $cart['coupon'] = null;
        $cart['discount'] = 0;
        $cart['coupon_discount'] = 0;
        
        if(!empty($_SESSION['shopping_cart'])){
            $session_items = $_SESSION['shopping_cart'];
            $variants = $this->variants->get_variants(array('id'=>array_keys($session_items)));
            $items = array();
            if(!empty($variants)){
                foreach($variants as $variant){
                    $items[$variant['id']]['variant'] = $variant;
                    $items[$variant['id']]['amount'] = $session_items[$variant['id']];
                    $products_ids[] = $variant['product_id'];
                }
            }
            
            // Добавляем продукты
            
            $products = array();
            foreach($this->products->get_products(array('id'=>$products_ids, 'limit' => count($products_ids))) as $p)
                $products[$p['id']] = $p;
            
            // Добавляем картинки
            $images = $this->products->get_images(array('product_id'=>$products_ids));

            /*
            foreach($images as $image)
                $products[$image['product_id']]['images'][$image['id']] = $image;
            */
            // имзененное
            foreach($images as $image){
                $products[$image['product_id']]['images'] = $image;
            }
            
            // Свойства продуктов
            $properties = $this->features->get_options(array('product_id'=>$products_ids, 'in_filter' => 1));
            foreach($properties as $property)
                $products[$property['product_id']]['options'][] = $property;
                
                
            foreach($items as $variant_id=>$item){    
                $purchase = null;
                if(!empty($products[$item['variant']['product_id']])){
                    $purchase = array();
                    $purchase['product'] = $products[$item['variant']['product_id']];                        
                    $purchase['variant'] = $item['variant'];
                    $purchase['amount'] = $item['amount'];

                    $cart['purchases'][] = $purchase;
                    $cart['total_price'] += $item['variant']['price']*$item['amount'];
                    $cart['total_products'] += $item['amount'];
                }
            }
            
            // Пользовательская скидка
                /*$cart['discount'] = 0;
                if(isset($_SESSION['user_id']) && $user = $this->users->get_user(intval($_SESSION['user_id'])))
                    $cart->discount = $user->discount;
                    
                $cart->total_price *= (100-$cart->discount)/100;*/
            
            // Скидка по купону
        }
        
        Request::$design->meta_title = $title;
        Request::$design->meta_description = $title;
        return $cart;
    }
    
    /*
     * Действия
     */
    function action($settings = NULL){
        if($this->cart_url == 'making'){
            Request::$design->meta_title = 'Оформление товара';
            return Template::factory(Registry::i()->settings['theme'],'content_orders_making',array('error'=>$settings));
        }
    }
}
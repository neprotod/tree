<?php

class Orders_Admin{

    function index(){}
    
    function __construct(){
        $this->orders = Module::factory('orders',TRUE);
    }
    
    function fetch(){
        
         $filter = array();
          $filter['page'] = max(1, Request::get('page', 'integer'));
              
          $filter['limit'] = 40;
        
        // параметры для передачи
        $assign = array();
        // Поиск
          $keyword = Request::get('keyword', 'string');
          if(!empty($keyword)){
              $filter['keyword'] = $keyword;
            
            
        }
        
        
        // Фильтр по метке
          $label = $this->orders->get_label(Request::get('label'));          
          if(!empty($label)){
              $filter['label'] = $label['id'];
            $assign['label'] = $label;
        }
        
            // Обработка действий
        if( Request::method('post')){

            // Действия с выбранными
            $ids = Request::post('check');
            if(is_array($ids)){
                switch(Request::post('action')){
                    case 'delete':
                    {
                        foreach($ids as $id){
                            $o = $this->orders->get_order(intval($id));
                            if($o->status<3){
                                $this->orders->update_order($id, array('status'=>3));
                                $this->orders->open($id);                            
                            }else{
                                $this->orders->delete_order($id);
                            }
                        }
                        break;
                    }
                }
            // Для метки
                switch(Request::post('label')){
                    case(preg_match('/^set_label_([0-9]+)/', Request::post('label'), $a) ? true : false):
                    {
                        $l_id = intval($a[1]);
                        if($l_id>0)
                        foreach($ids as $id){
                            $this->orders->add_order_labels($id, $l_id);
                        }
                        break;
                    }
                    case(preg_match('/^unset_label_([0-9]+)/', Request::post('label'), $a) ? true : false):
                    {
                        $l_id = intval($a[1]);
                        if($l_id>0)
                        foreach($ids as $id){
                            $this->orders->delete_order_labels($id, $l_id);
                        }
                        break;
                    }
                }
            }
        }
        
        if(empty($keyword)){
            $status = Request::get('status', 'integer');
            $filter['status'] = $status;
             $assign['status'] = $status;
        }
        
        $orders_count = $this->orders->count_orders($filter);

        // Показать все страницы сразу
        if(Request::get('page') == 'all')
            $filter['limit'] = $orders_count;    
        
        // Отображение
        $orders = array();
        foreach($this->orders->get_orders($filter) as $o)
            $orders[$o['id']] = $o;
         
        // Метки заказов
        $orders_labels = array();
          foreach($this->orders->get_order_labels(array_keys($orders)) as $ol)
              $orders[$ol['order_id']]['labels'][] = $ol;
        
        $assign['pages_count'] = ceil($orders_count/$filter['limit']);

          $assign['current_page'] = $filter['page'];

        $assign['orders_count'] = $orders_count;

        $assign['orders'] = $orders;
        // Метки заказов
          $labels = $this->orders->get_labels();
        $assign['labels'] = $labels;
        
        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_orders',$assign);
    }
    
    
}
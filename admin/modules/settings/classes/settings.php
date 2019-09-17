<?php

class Settings_Admin{

    function index(){}
    
    function __construct(){
        
    }
    
    function fetch(){
        if(Request::method('POST')){
            if($settings = Request::post('settings')){
                foreach($settings as $id => $seting)
                    $this->update($id, $seting);
            }
            $settings = $this->get();
        }else{
            $settings = $this->get();
        }
        
        Request::$design->settings = $settings;
        
        return Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_settings',array());
    }
    
    function update($id, $settings){
        $settings = Str::key_value($settings);

        $sql = Str::__('UPDATE __settings SET :settings WHERE setting_id IN(:id)', array(':settings'=>$settings));
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::UPDATE, $sql);
        $query->param(':id',$id);
        $query->execute();
    }
    

    function get(){
        $sql = "SELECT setting_id, name, value  FROM __settings";
        $sql = DB::placehold($sql);
        
        $query = DB::query(Database::SELECT, $sql);
        $result = $query->execute();
        
        $settings = array();
        if(!empty($result))
            foreach($result as $setting){
                $settings[$setting['name']] = $setting;
            }
        
        return $settings;
    }
}
<?php defined('MODPATH') OR exit();

class Model_Url_System{
    function theme($status = 1){
        // Проверяем существует ли псевдоним
        $sql = "SELECT path FROM theme WHERE status = :status";

        $query = DB::query(Database::SELECT, $sql);
        $query->param(':status',$status);
        
        $result = $query->execute();
        
        if($result = $result[0]['path'])
            return Str::separator($result,'.',DIRECTORY_SEPARATOR);
    }
}
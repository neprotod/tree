<?php
header("Cache-control: no-store,max-age=0");
header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
include 'bootstrap.php';

if(isset($_POST['image'])){
    if(!is_array($_POST['image'])){
        $return = array();
        $image = $_POST['dir'].$_POST['image'];
        $return['image'] = $image;
        
        $design = Module::factory('design',TRUE);
        $img = $design->resizeimage($image, NULL, 100, 100, NULL, NULL, NULL,$_POST['original'],$_POST['resize']);
        
        $return['src'] = $img;
        echo json_encode($return);
    }else{
        $return = array();
        foreach($_POST['image'] as $key => $images){
            $image = $images['dir'].$images['image'];
            $return[$key]['image'] = $image;
            
            $design = Module::factory('design',TRUE);
            
            $img = $design->resizeimage($image, NULL, 100, 100, NULL, NULL, NULL,$_POST['original'],$_POST['resize']);
            
            $return[$key]['src'] = $img;
        }
        echo json_encode($return);
    }
}

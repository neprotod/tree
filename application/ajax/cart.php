<?php
include "bootstrap.php";
$id = intval($_POST['id']);
if(empty($id) OR !isset($_SESSION['shopping_cart'][$id]))
    exit();
    
$result;

switch($_POST['type']){
    case 'drop':
        unset($_SESSION['shopping_cart'][$id]);
        $result = true;
        break;
    case 'update':
        Registry::i()->settings['max_order_amount'] = $_POST['max_order_amount'];
        $_SESSION['shopping_cart'][$id] = intval($_POST['amount']);
        
        $variants = Module::factory('variants',TRUE);
        $variant = $variants->get_variant($id);
        $result['price'] = (int)$variant['price'];
        $result['amount'] = $_POST['amount'];
        break;
}
    echo json_encode($result);
?>
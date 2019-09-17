<?php
$array = array(
    '0'=> array(
        'active' => '',
        'status' => 0,
        'module' => 'orders',
        'name' => 'Заказы',
    ),
    '1'=> array(
        'active' => '',
        'status' => 1,
        'module' => 'orders',
        'name' => 'Приняты',
    ),
    '2'=> array(
        'active' => '',
        'status' => 2,
        'module' => 'orders',
        'name' => 'Выполнены',
    ),
    '3'=> array(
        'active' => '',
        'status' => 3,
        'module' => 'orders',
        'name' => 'Удалены',
    ),
    '4'=> array(
        'active' => '',
        'status' => 0,
        'module' => 'labels',
        'name' => 'Метки',
    ),
);
if(array_key_exists($active, $array)){
    $array[$active]['active'] = 'active';
    if(isset($return))
        $array[$active]['return'] = TRUE;
}

foreach($array as $module => $tabs):
?>
<li class="<?=$tabs['active']?>">
    <a href="<?=(isset($tabs['return']))? Request::get('return') : Url::query_root(array('module'=>$tabs['module'],'status'=>$tabs['status'])) ?>"><?=$tabs['name']?></a>
</li>
<?php
endforeach;
?>
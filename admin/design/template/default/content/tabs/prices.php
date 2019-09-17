<?php
$array = array(
    '0'=> array(
        'active' => 'active',
        'status' => '',
        'module' => 'prices',
        'name' => 'Прайсы',
    )
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
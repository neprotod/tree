<?php
$array = array(
    'blog'=> array(
        'active' => '',
        'name' => 'Новости',
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
    <a href="<?=(isset($tabs['return']))? Request::get('return') : Url::query_root(array('module'=>$module)) ?>"><?=$tabs['name']?></a>
</li>
<?php
endforeach;
?>
<?php
IF(!empty($b['name'])):
if($b['url'] == Request::$design->brand_url){
    $select = 'select';
}else{
    $select = '';
}

?>
<td>
    <a class="brand_padding" href="<?=$catalog.$category->url.'/type/'.$b['url']?>">
        <span class="brand_name"><?=$b['name']?></span>
    </a>
</td>
<td>
    <a href="<?=$catalog.$category->url.'/type/'.$b['url']?>">
        <div class="<?=$select?> brand" style="float:left; background-image:url(/<?=Registry::i()->settings['brands_images_dir'].$b['image']?>);"></div>
    </a>
</td>
<?php
    ELSE:
?>
<td>
    <div>
        <a data-brand="<?=$b['id']?>}" href="<?=$catalog.'type/'.$category->url.'/type/'.$b['url']?>"><?=$b['name']?></a>
    <div>
</td>
<?php
ENDIF;
?>
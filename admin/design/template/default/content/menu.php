<?php
$design = Request::$design;
$menu = Request::param(Request::$design->menu);
$menus = Request::param(Request::$design->menus,NULL,array());
?>
<!-- Вкладки -->
<?php
$design->tabs('start');
?>
<li class="active">
    <a style="text-decoration:none;" href="<?=Url::query_root(array('module'=>'menus'))?>">Меню</a>
</li>
<?php
foreach($menus as $m):

?>
<li class="">
    <a href="<?=Url::query_root(array('module'=>'pages','menu_id'=>$m['id']))?>"><?=$m['name']?></a>
</li>
<?php
endforeach;
?>
<?php
$design->tabs('end');
/*Title*/
Registry::i()->meta_title = 'Меню';
?>

<?php
if(is_array($design->massage) AND !empty($design->massage)):
?>
<div class="message message_success">
    <?php
    foreach($design->massage as $massage):
    ?>
    <span><?=$massage?></span>
    <?php
    endforeach;
    ?>
    <?php
    if($return = Request::get('return')):
    ?>
    <a class="button" href="<?=Request::get('return')?>">Вернуться</a>
    <?php
    endif;
    ?>
</div>
<?php
endif;
?>
<?php
if(is_array($design->error) AND !empty($design->error)):
?>
<div class="message message_error">
    <?php
    foreach($design->error as $error):
    ?>
    <span><?=$error?></span>

    <?php
    endforeach;
    if($return = Request::get('return')):
    ?>
    <a class="button" href="<?=$return?>">Вернуться</a>
    <?php
    endif;
    ?>
</div>
<?php
endif;
?>

<!-- Основная форма -->
<form method="post" id="product" enctype="multipart/form-data">
    <input type="hidden" name="session_id" value="<?=session_id()?>">
    
     <div id="name">
        <input class="name" name="name" type="text" value="<?=Request::param($menu['name'],TRUE)?>"/> 
        <input name="id" type="hidden" value="<?=Request::param($menu['id'])?>"/> 
        <div class="checkbox">
            <input name="visible" value='1' type="checkbox" id="active_checkbox" <?=($menu['visible'] == 1)? 'checked="checked"' : '' ?> /> <label for="active_checkbox">Активен</label>
        </div>
    </div>

    <input class="button_green button_save" type="submit" name="" value="Сохранить" />
</form>
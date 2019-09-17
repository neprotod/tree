<?php
$design = Request::$design;

?>
<!-- Вкладки -->
<?php
$design->tabs('start');
echo Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_tabs_orders',array('active'=>4));
$design->tabs('end');
?>
<!-- Вкладки END -->
<?php
if($label['id']){
    Registry::i()->meta_title = $label['name'];
}else{
    Registry::i()->meta_title = 'Новая метка';
}
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


<form method="post" id="product" enctype="multipart/form-data">
    <input type="hidden" name="session_id" value="<?=session_id()?>">
    
     <div id="name">
        <input class="name" name="name" type="text" value="<?=Request::param($label['name'],TRUE)?>"/> 
        <input name="id" type="hidden" value="<?=Request::param($label['id'])?>"/> 
        <div class="checkbox">
            <span id="color_icon" style="background-color:#<?=Request::param($label['color'],TRUE)?>;" class="order_label_big"></span>
            <input id="color_input" name="color" class="simpla_inp" type="hidden" value="<?=Request::param($label['color'],TRUE)?>" />
        </div>
    </div>
    
    <!-- Описание товара (The End)-->
    <input class="button_green button_save" type="submit" name="" value="Сохранить" />
</form>

<link rel="stylesheet" media="screen" type="text/css" href="<?=Request::$design->root?>/js/colorpicker/css/colorpicker.css" />
<script src="<?=Request::$design->root?>/js/colorpicker/js/colorpicker.js"></script>
<?php
// Подключаем редактор
include('tinymce_init.php');
?>
<script>
$(function() {
    $('#color_icon, #color_link').ColorPicker({
        color: $('#color_input').val(),
        onShow: function (colpkr) {
            $(colpkr).fadeIn(500);
            return false;
        },
        onHide: function (colpkr) {
            $(colpkr).fadeOut(500);
            return false;
        },
        onChange: function (hsb, hex, rgb) {
            $('#color_icon').css('backgroundColor', '#' + hex);
            $('#color_input').val(hex);
            $('#color_input').ColorPickerHide();
        }
    });
});
</script>
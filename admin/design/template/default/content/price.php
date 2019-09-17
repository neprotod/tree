<?php
$design = Request::$design;
?>
<!-- Вкладки -->
<?php
$design->tabs('start');
echo Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_tabs_prices',array('active'=>'prices'));
$design->tabs('end');
?>
<!-- Вкладки END -->
<?php
if($price['id']){
    Registry::i()->meta_title = $price['name'];
}else{
    Registry::i()->meta_title = 'Новый бренд';
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
        <input class="name" name="name" type="text" value="<?=Request::param($price['name'],TRUE)?>"/> 
        <input name="id" type="hidden" value="<?=Request::param($price['id'])?>"/> 
    </div>
    
    <!-- Левая колонка свойств товара -->
    <div id="column_left">
            
        <!-- Параметры страницы -->
        <div class="block layer">
            <h2>Параметры прайса</h2>
            <ul>
                <?php
                    if(!empty($price['price'])):
                ?>
                <li>
                    <label class="property">Используемый файл</label>
                    <span><?=Request::param($price['price'],TRUE)?></span>
                    
                </li>
                <li>
                    <label class="property">Изменить файл</label>
                    <input style="display:block; float:;" class="upload_file" name="price" type="file" />    
                </li>
                <?php
                    else:
                ?>
                <li>
                    <label class="property">Добавить файл</label>
                    <input style="display:block; float:;" class="upload_file" name="price" type="file" />    
                </li>
                <?php
                    endif;
                ?>
            </ul>
        </div>
        <!-- Параметры страницы (The End)-->
    </div>
    
    <!-- Правая колонка свойств товара -->    
    <div id="column_right">
        
        <!-- Изображения товара -->    
        <div class="block layer images">
            <h2>
                Изображение прайса
            </h2>
            <input class="upload_image" name="img" type="file" />            
            <input type="hidden" name="delete_img" value="" />
            <?php
            if(!empty($price['img'])):
            ?>
            <ul>
                <li>
                    <a href="#" class="delete">
                        Удалить
                    </a>
                    <img width="120" src="<?='/'.trim($folder.'/'.$price['img'],'/')?>" alt="" />
                </li>
            </ul>
            <?php
            endif;
            ?>
        </div>
        
    </div>
    <!-- Правая колонка свойств товара (The End)--> 
    
    <!-- Описание товара (The End)-->
    <input class="button_green button_save" type="submit" name="" value="Сохранить" />
</form>




<script>
$(function() {


    // Удаление изображений
    $(".images a.delete").click( function() {
        $("input[name='delete_img']").val('1');
        $(this).closest("ul").fadeOut(200, function() { $(this).remove(); });
        return false;
    });
      
});
</script>
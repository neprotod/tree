<?php
$design = Request::$design;
$categories = Request::param(Request::$design->categories);
?>
<!-- Вкладки -->
<?php
$design->tabs('start');
echo Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_tabs_catalog',array('active'=>'categories'));
$design->tabs('end');
?>
<!-- Вкладки END -->

<!-- Заголовок -->
<div id="header">
    <h1>Категории товаров</h1>
    <a class="add" href="<?=URL::query_root(array('module'=>'category','return'=>urlencode(Url::root(NULL))),TRUE,'auto')?>">Добавить категорию</a>
</div>
<!-- Заголовок (The End) -->

<?php
if(!empty($categories)):
?>
<div id="main_list" class="categories">

    <form id="list_form" method="post">
    <input type="hidden" name="session_id" value="<?=session_id()?>">
        <?php
        function categories_tree($categories,$level = 0){
            ?>
            <div id="list" class="sortable">
            <?php
            foreach ($categories as $category):
        ?>
                <div class="<?=($category['visible'] == 1)? '' : 'invisible' ?> row">        
                    <div class="tree_row">
                        <input type="hidden" name="positions[<?=$category['id']?>]" value="<?=$category['position']?>">
                        <div class="move cell" style="margin-left:<?=$level*20?>px"><div class="move_zone"></div></div>
                        <div class="checkbox cell">
                            <input type="checkbox" name="check[]" value="<?=$category['id']?>" />                
                        </div>
                        <div class="cell">
                            <a href="<?=Url::query_root(array('module'=>'category','id'=>$category['id'],'return'=>urlencode(Url::root(NULL))))?>"><?=$category['name']?></a>                  
                        </div>
                        <div class="icons cell">
                            <a class="preview" title="" href="../catalog/<?=$category['url']?>" target="_blank">Предпросмотр в новом окне</a>                
                            <a class="enable" title="" href="#">Активен</a>
                            <a class="delete" title="" href="#">Удалить</a>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <?php
                        if(isset($category['subcategories'])){
                            echo categories_tree($category['subcategories'],$level+1);
                        }
                    ?>
                </div>
            
        <?php
            endforeach;
            ?>
            </div>
            <?php
        }
        
        echo categories_tree($categories,$level);
        ?>
        
        <div id="action">
        <label id="check_all" class="dash_link">Выбрать все</label>
        
        <span id="select">
        <select name="action">
            <option value="enable">Сделать видимыми</option>
            <option value="disable">Сделать невидимыми</option>
            <option value="delete">Удалить</option>
        </select>
        </span>
        
        <input id="apply_action" class="button_green" type="submit" value="Применить">
        
        </div>
    
    </form>
</div>
<?php
else:
?>
Нет категорий
<?php
endif;
?>

<script>
$(function() {

    // Сортировка списка
    $(".sortable").sortable({
        items:".row",
        handle: ".move_zone",
        tolerance:"pointer",
        scrollSensitivity:40,
        opacity:0.7, 
        axis: "y",
        update:function()
        {
            $("#list_form input[name*='check']").attr('checked', false);
            $("#list_form").ajaxSubmit();
        }
    });
    
    // для неактивных категорий
    $("#list .invisible .enable").text('Неактивный');
    
    // Выделить все
    $("#check_all").click(function() {
        $('#list input[type="checkbox"][name*="check"]:not(:disabled)').attr('checked', $('#list input[type="checkbox"][name*="check"]:not(:disabled):not(:checked)').length>0);
    });    

    // Показать категорию
    $("a.enable").click(function() {
        var icon        = $(this);
        var line        = icon.closest(".row");
        var id          = line.find('input[type="checkbox"][name*="check"]').val();
        var state       = line.hasClass('invisible')?1:0;
        $.ajax({
            type: 'POST',
            url: '<?=Url::root()?>/system/ajax/update_object.php',
            data: {'object': 'category', 'id': id, 'values': {'visible': state}, 'session_id': '<?=session_id()?>'},
            success: function(data){
                if(state){
                    line.removeClass('invisible');
                    icon.text('Активен');
                }else{
                    line.addClass('invisible');    
                    icon.text('Неактивный');
                }
            },
            dataType: 'json'
        });    
        return false;    
    });

    // Удалить 
    $("a.delete").click(function() {
        $('#list input[type="checkbox"][name*="check"]').attr('checked', false);
        $(this).closest("div.row").find('input[type="checkbox"][name*="check"]:first').attr('checked', true);
        $(this).closest("form").find('select[name="action"] option[value=delete]').attr('selected', true);
        $(this).closest("form").submit();
    });

    
    // Подтвердить удаление
    $("form").submit(function() {
        if($('select[name="action"]').val()=='delete' && !confirm('Подтвердите удаление'))
            return false;    
    });

});
</script>
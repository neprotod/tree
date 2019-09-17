<?php
$design = Request::$design;
$variant_word = array('новость','новости','новостей');

?>
<!-- Вкладки -->
<?php
$design->tabs('start');
echo Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_tabs_blog',array('active'=>'blog'));
$design->tabs('end');

Registry::i()->meta_title = 'Новости';
?>
<!-- Вкладки END -->

<!-- Заголовок -->
<div id="header">
    <h1><?=$posts_count." ".Translit::declension_words($products_count, $variant_word)?></h1>
    <a class="add" href="<?=URL::query_root(array('module'=>'post','return'=>urlencode(Url::root(NULL))),TRUE,'auto')?>">Добавить новость</a>
</div>
<!-- Заголовок (The End) -->

<?php
if(!empty($posts)):
?>
<div id="main_list" class="categories">

    <form id="list_form" method="post">
    <input type="hidden" name="session_id" value="<?=session_id()?>">
            <div id="list" class="sortable">
            <?php
            foreach ($posts as $post):
            ?>
                <div class="<?=($post['visible'] != 0)? '' : 'invisible' ?> row">        
                    <input type="hidden" name="positions[<?=$post['id']?>]" value="<?=$post['position']?>">
                    <div class="checkbox cell">
                        <input type="checkbox" name="check[]" value="<?=$post['id']?>" />                
                    </div>
                    <div class="name cell">
                        <a href="<?=Url::query_root(array('module'=>'post','id'=>$post['id'],'return'=>urlencode(Url::root(NULL))))?>"><?=$post['name']?></a>
                        <br/>
                        <?=Str::sql_date(Registry::i()->settings['date_format'],$post['date'])?>
                    </div>
                    <div class="icons cell">                
                        <a class="enable" title="" href="#">Активна</a>
                        <a class="delete" title="" href="#">Удалить</a>
                    </div>
                    <div class="clear"></div>
                </div>
            
            <?php
            endforeach;
            ?>
            </div>
        
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
    <?php
        include 'pagination.php';
    ?>
</div>
<?php
else:
?>
Нет новостей
<?php
endif;
?>
<script>
$(function() {
    // Раскраска строк
    function colorize(){
        $("#list div.row:even").addClass('even');
        $("#list div.row:odd").removeClass('even');
    }
    colorize();
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

    
    // Выделить все
    $("#check_all").click(function() {
        $('#list input[type="checkbox"][name*="check"]:not(:disabled)').attr('checked', $('#list input[type="checkbox"][name*="check"]:not(:disabled):not(:checked)').length>0);
    });    

    // для неактивных категорий
    $("#list .invisible .enable").text('Неактивный');
    
    // Показать новость
    $("a.enable").click(function() {
        var icon        = $(this);
        var line        = icon.closest(".row");
        var id          = line.find('input[type="checkbox"][name*="check"]').val();
        var state       = line.hasClass('invisible')?1:0;
        $.ajax({
            type: 'POST',
            url: '<?=Url::root()?>/system/ajax/update_object.php',
            data: {'object': 'blog', 'id': id, 'values': {'visible': state}, 'session_id': '<?=session_id()?>'},
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
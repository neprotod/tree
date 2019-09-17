<?php
$design = Request::$design;
?>
<!-- Вкладки -->
<?php
$design->tabs('start');
echo Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_tabs_catalog',array('active'=>'features'));
$design->tabs('end');

Registry::i()->meta_title = 'Свойства';
?>
<!-- Вкладки END -->

<!-- Заголовок -->
<div id="header">
    <h1>Свойства</h1>
    <a class="add" href="<?=URL::query_root(array('module'=>'feature','return'=>urlencode(Url::root(NULL))),TRUE,'auto')?>">Добавить свойство</a>
</div>
<!-- Заголовок (The End) -->

<?php
if(!empty($features)):
?>
<div id="main_list" class="categories">

    <form id="list_form" method="post">
    <input type="hidden" name="session_id" value="<?=session_id()?>">
            <div id="list" class="sortable">
            <?php
            foreach ($features as $feature):
            ?>
                <div class="<?=($feature['in_filter'] == 0)? '' : 'in_filter' ?> row">        
                    <input type="hidden" name="positions[<?=$feature['id']?>]" value="<?=$feature['position']?>">
                    <div class="move cell"><div class="move_zone"></div></div>
                    <div class="checkbox cell">
                        <input type="checkbox" name="check[]" value="<?=$feature['id']?>" />                
                    </div>
                    <div class="cell">
                        <a href="<?=Url::query_root(array('module'=>'feature','id'=>$feature['id'],'return'=>urlencode(Url::root(NULL))))?>"><?=$feature['name']?></a>                  
                    </div>
                    <div class="icons cell">        
                        <a class="in_filter" title="" href="#"><?=($feature['in_filter'] == 0)? 'Использовать в фильтре' : 'Убрать из фильтра'?></a>
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
            <option value="set_in_filter">Использовать в фильтре</option>
            <option value="unset_in_filter">Не использовать в фильтре</option>
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
Нет свойств
<?php
endif;
?>
<div id="right_menu">
    <?php
    //Функция древо
    function categories_tree($categories,$category,$sub = FALSE){
        if($categories):
    ?>
        <ul class="<?=($sub === TRUE)? 'subcategories' : ''?>">
            <?php
            if($categories[0]['parent_id'] == 0):
            ?>
            <li class="<?=(!$category['id'])? 'selected' : '' ?>">
                <a href="<?=Url::query_root(array('module'=>'features','category_id'=>NULL))?>">Все категории</a>
            </li>
            <?php
            endif;
            
            foreach($categories as $c):
            ?>
            <li class="<?=($category['id'] == $c['id'])? 'selected' : '' ?>">
                <a href="<?=Url::query_root(array('module'=>'features','category_id'=>$c['id']))?>"><?=$c['name']?></a>
                <?=categories_tree($c['subcategories'],$category,TRUE)?>
            </li>
            <?php
            endforeach;
            ?>
        </ul>
    <?php
        endif;
    }
    categories_tree($categories,$category);
    ?>
</div>
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

    // Указать "в фильтре"/"не в фильтре"
    $("a.in_filter").click(function() {
        var icon        = $(this);
        var line        = icon.closest(".row");
        var id          = line.find('input[type="checkbox"][name*="check"]').val();
        var state       = line.hasClass('in_filter')?0:1;
        icon.addClass('loading_icon');
        $.ajax({
            type: 'POST',
            url: '<?=Url::root()?>/system/ajax/update_object.php',
            data: {'object': 'feature', 'id': id, 'values': {'in_filter': state}, 'session_id': '<?=session_id()?>'},
            success: function(data){
                icon.removeClass('loading_icon');
                if(!state){
                    line.removeClass('in_filter');
                    icon.text("Использовать в фильтре");
                }else{
                    line.addClass('in_filter');
                    icon.text("Убрать из фильтра");
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
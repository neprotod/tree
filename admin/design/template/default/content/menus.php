<?php
$design = Request::$design;
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
<!-- Заголовок -->
<div id="header">
    <h1>Список меню</h1>
    <a class="add" href="<?=URL::query_root(array('module'=>'menu','return'=>urlencode(Url::root(NULL))),TRUE,'auto')?>">Добавить пункт меню</a>
</div>

<?php
if(!empty($menus)):
?>
<div id="main_list">
 
    <form id="list_form" class="page_main" method="post">
        <input type="hidden" name="session_id" value="<?=session_id()?>">
        <div id="list">
            <?php
            foreach($menus as $menu):
            ?>
            <div class="row">
                <input type="hidden" name="positions[<?=Request::param($menu['id'])?>]" value="<?=Request::param($menu['position'])?>">
                <div class="move cell"><div class="move_zone"></div></div>
                 <div class="checkbox cell">
                    <input type="checkbox" name="check[]" value="<?=Request::param($menu['id'])?>" />                
                </div>
                <div class="name cell">
                    <a href="<?=URL::query_root(array('module'=>'menu', 'id'=>$menu['id'], 'return'=>urlencode(Url::root(NULL))))?>"><?=Request::param($menu['name'],TRUE)?></a>
                </div>
                <div class="icons cell">
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
                <option value="0">Не указано</option>
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
    Нет меню

<?php
endif;
?>


<script>
$(function() {

    // Сортировка списка
    $("#list").sortable({
        items:             ".row",
        tolerance:         "pointer",
        handle:            ".move_zone",
        scrollSensitivity: 40,
        opacity:           0.7, 
        forcePlaceholderSize: true,
        axis: 'y',
        
        helper: function(event, ui){        
            if($('input[type="checkbox"][name*="check"]:checked').size()<1) return ui;
            var helper = $('<div/>');
            $('input[type="checkbox"][name*="check"]:checked').each(function(){
                var item = $(this).closest('.row');
                helper.height(helper.height()+item.innerHeight());
                if(item[0]!=ui[0]) {
                    helper.append(item.clone());
                    $(this).closest('.row').remove();
                }
                else {
                    helper.append(ui.clone());
                    item.find('input[type="checkbox"][name*="check"]').attr('checked', false);
                }
            });
            return helper;            
        },    
         start: function(event, ui) {
              if(ui.helper.children('.row').size()>0)
                $('.ui-sortable-placeholder').height(ui.helper.height());
        },
        beforeStop:function(event, ui){
            if(ui.helper.children('.row').size()>0){
                ui.helper.children('.row').each(function(){
                    $(this).insertBefore(ui.item);
                });
                ui.item.remove();
            }
        },
        update:function(event, ui)
        {
            $("#list_form input[name*='check']").attr('checked', false);
            $("#list_form").ajaxSubmit(function() {
                colorize();
            });
        }
    });
    
    // Выделить все
    $("#check_all").click(function() {
        $('#list input[type="checkbox"][name*="check"]').attr('checked', $('#list input[type="checkbox"][name*="check"]:not(:checked)').length>0);
    });    
    
    // Раскраска строк
    function colorize()
    {
        $(".row:even").addClass('even');
        $(".row:odd").removeClass('even');
    }
    // Раскрасить строки сразу
    colorize();
 
    // Удалить 
    $("a.delete").click(function() {
        $('#list_form input[type="checkbox"][name*="check"]').attr('checked', false);
        $(this).closest(".row").find('input[type="checkbox"][name*="check"]').attr('checked', true);
        $(this).closest("form").find('select[name="action"] option[value=delete]').attr('selected', true);
        $(this).closest("form").submit();
    });
    
    $("form").submit(function() {
        if($('select[name="action"]').val()=='delete' && !confirm('Подтвердите удаление'))
            return false;    
    });

});
</script>
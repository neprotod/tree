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

<!-- Заголовок -->
<div id="header">
    <h1>Прайсы</h1>
    <a class="add" href="<?=URL::query_root(array('module'=>'price','return'=>urlencode(Url::root(NULL))),TRUE,'auto')?>">Добавить прайс</a>
</div>
<!-- Заголовок (The End) -->

<?php
if(!empty($prices)):
?>
<div id="main_list" class="page_main">

    <form id="list_form" method="post">
        <input type="hidden" name="session_id" value="<?=session_id()?>">
        <div id="list">
            <?php
            foreach($prices as $price):
            ?>
            <div class="<?=($price['visible'] == 0)? 'invisible' :''?> row">
                <input type="hidden" name="positions[<?=Request::param($price['id'])?>]" value="<?=Request::param($price['position'])?>">
                <div class="move cell"><div class="move_zone"></div></div>
                 <div class="checkbox cell">
                    <input style="display:none;" type="checkbox" name="check[]" value="<?=Request::param($price['id'])?>" />                
                </div>
                <div class="name cell">
                    <a href="<?=URL::query_root(array('module'=>'price', 'id'=>$price['id'], 'return'=>urlencode(Url::root(NULL))))?>"><?=Request::param($price['name'],TRUE)?></a>
                </div>
                <div class="icons cell">
                    <a class="enable" title="" href="#">Активен</a>
                    <a class="delete" title="" href="#">Удалить</a>
                </div>
                <div class="clear"></div>
            </div>
            <?php
            endforeach;
            ?>
        </div>
        <div id="action" style="display:none;">    
            <span id="select">
            <select name="action">
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
Нет прайсов
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
    
    // для неактивных продуктов
    $("#list .invisible .enable").text('Неактивный');
 
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
    
    // Показать
    $("a.enable").click(function() {
        var icon        = $(this);
        var line        = icon.closest(".row");
        var id          = line.find('input[type="checkbox"][name*="check"]').val();
        var state       = line.hasClass('invisible')?1:0;
        icon.addClass('loading_icon');
        $.ajax({
            type: 'POST',
            url: '<?=Url::root()?>/system/ajax/update_object.php',
            data: {'object': 'price', 'id': id, 'values': {'visible': state}, 'session_id': '<?=session_id()?>'},
            success: function(data){
                if(state){
                    line.removeClass('invisible');
                    icon.text('Активен');
                }
                else{
                    line.addClass('invisible');
                    icon.text('Неактивный');
                }
            },
            dataType: 'json'
        });    
        return false;    
    });

    $("form").submit(function() {
        if($('select[name="action"]').val()=='delete' && !confirm('Подтвердите удаление'))
            return false;    
    });
});
</script>
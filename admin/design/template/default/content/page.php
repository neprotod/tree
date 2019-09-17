<?php
$design = Request::$design;
$page = Request::param(Request::$design->page);
$menus = Request::param(Request::$design->menus,NULL,array());
$menu = Request::param(Request::$design->menu);
$types = Request::param(Request::$design->types,NULL,array());
$formats = Request::param(Request::$design->formats,NULL,array());
?>
<!-- Вкладки -->
<?php
$design->tabs('start');
?>
<li class="">
    <a style="text-decoration:none;" href="<?=Url::query_root(array('module'=>'menus'))?>">Меню</a>
</li>
<?php
foreach($menus as $m):

?>
<li class="<?=($m['id'] == $menu['id'])?'active':'' ?>">
    <a href="<?=Url::query_root(array('module'=>'pages','menu_id'=>$m['id']))?>"><?=$m['name']?></a>
</li>
<?php
endforeach;
?>
<?php
$design->tabs('end');
/*Title*/
if(!empty($page['id'])){
    Registry::i()->meta_title = Request::param($page['name'],TRUE);
}else{
    Registry::i()->meta_title = 'Новая страница';
}
?>

<script src="/media/js/autocomplete/jquery.autocomplete-min.js"></script>

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
    <a class="link" target="_blank" href="../<?= Request::param($page['url'])?>">Открыть страницу на сайте</a>
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
        <input class="name" name="header" type="text" value="<?=Request::param($page['title'],TRUE)?>"/> 
        <input name="id" type="hidden" value="<?=Request::param($page['id'])?>"/> 
        <div class="checkbox">
            <input name="visible" value='1' type="checkbox" id="active_checkbox" <?=($page['visible'] == 1)? 'checked="checked"' : '' ?> /> <label for="active_checkbox">Активен</label>
        </div>
    </div>
    
    
    <!-- Меню страницы -->
    <div class="block format">
        <ul>
            <li>
                <label class="property">Название пункта в меню</label>
                <input name="name" class="inp" type="text" value="<?=Request::param($page['name'],TRUE)?>" />
            </li>
            <li>
                <label class="property">Меню</label>    
                <select name="menu_id">
                    <?php
                    foreach($menus as $m):
                    ?>
                        <option value="<?=$m['id']?>" <?=($page['menu_id'] == $m['id'])?'selected="selected"':'' ?>><?=Request::param($m['name'],TRUE)?></option>
                    <?php
                    endforeach;
                    ?>
                </select>
            </li>
            <li>
                <label class="property">Тип данных</label>    
                <select name="type_id">
                    <?php
                    foreach($types as $t):
                    ?>
                        <option value="<?=$t['id']?>" <?=($page['type_id'] == $t['id'])?'selected="selected"':'' ?>><?=Request::param($t['name'],TRUE)?></option>
                    <?php
                    endforeach;
                    ?>
                </select>
            </li>
            <li>
                <label class="property">Формат</label>    
                <select name="format_id">
                    <?php
                    foreach($formats as $f):
                    ?>
                        <option value="<?=$f['id']?>" <?=($page['format_id'] == $f['id'])?'selected="selected"':'' ?>><?=Request::param($f['name'],TRUE)?></option>
                    <?php
                    endforeach;
                    ?>
                </select>
            </li>
        </ul>
        <input class="button_green button_save" type="submit" name="" value="Сохранить" />
        <div class="clear"></div>
    </div>
    <!-- Меню страницы (The End)-->

    <!-- Левая колонка -->
    <div id="column_left">
            
        <!-- Параметры страницы -->
        <div class="block layer">
            <h2>Параметры страницы</h2>
            <ul>
                <li>
                    <label class="property">Адрес</label>
                    <div class="page_url">/</div>
                    <input name="url" class="page_url" type="text" value="<?=Request::param($page['url'],TRUE)?>" />
                </li>
                <li>
                    <label class="property">Заголовок</label>
                    <input name="meta_title" class="inp" type="text" value="<?=Request::param($page['meta_title'],TRUE)?>" />
                </li>
                <li>
                    <label class="property">Описание</label>
                    <textarea name="meta_description" class="inp"><?=Request::param($page['meta_description'],TRUE)?></textarea>
                </li>
            </ul>
        </div>
        <!-- Параметры страницы (The End)-->
                
        
    </div>
    
    <div class="block layer">
        <h2>Текст страницы</h2>
        <textarea name="body" class="editor_large"><?=Request::param($page['body'])?></textarea>
    </div>
    
    <!-- Описание товара (The End)-->
    <input class="button_green button_save" type="submit" name="" value="Сохранить" />
</form>








<?php
// Подключаем редактор
if(strtolower(Request::param($formats[$page['format_id']]['format'])) == 'tinymce')
include('tinymce_init.php');
?>


<script>
$(function() {

    // Автозаполнение мета-тегов
    menu_item_name_touched = true;
    meta_title_touched = true;
    meta_keywords_touched = true;
    meta_description_touched = true;
    url_touched = true;
    
    if($('input[name="menu_item_name"]').val() == generate_menu_item_name() || $('input[name="name"]').val() == '')
        menu_item_name_touched = false;
    if($('input[name="meta_title"]').val() == generate_meta_title() || $('input[name="meta_title"]').val() == '')
        meta_title_touched = false;
    if($('input[name="meta_keywords"]').val() == generate_meta_keywords() || $('input[name="meta_keywords"]').val() == '')
        meta_keywords_touched = false;
    if($('textarea[name="meta_description"]').val() == generate_meta_description() || $('textarea[name="meta_description"]').val() == '')
        meta_description_touched = false;
    if($('input[name="url"]').val() == generate_url())
        url_touched = false;
        
    $('input[name="name"]').change(function() { menu_item_name_touched = true; });
    $('input[name="meta_title"]').change(function() { meta_title_touched = true; });
    $('input[name="meta_keywords"]').change(function() { meta_keywords_touched = true; });
    $('textarea[name="meta_description"]').change(function() { meta_description_touched = true; });
    $('input[name="url"]').change(function() { url_touched = true; });
    
    $('input[name="header"]').keyup(function() { set_meta(); });
});

function set_meta()
{
    if(!menu_item_name_touched)
        $('input[name="name"]').val(generate_menu_item_name());
    if(!meta_title_touched)
        $('input[name="meta_title"]').val(generate_meta_title());
    if(!meta_keywords_touched)
        $('input[name="meta_keywords"]').val(generate_meta_keywords());
    if(!meta_description_touched)
    {
        descr = $('textarea[name="meta_description"]');
        descr.val(generate_meta_description());
        descr.scrollTop(descr.outerHeight());
    }
    if(!url_touched)
        $('input[name="url"]').val(generate_url());
}

function generate_menu_item_name()
{
    name = $('input[name="header"]').val();
    return name;
}

function generate_meta_title()
{
    name = $('input[name="header"]').val();
    return name;
}

function generate_meta_keywords()
{
    name = $('input[name="header"]').val();
    return name;
}

function generate_meta_description()
{
    try{
        if(typeof(tinyMCE.get("body")) =='object')
        {
            description = tinyMCE.get("body").getContent().replace(/(<([^>]+)>)/ig," ").replace(/(\&nbsp;)/ig," ").replace(/^\s+|\s+$/g, '').substr(0, 512);
            return description;
        }
        else
            return $('textarea[name=body]').val().replace(/(<([^>]+)>)/ig," ").replace(/(\&nbsp;)/ig," ").replace(/^\s+|\s+$/g, '').substr(0, 512);
    }catch(e){
        return $('textarea[name=body]').val().replace(/(<([^>]+)>)/ig," ").replace(/(\&nbsp;)/ig," ").replace(/^\s+|\s+$/g, '').substr(0, 512);
    }
}

function generate_url()
{
    url = $('input[name="header"]').val();
    url = url.replace(/[\s]+/gi, '-');
    url = translit(url);
    url = url.replace(/[^0-9a-z_\-]+/gi, '').toLowerCase();    
    return url;
}

function translit(str)
{
    var ru=("А-а-Б-б-В-в-Ґ-ґ-Г-г-Д-д-Е-е-Ё-ё-Є-є-Ж-ж-З-з-И-и-І-і-Ї-ї-Й-й-К-к-Л-л-М-м-Н-н-О-о-П-п-Р-р-С-с-Т-т-У-у-Ф-ф-Х-х-Ц-ц-Ч-ч-Ш-ш-Щ-щ-Ъ-ъ-Ы-ы-Ь-ь-Э-э-Ю-ю-Я-я").split("-")   
    var en=("A-a-B-b-V-v-G-g-G-g-D-d-E-e-E-e-E-e-ZH-zh-Z-z-I-i-I-i-I-i-J-j-K-k-L-l-M-m-N-n-O-o-P-p-R-r-S-s-T-t-U-u-F-f-H-h-TS-ts-CH-ch-SH-sh-SCH-sch-'-'-Y-y-'-'-E-e-YU-yu-YA-ya").split("-")   
     var res = '';
    for(var i=0, l=str.length; i<l; i++)
    { 
        var s = str.charAt(i), n = ru.indexOf(s); 
        if(n >= 0) { res += en[n]; } 
        else { res += s; } 
    } 
    return res;  
}


</script>
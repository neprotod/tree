<?php
$design = Request::$design;

?>
<!-- Вкладки -->
<?php
$design->tabs('start');
echo Admin_Template::factory(Registry::i()->settings['admin_theme'],'content_tabs_catalog',array('active'=>'features','return'=>TRUE));
$design->tabs('end');
?>
<!-- Вкладки END -->
<?php
if($feature['id']){
    Registry::i()->meta_title = $feature['name'];
}else{
    Registry::i()->meta_title = 'Новое свойство';
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
        <input class="name" name="name" type="text" value="<?=Request::param($feature['name'],TRUE)?>"/> 
        <input name="id" type="hidden" value="<?=Request::param($feature['id'])?>"/> 
    </div>
    
    <!-- Левая колонка свойств товара -->
    <div id="column_left">
            
        <!-- Параметры страницы -->
        <div class="block">
            <h2>Использовать в категориях</h2>
            <select class="multiple_categories" multiple name="feature_categories[]">
            <?php
                var_dump($feature_categories);
                function category_select($categories, $stap="",$feature_categories){
                    foreach($categories as $category):
                    ?>
                    <option <?=(in_array($category['id'],$feature_categories))? 'selected="selected"' : '' ?> value='<?=$category['id']?>'><?=$stap.$category['name']?></option>
                    <?php
                    if(isset($category['subcategories'])){
                        category_select($category['subcategories'], $stap."&nbsp;&nbsp;&nbsp;&nbsp;",$feature_categories);
                    }
                    endforeach;
                }
                echo category_select($categories, $stap="",$feature_categories);
            ?>
            </select>
        </div>
        <!-- Параметры страницы (The End)-->
    </div>
    
    <!-- Правая колонка свойств товара -->    
    <div id="column_right">
        
        <!-- Изображения товара -->    
        <div class="block layer images">
            <h2>
                Настройки свойства
            </h2>
            <ul>
                <li>
                    <input type="checkbox" name="in_filter" id="in_filter" <?=($feature['in_filter'] == '1')? 'checked="checked"' : ""?> value="1"> 
                    <label for="in_filter">Использовать в фильтре</label>
                </li>
            </ul>
            <!-- Параметры страницы (The End)-->
            <input class="button_green" type="submit" name="" value="Сохранить" />
        </div>
        
    </div>
    <!-- Правая колонка свойств товара (The End)--> 
</form>
































<style>

</style>
<?php
// Подключаем редактор
include('tinymce_init.php');
?>
<script>
$(function() {


    // Удаление изображений
    $(".images a.delete").click( function() {
        $("input[name='delete_image']").val('1');
        $(this).closest("ul").fadeOut(200, function() { $(this).remove(); });
        return false;
    });

    // Автозаполнение мета-тегов
    meta_title_touched = true;
    meta_keywords_touched = true;
    meta_description_touched = true;
    url_touched = true;
    
    if($('input[name="meta_title"]').val() == generate_meta_title() || $('input[name="meta_title"]').val() == '')
        meta_title_touched = false;
    if($('input[name="meta_keywords"]').val() == generate_meta_keywords() || $('input[name="meta_keywords"]').val() == '')
        meta_keywords_touched = false;
    if($('textarea[name="meta_description"]').val() == generate_meta_description() || $('textarea[name="meta_description"]').val() == '')
        meta_description_touched = false;
    if($('input[name="url"]').val() == generate_url() || $('input[name="url"]').val() == '')
        url_touched = false;
        
    $('input[name="meta_title"]').change(function() { meta_title_touched = true; });
    $('input[name="meta_keywords"]').change(function() { meta_keywords_touched = true; });
    $('textarea[name="meta_description"]').change(function() { meta_description_touched = true; });
    $('input[name="url"]').change(function() { url_touched = true; });
    
    $('input[name="name"]').keyup(function() { set_meta(); });
      
});

function set_meta()
{
    if(!meta_title_touched)
        $('input[name="meta_title"]').val(generate_meta_title());
    if(!meta_keywords_touched)
        $('input[name="meta_keywords"]').val(generate_meta_keywords());
    if(!meta_description_touched)
        $('textarea[name="meta_description"]').val(generate_meta_description());
    if(!url_touched)
        $('input[name="url"]').val(generate_url());
}

function generate_meta_title()
{
    name = $('input[name="name"]').val();
    return name;
}

function generate_meta_keywords()
{
    name = $('input[name="name"]').val();
    return name;
}

function generate_meta_description()
{
    if(typeof(tinyMCE.get("description")) =='object')
    {
        description = tinyMCE.get("description").getContent().replace(/(<([^>]+)>)/ig," ").replace(/(\&nbsp;)/ig," ").replace(/^\s+|\s+$/g, '').substr(0, 512);
        return description;
    }
    else
        return $('textarea[name=description]').val().replace(/(<([^>]+)>)/ig," ").replace(/(\&nbsp;)/ig," ").replace(/^\s+|\s+$/g, '').substr(0, 512);
}

function generate_url()
{
    url = $('input[name="name"]').val();
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
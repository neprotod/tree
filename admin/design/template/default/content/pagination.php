<?php
if($design->pages_count > 1):
$pages_count = $design->pages_count;
//Текущая
$current_page = $design->current_page;
if(isset(Request::$design->keyword))
    $separator = '&';
else
    $separator = '?';

// Количество выводимых ссылок на страницы
$visible_pages = 7;

// По умолчанию начинаем вывод со страницы 1
$page_from = 1;

// Если выбранная пользователем страница дальше середины "окна" - начинаем вывод уже не с первой
if($current_page > floor($visible_pages/2)){
    $page_from = max(1, $current_page-floor($visible_pages/2)-1);
}

//Если выбранная пользователем страница близка к концу навигации - начинаем с "конца-окно"
if($current_page > $pages_count - ceil($visible_pages/2)){
    $page_from = max(1, $pages_count-$visible_pages-1);
}

//До какой страницы выводить - выводим всё окно, но не более ощего количества страниц
$page_to = min($page_from + $visible_pages, $pages_count -1 );
//<script type="text/javascript" src="/media/js/ctrlnavigate.js"></script>
?>
<div class="pagination">
    <?php
        if($current_page == 2){
            ?>
                <a class="prev_page_link" href="<?=Url::query_root(array('page'=>'','scroll'=>''),TRUE,'auto')?>">&lt;</a>
            <?php
        }
    ?>
    <?php
        if($current_page > 2){
            ?>
                <a class="prev_page_link" href="<?=Url::query_root(array('page'=>$current_page - 1,'scroll'=>''),TRUE,'auto')?>">&lt;</a>
            <?php
        }
    ?>
    <a <?php if ($current_page==1){ ?> class="selected"<?php } ?> href="<?=Url::query_root(array('page'=>'','scroll'=>''),TRUE,'auto')?>">1</a>
    
    <?php
        for($i = $page_from;$i < $page_to;$i++):
        $p = $i+1;
        // Для крайних страниц "окна" выводим троеточие, если окно не возле границы навигации
        if(($p == $page_from+1 && $p!=2) || ($p == $page_to && $p != $pages_count-1)){
            ?>
                <a <?php if ($p==$current_page){ ?> class="selected"<?php } ?> href="<?=Url::query_root(array('page'=>$p,'scroll'=>''),TRUE,'auto')?>">...</a>
            <?php
        }else{
        
            ?>
                <a <?php if ($p==$current_page){ ?> class="selected"<?php } ?> href="<?=Url::query_root(array('page'=>$p,'scroll'=>''),TRUE,'auto')?>"><?=$p?></a>
            <?php
        }
    ?>
    
    <?php
        endfor;
    ?>
    <?php
        // ссылка на последнюю страницу отображается всегда
    ?>
        <a <?php if ($current_page==$pages_count){ ?> class="selected"<?php } ?> href="<?=Url::query_root(array('page'=>$pages_count,'scroll'=>''),TRUE,'auto')?>"><?=$pages_count?></a>
    <?php
        // Ссылка вперед если страница не последняя
        if($current_page < $pages_count){
            ?>
                <a class="next_page_link" href="<?=Url::query_root(array('page'=>$current_page + 1,'scroll'=>''),TRUE,'auto')?>">&gt;</a>
            <?php
        }
    ?>
    <?php
    // Ссылка вперед если страница не последняя
    if($pages_count > 1){
        ?>
            <a class="total_page" href="<?=Url::query_root(array('page'=>'all','scroll'=>''),TRUE,'auto')?>">Показать все</a>
        <?php
    }
    ?>
</div>
<?php
endif;
if($_GET['page'] == 'all'){
    ?>
        <div class="pagination">
            <a class="return_page" href="<?=Url::query_root(array('page'=>NULL,'scroll'=>''),TRUE,'auto')?>">Вывести постранично</a>
        </div>
    <?php
}
?>
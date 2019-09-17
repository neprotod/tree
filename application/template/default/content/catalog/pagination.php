
<?php
if($design->total_pages_num > 1):
$total_pages_num = $design->total_pages_num;
//Текущая
$current_page_num = $design->current_page_num;
if(isset(Request::$design->keyword))
    $separator = '&';
else
    $separator = '?';

// Количество выводимых ссылок на страницы
$visible_pages = 7;

// По умолчанию начинаем вывод со страницы 1
$page_from = 1;

// Если выбранная пользователем страница дальше середины "окна" - начинаем вывод уже не с первой
if($current_page_num > floor($visible_pages/2)){
    $page_from = max(1, $current_page_num-floor($visible_pages/2)-1);
}

//Если выбранная пользователем страница близка к концу навигации - начинаем с "конца-окно"
if($current_page_num > $total_pages_num - ceil($visible_pages/2)){
    $page_from = max(1, $total_pages_num-$visible_pages-1);
}

//До какой страницы выводить - выводим всё окно, но не более ощего количества страниц
$page_to = min($page_from + $visible_pages, $total_pages_num -1 );
//<script type="text/javascript" src="/media/js/ctrlnavigate.js"></script>
?>


<hr />
<div class="pagination">
    <?php
        if($current_page_num == 2){
            ?>
                <a class="prev_page_link" href="<?=$canonical?>">&lt;</a>
            <?php
        }
    ?>
    <?php
        if($current_page_num > 2){
            ?>
                <a class="prev_page_link" href="<?=$canonical?><?=$separator?>page=<?=$current_page_num - 1?>">&lt;</a>
            <?php
        }
    ?>
    <a <?php if ($current_page_num==1){ ?> class="selected"<?php } ?> href="<?=$canonical?>">1</a>
    
    <?php
        for($i = $page_from;$i < $page_to;$i++):
        $p = $i+1;
        // Для крайних страниц "окна" выводим троеточие, если окно не возле границы навигации
        if(($p == $page_from+1 && $p!=2) || ($p == $page_to && $p != $total_pages_num-1)){
            ?>
                <a <?php if ($p==$current_page_num){ ?> class="selected"<?php } ?> href="<?=$canonical?><?=$separator?>page=<?=$p?>">...</a>
            <?php
        }else{
        
            ?>
                <a <?php if ($p==$current_page_num){ ?> class="selected"<?php } ?> href="<?=$canonical?><?=$separator?>page=<?=$p?>"><?=$p?></a>
            <?php
        }
    ?>
    
    <?php
        endfor;
    ?>
    <?php
        // ссылка на последнюю страницу отображается всегда
    ?>
        <a <?php if ($current_page_num==$total_pages_num){ ?> class="selected"<?php } ?> href="<?=$canonical?><?=$separator?>page=<?=$total_pages_num?>"><?=$total_pages_num?></a>
    <?php
        // Ссылка вперед если страница не последняя
        if($current_page_num < $total_pages_num){
            ?>
                <a class="next_page_link" href="<?=$canonical?><?=$separator?>page=<?=$current_page_num + 1?>">&gt;</a>
            <?php
        }
    ?>
    <?php
    // Ссылка вперед если страница не последняя
    if($total_pages_num > 1){
        ?>
            <a class="total_page" href="<?=$canonical?><?=$separator?>page=all">Показать все</a>
        <?php
    }
    ?>
</div>
<?php
endif;
if(isset($_GET['page']) AND $_GET['page'] == 'all'){
    ?>
        <hr />
        <div class="pagination">
            <a class="return_page" href="<?=$canonical?>">Вывести постранично</a>
        </div>
    <?php
}
?>
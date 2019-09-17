<div class="padding other">
    <h1 class="other_head"><a href="/gallery">В галлерею</a> • <?=Request::$design->meta_title?></h1>
    <div id="gallery">

    <?php
    if(!empty($dirs)):
        foreach($dirs as $dir):
    ?>
            <a class="zoom image_box" rel="group" href="/<?=$gallery_full_path?>/<?=$dir?>"><img src="<?=Request::$design->resizeimage($dir, NULL, 100, 150,100, 0,NULL, $gallery_full_path, $gallery_resize)?>" /></a>
    <?php
        endforeach;
    else:
        ?>
            Фотографий нет
        <?php
    endif;
    ?>
        <div style="clear:both;"></div>
    </div>
</div>
<div class="padding other">
    <h1 class="other_head"><?=Request::$design->meta_title?></h1>
    <div id="gallery">
        <?php
        if(!empty($galleries)):
            foreach($galleries AS $gallery):
            $gallery_full_path = $gallery_path .'/'. $gallery['gallery_path'];
        ?>
        <div class="box">
            <div class="gallery_image">
                <a href="/gallery/<?=$gallery['url']?>"><img src="<?=Request::$design->resizeimage($gallery['img'], NULL, 199, 247,199, 0,NULL,$gallery_full_path, $gallery_resize)?>" width="247" height="199" /></a>
            </div>
            <a href="/gallery/<?=$gallery['url']?>"  class="string"><?=$gallery['name']?></a>
        </div>    
        <?php
            endforeach;
        else:
            ?>
                Нет галерей
            <?php
        endif;
        ?>
        <div style="clear:both;"></div>
    </div>
</div>
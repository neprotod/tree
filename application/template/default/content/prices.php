<?php
function FBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes?log($bytes):0)/log(1024));
    //echo $pow.'<br>';
    $pow = min($pow, count($units)-1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision).' '.$units[$pow];
}
$design = Request::$design;
$price = Module::factory('prices',TRUE);
$prices = $price->get_prices();
$folder = $price->folder;
?>
<style>
.table_prices a{
    position:relative;
}
.discet{
    width:23px;
    height:23px;
    background:url(<?=$folder?>/disk.jpg);
    position:absolute;
    right:-35px;
    margin-top:10px;
}
.box .string{
    font-size:30px !important;
}
.box span.string{
    color:#000 !important;
}
.content_prices .box {
    padding-top: 155px;
}
.image_prices > a{
    display:block;
}
.image_prices .sticker{
    position:absolute; 
    width:100%; 
    text-align:center; 
    font-size:40px; 
    font-weight:bold; 
    top:50%; 
    color:#F4F4F2;
}
.image_prices .sticker > div{
    position:relative; 
    margin-top:-30px; 
    padding:5px 0 10px;
}
.image_prices .sticker > div span{
    display:block; 
    position:relative; 
    z-idex:10;
}
.image_prices .sticker > div > div{
    position:absolute; 
    top:0; 
    bottom:0; 
    left:0; 
    right:1px; 
    background:rgba(0,0,0,0.55);
}
</style>
<div class="padding other">
    <h1 class="other_head"><?=$page['title']?></h1>
    <div id="prices">
        <table class="table_prices">
            <tbody>
                <tr>
                    <?php
                    if(!empty($prices)):
                    foreach($prices as $price):
                        if($price['visible'] == 0)
                            continue;
                        
                        $href = "";
                        if(!empty($price['price'])){
                            $href = $folder.'/'.$price['price'];
                        }
                    ?>
                    <td class="image_td_proces">
                        <div class="image_prices">
                            <a <?=(!empty($href))? "href=\"{$href}\"" : ''?>>
                                <?php
                                if($price['no_name'] != 0):
                                ?>
                                <img src="<?=$design->resizeimage($price['img'],NULL,361,424,NULL,NULL,NULL,trim($folder,'/'))?>"/>
                                <?php
                                else:
                                ?>
                                <div class="sticker">
                                    <div>
                                        <div></div>
                                        <span><?=$price['name']?></span>
                                    </div>
                                </div>
                                <img src="<?=$design->resizeimage($price['img'],NULL,361,424,NULL,NULL,NULL,trim($folder,'/'))?>"/>
                                <?php
                                endif;
                                ?>
                            </a>
                        </div>
                    </td>
                    <td class="content_td_proces">
                        <div class="content_prices">
                            <?php
                            if(!empty($price['price'])):
                            ?>
                                <div class="box">
                                    <a class="string" href="<?=$folder.'/'.$price['price']?>">
                                        Скачать 
                                        <div class="discet"></div>
                                    </a>
                                    <span class="information">Размер: <?=@FBytes(filesize(trim($folder.'/'.$price['price'],'/')))?></span>
                                </div>
                            <?php
                            else:
                            ?>
                                <div class="box">
                                    <span class="string">Прайса нет</span>
                                </div>
                            <?php
                            endif;
                            ?>
                        </div>
                    </td>
                </tr>
                <?php
                endforeach;
                else:    
                ?>
                <tr>
                    <td>
                        Нет прайсов
                    </td>
                </tr>
                <?php
                endif;    
                ?>
            </tbody>
        </table>
    </div>
</div>
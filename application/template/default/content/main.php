<div class="padding">
    <div class="content_text">
        <style type="text/css">
            @font-face {
                font-family:"lite";
                src: url('<?=Request::$design->root?>/font/segoeuil.ttf') format('truetype');
            }
            #main_text .string{
                font-family: "lite", "Segoe UI", sans-serif;
            }
        </style>
        <div id="main_text" class="text_box left">
            <h1 class="text_center h1"><?=$page['title']?></h1>
            <?=$page['body']?>
        </div>
        <div class="inline_dotted"></div>
        <div class="news_box left">
            <div id="news">
                <div class="box_padding">
                    <a class="h2" href="/news/cheren">Поступление новых черенков 2014 года</a>
                </div>
                <img class="img_news" src="/media/original/news/img_news.jpg" width="219" height="152" />
                <p>
                    Новый привоз из Польши 25.05.14, черенки плодовых деревьев, кустарники и многое другое. 
                </p>
                
                <div id="scroll">
                    <a href="/news/" class="box"><span class="string">Еще новости</span></a>
                </div>    
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <div id="slider">
        <a class="link_image" href="/catalog/woods/softwoods"><img src="<?=Request::$design->root?>/img/main4.jpg" /><a>
        <img src="<?=Request::$design->root?>/img/wave.jpg" />
    </div>
    <?php
        // Загружаем советы о растениях
        $get = Module::factory('page',TRUE);
        $plants = $get->get_page('useful-tips');
    ?>
    <div id="page_bottom">
        <div id="page_background_top">
            <h3 class="text_left_h3"><a href="<?=$plants['url']?>"><?=$plants['title']?></a></h3>
            <div class="advice_left">
                <div id="advice_img_1"></div>
                <div class="advice">
                    <div class="advice_name">cовет №1</div>
                    <div class="advice_body">"Все хвойные растения предпочтительно сажать в кислую почву, используя мульчу для закисления"</div>
                </div>
            </div>
            <div class="advice_right">
                <div id="advice_img_2"></div>
                <div class="advice">
                    <div class="advice_name">cовет №2</div>
                    <div class="advice_body">"Гортензия одно из немногих растений предпочитающее посадку в темные места участка"</div>
                </div>
            </div>
        </div>
        <div id="plant">
            <div class="advice_left">
                <div id="advice_img_3"></div>
                <div class="advice">
                    <div class="advice_name">cовет №3</div>
                    <div class="advice_body">"Самые неприхотливые почвопокровники это всевозможные очитки, ацена густинолистная, обриетта и тимьян"</div>
                </div>
            </div>
            <div class="advice_right">
                <div id="advice_img_4"></div>
                <div class="advice">
                    <div class="advice_name">cовет №4</div>
                    <div class="advice_body">"Туи первые 2-3 зимы нужно укрывать полипропеленовой тканью или другим укрывным матерьялом"</div>
                </div>
            </div>
        </div>
        <div style="clear:both;"></div>
    </div>
    
</div>
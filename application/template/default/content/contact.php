<?php
    
    $massage = Request::$design->user->contact_mail($_POST,Registry::i()->settings['order_email'],'Письмо с '.Registry::i()->settings['site_name']);
    //vesta-sad.nsk@mail.ru

?>
<div class="padding other">
    <script src="http://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
    <h1 class="other_head"><?=$page['title']?></h1>
    <?php
    if($massage['error'] === TRUE):
    ?>
    <div class="massage_box">
        <p class="error">Вы ошиблись в форме</p>
        <p class="error string">Проверьте вводимые данные еще раз</p>
    </div>
    <?php
    endif;
    ?>
    <?php
    if(Request::get('result') == 'complete'):
    ?>
    <div class="massage_box">
        <p class="complete">Форма отправлена</p>
    </div>
    <?php
    endif;
    ?>
    <div id="other_content">
        <!-- Карта -->
        <div id="yandex_map" style="width: 952px; height: 450px; margin-left:-35px;">
            <div class="inline_map" >
                <div class="addr_map">
                    <div class="box">
                        Россия, Новосибирск, Колыванское шоссе, 1а, ООО "Веста"
                    </div>
                </div>
                <div class="text_map">
                    <div class="map_phone">
                        <span class="string">Мобильный телефон:</span>
                        <span class="phone"><?=Registry::i()->settings['phone']?></span>
                    </div>
                    <div class="map_email">
                        <span class="string">Электронная почта:</span>
                        <span class="email"><?=Registry::i()->settings['order_email']?></span>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            ymaps.ready(init);
            var myMap, myPlacemark;

            function init(){ 
                myMap = new ymaps.Map("yandex_map", {
                    center: [55.043248,82.776295],
                    zoom: 14
                }); 
                
               myPlacemark = new ymaps.Placemark([55.042903,82.798158], { 
                    hintContent: 'ООО Веста'
                });
                
                myMap.geoObjects.add(myPlacemark);
            }
        </script>
        <div id="bottom_contact">
            <div id="contact_information">
                <p class="h3">Наше здание</p>
                <div class="house"></div>
                <p class="bold_string">Как до нас добратся</p>
                <div class="transport">
                    <span class="string">На транспорте:</span>
                    <div class="box">
                        <p class="head_box">От телецентра</p>
                        <div class="padding_box">
                            <span class="string">Автобусы</span><span>120, 1999k</span>
                        </div>
                        <div class="padding_box">
                            <span class="string">Маршрутка</span><span>320</span>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
                <div class="auto">
                    <span class="string">На машине:</span>
                    <div class="box">
                        От пл. Карла Маркса через Ватутина, по улице Станиславского 230м, въезд на перекресток с круговым движением ул Станционная, съезд с перекрестка с круговым движением, направо 5км, прямо 69м, вьезд на перекрестокс круговым движением 98м, прямо 1,1 км, Колыванское шоссе, направо 42м.
                    </div>
                </div>
            </div>
            
            <form id="contact_form" method="POST" action="/contact">
                <p class="h3">Обратная связь</p>
                <span class="string">Ваше имя и фамилия</span>
                <input value="<?=Request::param($_POST['name'],TRUE)?>" type="text" class="contact_input" name="name" />
                <span class="string">Телефон*</span>
                <?php
                if(isset($massage['phone'])):
                ?>
                <div class="contact_error"><?=$massage['phone']?></div>
                <?php
                endif;
                ?>
                <input style="<?=(isset($massage['phone']))? "border:1px solid #F30537;" :'' ?>"  value="<?=Request::param($_POST['phone'],TRUE)?>" type="text" class="contact_input" name="phone" />
                <span class="string">Электронная почта*</span>
                <?php
                if(isset($massage['email'])):
                ?>
                <div class="contact_error"><?=$massage['email']?></div>
                <?php
                endif;
                ?>
                <input style="<?=(isset($massage['email']))? "border:1px solid #F30537;" :'' ?>"  value="<?=Request::param($_POST['email'],TRUE)?>" type="text" class="contact_input" name="email" />
                <span class="string">Сообщение</span>
                <textarea class="contact_input area" name="massage"><?=Request::param($_POST['massage'],true)?></textarea>
                <button id="buy_button" type="submit"><span class="string">Отправить</span></button>
            </form>
        </div>
    </div>
    <div style="clear:both;"></div>
</div>
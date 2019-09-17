<?php
$design = Request::$design;
$settings = Request::param(Request::$design->settings);
$lock = $settings['lock'];
unset($settings['lock']);
?>
<!-- Вкладки -->
<?php
$design->tabs('start');
?>
<li class="active">
    <a href="<?=Url::query_root(array('module'=>'settings'))?>">Настройки</a>
</li>
<?php
$design->tabs('end');
/*Title*/
Registry::i()->meta_title = 'Настройки';
?>
<div id="middle">
    <form id="product" enctype="multipart/form-data" method="post">
        <input type="hidden" name="session_id" value="<?=session_id()?>">
        <div class="block">
            <h2>Настройки сайта</h2>
            <ul>
                <li>
                    <label class="property">Имя сайта</label>
                    <input class="inp" type="text" value="<?=Request::param($settings['site_name']['value'],TRUE)?>" name="settings[<?=$settings['site_name']['setting_id']?>][value]" />
                    <?php
                    unset($settings['site_name']);
                    ?>
                </li>
                <li>
                    <label class="property">Имя компании</label>
                    <input class="inp" type="text" value="<?=Request::param($settings['company_name']['value'],TRUE)?>" name="settings[<?=$settings['company_name']['setting_id']?>][value]" />
                    <?php
                    unset($settings['company_name']);
                    ?>
                </li>
                <li>
                    <label class="property">E-mail сайта</label>
                    <input class="inp" type="text" value="<?=Request::param($settings['order_email']['value'],TRUE)?>" name="settings[<?=$settings['order_email']['setting_id']?>][value]" />
                    <?php
                    unset($settings['order_email']);
                    ?>
                </li>
                <li>
                    <label class="property">Телефон сайта</label>
                    <input class="inp" type="text" value="<?=Request::param($settings['phone']['value'],TRUE)?>" name="settings[<?=$settings['phone']['setting_id']?>][value]" />
                    <?php
                    unset($settings['phone']);
                    ?>
                </li>
            </ul>
        </div>

        <div class="block layer">
            <h2>Настройки каталога</h2>
            <li>
                <label class="property">Товаров на страницу</label>
                <input class="inp" type="text" value="<?=Request::param($settings['products_num']['value'],TRUE)?>" name="settings[<?=$settings['products_num']['setting_id']?>][value]" />
                <?php
                unset($settings['products_num']);
                ?>
            </li>
            <li>
                <label class="property">Товаров на странице админки</label>
                <input class="inp" type="text" value="<?=Request::param($settings['products_num_admin']['value'],TRUE)?>" name="settings[<?=$settings['products_num_admin']['setting_id']?>][value]" />
                <?php
                unset($settings['products_num_admin']);
                ?>
            </li>
            <li>
                <label class="property">Максимум товаров в заказе где не определенно количество</label>
                <input class="inp" type="text" value="<?=Request::param($settings['max_order_amount']['value'],TRUE)?>" name="settings[<?=$settings['max_order_amount']['setting_id']?>][value]" />
                <?php
                unset($settings['max_order_amount']);
                ?>
            </li>
            <li>
                <label class="property">Единицы измерения товаров</label>
                <input class="inp" type="text" value="<?=Request::param($settings['units']['value'],TRUE)?>" name="settings[<?=$settings['units']['setting_id']?>][value]" />
                <?php
                unset($settings['units']);
                ?>
            </li>
        </div>

        <div class="block layer">
            <h2>Настройки изображений</h2>
            <div class="description">
                Изменение в этом месте могут повлечь исчезновение всех картинок. Если вы не знаете зачем этот раздел, не трогайте.
            </div>
            <li>
                <label class="property">Товары без картинки</label>
                <input class="inp" type="text" value="<?=Request::param($settings['no-image']['value'],TRUE)?>" name="settings[<?=$settings['no-image']['setting_id']?>][value]" />
                <?php
                unset($settings['no-image']);
                ?>
            </li>
            <li>
                <label class="property">Директория брендов</label>
                <input class="inp" type="text" value="<?=Request::param($settings['brands_images_dir']['value'],TRUE)?>" name="settings[<?=$settings['brands_images_dir']['setting_id']?>][value]" />
                <?php
                unset($settings['brands_images_dir']);
                ?>
            </li>
            <li>
                <label class="property">Директория оригинальных изображений</label>
                <input class="inp" type="text" value="<?=Request::param($settings['original']['value'],TRUE)?>" name="settings[<?=$settings['original']['setting_id']?>][value]" />
                <?php
                unset($settings['original']);
                ?>
            </li>
            <li>
                <label class="property">Директория измененных изображений</label>
                <input class="inp" type="text" value="<?=Request::param($settings['resize']['value'],TRUE)?>" name="settings[<?=$settings['resize']['setting_id']?>][value]" />
                <?php
                unset($settings['resize']);
                ?>
            </li>
        </div>

        <div class="block layer">
            <h2>Отстальные настройки</h2>
            <div class="description">
                Изменение в этом месте могут повлечь ненужные вам изменения. Если вы не знаете зачем этот раздел, не трогайте. <br />
                РАЗДЕЛ СГЕНЕРИРОВАН АВТОМАТИЧЕСКИ.
            </div>
            <?php
            foreach($settings as $seting):
            ?>
            <li>
                <label class="property"><?=Request::param($seting['name'],TRUE)?></label>
                <input class="inp" type="text" value="<?=Request::param($seting['value'],TRUE)?>" name="settings[<?=$seting['setting_id']?>][value]" />
            </li>
            <?php
            endforeach;
            ?>
        </div>
        <?php
        if($_SESSION['user'] == md5('admin')):
        ?>
        <div class="block layer">
            <li>
                <label class="property">Lock-Unlock</label>
                <input class="inp" type="text" value="<?=Request::param($lock['value'])?>" name="settings[<?=$lock['setting_id']?>][value]" />
            </li>
        </div>
        <?php
        endif;
        ?>
        <input class="button_green button_save" type="submit" name="" value="Сохранить" />
    </form>
</div>
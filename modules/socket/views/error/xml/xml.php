<style type="text/css">
.highlight {
    background: none repeat scroll 0 0 #7CED7C;
}
.line_separator{
    width:50%;
    margin-left:0%;
    border-bottom:1px solid;
}
</style>
<div id="navigation">
    <h3>Навигация</h3>
    <a class="link" href="/<?=Registry::i()->host?>">К статусу</a>
</div>
<div>
    <p>Количество ошибок: <?=count($errors)?></p>
</div>
<table id="error_table">
    <?php
    if(!empty($errors))
        foreach($errors as $id => $error):
    ?>
    <tr>
        <td>
            <div class="box">
                <form method="post" class="form">
                    <input type="submit" value="Удалить" />
                    <input type="hidden" name="drop" value="<?=$id?>" />
                </form>
                <p><b>Тип:</b> <?=$error['type']?></p>
                <p><b>Код ошибка:</b> <?=$error['code']?></p>
                <p><b>Сообщение:</b> <?=$error['message']?></p>
                <div class="line_separator"></div>
                
                <p><b>Класс:</b> <?=$error['class']?></p>
                <p><b>Дата ошибки:</b> <?=$error['date']?></p>
                <div class="line_separator"></div>
                
                <p><b>Файл:</b> <?=$error['file']?></p>
                <p><b>На линии:</b> <?=$error['line']?></p>
                <p><?=$error['debug']?></p>
                <p style="border:1px solid #000; padding:5px;">Полный путь читается с конца до верха</p>
                <?php
                $true = FALSE;
                if(!empty($error['trace'])):
                    $trace = unserialize($error['trace']);
                    foreach((array)$trace as $trac):
                ?>
                <?php
                        if($true === TRUE):
                ?>
                <pre>
                     |  |
                    _|  |_
                    \    /
                     \  /
                      \/
                </pre>
                <?php
                        else:
                            $true = TRUE;
                        endif;
                ?>
                <p>Файл: <b><?=(isset($trac['file']))? $trac['file']: ''?></b></p>
                <p>Линия запуска: <b><?=(isset($trac['line']))? $trac['line'] : ''?></b></p>
                <p>Функция которую запустили: <b><?=(isset($trac['function']))? $trac['function'] : '' ?></b></p>
                <p>Класс запускаемой функции: <b><?=(isset($trac['class']))? $trac['class'] :'' ?></b></p>
                <?php
                    endforeach;
                endif;
                
                ?>
            </div>
            <hr />
        </td>
    </tr>
    <?php
        endforeach;
    ?>
</table>
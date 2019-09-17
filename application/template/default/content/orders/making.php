<div class="padding other order">
    <h1>Оформление</h1> 
    <br />
    <p>Наши менеджеры забронируют товары и свяжутся с вами для уточнения деталей.</p> 
    <form id="contact_form" method="POST">
        <span class="string">Ваше имя и фамилия*</span>
        <input type="text" class="contact_input <?=($error['name'])? 'error':''?>" name="name" value="<?=Request::param($_POST['name'],TRUE)?>" />
        <span class="string">Телефон*</span>
        <input type="text" class="contact_input <?=($error['phone'])? 'error':''?>" name="phone" value="<?=Request::param($_POST['phone'],TRUE)?>" />
        <span class="string">Электронная почта (желательно)</span>
        <input  type="text" class="contact_input" name="email" value="<?=Request::param($_POST['email'],TRUE)?>" />
        <span class="string">Город</span>
        <input  type="text" class="contact_input city" name="city" value="<?=Request::param($_POST['city'],TRUE)?>" />
        <span class="string">Комментарий</span>
        <textarea class="contact_input area" name="massage"><?=Request::post('massage')?></textarea>
        <button id="buy_button" type="submit"><span class="string">Оформить</span></button>
    </form>
</div>
<?php
namespace crm;
use processors\uFunc;
use uAuth\common;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uAuth/classes/common.php";

class call_script {
    public $user_id;
    public $uFunc;
    public $uSes;
    public $uAuth;
    private $uCore;
    private function check_data() {

    }

//    public function text($str) {
//        return $this->uCore->text(array('uPage','setup_uPage_page'),$str);
//    }

    public function print_script() {
        $this->user_id=$this->uSes->get_val("user_id");
        $caller_info_obj=$this->uAuth->user_id2user_data($this->user_id,"firstname");
        if(isset($caller_info_obj->firstname)) $caller_name=$caller_info_obj->firstname;
        else $caller_name='<span class="text-muted">"<i>Ваше имя</i>"';
        $page_url = u_sroot . $this->uCore->mod . '/' . $this->uCore->page_name;
        $company_name='Компания MAD';
        $company_phone='+78124081269';
        $company_site='madwww.ru';

        $call_meeting = '<p><a href="' . $page_url . '#Р_1">Встреча/Звонок&nbsp;&nbsp;Р-1</a></p>';
        $non_core_client = '<p><a href="' . $page_url . '#Р_4">Непрофильный клиент Р-4</a></p>';

        $connecting = '<p><a href="' . $page_url . '#П_10">Соединяю&nbsp;&nbsp; П-10</a></p>';
        $listening_to_you = '<p><a href="' . $page_url . '#П_10_0">Я слушаю/Со мной&nbsp;&nbsp; П-10_0</a></p>';
        $listening_to_you_1 = '<p><a href="' . $page_url . '#П_10_1">Я слушаю/Со мной&nbsp;&nbsp; П-10_1</a></p>';
        $connecting_2 = '<p><a href="' . $page_url . '#П_10_2">Соединяю/Перезвон быстрый&nbsp;&nbsp; П-10_2</a></p>';
        $connecting_3 = '<p><a href="' . $page_url . '#П_10_3">Соединяю/Перезвон долгий&nbsp;&nbsp; П-10_3</a></p>';
        $what_do_you_offer = '<p class="text-warning"><a href="' . $page_url . '#П_11">Что вы предлагаете/Подробнее&nbsp;&nbsp;П-11</a></p>';
        $have_not_heard_before = '<p><a href="' . $page_url . '#П_11_1">Не слышали о вас/Подробнее о компании&nbsp;&nbsp; П-11_1</a></p>';
        $your_advantages = '<p><a href="' . $page_url . '#П_11_2">Ваши преимущества/Выгоды&nbsp;&nbsp; П-11_2</a></p>';
        $we_do_not_need_it = '<p class="text-warning"><a href="' . $page_url . '#П_12">Нам ничего не надо/Отказ&nbsp;&nbsp; П-12</a></p>';
        $we_work_with_another = '<p class="text-warning"><a href="' . $page_url . '#П_13">Работаем с другими&nbsp;&nbsp П-13</a></p>';
        $have_no_money = '<p><a href="' . $page_url . '#П_14">У нас нет денег/Нет бюджета&nbsp;&nbsp; П-14</a></p>';
        $how_much = '<p><a href="' . $page_url . '#П_14_1">Сколько стоит&nbsp;&nbsp; П-14_1</a></p>';
        $expensive = '<p><a href="' . $page_url . '#П_14_2">Дорого/Предложили дешевле&nbsp;&nbsp; П-14_2</a></p>';
        $we_are_not_interesting_for_you = '<p><a href="' . $page_url . '#П_14_3">Мы вам не интересны/Маленькая компания&nbsp;&nbsp; П-14_3</a></p>';
        $wrong_contact = '<p><a href="' . $page_url . '#П_15">Ошибочный ЛПР/ЛПР нет на месте&nbsp;&nbsp; П-15</a></p>';
        $have_no_time = '<p class="text-warning"><a href="' . $page_url . '#П_15_1">Нет времени/Не удобно разговаривать&nbsp;&nbsp; П-15_1</a></p>';
        $send_an_offer = '<p><a href="' . $page_url . '#П_16">Пришлите КП,  факс, e’mail&nbsp;&nbsp; П-16</a></p>';
        $we_will_call_back = '<p class="text-warning"><a href="' . $page_url . '#П_17">Дайте телефон/Сами перезвоним&nbsp;&nbsp; П-17</a></p>';
        $already_have = '<p class="text-warning"><a href="' . $page_url . '#П_18">Уже купили/Сейчас не актуально&nbsp;&nbsp; П-18</a></p>';
        $we_have_to_think = '<p class="text-warning"><a href="' . $page_url . '#П_20">Надо подумать/Посоветоваться&nbsp;&nbsp; П-20</a></p>';
        $marry_on_me = '<p><a href="' . $page_url . '#П_21">Выходите за меня  замуж&nbsp;&nbsp; П-21</a></p>';
        $work_on_me = '<p><a href="' . $page_url . '#П_22">Переходите ко  мне работать/сколько получаете&nbsp;&nbsp; П-22</a></p>';
        $universal_answer = '<p><a href="' . $page_url . '#П_24">Универсальный  ответ&nbsp;&nbsp; П-24</a></p>';
        $where_have_you_got_my_number = '<p><a href="' . $page_url . '#П_26">Откуда у Вас  этот номер&nbsp;&nbsp; П-26</a></p>';
        $parent_company_decides = '<p><a href="' . $page_url . '#П_27">Решает головная  организация&nbsp;&nbsp; П-27</a></p>';
        $already_work_with_you = '<p><a href="' . $page_url . '#П_30">Уже работаем с  вами&nbsp;&nbsp; П-30</a></p>';
        $worked_with_you_previously = '<p><a href="' . $page_url . '#П_31">Работали с Вами, не понравилось&nbsp;&nbsp; П-31</a></p>';
        $what_is_your_location = '<p><a href="' . $page_url . '#П_32">Где вы находитесь&nbsp;&nbsp; П-32</a></p>';

        $c2 = '<p><a href="' . $page_url . '#С_2">Нет (<i>либо ответивший на звонок&nbsp;сообщает название компании, не совпадающее с названием в базе</i>) С-2</a></p>';
        $c4 = '<p><a href="' . $page_url . '#С_4">Да <i>(либо ответивший на звонок  сообщает название компании, совпадающее с названием в базе</i>)&nbsp;&nbsp; С-4</a></p>';
        $who_to_connect = '<p><a href="' . $page_url . '#С_4_1">С кем именно соединить?&nbsp;&nbsp; С-4_1</a></p>';
        $what_do_you_want = '<p><a href="' . $page_url . '#С_4_2">Конкретнее, что хотите/Что вы предлагаете&nbsp;&nbsp; С-4_2</a></p>';
        $do_you_work_with_us = '<p><a href="' . $page_url . '#С_4_3">Вы с нами работаете?&nbsp;&nbsp; С-4_3</a></p>';
        $what_is_the_subject = '<p><a href="' . $page_url . '#С_5">По какому вопросу&nbsp;&nbsp; С-5 </a></p>';
        $send_your_offer = '<p><a href="' . $page_url . '#С_6">Пришлите предложение&nbsp;&nbsp; С-6</a></p>';
        $we_will_call_back_secretary = '<p><a href="' . $page_url . '#С_6_1">Мы сами перезвоним Вам/Оставьте телефон&nbsp;&nbsp; С-6_1</a></p>';
        $send_your_offer_again = '<p><a href="' . $page_url . '#С_6_2">Пришлите предложение (а мы уже  присылали)&nbsp;&nbsp; С-6_2</a></p>';
        $we_do_not_need_it_secretary = '<p><a href="' . $page_url . '#С_7">Нам ничего не нужно /Отказ&nbsp;&nbsp; С-7</a></p>';
        $already_have_secretary = '<p><a href="' . $page_url . '#С_8">Уже купили недавно&nbsp;&nbsp; С-8 </a></p>';
        $what_is_your_location_secretary = '<p><a href="' . $page_url . '#С_9">Где вы находитесь&nbsp;&nbsp; С-9</a></p>';
        $can_not_connect = '<p><a href="' . $page_url . '#С_10">Не могу соединить&nbsp;&nbsp; С-10</a></p>';
        $universal_answer_secretary = '<p><a href="' . $page_url . '#С_11">Универсальный ответ&nbsp;&nbsp; С-11</a></p>';
        $specific_questions = '<p><a href="' . $page_url . '#С_12">Специфические вопросы&nbsp;&nbsp; С-12</a></p>';

        $non_core_client_1 = '<p><a href="' . $page_url . '#К_1">Непрофильная организация&nbsp;&nbsp;&nbsp;&nbsp; К-1</a></p>';
        $responder_is_busy = '<p><a href="' . $page_url . '#К_6">Человек занят/Нет на месте&nbsp;&nbsp; К-6</a></p>';
        $non_core_client_7 = '<p><a href="' . $page_url . '#К_7">Непрофильная организация&nbsp;&nbsp;&nbsp;&nbsp; К-7</a></p>';

        $what_is_the_secret='<p><a href="' . $page_url . '#U_1">В чем подвох&nbsp;&nbsp;&nbsp;&nbsp; U-1</a></p>';
        $pts='<h4>Точки соприкосновения:</h4>
        
        <p><b><u>Пилот:</u></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span>Я звоню Вам, потому что Вы <span class="text-muted"><i> занимаетесь строительством/ кофейня</i></span>,<br> правильно? А у нас в компании как раз сейчас запущен пилотный проект по работе с <span class="text-muted"><i>надежными застройщиками/кофейнями Петербурга</i></span>,<br>, который приводит <span class="text-muted"><i>новых клиентов/посетителей</i></span></p>
        
        <p><b><u>Были у вас:</u></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span>Я звоню Вам, потому что как раз недавно заходили к вам - очень понравилось <span class="text-muted"><i>кофе/выпечка</i></span>.<br>
         Хотим с вами поработать.</p>
        
        <p><b><u>Положительный опыт:</u></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span>Я звоню Вам, потому что мы ранее работали с <span class="text-muted"><i>(кофейнями)</i></span> - и нам, и клиентам очень понравилось работать вместе. Как раз хотел обсудить работу с вами</p>
        
        <p><b><u>Открыл группу/Объявление:</u></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span>я сейчас нахожусь в вашей группе ВК и вижу, что вы через нее ищите клиентов. Как раз хочу обсудить этот вопрос...</p>
        
        <p><b><u>Похвала:</u></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span>я сейчас нахожусь в вашей группе ВК - вы занимаетесь <span class="text-muted"><i>интересным/добрым</i></span> делом - <span class="text-muted"><i>назвать дело</i></span>. Очень захотелось с вами поработать</p>
                    <p><span class="text-muted">(пауза - не тараторить)</span></p>';

        $beginning_secretary='<li><a href="'.$page_url.'#С_1">Начало разговора/ Секретарь&nbsp;&nbsp; С-1</a></li>';
        $recall_after_email_secretary='<li><a href="'.$page_url.'#С_1_1">Перезвон после отправки информационных материалов&nbsp;&nbsp; С-1_1</a></li>';
        
        $beginning_lpr='<li><a href="'.$page_url.'#П_10">Начало разговора/ЛПР&nbsp;&nbsp; П-10</a></li>';
        $recall_after_email_lpr='<li><a href="'.$page_url.'#П_10_0">Перезвон ЛПР после отправки информационных материалов</a>&nbsp;&nbsp; П-10_0</li>';


        ?>
        <style type="text/css">
            .table td {
                width: 50%;
            }
            .answers_menu p {
                margin:0;
                padding:0;
                line-height:1.4em;
            }
        </style>


        <h1 class="page-header">Сценарий переговоров с клиентом (юридическое лицо) <br>
            <small>Назначение телефонных переговоров для Компании MAD</small>
        </h1>


        <h3>Видео тренинг - обязательно к просмотру!</h3>
        <p><a href="https://www.youtube.com/watch?v=q707tQ-9olk&feature=youtu.be" target="_blank">РЕАЛЬНЫЕ холодные звонки. Примеры продаж веб-услуг по телефону</a></p>
        <p><a href="https://www.youtube.com/watch?v=XSAnAj0vsX0" target="_blank">Техника ПТС. Сильнейшая техника продаж. 5 вариантов</a></p>

        <a name="Структура_сценария"></a>

        <h2>Структура сценария</h2>

        <h4><a href="<?= $page_url ?>#Цели">Цели сценария</a></h4>
        <ul>
            <li><a href="<?= $page_url ?>#Целевой_сегмент_клиентов">Целевой сегмент клиентов</a></li>
            <li><a href="<?= $page_url ?>#Портрет_клиента">Портрет клиента</a></li>
            <li><a href="<?= $page_url ?>#Непрофильный_клиент">Непрофильный клиент</a></li>
            <li><a href="<?= $page_url ?>#Возможные_ЛПР">Возможные ЛПР</a></li>
            <li><a href="<?= $page_url ?>#Условные_обозначения_в_сценарии">Условные обозначения в сценарии</a></li>
        </ul>

        <h4><a href="<?= $page_url ?>#I_Этап">I ЭТАП "Обход секретаря, выявление ЛПР и его контактных данных"</a></h4>
        <ul>
            <?=$beginning_secretary?>
            <?=$recall_after_email_secretary?>
            <li><a href="<?= $page_url ?>#С_1_2">Перезвон/ Перезвон после отказа ЛПР&nbsp;&nbsp; С-1_2</a></li>
            <li><a href="<?= $page_url ?>#С_1_3">Перезвон/Без имени ЛПР не соединяем/ Корпоративная политика&nbsp;&nbsp; С-1_3</a></li>
        </ul>

        <h4><a href="<?= $page_url ?>#II_Этап">II ЭТАП "Работа с ЛПР"</a></h4>
        <ul>
            <?=$beginning_lpr?>
            <?=$recall_after_email_lpr?>
            <li><a href="<?= $page_url ?>#П_10_2">Перезвон короткий после отказа ЛПР (инфо не высылали)&nbsp;&nbsp;&nbsp; П-10_2</a></li>
            <li><a href="<?= $page_url ?>#П_10_3">Перезвон через длительное время&nbsp;&nbsp; П-10_3</a></li>
        </ul>

        <h4><a href="<?= $page_url ?>#III_Этап">III ЭТАП "Завершение"</a></h4>

        <hr>

        <a name="Цели"></a>
        <h2>Цели сценария</h2>
        <p><b>Основная цель (одна):</b> Назначение телефонных переговоров с ведущим специалистом.</p>
        <p><b>Вторичные цели: </b>Выявление ЛПР и его контактных данных, отправка информационных материалов (презентация, прайсы, КП), сбор информации о клиенте (портрет клиента).</p>

        <a name="Целевой_сегмент_клиентов"></a>
        <h2>Целевой сегмент клиентов</h2>
        <ul>
            <li>Маленькие организации</li>
            <li>Частники</li>
            <li>Молодые организации</li>
            <li>Любые виды деятельности</li>
            <li>Юридические и физические лица</li>
        </ul>


        <a name="Портрет_клиента"></a>
        <a name="_Возможные_ЛПР"></a>
        <a name="Непрофильный_клиент"></a><a name="_Непрофильный_клиент"></a>
        <h2>Портрет клиента</h2>

        <h3>Вопросы по непрофильности:</h3>

        <ul>
            <li>У вас есть сайт? - <b>Да</b></li>
        </ul>

        <a name="Возможные_ЛПР"></a><a name="_Портрет_клиента"></a>
        <h2>Возможные ЛПР</h2>

        <ul>
            <li>Руководитель</li>
            <li>Кто занимается группой, рекламой</li>
        </ul>

        <a name="Условные_обозначения_в_сценарии"></a>
        <h2>Условные обозначения в сценарии</h2>

        <ol>
            <li>Колл-менеджер читает только тот текст, который написан шрифтом без курсивного начертания: черным или серым цветом; обычным или полужирным...<br>
                Например: "Добрый день! Это компания <i><span class="text-muted">"название компании"</span></i>?".&nbsp;&nbsp;"<span class="text-muted">Как Вас зовут,
скажите, пожалуйста?</span>".</li>

            <li>Текст, написанный черным цветом обычным шрифтом без курсивного начертания, является для колл-менеджера обязательным для чтения. <br>
                Например: "Подскажите, пожалуйста, как правильно называется Ваша должность?".</li>

            <li>Текст, который написан серым цветом обычным шрифтом без курсивного начертания, колл-менеджер читает по ситуации. <br>
                Например: <span class="text-muted">"Как Вас зовут, скажите, пожалуйста?". </span></li>

            <li>В процессе переговоров на определенном этапе необходимо выяснить имя секретаря. Если колл-менеджер уже выяснил имя секретаря, то перейдя на следующую ссылку, содержащий этот текст, снова спрашивать имя секретаря не нужно.</li>

            <li>Если какое-то слово в тексте выделено полужирным начертанием, значит, на этом слове нужно сделать интонационный акцент. <br>
                Например: "Мы можем предложить вам более <b>выгодные </b>условия!". В данном случае интонационный акцент необходим на слове "<b>выгодные</b>".</li>

            <li>Большая буква в середине слова означает ударение на эту букву.<br>
                Например: "оптОвый", "звонИт", "стОит", "стоИт".</li>

            <li>Текст, написанный курсивом, колл-менеджер не читает.<br>
                Текст, написанный курсивным начертание является указанием для колл-менеджера или побуждением к определенному действию. <br>
                Например: "Добрый день! Это компания <i><span class="text-muted">"название компании"?" </span></i><br>
                Вместо слов<i> <span class="text-muted">"название компании" </span></i>колл-менеджер должен произнести название организации клиента, которому звонит. Если звоним в компанию "Ромашка", то читаем так: "Добрый день! Это компания <span class="text-muted">Ромашка<i>"?" </i></span><br>
                <b>&nbsp;ИЛИ</b><br>
                Например: <a href="<?= $page_url ?>#П31"><i><span class="text-muted">Результат "<b>Отказ ЛПР"</b></span></i></a><span class="text-muted"> + </span><a href="<?= $page_url ?>#П31"><i><span class="text-muted">Оставить комментарий для следующего оператора. При перезвоне спросить ЛПР к телефону</span></i>.</a></li>

            <li>
                <p>Текст, написанный подчеркнутым курсивом с полужирным начертанием, означает варианты разговора в рамках одного переговорного блока.</p>
                <p><b>Например:</b> <b><i><u>"Если имя ЛПР&nbsp; неизвестно":</u></i></b></p>
                <p><b><i><u>"Если имя ЛПР&nbsp; известно":</u></i></b></p>
            </li>

            <li>
                <p><b><span class="text-danger">Красный текст курсивом</span></b> <b><span class="text-danger">или без</span></b> – это указание сценаристу доработать речевой модуль, вставить текст определенного содержания.</p>
                <p><b>Например</b>: <span class="text-danger">"<b>Укажите ваши конкурентные преимущества".</b></span></p>
                <p>В данном случае в это место сценария сценарист должен расписать конкурентные преимущества компании или продукта, который предлагается клиенту.</p>
            </li>
        </ol>


        <a name="I_Этап">&nbsp;</a>
        <h2>I ЭТАП. <small>"Обход секретаря, выявление ЛПР и его контактных данных"</small></h2>

        <table class="table table-condensed table-striped table-bordered">
            <tbody>

            <tr>
                <td>
                    <h3>Начало разговора <a name="С_1">С-1</a></h3>
                    <p>Добрый день! Это <span class="text-muted">"<i>название компании</i>"?</span></p>
                </td>
                <td>
                    <?= $c4 ?>
                    <?= $c2 ?>
                    <?= $non_core_client_1 ?>
                </td>
            </tr>

            <tr>
                <td>
                    <h3>Перезвон после отправки информационных материалов <a name="С_1_1">С-1_1</a></h3>
                    <p><b><i><u>ИНФОРМАЦИЮ ОТПРАВЛЯЛИ на имя ЛПР (смотрим в базе):</u></i></b></p>
                    <p>Добрый день!</p>
                    <p>Меня зовут <?=$caller_name?>, <?=$company_name?>. Соедините, пожалуйста, с <span class="text-muted">"<i>Имя ЛПР</i>".</span></p>

                    <p><b><i><u>ИНФОРМАЦИЮ ОТПРАВЛЯЛИ НА ИМЯ СЕКРЕТАРЯ:</u></i></b></p>
                    <p>Добрый день!</p>
                    <p>Меня зовут <?=$caller_name?>,  <?=$company_name?>.</p>
                    <p>Мы высылали Вам информационные материалы для <span class="text-muted">"<i>должность ЛПР</i>". </span>Соедините,  пожалуйста, с ним.</p>
                    <p> &nbsp;</p>

                    <p><b><i><u>Никто не ознакомился</u></i></b>: А Вы получили материалы?</p>
                    <p><b><i><u>Получили</u></i></b>: А когда Вы сможете передать информацию? В какое время перезвонить? <span class="text-muted">(<i>записать</i>) </span></p>
                    <p>Хорошо, мы перезвоним <span class="text-muted">(<i>назвать дату</i>) </span>До свидания.</p>

                    <p><b><i><u>Не получили</u></i></b>: А в спаме смотрели? <i><span class="text-muted">(дать ответить)</span></i></p>
                    <p>Это адрес <b>вашей</b> электронной почты? <span class="text-muted">(<i>прочитать адрес почты из базы</i>). </span>Подскажите
  адрес другой (личной) почты, мы отправим информацию повторно<i>. </i><span class="text-muted">(<i>записать</i>) </span>Отправим сегодня же и перезвоним
  через пару дней<a href="<?= $page_url ?>#К_1">. &nbsp;&nbsp;Отправка КП К-1</a></p>
                </td>

                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>

            <tr>
                <td>
                    <h3>Перезвон после отказа ЛПР (<span class="text-danger">инфо не высылали</span>) <a name="С_1_2">С-1_2 </a></h3>
                    <p>Добрый день!  <?=$company_name?><i>,</i>менеджер <?=$caller_name?>.
  Соедините, пожалуйста, с <span class="text-muted">"<i>Имя ЛПР</i>".</span></p>
                    <p>&nbsp;</p>
                    <p><b><i><u>ПО КАКОМУ ВОПРОСУ:</u></i></b></p>
                    <p><b><i><u>Перезвон быстрый/ через непродолжительное время:</u></i></b></p>
                    <p>Мы общались с <i><span class="text-muted">"Имя ЛПР" </span></i>несколько дней назад <i><span class="text-muted">"назвать дату из базы". </span></i>Соедините, пожалуйста, с ним.</p>
                    <?= $connecting_2 ?>
                    <p> &nbsp;</p>

                    <p><b><i><u>Перезвон долгий/ через длительное время:</u></i></b></p>
                    <p>Мы разговаривали с <i><span class="text-muted">"Имя ЛПР" "назвать дату из базы" </span></i>по вопросу разработки сайта.</p>
                    <p>Соедините, пожалуйста, с ним.</p>
                    <?= $connecting_3 ?>
                    <p> &nbsp;</p>
                </td>
                <td class="answers_menu">
                    <?= $connecting_2 ?>
                    <?= $connecting_3 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>

            <tr>
                <td>
                    <h3>Перезвон/ Без имени ЛПР не соединяем/ Корпоративная политика <a name="С_1_3">С-1_3 </a></h3>
                    <p>Добрый день, соедините пожалуйста с бухгалтерией.</p>
                    <p> &nbsp;</p>
                    <p><b><i><u>ПО КАКОМУ ВОПРОСУ:</u></i></b></p>
                    <p><b><i><u>Вариант 1: </u></i></b>У нас остались незакрытые суммы. И мне нужен "Акт сверки" с начала года.</p>
                    <p>Соедините, пожалуйста!</p>
                    <p><a href="<?= $page_url ?>#П_10_4">Соединяю&nbsp;&nbsp; П-10_4</a></p>
                    <p> &nbsp;</p>
                    <p><b><i><u>Вариант 2/ Какая компания?: </u></i></b>Я представляю <?=$company_name?>. Соедините, пожалуйста!
                    </p>
                    <p><a href="<?= $page_url ?>#П_10_4">Соединяю&nbsp;&nbsp; П-10_4</a></p>
                    <p> &nbsp;</p>
                    <p><b><i><u>Вариант 3/ Любые уточняющие вопросы:</u></i></b><b><i></i></b>Я не готова сейчас ответить на этот вопрос, меня просто попросили получить "Акт сверки"... Но я могу уточнить и перезвонить Вам.</p>
                    <p>А сейчас, соедините, пожалуйста, с бухгалтерией.</p>
                    <p><a href="<?= $page_url ?>#П_10_4">Соединяю&nbsp;&nbsp; П-10_4</a></p>
                </td>
                <td>

                </td>
            </tr>

            <tr>
                <td>
                    <h3>Номер телефона подтвержден <a name="С_2">С-2 </a></h3>
                    <p>Я позвонила по номеру <i><span class="text-muted">"назвать номер из базы"</span></i><i>?</i></p>
                </td>
                <td>
                    <p><a href="<?= $page_url ?>#С_3">Да___________С-3</a></p>
                    <p><a href="<?= $page_url ?>#К1">Нет__________К-1</a></p>
                    <p><a href="<?= $page_url ?>#К_1">Квартира_____К-1</a></p>

                </td>
            </tr>

            <tr>
                <td>
                    <h3>Другая организация <a name="С_3">С-3 </a></h3>
                    <p>Этот
  номер был у нас в базе, как контактный телефон компании <i><span class="text-muted">"название компании из базы".</span></i></p>
                    <p>Он
  давно Вам принадлежит? <i><span class="text-muted">(дать ответить)</span></i></p>
                    <p>Скажите,
  а как Вас зовут? <i><span class="text-muted">(записать)</span></i></p>
                    <p><i><span class="text-muted">"Имя секретаря",</span></i>а чем занимается
  Ваша компания? <i><span class="text-muted">(записать)</span></i></p>


                    <p><a href="<?= $page_url ?>#С_4">Если  профильный клиент, продолжаем разговор:&nbsp;&nbsp;&nbsp;С-4</a></p>
                    <p>&nbsp;</p>
                    <p><b><i><u>Если профиль явно не наш: </u></i></b><i> &nbsp;</i><a href="<?= $page_url ?>#К_1">Непрофильная организация&nbsp;&nbsp;&nbsp;&nbsp; К-1</a>
                    </p>



                </td>
                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you_1 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Название организации подтверждено <a name="С_4">С-4 </a></h3>
                    <p>Меня зовут <?=$caller_name?>,  <?=$company_name?>.</span></p>
                    <?=$pts?>

                    <h4>Если имя ЛПР еще неизвестно:</h4>
                    <p>С кем я могу поговорить по вопросу сотрудничества?</p>


                    <h4>Если имя ЛПР известно (смотрим в базе):</h4>
                    <p>Соедините, пожалуйста, с <span class="text-muted">"<i>Имя ЛПР</i>".</span></p>

                </td>
                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you_1 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>С кем именно соединить? <a name="С_4_1">С-4_1 </a></h3>
                    <p><b><i><u>Вариант 1/легенда:</u></i></b><b><i></i></b><i><span class="text-muted">"Имя секретаря",</span></i> дело в том, что <b>мы работали</b>
  раньше с <i><span class="text-muted">Сергеем Ивановичем</span></i> по этому вопросу …</p>
                    <p>А кто у вас <b>сейчас</b> занимается разработкой сайта?</p>
                    <p><span class="text-muted">Скажите, как его зовут. <i>(записать)</i></span></p>
                    <p><span class="text-muted">Как правильно называется его должность <i>(записать)</i></span></p>
                    <p><span class="text-muted">Как с ним можно связаться. <i>(записать</i>)</span></p>
                    <p>Соедините с ним/с ней тогда, пожалуйста!</p>

                    <p><b><i><u>Вариант 2/легенда:</u></i></b><b><i></i></b><i><span class="text-muted">"Имя секретаря",</span></i> помогите мне, пожалуйста, прояснить
  ситуацию. </p>
                    <p>Мне в наследство досталась база нашего менеджера <i><span class="text-muted">(Ивана/Ольги).</span></i> Он/она работал(а) с вами… </p>
                    <p>Прошло уже прилично времени, но, судя по последним записям, у вас был <b>интерес</b> к разработке сайта.</p>
                    <p>Поэтому я и хочу поговорить с тем, кто <b>сейчас </b>занимается этими вопросами.</p>
                    <p>Соедините, пожалуйста!</p>

                    <p><b><i><u>Вариант 1/традиционный:</u></i></b> Соедините меня, пожалуйста, с тем, кто у вас отвечает за разработку сайта.</p>
                    <p><span class="text-muted">Скажите, как его зовут. <i>(записать)</i></p>
                    <p><span class="text-muted">Как правильно называется его должность <i>(записать)</i></p>
                    <p><span class="text-muted">Как с ним можно связаться. <i>(записать</i>)</p>
                    <p>Соедините, пожалуйста!</p>

                    <p><b><i><u>Вариант 2/Не знаю, кто занимается: </u></i></b>Обычно в компаниях этим вопросом занимается <span class="bg-primary">Руководитель</span> или <span class="bg-primary">Тот, кто занимается группой, рекламой</span>.
                        У Вас в компании есть сейчас такие должности?</p>
                    <p><span class="text-muted">Скажите, как его зовут. <i>(записать)</i></span></p>
                    <p><span class="text-muted">Как правильно называется его должность <i>(записать)</i></span></p>
                    <p><span class="text-muted">Как с ним можно связаться. <i>(записать</i>)</span></p>
                    <p>Соедините, пожалуйста!</p>

                    <p><b><i><u>Вариант 3/Нет такой должности:</u></i></b></p>
                    <p>Тогда соедините, пожалуйста с генеральным
  директором.</p>
                    <p>Я уточню у него этот вопрос.</p>
                    <p><span class="text-muted">Скажите, как его зовут по имени,
  отчеству? <i>(записать)</i> </span></p>
                    <p>&nbsp;</p>

                </td>
                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you_1 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Конкретнее, что хотите/Что вы предлагаете <a name="С_4_2">С-4_2 </a></h3>
                    <p><span class="text-muted">Как Вас зовут, скажите, пожалуйста?</span></p>
                    <p><span class="text-muted">"<i>Имя секретаря</i>", </span>мы хотим предложить вам <span class="bg-primary">разработку сайта под ключ</span>на очень выгодных условиях - <span class="bg-primary">всего 10 000 и <b>без</b> предоплаты</span>. Поэтому мне нужно поговорить с тем, кто за это отвечает. Соедините, пожалуйста!</p>
                </td>
                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you_1 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <a name="С_4_3"></a>
                    <h3>Вы с нами работаете? <a name="С_4_4">С-4_3 </a></h3>
                    <p><i><span class="text-muted">"Имя секретаря", </span></i>мы работали с вами пару лет назад. Сейчас хотим
  возобновить сотрудничество. </p>
                    <p>Переключите, пожалуйста, на <i><span class="text-muted">"имя/ должность ЛПР".</span></i>
                    </p>

                </td>
                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you_1 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>По какому вопросу <a name="С_5">С-5 </a></h3>
                    <p><b><i><u>Вариант 1</u></i></b>: Скажите, пожалуйста, что звонит <?=$caller_name?> компания MAD</p>
                    <p><span class="text-muted">(Ждем реакции)</span></p>
                    <p><b><i><u>Вариант 2</u></i></b>: Я звоню по вопросу разработки сайта для вас</p>
                    <p>Мне нужно обсудить несколько вопросов с <i><span class="text-muted">"имя/ должность ЛПР".</span></i> Соедините, пожалуйста!</p>

                    <p><b><i><u>После отправки информационных материалов: </u></i></b></p>
                    <p><b><i><u>Вариант 1</u></i></b>: Мы разговаривали с <span class="text-muted">"<i>Имя/должность ЛПР</i>"
  </span>и договорились, что созвонимся сегодня.</p>

                    <p><b><i><u>Вариант 2: </u></i></b>Мы звонили <span class="text-muted">"<i>назвать дату</i>"
  </span>и отправляли информацию на имя <span class="text-muted">"<i>Имя ЛПР</i>".
  </span>Соедините, пожалуйста, он ждет моего звонка.</p>
                </td>
                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you_1 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Пришлите предложение <a name="С_6">С-6 </a></h3>
                    <p><span class="text-muted">Как Вас зовут, скажите, пожалуйста?</span></p>
                    <p><b><i><u>Вариант 1:</span></u></i></b> Мы не делаем "веерную" рассылку". <i><span
                                        class="text-muted">(пауза)</span></i></span></p>
                    <p>У нас серьезная компания, с именем и репутацией. Чтобы предложить Вам <span class="bg-primary">разработку сайта </span>,я должен(на) сначала переговорить с вашим руководителем.</span></p>
                    <p>&nbsp;</p>
                    <p><b><i><u>Вариант 2:</span></u></i></b> Чтобы составить предложение, мне нужно понять, что именно представляет интерес для вас. Поэтому мне лучше пообщаться с <span class="bg-primary">Руководителем.</span> Соедините, пожалуйста! </span></p>
                    <p>&nbsp;</p>
                    <p><b><i><u>Вариант 3</span></u></i></b><i><u> <b>(Присылайте все подряд):</b></span></u></i><b></b>Мы хотим
  сэкономить время руководства. Разговор займет не более 5 минут. Переключите, пожалуйста!</span></p>

                    <p><b><i><u>Вариант 4:</span></u></i></b> Хорошо, <span class="text-muted">"<i>Имя секретаря</i>"! </span></span></p>
                    <p>Продиктуйте, пожалуйста, адрес электронной почты. <span class="text-muted">(<i>записать</i>) </span>Скажите, на чье имя выслать? <span class="text-muted">(<i>записать</i>)</span></p>
                    <p><span class="text-muted">Скажите, как правильно называется его должность?</span></p>

                    <p><b><i><u>Если не называют имя:</span></u></i></b> Согласитесь, некорректно высылать письмо без имени адресата, скорее всего оно попадет в спам. Скажите, фамилию, имя, отчество <span class="bg-primary">Руководителя</span>? <span class="text-muted">(<i>записать</i>)</span></span></p>
                    <p><b><i><u>Присылайте на общий ящик</span></u></i></b>: <span class="text-muted">"<i>Имя секретаря"</i>, </span>а Вы проверяете этот почтовый ящик? <span class="text-muted">(<i>дать ответить</i>)
  </span></span></p>
                    <p>Тогда я отправлю на <b>Ваше</b> имя! Вы же передадите руководителю?</span></p>

                    <p>Я перезвоню через пару дней, чтобы убедиться, что Вы получили письмо <span class="text-muted">(<i>записать, если называет другую дату)</i></span> <a href="<?= $page_url ?>#К_1">К-1</span></a>
  </span></span></p>
                </td>
                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you_1 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Мы сами перезвоним Вам/Оставьте телефон <a name="С_6_1">С-6_1 </a></h3>
                    <p><span class="text-muted">Как Вас зовут, скажите, пожалуйста?</span></p>
                    <p><b><i><u>ЕСЛИ ДОГОВОРИЛИСЬ ОБ ОТПРАВКЕ ПИСЬМА:</span></u></i></b></p>
                    <p><span class="text-muted">"<i>Имя секретаря</i>", </span>у нас многоканальный телефон и на одном номере
  работают несколько человек. Поэтому будет удобнее, если мы перезвоним сами.</span></p>
                    <p>К тому же руководитель уже успеет ознакомиться с
  информацией. Скажите, в какое время его/ее можно застать? <span class="text-muted">(<i>записать</i>)</span></span></p>
                    <p><b><i><u>Дает информацию</span></u></i></b>: Договорились,
  мы перезвоним <span class="text-muted">(<i>повторить названные дату и время)</i></span><a
                                href="<?= $page_url ?>#К_1">_________К-1</span></a></p>
                    <p><b><i><u>ДАЙТЕ ТЕЛЕФОН:</span></u></i></b></p>
                    <p><b><i><u>Вариант 1:</span></u></i></b> <span class="text-muted">"<i>Имя секретаря</i>", </span>у нас многоканальный
  телефон и на одном номере работают несколько человек. Поэтому будет удобнее,
  если мы перезвоним сами.</span></p>
                    <p>Скажите, в какое время его/ее можно застать? <span class="text-muted">(<i>записать</i>)</span>
                    </p>
                    <p><b><i><u>Дает информацию</span></u></i></b>: Договорились,
  мы перезвоним <span class="text-muted">(<i>повторить названные дату и время</i></span><a
                                href="<?= $page_url ?>#К_1"><i>)</span></i>_________К-1</span></a></p>

                    <p><b><i><u>Вариант 2:</span></u></i></b> Давайте поступим следующим образом. Я отправлю Вам информационные
  материалы. Там есть все наши контактные данные: телефоны, адрес почты, сайт.
  Вы всегда сможете связаться с нами.</span></p>
                    <p><b><i><u>Согласен на инфо:</span></u></i></b><b><i></i></b><a
                                    href="<?= $page_url ?>#С_6">Пришлите предложение&nbsp;&nbsp; С-6
  <i>(последний вариант)</i></a></span></p>
                    <p><b><i><u> &nbsp;</u></i></b></span></p>
                    <p><b><i><u>Дайте телефон (настаивает): </span></u></i></b>Хорошо! </span></p>
                    <p>Запишите наш телефон <span class="bg-primary"><?=$company_phone?></span></span></p>
                    <p>и адрес сайта <span class="bg-primary"><?=$company_site?></span>.
  <a href="<?= $page_url ?>#К_1">________К-1</span></a></p>
                </td>
                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you_1 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Пришлите предложение (а мы уже присылали) <a name="С_6_2">С-6_2 </a></h3>
                    <p>Мы уже высылали предложение для <span class="text-muted">"<i>Имя ЛПР</i>", </span>и договаривались о перезвоне в
  это время. </span></p>
                    <p>Соедините, пожалуйста, с <span class="text-muted">"<i>Имя
  ЛПР</i>".</span></span></p>
                    <p><b><i><u></u></i></b></p>
                    <p><b><i><u></u></i></b></p>
                    <p><b><i><u>Если говорит: "Значит, не интересно! и
  т.п."</span></u></i></b><i>:</span></i> Скорее всего,
  он не перезвонил, так как не успел просмотреть предложение. Я хочу сэкономить
  время руководителя &nbsp;и сейчас расскажу ему суть нашего предложения. &nbsp;Соедините,
  пожалуйста!</span></p>
                    <p> &nbsp;</p>

                </td>
                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you_1 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Нам ничего не нужно /Отказ <a name="С_7">С-7 </a></h3>
                    <p><span class="text-muted">Как Вас зовут, скажите, пожалуйста?</span></p>
                    <p><b><i><u>Вариант 1:</span></u></i></b> <span class="text-muted">"<i>Имя секретаря</i>", </span>почему вы
  решили, что вашему руководству это не нужно? <i><span class="text-muted">(дать
  ответить)</span></i></span></p>

                    <p><b><i><u>Вариант 2:</u></i></b> <span class="text-muted">"<i>Имя секретаря</i>", </span><span class="bg-primary">Сайт под ключ за 1 день за 10 000 <b>без предоплаты</b> </span>, поэтому наши
  предложения обычно очень интересны. </p>
                    <p>Соедините, меня, пожалуйста, с <span class="text-muted">"<i>Имя/должность ЛПР</i>"</span>. Уверен(а), что он захочет заключить выгодную сделку.</span></p>

                    <p><b><i><u>Вариант 3:</span></u></i></b><b><i></i></b>Скажите,
  пожалуйста, я правильно понял(а), что именно Вы принимаете решение по данному
  вопросу?</span></p>
                    <p><b><i><u>Да:</span></u></i></b>
  Скажите, как правильно называется Ваша должность? <i><span class="text-muted">(записать)</span></i><span
                                    class="text-muted"> </span><a href="<?= $page_url ?>#П_12">Нам ничего не надо/Отказ&nbsp;&nbsp; П-12</span></a>
                    </p>

                    <p><b><i><u>Вариант
  4:</span></u></i></b>
  <i><span class="text-muted">"Имя секретаря",<b> </b></span></i>Вы сейчас
  лишаете руководителя возможности сократить расходы и получить
  дополнительную прибыль. Пусть он сам решит, что ему нужно! Переключите,
  пожалуйста!</span></p>
                    <p> &nbsp;</p>
                    <p><b><i><u>Вариант
  5:</span></u></i></b>
  Может, Вы хотя бы спросите, насколько ему это интересно? Мы же не сетевой
  маркетинг предлагаем! Переключите, пожалуйста!</span></p>
                    <p> &nbsp;</p>
                    <p><b><i><u>Вариант 6:</span></u></i></b> <a href="<?= $page_url ?>#С_10">Не могу
  соединить &nbsp;&nbsp;С-10</span></a></p>
                    <p> &nbsp;</p>

                </td>
                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you_1 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Уже купили недавно <a name="С_8">С-8 </a></h3>
                    <p><span class="text-muted">Как Вас зовут, скажите, пожалуйста?</span></p>
                    <p><b><i><u>Вариант 1:</span></u></i></b> <span class="text-muted">"<i>Имя секретаря</i>", </span>даже в этом
  случае, у нас есть, что предложить Вашему руководству. </span></p>
                    <p>Соедините меня, пожалуйста, с тем, кто у вас отвечает за <span class="bg-primary">разработку сайта</span>!</span></p>
                    <p>&nbsp;</p>
                    <p><b><i><u>Вариант 2</span></u></i></b>: Скажите, пожалуйста, я правильно понял(а), что именно Вы принимаете
  решение по данным вопросам?</span></p>
                    <p><b><i><u>Да:</span></u></i></b>
  Скажите, как правильно называется Ваша должность? <span class="text-muted">(<i>записать)</i></span>
                        &nbsp;<a href="<?= $page_url ?>#П_18">Уже купили/ Сейчас не актуально&nbsp;&nbsp; П-18</span></a>
                    </p>
                    <p><b><i><u>Нет:</span></u></i></b> Соедините, пожалуйста, с <span class="bg-primary">Руководителем</span>.Я уверен(а), что он всегда рад хорошему предложению!</span></p>

                </td>
                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you_1 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Где вы находитесь/как с вами связаться <a name="С_9">С-9 </a></h3>
                    <p>
                        <i><span class="text-muted">"Имя секретаря", </span></i>наша компания находится по адресу: 
                    </p>
                    <p><span class="bg-primary">Санкт-Петербург. Кондратьевский, 15/3 офис 319</span>. </span></p>
                    <p> &nbsp;</p>
                    <p>Вы можете
  связаться с ними по телефону: <span class="bg-primary"><?=$company_phone?></span>.</p>

                    <p>Наш сайт: <span class="bg-primary"><?=$company_site?></span>.</p>
                </td>
                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you_1 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Не могу соединить/ Запрещено соединять <a name="С_10">С-10 </a></h3>
                    <p><span class="text-muted">Как Вас зовут, скажите, пожалуйста?</span></p>
                    <p><b><i><u>ЗАПРЕЩЕНО СОЕДИНЯТЬ:</span></u></i></b><i></i></p>
                    <p><b><i><u>Вариант 1:</span></u></i></b> <i><span
                                        class="text-muted">"Имя секретаря", </span></i>я Вас
  прекрасно понимаю… я сама завишу от решения руководителя… Но разговор займет
  буквально пару минут. Соедините, пожалуйста!<span class="text-muted"> </span></span></p>

                    <p><b><i><u>Вариант 2:</span></u></i></b> <span class="text-muted">"<i>Имя секретаря</i>", </span>наверняка
  Вам запрещено соединять с теми, кто звонИт по пустякам…</span></p>
                    <p>Я же хочу прояснить, чем мы можем быть полезны Вам в вопросе <span class="bg-primary">разработки сайта</span>.</span></p>

                    <p><b><i><u>Вариант 3:</span></u></i></b> <i><span
                                        class="text-muted">"Имя секретаря", </span></i>посоветуйте
  мне, пожалуйста, как можно связаться с <span class="text-muted">"<i>имя
  ЛПР/должность ЛПР"</i>. </span>Как принято у вас в компании? Ведь есть же
  какой-то путь? <i><span class="text-muted">(записать) </span></i><a
                                href="<?= $page_url ?>#К_1">К-1</span></a></p>

                    <p><b><i><u>НЕ МОГУ СОЕДИНИТЬ:</span></u></i></b></p>
                    <p><b><i><u>Вариант 1:</u></i></b> Скажите, а с кем еще можно поговорить по вопросу <span class="bg-primary">разработки сайта</span>?</p>
                    <p><span class="text-muted">Как его зовут по имени, отчеству?&nbsp; <i>(записать)</i></span></p>
                    <p><span class="text-muted">Как правильно называется его должность? <i>(записать)</i></span></p>
                    <p><span class="text-muted">Как с ним можно связаться? <i>(записать)</i></span></p>

                    <p><b><i><u>Вариант 2: </span></u></i></b>Хорошо, мы напишем ему официальное письмо. 
                    </p>
                    <p>Назовите, пожалуйста, его полные фамилию, имя,
  отчество и адрес электронной почты. <i><span class="text-muted">(записать)</span></i></span></p>
                    <p>По какому телефону ему можно перезвонить, чтобы
  узнать, получил ли он письмо? <i><span class="text-muted">(записать) </span></i><a href="<?= $page_url ?>#К_1">К-1</span></a>
                    </p>

                </td>
                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you_1 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Универсальный ответ <a name="С_11">С-11 </a></h3>
                    <p><i><span class="text-muted">"Имя секретаря",</span></i><span class="text-muted"> </span>сейчас я звоню с предложением <span class="bg-primary">разработки сайта</span> вашему руководству. Если у <span class="text-muted">"<i>имя ЛПР/должность ЛПР"</i></span><span class="text-muted"> </span>&nbsp;появятся такие вопросы – мы их обсудим лично. Переключите, пожалуйста.</span></p>
                </td>
                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you_1 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Специфические вопросы <a name="С_12">С-12 </a></h3>
                    <p>Скажите, пожалуйста, я правильно понимаю, именно <b>Вы</b> занимаетесь вопросами <span class="bg-primary">разработки сайта</span>?</span></p>
                    <p><b><i><u>Да:</span></u></i></b> Скажите, как правильно называется Ваша должность? <span class="text-muted">(<i>записать)</i><a href="<?= $page_url ?>#П_10_1"></a></span></span></p>
                    <p><i><span class="text-muted">Если попали на ЛПР читаем</span></i><span class="text-muted"> </span><a href="<?= $page_url ?>#П_10_1">Я слушаю/Со мной П-10_1</a></span></p>
                    <p><b><i><u>Нет:</span></u></i></b> Соедините, пожалуйста, с руководителем. Я уверен(а), он всегда рад хорошему предложению!</span></p>
                </td>
                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you_1 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Окончание разговора <a name="К_1">К-1 </a></h3>
                    <p>Спасибо за информацию, всего доброго.</span></p>

                    <p>
                        <b><i><u>Если квартира:</span></u></i></b> извините за беспокойство. Всего доброго.
                    </p>
                    <p> &nbsp;</p>
                </td>
                <td>
                    <p> &nbsp;</p>
                </td>
            </tr>
            <tr>
                <td>
                    <h3>Человек занят /Нет на месте <a name="К_6">К-6 </a></h3>
                    <p>Когда лучше перезвонить? <span class="text-muted">(<i>записать</i>)
  </span></span></p>
                    <p>Кого спросить? <span class="text-muted">(<i>записать</i>)
  </span></span></p>
                    <p>Спасибо. Всего доброго.</span></p>
                </td>
                <td>
                    <p> &nbsp;</p>
                </td>
            </tr>
            </tbody>
        </table>

        <a name="II_Этап"></a>
        <h2>II ЭТАП
            <small>"Представление. Обработка возражений"</small>
        </h2>

        <table class="table table-condensed table-striped table-bordered">
            <tbody>
            <tr>
                <td>
                    <h3>Соединяю <a name="П_10">П-10 </a></h3>
                    <h4><u>Если имя ЛПР известно</u><small>(смотрим в базе)</small>:</h4>

                    <p>Добрый день, <i><span class="text-muted">"Имя ЛПР"</span></i>!<br>
                        Меня зовут <?=$caller_name?>, <?=$company_name?><i>.</i></span></p>

                    <h4><u>Если имя ЛПР еще неизвестно:</u></h4>
                    <p>Добрый день! </p>
                    <p>Меня зовут <?=$caller_name?>, &nbsp;<?=$company_name?>.<span class="text-muted"> </span></span></p>

                    <p>Мне сказали, что именно <b>Вы</b> занимаетесь вопросами <span class="bg-primary">разработки сайта</span>.Это так?</span></p>
                    <p><b><i><u>Да:</span></u></i></b> Скажите, пожалуйста, как Вас зовут? <span class="text-muted">(<i>записать</i>)</span></span></p>
                    <p>Очень приятно.</span></p>
                    <p>&nbsp;</p>

                    <p><span class="text-muted">Вам удобно сейчас разговаривать, есть
  пара минут?</span></p>

                    <p>&nbsp;</p>

                    <p><a href="<?= $page_url ?>#П_10_1">Я слушаю/Удобно:&nbsp;П-10_1</a></p>
                    <p><a href="<?= $page_url ?>#П_15_1">Не удобно разговаривать: П-15_1</a></p>
                    <p><a href="<?= $page_url ?>#П_15">Не правильно соединили: Ошибочный ЛПР - П-15</a></p>

                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Перезвон после отправки информационных материалов <a name="П_10_0">П-10-0 </a></h3>
                    <p>Здравствуйте, <span class="text-muted">"<i>Имя ЛПР</i>"!
  </span></span></p>
                    <p><?=$caller_name?>, &nbsp;<?=$company_name?>. Мы говорили с Вами <span class="text-muted">"<i>назвать число и день"</i> </span>и отправляли информацию о нас и нашем &nbsp;предложении по&nbsp; <span class="bg-primary">разработке сайта</span>.</span></p>
                    <p>Вы успели с ним ознакомиться? <span class="text-muted">(<i>дать ответить</i>)</span></span></p>

                    <p><b><i><u>Ознакомился:</span></u></i></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span>я предлагаю пообщаться с нашим специалистом. Это Вас ничем не обяжет, зато будет более полное представление о том, чем сотрудничество с нами будет выгодно для вас.&nbsp; </span></p>
                    <p>Вам удобнее завтра в <b>первой</b> или во второй половине дня? <span class="text-muted">(<i>или называет другое время,записать</i>)</span> </span></p>

                    <p><b><i><u>Не получили:</span></u></i></b> Мы высылали письмо на адрес <span class="text-muted">"<i>назвать из базы</i>".</span></span></p>
                    <p>А в спаме смотрели письмо? <span class="text-muted">(<i>дать
  ответить</i>)</span></span></p>
                    <p>Продиктуйте, пожалуйста, адрес другой электронной
  почты<i>. </i>Я вышлю информацию повторно<i>. <span class="text-muted">(записать
  или проверить в базе</span><span class="text-muted">)</span></i><span class="text-muted"> <i>&nbsp;</i></span>Через пару дней я свяжусь с Вами, чтобы
  убедиться, что Вы получили все необходимое<a href="<?= $page_url ?>#Р_2_1">____Р-2_1</span></a></p>

                    <p><b><i><u>Не ознакомился:</span></u></i></b><b><i></i></b>Хорошо,
  я перезвоню Вам в конце недели, Вы успеете ознакомиться с нашим предложением?
  <i><span class="text-muted">(или называет другое время, записать)</span></i><a href="<?= $page_url ?>#Р_2"> Р-2</span></a></p>
                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>
                </td>
            </tr>
            <tr>
                <td>
                    <h3>Я слушаю/Со мной <a name="П_10_1">П-10_1 </a></h3>
                    <p><span class="text-muted">Как Вас зовут, скажите, пожалуйста? <i>(записать)</i></span></p>
                    <p><span class="text-muted">Как правильно называется Ваша должность? <i>(записать)</i></span></p>

                    <p>&nbsp;</p>
                    <?=$pts?>

                    <h4>Предложение</h4>
                    <p>Наша компания <span class="bg-primary">разрабатывает сайты под ключ за 1 день, выгодно, работаем без предоплаты</span></p>
                    <p>Скажите<span class="bg-primary">, У вас есть сайт?</span><i>?</i><span class="text-muted">(<i>записать</i>)</span></span></p>
                    <p><b><u>Да: </u></b> - не удалось его найти. Он работает?</p>

                    <p>Мы предлагаем &nbsp;<span class="bg-primary">разработку сайта под ключ за 1 день <b>без предоплаты</b> </span><br>
                        <span class="bg-primary">- Размещение на наших серверах<br>-
                            Пожизненная бесплатная техническая поддержка<br>
                            - Соответствие закону о персональных данных</span>.
                    </p>
                    <p>А,  кроме того, сейчас  у нас действует специальные условия: <span class="bg-primary">Нашей компании 10 лет - до конца сентября стоимость разработки 10 000 вместо 20</span>.</p>
                    <p>Поэтому я предлагаю пообщаться с нашим ведущим специалистом. Он грамотно и доступно расскажет о нашем &nbsp;предложении. Вы сможете обсудить технические моменты и озвучить ваши потребности. </p>
                    <p>В <i><span class="text-muted">понедельник</span></i> в 10 утра Вас устроит? <span
                                    class="text-muted">(<i>или называет другое время, записать</i>)</span></span></p>
                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>
                </td>
            </tr>
            <tr>
                <td>
                    <h3>Перезвон после отказа ЛПР (<span class="text-danger">инфо не высылали</span>) <a name="П_10_2">П-10_2 </a></h3>
                    <p>Добрый день, <i><span class="text-muted">"Имя
  ЛПР"!</span></i><span class="text-muted"> </span></p>
                    <p>Это <?=$caller_name?><span
                                class="text-muted"> </span>компания &nbsp;<?=$company_name?>.</span></p>
                    <p>Мы разговаривали с Вами <span class="text-muted">"<i>вчера</i>" </span>по поводу <span class="bg-primary">разработки сайта</span>.<span class="text-muted"> Я помню, что Вы мне сказали и ситуация на данный момент понятна.</span></p>
                    <p>Сейчас я звоню, чтобы отправить Вам на будущее наше общее коммерческое предложение.</span></p>
                    <p>Там есть информация о нашей компании,<i></i>адрес сайта, а также контактная информация. Вы сможете подробнее познакомиться с <i>нашим продуктом,</i> и понять, чем сотрудничество с нами будет выгодно для вас…</span></p>
                    <p> &nbsp;</p>
                    <p> &nbsp;</p>
                    <p><b><i><u>Есть адрес почты в базе:</span></u></i></b>Давайте проверим адрес электронной почты <span class="text-muted">"<i>назвать адрес из базы"</i>.</span></p>
                    <p>Я сегодня вышлю материалы и перезвоню через пару дней, чтобы убедиться, что Вы получили их. <i><span class="text-muted">(если называет другое записать)</span></i></p>
                    <p><a href="<?= $page_url ?>#Р_2_1">Р-2_1</span></a></p>

                    <p><b><i><u>Нет адреса почты в базе:</span></u></i></b> </span></p>
                    <p><a href="<?= $page_url ?>#П_16">Пришлите КП, факс, e’mail&nbsp;&nbsp; П-16</span></a>
                        <i><span class="text-muted">(последний вариант)</span></i></p>
                    <p><i><span class="text-muted">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></i>
                    </p>

                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h4><a name="П_10_3">П-10_3 </a>Перезвон через длительное время</h4>
                    <p>Добрый день, <span class="text-muted">"<i>Имя ЛПР</i>"!
  </span></span></p>
                    <p>Меня зовут <?=$caller_name?>,  &nbsp;<?=$company_name?>.</span></p>
                    <p>Мы общались с Вами <i><span class="text-muted">"примерная дата"</span></i><span class="text-muted"> </span>по вопросу <span class="bg-primary">разработки сайта</span>.<i></i>Помните меня?</span></p>

                    <p><i><span class="text-muted">Далее:</span></i> <a href="<?= $page_url ?>#П_10_1">Я слушаю/Со
  мной&nbsp;&nbsp; П-10_1</span></a></p>

                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Перезвон/ Выход на ЛПР через бухгалтерию <a name="П_10_4">П-10_4 </a></h3>
                    <p>Я просила <i><span class="text-muted">"имя
  секретаря"</span></i><span class="text-muted"> </span>соединить меня с <i><span class="text-muted">"должность ЛПР/&nbsp; имя ЛПР /&nbsp; название отдела",</span></i><span
                                    class="text-muted"> </span>а переключили на вас…</span></p>
                    <p>А переключите, пожалуйста, на <i><span class="text-muted">"должность ЛПР/&nbsp; имя ЛПР /&nbsp; название отдела"</span></i>…
                    </p>
                    <p><a href="<?= $page_url ?>#П_10">Соединяю&nbsp;&nbsp; П-10</span></a></p>
                    <p><b><i><u>Не могу переключить</span></u></i></b>: </span></p>
                    <p>А как я могу с ним связаться? <i><span class="text-muted">(записать) </span></i><a href="<?= $page_url ?>#Р_2">Р-2</span></a></p>

                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Что вы предлагаете/Подробнее <a name="П_11">П-11 </a></h3>
                    <p><b><i><u>Вариант 1:</span></u></i></b><i></i></p>
                    <p><span class="text-muted">"<i>Имя ЛПР</i>", </span>мы предлагаем Вам &nbsp;<span class="bg-primary">Разработку сайта под ключ за 1 день за 10 000 без предоплаты .<br>
                            В эту сумму включен профессиональный дизайн, верстка, программирование, наполнение стартовым контентом.</span>
                    </p>
                    <p><i><span class="text-muted">Переходим на </span></i><a href="<?= $page_url ?>#П_11_2">Ваши преимущества/Чем вы лучше&nbsp;&nbsp; П-11_2</span></a>
                    </p>

                    <p><b><i><u>Вариант 2: </span></u></i></b><span class="text-muted">"<i>Имя ЛПР</i>", </span>чтобы говорить подробнее, мне нужно лучше узнать
  Вашу специфику. </span></p>
                    <p>Ответьте, пожалуйста, на несколько вопросов </span></p>
                    <p><i><span class="text-muted">Переходим на </span></i><a href="<?= $page_url ?>#П_29">Портрет клиента П-29</span></a>
                    </p>

                    <p><b><i><u>Вариант 3: </span></u></i></b><span class="text-muted">"<i>Имя ЛПР</i>", </span>я предлагаю назначить время и &nbsp;<span class="bg-primary">пообщаться</span> с ведущим специалистом по нашим продуктам, чтобы обсудить
  возможное сотрудничество и узнать обо всех выгодах, которые Ваша компания может получить. </span></p>
                    <p>Это займет немного времени и ни к чему Вас не обязывает. В понедельник в 10 утра Вас устроит? <span class="text-muted">(<i>или называет другое время, записать</i>).</span></span></p>
                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Не слышали о вас/Подробнее о компании <a name="П_11_1">П-11_1 </a></h3>
                    <p><b><i><u>Вариант 1: </span></u></i></b><span class="text-muted">"<i>Имя ЛПР</i>", </span>&nbsp;<span class="bg-primary">Мы занимаемся разработкой веб-сервисов и сайтов</span>. <i><span class="text-muted">Переходим на </span></i><a href="<?= $page_url ?>#П_11_2">Ваши преимущества/Чем вы лучше&nbsp;&nbsp;
  П-11_2</span></a></p>
                    <p>&nbsp;</p>
                    <p><b><i><u>Вариант 2: </span></u></i></b>Более подробно о нашей компании расскажет Вам наш специалист. Вам удобно &nbsp;<span class="bg-primary">пообщаться</span> с ним&nbsp; завтра в 10 утра? <i><span class="text-muted">(если называет другое время, записать)</span></i></span></p>

                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Ваши преимущества/Выгоды <a name="П_11_2">П-11_2 </a></h3>
                    <p><b><i><span class="text-muted">(за раз читаем не более 3-х пунктов)</span></i></b></p>
                    <p><span class="text-muted">"<i>Имя ЛПР</i>", </span></p>
                    <ul>
                        <li>Стоимость 10 000 <b>без предоплаты</b></li>
                        <li>Размещение на наших серверах на 1 год в подарок</li>
                        <li>Бесплатная техническая поддержка пожизненно</li>
                        <li>Соответствие закону о персональных данных</li>
                        <li>Адаптивный дизайн - будет работать на всех устройствах</li>
                        <li>Бесплатное обновление и развитие пожизненно</li>
                    </ul>
                    <p><span class="text-muted">"<i>Имя ЛПР</i>", </span>наш специалист готов &nbsp;<span class="bg-primary">пообщаться </span> с Вами и подробно рассказать о преимуществах работы с нашей компанией. Скажите, в понедельник в 10 Вас устроит? <i><span class="text-muted">(или называет другое время, записать)</span></i>
                    </p>
                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>В чем подвох <a name="U_1"> U-1 </a></h3>
                    <p><b><u>Дешево</u></b></p>
                    <p><span class="text-muted">&nbsp;"<i>Имя ЛПР</i>", </span> это цена в рамках акции к дню рождения компании. Обычная цена - 20 000.</p>

                    <p><b><u>Подозрительно</u></b></p>
                    <p><span class="text-muted">&nbsp;"<i>Имя ЛПР</i>", </span> это нормально, особенно в нашей стране, что у вас есть опасения. Поэтому мы работаем без предоплаты. Сначала вы увидите свой рабочий сайт, потом будет происходить оплата</p>

                    <p><b><u>Слишком быстро</u></b></p>
                    <p><span class="text-muted">&nbsp;"<i>Имя ЛПР</i>", </span> у нас конструктор сайтов собственной разработки. Это полностью наш код. У нас много готовых наработок по дизайну. Поэтому наши специалисты могут очень быстро сделать хороший сайт для вас.</p>

                    <p><b><u>Если нам не понравится?</u></b></p>
                    <p><span class="text-muted">&nbsp;"<i>Имя ЛПР</i>", </span> наш специалист опросит у вас все пожелания к новому сайту. Это будет современный удобный дизайн. Если даже в результате что-то вас не устроит, что происходит очень редкто, то всегда можно сделать корректировки.</p>

                    <p><b><u>Если мы не заплатим?</u></b></p>
                    <p><span class="text-muted">&nbsp;"<i>Имя ЛПР</i>", </span> нашей компании 10 лет. Такое, к сожалению, иногда случается. Но это не затмевает наш положительный опыт работы с клиентами - мы верим в свою разработку и готовы работать без предоплаты.</p>

                   <p>&nbsp;</p>
                    <p><span class="text-muted">&nbsp;"<i>Имя ЛПР</i>", </span> Я предлагаю &nbsp;<span class="bg-primary">пообщаться</span> с нашим ведущим специалистом. Он подробно расскажет о нашей системе, покажет примеры работ. &nbsp;Он очень грамотно и доступно ответит на все ваши вопросы. В <i><span class="text-muted">понедельник</span></i> в 10 утра Вас устроит? <span class="text-muted">(<i>или
  называет другое время, записать</i>) </span></span></p>
                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Нам ничего не надо/Отказ <a name="П_12">П-12 </a></h3>
                    <p><span class="text-muted">&nbsp;"<i>Имя ЛПР</i>", а по какой причине? <i>(дать
  ответить)</i></span></p>

                    <p>Скажите, &nbsp;<span class="bg-primary">У вас есть сайт</span>?<i><span class="text-muted">(дать ответить)</span></i></span></p>
                    <p><b><i><u>Да:</span></u></i></b> <a href="<?= $page_url ?>#Р_4">Непрофильный клиент Р-4</a></span></p>

                    <p><b><i><u>Вариант 1:</span></u></i></b> </span></p>
                    <p><span class="text-muted">"<i>Имя ЛПР</i>",</span>&nbsp;Сайт под ключ за 1 день<br>
                            Без предоплаты<br>
                            Цена всего 10 000<br>
                            <span class="text-muted"> размещение на наших серверах на 1 год в подарок<br>
                            пожизненная бесплатная техподдержка!</span><br>
                            Именно поэтому, я предлагаю Вам <span class="bg-primary">пообщаться</span> с нашим
  специалистом. В <i><span class="text-muted">понедельник</span></i> в 10 утра Вас устроит? <span class="text-muted">(<i>или называет другое время, записать</i>).</span>
                    </p>
                    <p><b><i><u>Вариант 2</span></u></i></b><b>:</span></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span>только &nbsp;<span class="bg-primary">телефонные переговоры</span> с нашим специалистом выявят реальную потребность в разработке. К тому же это вас ни к чему не обяжет, и при этом вы больше узнаете о нашем &nbsp;предложении на будущее. Я предлагаю &nbsp;<span class="bg-primary">пообщаться</span> с ведущим специалистом по нашим продуктам. Он подробно расскажет о нашей системе, покажет примеры работ. &nbsp;Он очень грамотно и доступно ответит на все ваши вопросы. В <i><span class="text-muted">понедельник</span></i> в 10 утра Вас устроит? <span class="text-muted">(<i>или
  называет другое время, записать</i>) </span></span></p>
                    <p> &nbsp;</p>
                    <p><b><i><u>Вариант последний:</span></u></i></b> <i><span class="text-danger">(использовать только, если прочитали предыдущие варианты!!!!)</span></i></span></p>
                    <p><i><span class="text-muted">Далее читаем: &nbsp;</span></i><a href="<?= $page_url ?>#П_33">Последний вариант&nbsp;&nbsp; П-33</span></a>
                    </p>
                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Работаем с другими <a name="П_13">П-13 </a></h3>
                    <p>Скажите, а с кем Вы сотрудничаете сейчас? <span class="text-muted">(<i>записать)</i></span></span></p>
                    <p><b><u>Вариант 1:</u></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span> А ваш сайт соответствует федеральному закону 152 "О персональных данных"<br>
                    <span class="text-muted">С 1 июля на сайте должна быть опубликована политика по работе с персональными данными, все формы должны быть снабжены согласием на обработку персональных данных, должно быть предупреждение об использовании "кукис". Если этого нет, то Роскомнадзор может выписать штраф до 300 000 или заблокировать сайт</span></p>
                    <p><b><u>Вариант 2:</u></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span> А ваш сайт одинаково хорошо работает на всех устройствах? Компьютеры, планшеты, телефоны?<br>
                    <span class="text-muted">Наши сайты делаются с адаптивной версткой</span> </p>
                    <p><b><u>Вариант 3:</u></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span> А на вашем сайте есть такие элементы, как кнопка обратного звонка, форма заказа?<br>
                        <span class="text-muted">Эти элементы позволяют не потерять заинтересованного посетителя.</span></p>
                    <p><b><u>Вариант 4:</u></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span> Вы платите больше 2 000 за обслуживание сайта в год?<br>
                    <span class="text-muted">У нас свои серверы в датацентре. Первый год обслуживания для вас бесплатно. Затем 2000 в год.</span> </p>
                    <p><b><u>Вариант 5:</u></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span> Ваш текущий разработчик устанавливает все обновления бесплатно?<br>
                    <span class="text-muted">Мы сами разрабатываем свою систему - все обновления устанавливаются на все сайты автоматически и бесплатно</span> </p>
                    <p><b><u>Вариант 5:</u></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span> Ваш текущий разработчик самостоятельно следит за защитой сайта от взлома и вирусов?<br>
                        <span class="text-muted">У нас свои специалисты, которые постоянно улучшают защиту сайтов, делают резервные копии и препятствуют возможным атакам</span>
                    </p>
                    <p><b><u>Вариант 6:</u></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span> Ваш текущий разработчик предоставляет вам техническую поддержку бесплатно и будет предоставлять всегда?<br>
                        <span class="text-muted">Мы предоставляем пожизненную техподдержку бесплатно</span></p>
                    <p><b><u>Вариант 6:</u></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span> Многие наши клиенты ранее работали с другими студиями, но условия работы с нами намного лучше - мы сами разрабатываем свою систему под наших пользователей - под их требования, как им удобней пользоваться, а не как удобней программировать</p>
                    <p class="bg-primary">Именно поэтому я предлагаю пообщаться с нашим специалистом, и обсудить все преимущества сотрудничества с нами. Скажите, в понедельник в 10 утра Вас устроит? <span class="text-muted">(<i>или называет другое время, записать</i>)</span></p>

                    <p><b><i><u>Вариант 7</span></u></i></b>:<span class="text-muted">"<i>Имя ЛПР</i>", </span>есть как минимум три причины, по которым Вам стОит рассмотреть наше предложение. </span></span></p>
                    <?= $your_advantages ?>
                    <p><b><i><u>Вариант 8:</span></u></i></b><i><u></span></u></i><span class="text-muted">"<i>Имя ЛПР</i>", </span>я не призываю Вас немедленно заключить с нами договор и отказаться от вашего постоянного поставщика. Сейчас я предлагаю &nbsp;<span class="bg-primary">пообщаться</span> с нашим специалистом, чтобы понять, чем сотрудничество с нами будет выгодно для вас<b>.</b> </span></p>
                    <p>Скажите, в <i><span class="text-muted">понедельник</span></i> в 10 утра Вас устроит? <span class="text-muted">(<i>или называет другое время, записать</i>)</span></p>

                    <p><b><i><u>Вариант 9:</span></u></i></b><i></i><span class="text-muted">"<i>Имя ЛПР</i>", </span>речь идёт о выборе <b>оптимальных</b> вариантов и условий сотрудничества. Сейчас я предлагаю только &nbsp;<span class="bg-primary">пообщаться</span> с нашим специалистом и обсудить возможное сотрудничество. &nbsp;Это займет немного времени и при этом вы получите больше информации о нашем предложении. В <i>понедельник</i> в 10 утра Вас удобно? <span class="text-muted">(<i>или называет другое время, записать</i>) </span></span></p>
                    <p><b><i><u>Вариант последний:</span></u></i></b> <i><span class="text-danger">(использовать только, если прочитали предыдущие варианты!!!!) </span></i><a href="<?= $page_url ?>#П_33">Последний вариант&nbsp;&nbsp; П-33</span></a>
                    </p>
                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Нет денег/Нет бюджета <a name="П_14">П-14 </a></h3>
                    <p><span class="text-muted">Разработка к юбилею компании всего 10 000 - никакой предоплаты не требуется. Это самые выгодные условия на рынке</span></p>

                    <p><b><i><u>Вариант 1</span></u></i></b>: <span class="text-muted">"<i>Имя ЛПР</i>", </span>в этой ситуации,
                        сотрудничество с нами поможет Вам получить дополнительную прибыль. </span></p>
                    <p>Как раз наш специалист и расскажет Вам, чем именно сотрудничество с нами будет выгодно для Вас. Скажите, в понедельник в 10 утра Вам удобно<span class="text-muted">? (<i>или называет другое время, записать</i>)</span></span></p>

                    <p><b><i><u>Вариант 2:</span></u></i></b> Раз есть потребность, появятся и деньги... А пока мы можем обсудить, что именно Вам будет необходимо, исходя из Ваших задач и профиля деятельности. </span></p>
                    <p><i><span class="text-muted">"Имя ЛПР", </span></i>наш специалист готов &nbsp;<span class="bg-primary">пообщаться</span> с Вами и ответить на все Ваши вопросы и обсудить условия сотрудничества. Возможно он сможет предложить вам отсрочку платежа. Скажите, в <i><span class="text-muted">понедельник</span></i> в 10 утра Вас удобно? <span class="text-muted">(<i>или называет другое время, записать</i>) </span></span></p>

                    <p><b><i><u>Вариант 3</span></u></i></b>: <span class="text-muted">"<i>Имя ЛПР</i>", </span>именно благодаря сотрудничеству с нами Вы сможете сократить расходы по привлечению клиентов!<br>
                        Наш специалист готов &nbsp;<span class="bg-primary">пообщаться</span> с Вами и рассказать, чем именно сотрудничество с нами будет выгодно для Вас. Скажите, в <i><span class="text-muted">понедельник</span></i> в 10 утра Вас удобно? (<i><span class="text-muted">или называет другое
  время, записать</span></i><span class="text-muted">) </span></span></p>

                    <p>&nbsp;</p>
                    <p><b><i><u>Вариант последний:</span></u></i></b> <i><span class="text-danger">(использовать только, если прочитали предыдущие варианты!!!!) </span></i><a href="<?= $page_url ?>#П_33">Последний вариант&nbsp;&nbsp; П-33</span></a>
                    </p>
                    <p> &nbsp;</p>
                    <p> &nbsp;</p>

                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Сколько стоит <a name="П_14_1">П-14_1 </a></h3>
                    <p><b><i><u>Вариант 1:</u></i></b> <span class="text-muted">"<i>Имя ЛПР</i>",</span> 20 000, но сейчас акция и до конца сентября всего 10 000 и никакой предоплаты.</span></p>
                    <p class="text-muted">
                        Размещение на наших серверах 1-й год бесплатно<br>
                        Далее всего 2 000 в год<br>
                        Включен дизайн, верстка, сборка в конструкторе MAD
                    </p>
                    <p>Более подробно все условия Вам лучше обсудить с нашим специалистом - помимо самой разработки он вам предложит массу других бонусов в подарок.<br>
                        Завтра в 10 утра Вам удобно? <span class="text-muted">(<i>или называет другое время, записать</i>)</span></span></p>
                    <p><b><i><u>Вариант 2:</span></u></i></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span>окончательную цену Вам лучше обсудить с нашим специалистом, он сможет подобрать для вас специальное предложение, исходя из Ваших задач.</span></p>
                    <p>Завтра в 10 утра Вам удобно? <span class="text-muted">(<i>или называет другое время, записать</i>)</span></span></p>

                    <p><b><i><u>Вариант 3:</span></u></i></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span>наш специалист проконсультирует
  Вас, расскажет, чем именно сотрудничество с нами будет выгодно для Вас и… подготовит
  предложение, от которого Вы просто не сможете отказаться.</span></p>
                    <p>Завтра в 10 утра Вам удобно? <span class="text-muted">(<i>или называет другое время, записать</i>)</span></span></p>
                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Дорого <a name="П_14_2">П-14_2 </a></h3>
                    <p><b><i><u>Вариант 1:</span></u></i></b><span class="text-muted">"<i>Имя ЛПР</i>", </span>это самая низкая цена за разработку на рынке. Плюс мы предлагаем много вещей, за которые все берут деньги, бесплатно.<br>
                        Тем более по акции мы разработаем сайт за 10 000 вместо 20.<br>
                        Именно поэтому я предлагаю Вам &nbsp;<span class="bg-primary">пообщаться</span> с нашим специалистом, чтобы обсудить условия. &nbsp;Вы сможете сами оценить, что дорого, а что <b>ВЫГОДНО</b>!.. В <i><span class="text-muted">понедельник</span></i> в 10 утра Вам удобно? <span class="text-muted">(<i>или называет другое время, записать</i>).</span></p>

                    <p><b><i><u>Вариант 2:</span></u></i></b><span class="text-muted">"<i>Имя ЛПР</i>", </span>пока Вы не знаете, за что платите, любая цена будет высокой!<br>
                        Именно поэтому я предлагаю Вам &nbsp;<span class="bg-primary">пообщаться</span> с нашим специалистом, чтобы обсудить условия. &nbsp;Вы сможете сами оценить, что дорого, а что <b>ВЫГОДНО</b>!.. В <i><span class="text-muted">понедельник</span></i> в 10 утра Вам удобно? <span class="text-muted">(<i>или называет другое время, записать</i>).</span></p>
                    <p>&nbsp;</p>
                    <p><b><i><u>Вариант 3: </span></u></i></b>&nbsp;<span class="text-muted">"<i>Имя ЛПР</i>", </span>оценить реальную ценность и стоимость нашего &nbsp;предложения можно только при общении со специалистом.<br>
                        Поэтому, я предлагаю &nbsp;<span class="bg-primary">пообщаться</span> с ведущим специалистом по нашим продуктам. У него есть яркая презентация и понятные материалы. Вы получите ответы на все ваши вопросы. Это займет немного времени и ни к чему Вас не обязывает. В <i><span class="text-muted">понедельник </span></i>в 10 утра Вас устроит? <span class="text-muted">(<i>или называет другое время, записать</i>).</span></span></p>
                    <p> &nbsp;</p>
                    <p><b><i><u>Вариант последний:</span></u></i></b> <i><span class="text-danger">(использовать только, если прочитали предыдущие варианты!!!!) </span></i><a href="<?= $page_url ?>#П_33">Последний вариант&nbsp;&nbsp; П-33</span></a>
                    </p>
                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Мы вам не интересны/ Маленькая компания <a name="П_14_3">П-14_3 </a></h3>
                    <p><i><span class="text-muted">"Имя ЛПР", </span></i></p>
                    <ol class="bg-primary text-muted">
                        <li>У вас есть сайт?</li>
                        <li>Вы хотите привлечь новых клиентов?</li>
                        <li>Вы хотите расширяться и повышать прибыль?</li>
                        <li>Вы хотите, чтобы вас находили люди через интернет?</li>
                        <li>Вы хотите, чтобы ваша компания вызывала доверие у потенциальных клиентов?</li>
                    </ol>
                    <span class="text-muted">(<i>дать ответить</i>)</span>

                    <p><a href="<?= $page_url ?>#П_33"><b><i>Непрофильный клиент:</i></b><br>
                            <a href="<?= $page_url ?>#Р_4">Непрофильный клиент&nbsp;&nbsp; Р-4</a></p>
                    <p><b><i><u>Вариант 1: </span></u></i></b><i><span
                                    class="text-muted">"Имя ЛПР"</span></i>, наш специалист подберет для Вас индивидуальное
  предложение, которое, я уверена, Вас заинтересует. Давайте, я назначу Вам время для общения с нашим
  специалистом, он вам расскажет об этом подробнее. &nbsp;В <span class="text-muted">понедельник</span>
  в 10 утра Вас удобно? (<i><span class="text-muted">или называет другое
  время, записать</span></i><span class="text-muted">) </span></span></p>

                    <p><b><i><u>Вариант последний:</span></u></i></b> <i><span class="text-danger">(использовать только, если прочитали предыдущие варианты!!!!) </span></i><a href="<?= $page_url ?>#П_33">Последний вариант&nbsp;&nbsp; П-33</span></a>
                    </p>
                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Ошибочный ЛПР/ ЛПР нет на месте <a name="П_15">П-15 </a></h3>

                    <p><b><i><u>ОШИБОЧНЫЙ ЛПР:</span></u></i></b></p>
                    <p><b><i><u>Вариант 1: </span></u></i></b><span
                                class="text-muted">"<i>Имя сотрудника</i>", </span>скажите, а кто принимает решение по данному вопросу?
  <i><span class="text-muted">(дать ответить)</span></i><span class="text-muted"> </span></span></p>
                    <p><span class="text-muted">Подскажите
  его Фамилия, Имя, Отчество? <i>(записать)</i> </span></p>
                    <p><span class="text-muted">Как
  правильно называется его должность? (<i>записать</i>) </span></p>
                    <p>Как с ним можно
  связаться? <span class="text-muted">(<i>записать</i>)</span></span></p>
                    <p><span class="text-muted">Можете
  переключить прямо сейчас?</span></p>

                    <p><b><i><u>Вариант 2:</span></u></i></b> А Вы можете мне помочь и немного прояснить ситуацию? </p>
                    <p><i><span class="text-muted">Если согласен, далее читаем</span></i><span class="text-muted">&nbsp; </span><a href="<?= $page_url ?>#П_29">Портрет клиента&nbsp;&nbsp; П-29</span></a></p>

                    <p><b><i><u>ЛПР НЕТ НА МЕСТЕ:</span></u></i></b></p>
                    <p>Подскажите,
  пожалуйста, когда я могу его/ее застать… <span class="text-muted">(<i>записать</i>)
  </span></span></p>
                    <p>Уточните,
  пожалуйста, его/ее имя и отчество? <span class="text-muted">(<i>записать</i>)
  </span></span></p>
                    <p><span class="text-muted">Как
  правильно называется его должность? (<i>записать</i>) </span></p>
                    <p> &nbsp;</p>
                    <p>По
  этому номеру перезвонить? <span class="text-muted">(<i>записать</i></span><span class="text-muted">) </span><a href="<?= $page_url ?>#Р_2">_Р-2</span></a></p>

                </td>
                <td class="answers_menu">
                    <?= $connecting ?>
                    <?= $listening_to_you_1 ?>
                    <?= $who_to_connect ?>
                    <?= $what_do_you_want ?>
                    <?= $do_you_work_with_us ?>
                    <?= $what_is_the_subject ?>
                    <?= $send_your_offer ?>
                    <?= $send_your_offer_again ?>
                    <?= $what_is_your_location_secretary ?>
                    <p>&nbsp;</p>
                    <?= $we_will_call_back_secretary ?>
                    <?= $we_do_not_need_it_secretary ?>
                    <?= $already_have_secretary ?>
                    <?= $can_not_connect ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer_secretary ?>
                    <?= $specific_questions ?>
                    <?= $responder_is_busy ?>
                    <?= $non_core_client_7 ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>
                </td>
            </tr>
            <tr>
                <td>
                    <h3>Нет времени/Не удобно разговаривать <a name="П_15_1"> П-15_1 </a></h3>

                    <p><b><i><u>НЕТ ВРЕМЕНИ НА ВСТРЕЧУ/ЗВОНОК:</span></u></i></b></p>
                    <p><b><i><u>Вариант 1: </span></u></i></b><span class="text-muted">"<i>Имя ЛПР</i>", </span>общение с нашим специалистом займет не более 10 минут. </a></p>
                    <p>Но за это время у вас появится представление о том, что мы <b>действительно</b> можем быть полезны для вас. </span></p>
                    <p>Вам удобнее на этой неделе или на следующей?</span></p>
                    <p>В <i><span class="text-muted">понедельник </span></i>в первой половине дня Вас устроит? <i><span class="text-muted">(записать)</span></i></span></p>



                    <p><b><i><u>НЕ УДОБНО РАЗГОВАРИВАТЬ:</span></u></i></b></p>
                    <p><i><span class="text-muted">"Имя ЛПР", </span></i>когда я могу Вам перезвонить, чтобы продолжить
  разговор? <span class="text-muted">(<i>записать</i>).</span></span></p>
                    <p>Хорошо, тогда <i><span class="text-muted">"назвать
  дату и время"</span></i><span class="text-muted"> </span>я Вам перезвоню. </span></p>
                    <p>Всего доброго, до свидания! <a href="<?= $page_url ?>#Р_2">_Р-2</span></a></p>

                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Пришлите КП, факс, e’mail <a name="П_16">П-16</a> </h3>
                    <p>&nbsp;</p>
                    <p><b><i><u>Вариант
  1:</span></u></i></b>
  <i><span class="text-muted">"Имя ЛПР",</span></i><span class="text-muted"> </span>сейчас я могу выслать Вам лишь
  брошюру. Она даст только общую информацию, но не ответит на Ваши вопросы. А с нашим специалистом Вы сможете более предметно обсудить варианты, условия, а также выгоды сотрудничества с нами.</span></p>
                    <p>В <i><span class="text-muted">понедельник</span></i>
  в 10 утра Вам удобно? <span class="text-muted">(<i>или называет другое время,
  записать</i>) </span></span></p>
                    <p><span class="text-muted">&nbsp;</span></p>
                    <p><b><i><u>Вариант 2:</span></u></i></b></p>
                    <p>Хорошо. Продиктуйте, пожалуйста, ваш электронный
  адрес <span class="text-muted">(<i>записываем</i>)</span> Выслать на Ваше
  имя? <span class="text-muted">(<i>записываем</i>)</span></span></p>
                    <p>Подскажите, пожалуйста, у вас есть группы в соц. сетях? Чтобы лучше узнать, чем Вы занимаетесь, и что может быть Вам интересно<span
                                    class="text-muted">…(<i>записать)</i> </span></span></p>
                    <p class="bg-primary">Портрет клиента</p>
                    <p class="bg-primary"><span class="text-muted">(<i>деликатно объяснить</i>) </span><span
                                class="text-muted">"<i>Имя ЛПР</i>", </span>чтобы подобрать то, что нужно именно Вам, &nbsp;ответьте,
  пожалуйста, на несколько вопросов: </span></p>
                    <ol class="bg-primary">
                        <li>Чем занимается ваша компания?</li>
                        <li>Вы тратите бюджет на рекламу?</li>
                        <li>У вас есть специалисты по маркетингу?</li>
                    </ol>

                    <p>Спасибо, <span
                                    class="text-muted">"<i>Имя ЛПР</i>", </span>я вышлю вам наше предложение. </span></p>
                    <p>Я
  перезвоню через пару дней. Вы успеете посмотреть материалы? <span
                                    class="text-muted">(<i>записать</i></span><span class="text-muted">)</span><a href="<?= $page_url ?>#Р_2_1">____Р-2_1</span></a></p>

                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3> Дайте телефон/Сами перезвоним <a name="П_17">П-17</a></h3>

                    <p><b><i><u>ЕСЛИ ДОГОВОРИЛИСЬ ОБ ОТПРАВКЕ
  ПИСЬМА:</span></u></i></b>
  </span></p>
                    <p><span class="text-muted">"<i>Имя ЛПР</i>", </span>у нас
  многоканальный телефон и на одном номере работают несколько человек. Поэтому
  будет удобнее, если мы перезвоним сами.</span></p>
                    <p><span class="text-muted">К тому же Вы уже успеете
  ознакомиться с информацией</span>. Удобно если я перезвоню через пару дней? <span class="text-muted">(<i>повторить названные дату и время)</i></p>


                    <p><b><i><u>ДАЙТЕ ТЕЛЕФОН:</span></u></i></b></p>
                    <p><b><i><u>Вариант 1:</span></u></i></b> <span class="text-muted">"<i>Имя ЛПР</i>", </span>у нас многоканальный телефон и на
  одном номере работают несколько человек. Поэтому будет удобнее, если мы
  перезвоним сами.</span></p>
                    <p>Скажите, в какое время лучше перезвонить?
  <span class="text-muted">(<i>записать</i>)</span></span></p>
                    <p><b><i><u>Дает информацию</span></u></i></b>:
  Договорились, мы перезвоним <span class="text-muted">(<i>повторить названные
  дату и время)</i></span></span></p>
                    <p><b><i><u>Вариант 2:</span></u></i></b> Давайте поступим следующим образом. Я отправлю Вам информационные
  материалы. Там есть все наши контактные данные: телефоны, адрес почты, сайт,
  а также презентация о нас и наших услугах. Если вас что-нибудь заинтересует,
  вы всегда сможете связаться с нами и мы будем рады оказать вам услуги </span></p>
                    <p><a href="<?= $page_url ?>#П_16">П-16 Пришлите КП, факс, e’mail</span></a>&nbsp;&nbsp;
                        <i><span class="text-muted">(последний вариант)</span></i></p>

                    <p>
                        <b><i><u>Дайте Ваш телефон 2: </span></u></i></b>Запишите, пожалуйста, наш телефон &nbsp;<span class="bg-primary"><?=$company_phone?></span>.</span></p>
                    <p>Также запишите наш сайт &nbsp;<span class="bg-primary"><?=$company_site?></span>.</span></p>
                    <p>Мы будем ждать Вашего звонка и с Вашего позволения, сами
  перезвоним через некоторое время.</span></p>

                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Сейчас не актуально <a name="П_18">П-18</a></h3>
                    <h4>Уже купили</h4>
                    <p>Скажите, пожалуйста, а ваш сайт уже готов и работает? Какой у него адрес? <span class="text-muted">(записать адрес сайта)</span> </p>
                    <p><u><b>Если не готов: </b></u> если вы закажете сайт у нас сегодня, то завтра он уже будет готов и им можно будет пользоваться - наши специалисты все сами сделают.<br>
                    Стоимость разработки сейчас - 10 000. Я точно уверен(а), что это интересней, чем у ваших разработчиков.</p>
                    <p>Давайте наш ведущий специалист с вами пообщается и сможет сделать более интересное предложение?</p>
                    <h4>Не актуально</h4>
                    <p>Скажите, а не актуально по какой причине?<span class="text-muted"> <i>(дать ответить и выбрать ответ из списка возражений
  справа)</i></span></span></p>
                    <p><span class="text-muted">Скажите, а когда этот вопрос будет актуален для Вас? (<i>записать</i>)</span></p>
                    <p>Хорошо, <i><span class="text-muted">"Имя ЛПР", </span></i>тогда <span class="text-muted">"<i>примерная дата: месяц, год</i>" </span>я Вам перезвоню. </span></p>
                    <p>А сейчас давайте я вышлю Вам наши информационные материалы. <a href="<?= $page_url ?>#П_33">Последний вариант&nbsp;&nbsp; П-33</span></a></p>

                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Надо подумать/ Посоветоваться <a name="П_20">П-20 </a></h3>
                    <p>А что Вас смущает? <i><span class="text-muted">(дать ответить)</span></i></span></p>
                    <p> &nbsp;</p>
                    <p><b><i><u>Вариант 1:</span></u></i></b> <i><span class="text-muted">"Имя ЛПР",</span></i> может быть Вам
  нужна дополнительная информация? Мы можем договориться о консультации нашего
  специалиста. Например, в <i><span class="text-muted">понедельник</span></i> в
  10 утра Вам удобно?</span></p>

                    <p><b><i><u>Вариант 2:</span></u></i></b> <i><span class="text-muted">"Имя ЛПР",</span></i> чтобы вам было
  легче думать, давайте еще раз вспомним все выгоды, которые Вам даст
  сотрудничество с нами.</span></p>
                    <p><i><span class="text-muted">Далее
  переходим на </span></i> &nbsp;<a href="<?= $page_url ?>#П_11_2">Ваши
  преимущества/Выгоды&nbsp;&nbsp; П-11_2</span></a></p>
                    <p> &nbsp;</p>
                    <p><b><i><u>Вариант 3: </span></u></i></b><i><span class="text-muted">"Имя ЛПР", </span></i>у нас сейчас проходит акция &nbsp;<span class="bg-primary">В честь 10 лет компании до конца сентября стоимость разработки всего 10 000 вместо 20.</span>Давайте ещё раз я расскажу о том, <b>что</b> вы  получите.</p>
                    <p><i>&nbsp;<span class="text-muted">Далее переходим на </span></span></i><a
                                href="<?= $page_url ?>#П_11_2">Ваши
  преимущества/Выгоды&nbsp;&nbsp; П-11_2</span></a></p>




                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Выходите за меня замуж <a name="П_21">П-21 </a></h3>
                    <p><span class="text-muted">(<i>очень вежливо</i>) "<i>Имя ЛПР</i>", </span>Ваше предложение
  очень лестно, но, не сочтите за грубость, Вы двенадцатый сегодня, кто это
  предлагает. Я предлагаю вернуться к <b>непосредственной</b> теме разговора. </span></p>
                    <p>Наш специалист готов &nbsp;<span class="bg-primary">пообщаться</span> с Вами, ответить на все Ваши вопросы и обсудить условия сотрудничества. Скажите, Вам удобнее &nbsp;<span class="bg-primary"></span> с ним завтра в первой или второй половине дня? <span class="text-muted">(<i>или называет другое время, записать</i>)</span></span></p>
                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Переходите ко мне работать/сколько получаете <a name="П_22">П-22 </a></h3>
                    <p><span class="text-muted">(<i>очень вежливо</i>) "<i>Имя ЛПР</i>",&nbsp;
  </span>я довольна своей работой. Поэтому
  предлагаю вернуться к непосредственной теме разговора. </span></p>
                    <p>Наш специалист готов &nbsp;<span class="bg-primary">пообщаться</span> с Вами и ответить на все Ваши вопросы и обсудить условия сотрудничества. Скажите, Вам удобнее &nbsp;<span class="bg-primary">поговорить</span> с ним завтра в первой или второй половине дня? <span class="text-muted">(<i>или называет другое время, записать</i>) </span></span></p>

                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Универсальный ответ <a name="П_24">П-24</a></h3>
                    <p><b><i><u>Вариант
  1:</span></u></i></b>
  <span class="text-muted">"<i>Имя ЛПР</i>", </span>я, к сожалению, не могу сейчас ответить на Ваш вопрос. </span></p>
                    <p>Но наш специалист <b>готов </b>&nbsp;<span class="bg-primary">пообщаться</span> с Вами, ответить на все Ваши вопросы и обсудить условия сотрудничества. Скажите, в <i><span class="text-muted">понедельник</span></i> в 10 Вас устроит? <span class="text-muted">(<i>или называет другое время, записать</i>)</span></span></p>
                    <p> &nbsp;</p>
                    <p> &nbsp;</p>
                    <p><b><i><u>Вариант
  2:</span></u></i></b>
  <span class="text-muted">"<i>Имя ЛПР</i>", </span>я, к сожалению, не обладаю
  расширенной информацией по данному вопросу, поэтому не могу ответить на Ваш
  вопрос. </span></p>
                    <p>Но я могу выслать Вам наше предложение или наш специалист <b>может </b>&nbsp;<span class="bg-primary">пообщаться</span> с Вами и ответить на этот и другие вопросы и обсудить условия сотрудничества. </span></p>
                    <p>Скажите,
  в <i><span class="text-muted">понедельник</span></i> в 10 Вас устроит? <span class="text-muted">(<i>или называет другое время, записать</i>)</span>
                    </p>
                    <p><span class="text-muted">&nbsp;</span></p>

                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Откуда у Вас этот номер <a name="П_26">П-26 </a></h3>
                    <p><span class="text-muted">"<i>Имя ЛПР</i>", </span>данные о вас – номер и название – указаны
  в базе данных по вашей сфере деятельности. </span></p>

                    <p><b><i><u>Если настаивает: </span></u></i></b><span class="text-muted">"<i>Имя ЛПР</i>", </span>моя задача, договориться об общении со специалистом.
  Мне предоставили базу для работы. Поэтому я звоню в вашу компанию.</span></p>

                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Решает головная организация <a name="П_27">П-27 </a></h3>
                    <p><span class="text-muted">"<i>Имя ЛПР</i>", </span>подскажите, как
  связаться с головной организацией? </span></p>
                    <p>Кого спросить? <span class="text-muted">(<i>записать)</i></span></span></p>

                    <p><i><span class="text-muted">"Имя ЛПР",</span></i><span class="text-muted"> </span>давайте поступим
  следующим образом.</span></p>
                    <p><i><span class="text-muted">Далее
  читаем</span></i><span class="text-muted"> </span><a href="<?= $page_url ?>#П_33">Последний вариант&nbsp;&nbsp; П-33</span></a></p>
                </td>
                <td  class="answers_menu">
                    <?=$call_meeting?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Портрет клиента <a name="П_29">П-29 </a></h3>
                    <p><b><i><span class="text-muted">Каждый раз, попадая на этот пункт, читать те вопросы, которые не
  были заданы в разговоре.</span></i></b></p>
                    <ol class="bg-primary">
                        <li>У вас есть сайт?</li>
                        <li>Вы хотите привлечь новых клиентов?</li>
                        <li>Вы хотите расширяться и повышать прибыль?</li>
                        <li>Вы хотите, чтобы вас находили люди через интернет?</li>
                        <li>Вы хотите, чтобы ваша компания вызывала доверие у потенциальных клиентов?</li>
                    </ol>

                    <p>У нас <b>есть</b> решение для вас… Но правильнее будет обсудить его с нашим специалистом. Он готов &nbsp;<span class="bg-primary">пообщаться</span> с Вами, ответить на все Ваши вопросы и обсудить  условия сотрудничества. </span></p>
                    <p>В <i><span class="text-muted">понедельник</span></i>
  в 10 утра Вам удобно? <span class="text-muted">(<i>или называет другое время,
  записать</i>).</span></span></p>

                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Уже работаем с Вами <a name="П_30">П-30 </a></h3>
                    <p>Приятно
  это слышать!</span></p>
                    <p>Скажите,
  кто из наших менеджеров работает с Вами?<i><span class="text-muted"> (записать)</span></i></span></p>
                    <p>А
  как давно Вы последний раз к нам обращались? </span></p>
                    <p><i><span class="text-muted">(если больше месяца, то продолжаем разговор по сценарию…)</span></i></p>

                    <p><b><i><u>Более месяца:</span></u></i></b> Тогда, я предлагаю, &nbsp;<span class="bg-primary">пообщаться</span> с нашим специалистом и обсудить, чем сотрудничество с нами будет выгодно для вас<b>.</b> </span></p>
                    <p>&nbsp;Это займет немного времени и ни к чему Вас не
  обязывает. &nbsp;</span></p>
                    <p>В <i><span class="text-muted">понедельник</span></i><span class="text-muted"> </span>в 10 утра Вас удобно? <span
                                    class="text-muted">(<i>или
  называет другое время, записать</i>) </span></span></p>

                    <p><b><i><u>Менее месяца: </span></u></i></b><i><span class="text-muted">"Имя ЛПР",<b> </b></span></i>скажите<i>, </i>чем еще на сегодняшний день мы можем
  быть Вам полезны?<i><span class="text-muted">(дать ответить, записать)</span></i></span></p>
                    <p>Всего доброго, до свидания.</span></p>




                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Работали с Вами, не понравилось <a name="П_31">П-31 </a></h3>
                    <p>А  что произошло? <i><span class="text-muted">(дать ответить, записать)</span></i></span></p>
                    <p>Скажите,
  а как давно это было? Кто из менеджеров с Вами работал? <i><span
                                        class="text-muted">(дать ответить, записать)</span></i></span></p>

                    <p>Уточните,
  пожалуйста,</span></p>
                    <ol class="bg-primary">
                        <li>У вас есть сайт?</li>
                        <li>Вы хотите привлечь новых клиентов?</li>
                        <li>Вы хотите расширяться и повышать прибыль?</li>
                        <li>Вы хотите, чтобы вас находили люди через интернет?</li>
                        <li>Вы хотите, чтобы ваша компания вызывала доверие у потенциальных клиентов?</li>
                    </ol>

                    <p>Спасибо
  за информацию. Я обязательно передам ее нашему Руководителю. Уверена, мы учтем
  ее и сделаем все возможное, чтобы такие ситуации не повторялись.</span></p>
                    <p>А сейчас я предлагаю назначить время и &nbsp;<span class="bg-primary">пообщаться</span> с нашим специалистом, чтобы понять, чем мы можем быть полезны Вам сегодня.</span></p>
                    <p> &nbsp;</p>

                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Где вы находитесь <a name="П_32">П-32 </a></h3>
                    <p><i><span class="text-muted">"Имя ЛПР",</span></i><span class="text-muted"> </span>Основные представительства нашей компании находется в Загребе <span class="text-muted">(Хорватия)</span> и Санкт-Петербурге.</span></p>
                    <p><span class="bg-primary">Также наши специалисты находятся в разных частях страны и работают удаленно</span>. </span></p>
                    <p> &nbsp;</p>
                    <p>Вы можете связаться с ними по телефону &nbsp;<span class="bg-primary"><?=$company_phone?></span>.</span></p>
                    <p>Наш сайт: &nbsp;<span class="bg-primary"><?=$company_site?></span><i>.</span></i></p>
                    <p><b>Если настаивает на адресе:</b> Мы работаем с клиентами по всей России и у нас не возникает необходимости в личной встрече с клиентом.<br>
                        К сожалению у нас нет помещения, в котором мы могли бы с вами встретиться.<br>
                        Если Вы настаиваете - я могу предоставить вам наш юридический адрес - его нужно уточнить у бухгалтерии <span class="text-muted"> - я отправлю на почту</span>
                    </p>
                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?><?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Последний вариант <a name="П_33">П-33 </a></h3>
                    <p><i><span class="text-muted">"Имя ЛПР",</span></i><span class="text-muted"> </span>предлагаю
  такой вариант: я вышлю вам информационные материалы. Если вас что-нибудь
  заинтересует, вы всегда сможете связаться с нами и мы будем рады оказать вам
  услуги.</span></p>
                    <p>Продиктуйте, пожалуйста, адрес Вашей электронной почты. <i><span class="text-muted">(записать) </span></i>Выслать на <i><span class="text-muted">(Имя ЛПР) </span></i>?
  <i><span class="text-muted">(записать)</span></i></span></p>
                    <p>Сегодня же вышлю информацию.&nbsp; Вы позволите мне
  позвонить Вам&nbsp; через некоторое время? <i><span class="text-muted">(дать
  ответить) </span></i></span></p>
                    <p>Договорились, всего доброго, до свидания.</span></p>


                </td>
                <td class="answers_menu">
                    <?= $call_meeting ?>
                    <?= $non_core_client ?>
                    <?= $what_do_you_offer ?>
                    <?= $have_not_heard_before ?>
                    <?= $your_advantages ?>
                    <?= $how_much ?>
                    <?= $send_an_offer ?>
                    <?= $parent_company_decides ?>
                    <?= $already_work_with_you ?>
                    <p>&nbsp;</p>
                    <?=$what_is_the_secret?>
                    <p>&nbsp;</p>
                    <?= $we_do_not_need_it ?>
                    <?= $we_work_with_another ?>
                    <?= $have_no_money ?>
                    <?= $expensive ?>
                    <?= $we_are_not_interesting_for_you ?>
                    <?= $wrong_contact ?>
                    <?= $have_no_time ?>
                    <?= $we_will_call_back ?>
                    <?= $already_have ?>
                    <?= $we_have_to_think ?>
                    <?= $worked_with_you_previously ?>
                    <p>&nbsp;</p>
                    <?= $universal_answer ?>
                    <?= $where_have_you_got_my_number ?>
                    <?= $what_is_your_location ?>
                    <?= $marry_on_me ?>
                    <?= $work_on_me ?>
                    <p>&nbsp;</p>
                    <?=$beginning_secretary?><?=$recall_after_email_lpr?><?=$beginning_lpr?><?=$recall_after_email_secretary?>

                </td>
            </tr>
            </tbody>
        </table>


        <a name="III_Этап">&nbsp;</a>
        <h2>III ЭТАП
            <small>"Завершение"</small>
        </h2>

        <table class="table table-striped table-condensed table-bordered">
            <tbody>
            <tr>
                <td>
                    <h3>Встреча/Звонок специалиста <a name="Р_1">Р-1 </a></h3>
                    <p><b><i><u>Если звонок: </span></u></i></b>Уточните, пожалуйста Ваши полные Фамилию Имя
  Отчество. <i><span class="text-muted">(записать</span></i><span class="text-muted">)</span> Как правильно называется Ваша должность? <i><span
                                        class="text-muted">(записать)</span></i></span></p>
                    <p>Дайте, пожалуйста, ваш прямой номер телефона и
  электронную почту для связи. <span class="text-muted">(<i>записать</i>)</span></span></p>
                    <p><span class="text-muted">Подскажите, пожалуйста, у вас есть группы в соц. сетях? Чтобы лучше узнать, чем вы занимаетесь и что может быть Вам интересно…(<i>записать)</i> </span></p>

                    <p><b><i><u>Если встреча: </span></u></i></b><span class="text-muted">Избегать! Только по телефону!!! Выкручиваться как угодно. У нас распределенная компания. Все специалисты сидят в разных городах.</span>
<!--                        Давайте уточним, по какому адресу к Вам подъехать? <span-->
<!--                                class="text-muted">(<i>записать</i>) </span></span></p>-->
<!--                    <p>Есть какие-либо особые условия, чтобы к вам попасть, может надо оформить пропуск или позвонить с проходной по внутреннему номеру?.. <span class="text-muted">(<i>записать</i>)</span></span></p>-->

                    <p>Уточните, пожалуйста,</span></p>
                    <ol class="bg-primary">
                        <li>У вас есть сайт?</li>
                        <li>Вы хотите привлечь новых клиентов?</li>
                        <li>Вы хотите расширяться и повышать прибыль?</li>
                        <li>Вы хотите, чтобы вас находили люди через интернет?</li>
                        <li>Вы хотите, чтобы ваша компания вызывала доверие у потенциальных клиентов?</li>
                    </ol>
                    <p>Хорошо, <span class="text-muted">"<i>Имя ЛПР</i>" </span>тогда
  <i><span class="text-muted">"назвать согласованную дату"</span></i> в <i><span class="text-muted">"назвать согласованное время"</span></i> наш специалист свяжется
  с Вами.</span></p>
                    <p>Всего доброго. До свидания.</span></p>
                </td>
                <td>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Перезвонить <a name="Р_2">Р-2 </a></h3>
                    <p>Всего доброго, <span class="text-muted">"<i>Имя ЛПР</i>".
  </span>До свидания!</span></p>
                </td>
                <td>

                </td>
            </tr>
            <tr>
                <td>
                    <h3>Отправить информационные материалы <a name="Р_2_1">Р-2_1 </a></h3>
                    <p>Всего доброго, <span class="text-muted">"<i>Имя ЛПР</i>".
  </span></span></p>
                    <p>До свидания!</span></p>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <h3>Непрофильный клиент <a name="Р_4">Р-4 </a></h3>
                    <p>Все понятно. Спасибо за информацию.</span></p>
                    <p>До свидания<a href="<?= $page_url ?>#П31">.</span></a></p>
                </td>
                <td>

                </td>
            </tr>
            </tbody>
        </table>
    <?}

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uAuth=new common($this->uCore);
        if(!$this->uSes->access(2)) {
            header('Location: '.u_sroot);
            exit;
        }
        if($this->uSes->get_val("user_id")!=1&&$this->uSes->get_val("user_id")!=468484) {
            header('Location: '.u_sroot);
            exit;
        }

        $this->check_data();

//        $this->uCore->uInt_js('uPage','setup_uPage_page');
    }
}
$crm=new call_script($this);
ob_start();

$crm->print_script();

$this->page_content = ob_get_contents();
ob_end_clean();

include "templates/template.php";
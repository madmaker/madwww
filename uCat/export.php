<?php
namespace uCat;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uCat/classes/common.php";

class export {
    /**
     * @var int
     */
    public $print_data;
    /**
     * @var common
     */
    public $uCat;
    /**
     * @var uFunc
     */
    private $uFunc;
    /**
     * @var uSes
     */
    private $uSes;
    private $uCore;


    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);

        ob_start();
        if(!$this->uSes->access(2)) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">Вы не авторизованы</h1>
                <p>Пожалуйста, авторизуйтесь</p>
                <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
            </div>';
            return 0;
        }
        elseif(!$this->uSes->access(25)) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">У вас нет прав</h1>
                <p>У вас нет прав для доступа к этой странице</p>
            </div>';
            return 0;
        }
        else $this->print_data=1;

        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);

        $this->uFunc->incJs("/uCat/js/export.min.js");
        return 1;
    }
}
$uCat=new export($this);
if($uCat->print_data) {?>
    <h1 class="page-header">Экспорт</h1>
    <a href="javascript:void(0)" class="btn btn-primary btn-lg" style="margin: 0 auto; display: table" id="export_btn" onclick="uCat.items_export.run()">Экспортировать в Excel</a>

    <div>
        <h2>Инструкция</h2>
        <ol>
            <li>Нажмите кнопку "Экспортировать в Excel"</li>
            <li>Файл подготавливается сервером</li>
            <li>Когда будет готова - кнопка "Экспортировать в Excel" заменится на "Скачать файл"</li>
            <li>Нажмите на кнопку "Скачать файл"</li>
            <li>Вы скачаете таблицу со всеми товарами вашего сайта в формате Excel. Описание столбцов таблицы ниже на этой странице</li>
        </ol>
        <div class="bs-callout">Вы можете скачать Excel-файл, внести в него изменения и загрузить назад на сайт.<br>Сайт обновит информацию о товарах согласно файлу</div>
    </div>

    <div>
        <h2>Описание полей</h2>
        <h3>Основная информация о товаре</h3>
        <dl class="dl-horizontal">
            <dt>ID товара на сайте</dt>
            <dd>Число. Идентификатор товара на сайте</dd>
            <dt>UUID товара</dt>
            <dd>Число-буквенный уникальный идентификатор товара на сайте или кассе</dd>
            <dt>ID варианта товара</dt>
            <dd>Число - если у товара несколько вариантов, то здесь отображается ID варианта</dd>
            <dt>Вариант по умолчанию</dt>
            <dd>Если у товара несколько вариантов, то один из вариантов должен быть основным - по умолчанию<br>
            0 - нет<br>
            1 - вариант по умолчанию
            </dd>
            <dt>Название товара</dt>
            <dd>Текст - название товара</dd>
            <dt>Описание товара</dt>
            <dd>Текст в формате HTML - Описание товара</dd>
            <dt>Артикул</dt>
            <dd>Число-буквенный артикул товара</dd>
            <dt>Цена товара</dt>
            <dd>Дробное число</dd>
            <dt>Перечеркнутая цена товара</dt>
            <dd>Дробное число</dd>
            <dt>Цену нужно запрашивать</dt>
            <dd>Если 1 - вместо кнопки "Купить" отображается кнопка "Запросить цену"<br>
            По умолчанию 0</dd>
            <dt>Цена указана ориентировочно</dt>
            <dd>Если 1 - рядом с ценой отображается звездочка, а ниже будет подпись о том, что цена указана ориентировочно и ее нужно уточнять<br>
            По умолчанию 0</dd>
            <dt>URL изображения товара</dt>
            <dd>URL - адрес в интернете</dd>
            <dt>ID основной категории товара</dt>
            <dd>Число. Идентификатор основной категории товара</dd>
            <dt>Название основной категории </dt>
            <dd>Текст</dd>
            <dt>ID основного раздела для категории товара</dt>
            <dd>Число. Идентификатор основного раздела, которому принадлежит основная категория товара</dd>
            <dt>Название основного раздела</dt>
            <dd>Текст</dd>
            <dt>URL товара</dt>
            <dd>Человекопонятный адрес страницы товара. Вместо сайт.ru/uCat/item/123 можно сделать /uCat/item/iphone</dd>
            <dt>ID файла товара</dt>
            <dd>Число. Если товар - электронный, то после его покупки, клиенту выдается ссылка на скачивание этого файла</dd>
            <dt>Адрес файла товара</dt>
            <dd>URL. Если товар - электронный, то после его покупки, клиенту выдается ссылка на скачивание этого файла</dd>
            <dt>Остаток товара</dt>
            <dd>Число. Количество товара в остатках</dd>
            <dt>ID наличия товара</dt>
            <dd>Число - идентификатор наличия товара на сайте</dd>
            <dt>Название наличия</dt>
            <dd>Текст - название наличия товара на сайте</dd>
            <dt>Описание наличия</dt>
            <dd>Число - описание наличия товара на сайте</dd>
            <dt>Тип наличия</dt>
            <dd>Число.<br>
                1 - Можно купить<br>
                2 - Нет в наличии<br>
                3 - Купить нельзя<br>
                4 - Под заказ<br>
                5 - Осталось мало
            </dd>


            <dt>ID типа товара</dt>
            <dd>Число - идентификатор типа товара</dd>
            <dt>Название типа товара</dt>
            <dd>Текст - название типа товара</dd>
            <dt>Базовый тип товара</dt>
            <dd>Число.<br>
                0 - Обычный товар<br>
                1 - Электронный товар, например электронная книга или файл, или песня. После оплаты такого товара, клиенту выдается ссылка на скачивание этого товара.
            </dd>
            <dt>ID единицы измерения на сайте</dt>
            <dd>Число - идентификатор единицы измерения товара на сайте</dd>
            <dt>Название единицы измерения</dt>
            <dd>Текст - название единицы измерения товара</dd>
            <dt>Является единицей измерения по умолчанию</dt>
            <dd>0 - нет<br>
            1 - да. Если 1, то эта единица измерения присваивается всем товарам по умолчанию</dd>
            <dt>SEO - Ключевые слова товара</dt>
            <dd>Текст - ключевые слова товара для поисковой системы</dd>
            <dt>SEO - Название товара</dt>
            <dd>Текст - название товара для поисковой системы.<br>Если не заполнено, то берется обычное название товара</dd>
            <dt>SEO - Описание товара</dt>
            <dd>Текст - описание товара для поисковой системы<br>Если не заполнено, то берется обычное описание товара</dd>
            <dt>Загружать на Яндекс Маркет (ЯМ)</dt>
            <dd>1 - Товар будет выгружаться на Яндекс Маркет<br>
                0 - Не будет выгружаться
            </dd>
            <dt>ЯМ - Описание</dt>
            <dd>Текст - Описание товара для Яндекс Маркета.<br>Если не заполнено, то берется обычное описание товара</dd>
            <dt>ЯМ - Страна производства</dt>
            <dd>Текст - страна производства товара для Яндекс Маркета</dd>
            <dt>ЯМ - Есть гарантия производителя</dt>
            <dd>Для Яндекс Маркета<br>
                1 - есть<br>
                0 - нет
            </dd>
            <dt>ЯМ - Производитель</dt>
            <dd>Текст - наименование производителя товара для Яндекс Маркета</dd>
            <dt>ЯМ - Товар можно купить без предварительного заказа</dt>
            <dd>Для Яндекс Маркета<br>
            0 - нельзя. Необходимо предварительно оформить заказ этого товара. Просто прийти в магазин или пункт выдачи и купить на месте нельзя.<br>
            1 - можно.
            </dd>
            <dt>ЯМ - Доступен самовывоз</dt>
            <dd>Яндекс Маркет - покупатель может забрать товар самостоятельно из магазина или пункта выдачи</dd>
            <dt>ЯМ - Доступна доставка</dt>
            <dd>Яндекс Маркет - вы можете доставить этот товар покупателю</dd>
            <dt>ЯМ - Время доставки, сек</dt>
            <dd>Яндекс Маркет - время, необходимое магазину на доставку товара. <b>Указывается в секундах!!!</b></dd>
            <dt>ЯМ - Стоимость доставки</dt>
            <dd>Яндекс Маркет - стоимость доставки товара</dd>
            <dt>Parts - код запчасти по производителю</dt>
            <dd>Для модуля "Запчасти" - код запчасти по производителю</dd>
            <dt>Parts - код запчасти по поиску</dt>
            <dd>Для модуля "Запчасти" - код запчасти по поиску</dd>
            <dt>Parts - Оригинал или замена</dt>
            <dd>Для модуля "Запчасти"<br>
            0 - Оригинал<br>
            1 - Замена
            </dd>
        </dl>
        <h3>Характеристики товара</h3>
        <dl class="dl-horizontal">
            <?$fields=$uCat->uCat->get_site_fields_and_fields_types("field_title,field_comment,field_type_title,field_type_descr,field_sql_type,field_style");
            foreach ($fields AS $i=>$field) {?>
                <dt><?=$field->field_title?></dt>
                <dd>
                    <b>Комментарий</b>: <?=nl2br($field->field_comment)?><br>
                    <b>Тип поля</b>: <?=$field->field_type_title?><br>
                    <b>Описание типа</b>: <?=$field->field_type_descr?><br>
                    <b>Допустимые значения</b>: <?
                    if($field->field_sql_type==="INT") print "Целое число";
                    elseif($field->field_sql_type==="DOUBLE") print "Дробное число";
                    elseif($field->field_sql_type==="TINYTEXT") print "Короткий текст";
                    elseif($field->field_sql_type==="TEXT") print "HTML-текст с поддержкой HTML-разметки и тегов";
                    elseif($field->field_sql_type==="!unused") print "Не используется";
                    ?><br>
                    <b>Стиль отображения</b>: <?
                    if($field->field_style==="hidden") print "Не отображается на сайте";
                    elseif($field->field_style==="text line") print "Строка текста";
                    elseif($field->field_style==="date") print "Дата";
                    elseif($field->field_style==="datetime") print "Дата и время";
                    elseif($field->field_style==="multiline") print "Блок текста на несколько строк";
                    elseif($field->field_style==="link") print "Ссылка";
                    elseif($field->field_style==="html text") print "HTML-текст с поддержкой HTML-разметки и тегов";
                    ?><br>
                </dd>
            <?}?>
        </dl>
        <h3>Опции вариантов товара</h3>

        <dl class="dl-horizontal">
            <?$options=$uCat->uCat->get_site_options("option_name,site_id,option_type,option_display_style");
            foreach ($options AS $i=>$option) {?>
                <dt><?=$option->option_name?></dt>
                <dd>
                    <b>Тип опции</b>: <?
                    $option->option_type=(int)$option->option_type;
                    if($option->option_type===0) print "Текст";
                    elseif($option->option_type===1) print "Цвет";
                    ?><br>
                    <b>Стиль отображения опции</b>: <?
                    $option->option_display_style=(int)$option->option_display_style;
                    if($option->option_display_style===0) print "Таблица";
                    elseif($option->option_display_style===1) print "В строку";
                    ?><br>
                </dd>
            <?}?>
        </dl>
    </div>
<?}

$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/template.php';


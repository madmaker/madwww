<ul class="list-unstyled">
    <li><a href="http://madwww.ru/uPage/wiki_uCat_tutorial" target="_blank">Обучение работе с каталогом</a></li>
    <li>&nbsp;</li>
    <?//ITEM
    if($this->mod=='uCat'&&$this->page_name=='item') {?>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_item_how_to_create" target="_blank">Создать товар</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_item_how_to_edit" target="_blank">Изменить товар</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_item_how_to_edit" target="_blank">Прикрепить к категориям</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_fields" target="_blank">Задать значения характеристик</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_item_how_to_set_settings" target="_blank">Настройки товаров</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_item_how_to_delete" target="_blank">Удалить товар?</a></li>
        <li>&nbsp;</li>
    <?}?>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_item_how_to_attach_to_cat" target="_blank">Как работать с товаром?</a></li>
    <?//ART
    if($this->mod=='uCat'&&$this->page_name=='article') {?>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_art_how_to_attach_to_item" target="_blank">Добавить статью к товару</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_art_how_to_edit" target="_blank">Редактировать статью</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_art_how_to_unattach" target="_blank">Убрать статью из товара</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_art_settings" target="_blank">Настройки статей</a></li>
        <li>&nbsp;</li>
    <?}?>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_arts" target="_blank">Как работать со статьями?</a></li>
    <?//CAT
    if($this->mod=='uCat'&&$this->page_name=='items') {?>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_cat_how_to_create" target="_blank">Создать новую категорию</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_cat_how_to_edit" target="_blank">Редактировать категорию</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_cat_how_to_attach_sect" target="_blank">Прикрепить к разделу</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_cat_how_to_attach_items" target="_blank">Прикрепить товары</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_cat_how_to_delete" target="_blank">Удалить</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_cat_how_to_set_settings" target="_blank">Настройки категорий</a></li>
        <li>&nbsp;</li>
    <?}?>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_cats" target="_blank">Как работать с категориями?</a></li>
    <?//SECT
    if($this->mod=='uCat'&&$this->page_name=='cats') {?>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_sect_how_to_create" target="_blank">Создать раздел</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_sect_how_to_edit" target="_blank">Редактировать раздел</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_sect_how_to_attach_cats" target="_blank">Прикрепить категории</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_sect_how_to_delete" target="_blank">Удалить раздел</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_sect_setttings" target="_blank">Настройки раздела</a></li>
        <li>&nbsp;</li>
    <?}?>
    <?//ORDERS
    if($this->mod=='uCat'&&$this->page_name=='admin_orders') {?>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_admin_orders" target="_blank">Список заказов</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_order_info" target="_blank">Статусы заказа</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_price_request" target="_blank">Запросы цены</a></li>
        <li><a href="http://madwww.ru/uPage/wiki_uCat_orders_settings" target="_blank">Настройки заказов, доставки</a></li>
        <li>&nbsp;</li>
    <?}?>
    <li><a href="http://madwww.ru/uPage/wiki_uCat_orders" target="_blank">Как работать с заказами?</a></li>
    <li><a href="http://madwww.ru/uPage/wiki_uCat_sects" target="_blank">Как работать с разделами?</a></li>
    <li><a href="http://madwww.ru/uPage/wiki_uCat_fields" target="_blank">Как работать с характеристиками?</a></li>
    <li><a href="http://madwww.ru/uPage/wiki_uCat_settings" target="_blank">Все настройки каталога</a></li>
    <li>&nbsp;</li>
    <li><a href="<?= u_sroot ?>uCat/sects" target="_blank">Список разделов</a></li>
    <li><a href="<?= u_sroot ?>uCat/articles" target="_blank">Список статей</a></li>
    <li><a href="<?= u_sroot ?>uCat/admin_orders" target="_blank">Список заказов</a></li>
    <li><a href="<?= u_sroot ?>uCat/admin_buy_orders" target="_blank">Список запросов цены</a></li>
</ul>
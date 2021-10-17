<div class="modal fade" id="u235_create_new_dg" tabindex="-1" role="dialog" aria-labelledby="u235_help_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="u235_help_dgLabel"><?=$this->uInt->text(array('creator','inline_create'),"Create - dg title"/*Создать*/)?></h4>
            </div>
            <div class="modal-body">
                <?if($this->access(7)) {?>
                <h3><?=$this->uInt->text(array('creator','inline_create'),"Content - mod title"/*Контент*/)?></h3>
                <ul class="list-unstyled">
<!--                    <li><a href="javascript:void(0);" onclick="uEditor_common.create_page()">--><?//=$this->uInt->text(array('creator','inline_create'),"Article"/*Cтатью*/)?><!--</a></li>-->
                    <li><a href="javascript:void(0);" onclick="uPage_common.create_page()"><?=$this->uInt->text(array('creator','inline_create'),"Page"/*Cтраницу*/)?></a></li>
                    <?if($this->uFunc->mod_installed("configurator")){?><li><a href="javascript:void(0);" onclick="configurator.products_admin.new_product_init()">Продукт</a></li><?}?>
<!--                    <li><a href="javascript:void(0);" onclick="uEditor_common.create_rubric()">--><?//=$this->uInt->text(array('creator','inline_create'),"Rubric"/*Рубрику*/)?><!--</a></li>-->
                    <?if($this->access(5)) {?>
<!--                    <li><a href="javascript:void(0);" onclick="uForms_common.create_form()">--><?//=$this->uInt->text(array('creator','inline_create'),"Form"/*Форму*/)?><!--</a></li>-->
                    <?}?>
                </ul>
                <?}
                if($this->uFunc->mod_installed('uEvents')&&$this->access(300)) {?>
                    <h3><?=$this->uInt->text(array('creator','inline_create'),"Events - mod title"/*События*/)?></h3>
                    <ul class="list-unstyled">
                        <li><a href="javascript:void(0);" onclick="uEvents_inline_create.new_event_init()"><?=$this->uInt->text(array('creator','inline_create'),"Event"/*Событие*/)?></a> </li>
                        <li><a href="javascript:void(0);" onclick="uEvents_inline_create.new_event_type_init()"><?=$this->uInt->text(array('creator','inline_create'),"Events type"/*Тип событий*/)?></a> </li>
                    </ul>
                <?}
                if($this->uFunc->mod_installed('uCat')&&$this->access(25)) {?>
                <h3><?=$this->uInt->text(array('creator','inline_create'),"Catalog - mod title"/*Каталог*/)?></h3>
                <ul class="list-unstyled">
                    <li><a href="javascript:void(0);" onclick="uCat_common.create_item()"><?=$this->uInt->text(array('creator','inline_create'),"Item"/*Товар*/)?></a></li>
                    <li><a href="javascript:void(0);" onclick="uCat_common.create_cat()"><?=$this->uInt->text(array('creator','inline_create'),"Category"/*Категорию*/)?></a></li>
                    <li><a href="javascript:void(0);" onclick="uCat_common.create_sect()"><?=$this->uInt->text(array('creator','inline_create'),"Section"/*Раздел*/)?></a></li>
                    <li><a href="javascript:void(0);" onclick="uCat_common.create_art()"><?=$this->uInt->text(array('creator','inline_create'),"Article about item"/*Статью о товаре*/)?></a></li>
                </ul>
                <?}
                if($this->uFunc->mod_installed('uSup')&&
                    ($this->access(8)||$this->access(9))
                ) {?>
                <h3><?=$this->uInt->text(array('creator','inline_create'),"Help desk - mod - title"/*Техподдержка*/)?></h3>
                <ul class="list-unstyled">
                    <?if($this->access(8)||$this->access(9)){?>
                    <li><a <?=($this->mod=='uSupport'&&$this->page_name=='requests')?'href="javascript:void(0)" onclick="uSup.new_request_init()"':'href="'.u_sroot.'uSupport/requests?create_new_request=1"'?>><?=$this->uInt->text(array('creator','inline_create'),"Request"/*Запрос*/)?></a></li>
                    <?}
                    if($this->access(8)){?>
                    <li><a href="javascript:void(0);" onclick="uSup_inline_create.new_com_init()"><?=$this->uInt->text(array('creator','inline_create'),"Company"/*Компанию*/)?></a></li>
                    <?}?>
                </ul>
                <?}
                if($this->uFunc->mod_installed('uKnowbase')&&$this->access(33)) {?>
                <h3><?=$this->uInt->text(array('creator','inline_create'),"Knowledge base - mod title"/*База знаний*/)?></h3>
                <ul class="list-unstyled">
                    <li><a href="javascript:void(0);" onclick="uKnowbase_inline_create.new_record_init()"><?=$this->uInt->text(array('creator','inline_create'),"Solution"/*Решение*/)?></a></li>
                </ul>
                <?}
                if($this->uFunc->mod_installed('uPeople')&&$this->access(10)) {?>
                <h3><?=$this->uFunc->getConf("mod_title","uPeople")?></h3>
                <ul class="list-unstyled">
                    <li><a href="javascript:void(0);" onclick="uPeople_inline_create.create_user_init()">Человек</a></li>
                </ul>
                <?}
                if($this->uFunc->mod_installed('obooking')/*&&$this->access(33)*/) {?>
                <h3>Записи</h3>
                <ul class="list-unstyled">
                    <li><a href="/obooking/calendar?create=1">Занятие</a></li>
                    <li><a href="javascript:void(0);" onclick="obooking_inline_create.new_manager_init()">Наставник</a></li>
                    <li><a href="javascript:void(0);" onclick="obooking_inline_create.new_client_init()">Ученик</a></li>
                    <li><a href="javascript:void(0);" onclick="obooking_inline_create.new_office_init()">Филиал</a></li>
                    <li><a href="javascript:void(0);" onclick="obooking_inline_create.new_class_init()">Класс</a></li>
                    <li><a href="javascript:void(0);" onclick="obooking_inline_create.new_rec_type_init()">Тип занятия</a></li>
                </ul>
                <?}?>
            </div>
        </div>
    </div>
</div>


<?
if($this->access(7)) {/*uPage and uEditor*/
    include_once 'uEditor/inc/inline_create_dialogs.php';
    include_once 'uPage/inc/inline_create_dialogs.php';
    include_once 'uNavi/inc/inline_create_dialogs.php';
    include_once 'uForms/inc/inline_create_dialogs.php';
    include_once 'uSlider/inc/inline_create_dialogs.php';
}

/*uCat*/
if($this->uFunc->mod_installed('uCat')) {
    include_once 'uCat/dialogs/inline_create.php';
}
//uSup
if($this->uFunc->mod_installed('uSup')) {
    include_once 'uSupport/inc/inline_create_dialogs.php';
}
//uSup
if($this->uFunc->mod_installed('uKnowbase')) {
    include_once 'uKnowbase/inc/inline_create_dialogs.php';
}
//uEvents
if($this->uFunc->mod_installed('uEvents')) {
    include_once 'uEvents/inc/inline_create_dialogs.php';
}
//obooking
if($this->uFunc->mod_installed('obooking')) {
    include_once 'obooking/dialogs/inline_create_dialogs.php';
}
//obooking
if($this->uFunc->mod_installed('uPeople')&&$this->access(10)) {
    include_once 'uPeople/dialogs/inline_create_dialogs.php';
}
if($this->uFunc->mod_installed('configurator')&&$this->access(10)) {
    include_once 'configurator/dialogs/products.php';
}
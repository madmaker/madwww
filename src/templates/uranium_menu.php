<?php

if(!isset($uSes)) {
    require_once 'processors/uSes.php';
    $uSes=new uSes($this);
}
if(!isset($uFunc)) {
    require_once 'processors/classes/uFunc.php';
    $uFunc=new \processors\uFunc($this);
}
$translator_1588459994=new \translator\translator(site_lang,'templates/uranium_menu.php');

$about_cookies_link= '';

if($uSes->access(12)) { ?>
    <div id="u235_spanel" class="<?=$uSes->get_val('u235_panel_visible')?'u235_panel_visible':''?>">
        <div class="btn-group btn-group-sm btn-group-justified">
            <div class="btn-group btn-group-sm"><a href="<?=u_sroot?>uAuth/profile" title="<?=$translator_1588459994->txt( 'my profile'/*Мой профиль*/)?>" class="btn btn-default btn-outline "><span class="icon-user"></span></a></div>

            <!--            <div class="btn-group btn-group-sm">
                <button class="btn btn-default btn-outline " onclick="jQuery('#u235_help_dg').modal('show');" title="--><?//=$translator_1588459994->txt("Help"/*Подсказка*/)?><!--"><span class="icon-question"></span></button></div>--><!--TODO-nik87 Вернуть, когда документацию доделаю-->
            <div class="btn-group btn-group-sm"><button class="btn btn-default btn-outline" onclick="u235_common.make_screenshots()" title="SCREENSHOTS"><span class="icon-eye"></span></button></div>
            <div class="btn-group btn-group-sm u235_hide_panel_btn"><button class="btn btn-default btn-outline" onclick="u235_admin_menu.hide_menu()" title="<?=$translator_1588459994->txt( 'Hide panel')?>"><span class="icon-left-open"></span></button></div>
            <div class="btn-group btn-group-sm u235_pin_panel_btn"><button class="btn btn-default btn-outline" onclick="u235_admin_menu.pin_menu()" title="<?=$translator_1588459994->txt( 'Pin panel')?>"><span class="icon-right-open"></span></button></div>

            <?if(
                $uSes->access(25)//edit uCat
                ||$uSes->access(5)//uForms manager
                ||$uSes->access(7)//content-management
                ||$uSes->access(8)//uSup operator
                ||$uSes->access(9)//uSup consultant
                ||$uSes->access(14)//uAuth Edit any user profile
                ||$uSes->access(33)//uKnowbase add new record
                ||$uSes->access(300)//uEvents add new event
            ) {?>
        </div>
        <div class="btn-group btn-group-sm btn-group-justified">
            <div class="btn-group btn-group-sm"><button class="btn btn-default btn-outline  " onclick="jQuery('#u235_create_new_dg').modal('show');" title="<?=$translator_1588459994->txt( 'Create'/*Создать*/)?>"><span class="icon-plus"></span></button></div>
            <?}


            if($this->mod==='uForms'&&$this->page_name==='form'&&$uSes->access(5)) {?>
                <div class="btn-group btn-group-sm"><a class="btn btn-default btn-outline  " href="<?=u_sroot?>uForms/admin_form_editor/<?=$uForms_form->form_id?>" title="<?=$translator_1588459994->txt( 'Switch on editor'/*Включить редактор*/)?>"><span class="icon-pencil"></span></a></div>
            <?}
            elseif($this->mod==='page'&&$uSes->access(7)) {?>
                <div class="btn-group btn-group-sm"><button class="btn btn-default btn-outline  " id="u235_panel_uEditor_eip_btn" onclick="uEditor.initEditor();" title="<?=$translator_1588459994->txt( 'Switch on editor')?>"><span class="icon-pencil"></span></button></div>
            <?}
            elseif($this->mod === 'uKnowbase'&&$this->page_name === 'solution') {
                if($uKb->edit_allowed){?>
                    <div class="btn-group btn-group-sm"><button class="btn btn-default btn-outline  " id="u235_panel_uKnowbase_eip_btn" onclick="uKnowbase.initEditor();" title="<?=$translator_1588459994->txt( 'Switch on editor')?>"><span class="icon-pencil"></span></button></div>
                <?}
            }
            elseif($this->page_name === 'records'&&$this->mod === 'uKnowbase'&&$uSes->access(33)) {?>
                <div class="btn-group btn-group-sm"><button class="btn btn-default btn-outline  " id="u235_panel_uKnowbase_eip_btn" onclick="uKnowbase.initEditor();" title="<?=$translator_1588459994->txt( 'Switch on editor')?>"><span class="icon-pencil"></span></button></div>
            <?}
            else if($this->mod==='uAuth'&&$this->page_name==='profile'&&$uSes->access(14)) {?>
                <div class="btn-group btn-group-sm"><a class="btn btn-default btn-outline " id="u235_admin_menu_edit_btn" href="<?=u_sroot.$this->mod?>/profile/<?=$uAuth->user_id?>"><span class="icon-pencil"></span></a></div>
            <?}
            else if(
                $uSes->access(7)||
                ($uSes->access(25)&&$this->mod === 'uCat')||
                ($this->mod === 'uPage'&&($this->page_name !== 'admin_pages')&&$uSes->access(7))||
                ($this->mod === 'uEvents'&&$this->page_name !== 'admin_events'&&$this->page_name !== 'admin_events_types'&&$uSes->access(300))
            ) {?>
                <div class="btn-group btn-group-sm"><button class="btn btn-default btn-outline " id="u235_admin_menu_edit_btn" onclick="u235_admin_menu.edit_mode()"><span class="icon-pencil"></span></button></div>
            <?}?>
        </div>

        <div id="u235_spanel_wrapper">
            <div class="container-fluid">
                <h4>MAD WWW</h4>
                    <ul>
<!--                        <li><a href="--><?//=u_sroot?><!--dashboard/home">--><?//=$translator_1588459994->txt("Dashboard btn")?><!--</a></li>-->
                        <?php if($uSes->access(17)){?>
                        <li><a href="<?=u_sroot?>uConf/sites_settings"><?=$translator_1588459994->txt( 'Sites settings')?></a></li>
                        <li><a href="<?=u_sroot?>dashboard/root">MAD <?=$translator_1588459994->txt( 'Dashboard btn'/*Настройки сайтов*/)?></a></li>
                        <?}?>
                    </ul>


                <?php

                //uDrive
                if($uSes->access(1900)) {?>
                    <hr>
                    <h4><?=$translator_1588459994->txt( 'uDrive'/*Диск*/)?>
                    </h4>
                    <ul>
                        <li><a href="<?=u_sroot?>uDrive/my_drive"><?=$translator_1588459994->txt( 'Drive'/*Диск сайта*/)?></a></li>
                        <?if($uSes->access(1901)) {?>
                        <li><a href="<?=u_sroot?>uDrive/file_types"><?=$translator_1588459994->txt( 'file types'/*Типы файлов*/)?></a></li>
                        <?}?>
                    </ul>
                <?}

               //uEvents
               if($uSes->access(300)) {?>
                   <hr>
                   <h4><?=$translator_1588459994->txt( 'uEvents'/*События*/)?>
                       <small><a href="<?=u_sroot ?>uConf/settings_admin?mod=uEvents"><span class="glyphicon glyphicon-cog pull-right " title="<?=$translator_1588459994->txt( 'uEvents settings'/*Настройки событий*/)?>"></span></a></small>
                   </h4>
                   <ul>
                       <li><a href="<?=u_sroot?>uEvents/admin_events_types"><?=$translator_1588459994->txt( 'Events types'/*Типы событий*/)?></a></li>
                       <li><a href="<?=u_sroot?>uEvents/admin_events"><?=$translator_1588459994->txt( 'events'/*События*/)?></a></li>
                   </ul>
               <?}
               ?>

                <?//CONTENT
                if($uSes->access(7)) {?>
                    <hr>
                    <h4><?=$translator_1588459994->txt( 'Content'/*Контент*/)?><?if($uSes->access(100)){?><small><a href="<?=u_sroot ?>uConf/settings_admin?mod=content"><span class="glyphicon glyphicon-cog pull-right " title="<?=$translator_1588459994->txt( 'Content settings'/*Настройки контента*/)?>"></span></a></small><?}?></h4>
                    <ul>
                        <li><a href="uEditor/pages_list"><?=$translator_1588459994->txt( 'Articles'/*Статьи*/)?></a></li>
                        <li><a href="uPage/admin_pages"><?=$translator_1588459994->txt( 'Pages'/*Страницы*/)?></a></li>
                        <li><a href="uRubrics/news_list"><?=$translator_1588459994->txt( 'News - new element name')?></a></li>
                        <li>&nbsp;</li>
                        <?php
                        $header_page_id=$this->uFunc->getConf('header_page_id', 'content');
                        $header_page_id_others=$this->uFunc->getConf('header_page_id_others', 'content');
                        $footer_page_id=$this->uFunc->getConf('footer_page_id', 'content');
                        $footer_page_id_others=$this->uFunc->getConf('footer_page_id_others', 'content');
                        if($this->uFunc->mod_installed('uCat') && $uCat_navbar_top_page_id = (int)$this->uFunc->getConf('uCat_navbar_top_page_id', 'uCat')) {?>
                            <li><a href="/uPage/<?=$uCat_navbar_top_page_id ?>"><?=$translator_1588459994->txt( 'uCat top panel - sidemenu button name')?></a></li>
                        <?}?>
                        <li><a href="/uPage/<?=$header_page_id ?>"><?=$translator_1588459994->txt( 'header template on homepage'/*Шаблон шапки на главной*/)?></a></li>
                        <?if($header_page_id!=$header_page_id_others) {?>
                        <li><a href="/uPage/<?=$header_page_id_others ?>"><?=$translator_1588459994->txt( 'header template on other pages'/*Шаблон шапки на остальных*/)?></a></li>
                        <?}?>
                        <li><a href="/uPage/<?=$footer_page_id ?>"><?=$translator_1588459994->txt( 'footer template on homepage'/*Шаблон подвала на главной*/)?></a></li>
                        <?if($footer_page_id!=$footer_page_id_others) {?>
                        <li><a href="/uPage/<?=$footer_page_id_others ?>"><?=$translator_1588459994->txt( 'footer template on other pages'/*Шаблон подвала на остальных*/)?></a></li>
                        <?}?>
                        <?if(site_id==8||site_id==3) {?>
                            <li>&nbsp;</li>
                            <li><a href="/uPage/page_templates"><?=$translator_1588459994->txt( 'Page templates - sidemenu btn')?></a></li>
                            <li><a href="/uPage/row_templates"><?=$translator_1588459994->txt( 'Row templates - sidemenu btn')?></a></li>
                            <li><a href="/uPage/el_templates"><?=$translator_1588459994->txt( 'Element templates - sidemenu btn')?></a></li>
                        <?}?>
                    </ul>
                <?}

                //CONFIGURATOR
                if($this->uFunc->mod_installed('configurator')&&$uSes->access(7)){?>
                    <hr>
                    <h4><?=$translator_1588459994->txt( 'Configurator - mod name'/*Конфигуратор*/)?><?if($uSes->access(7)){?><small><a href="<?=u_sroot ?>uConf/settings_admin?mod=configurator"><span class="glyphicon glyphicon-cog pull-right " title="<?=$translator_1588459994->txt( 'Configurator - settings'/*Настройки конфигуратора*/)?>"></span></a></small><?}?></h4>
                    <ul>
                        <li><a href="/configurator/products"><?=$translator_1588459994->txt( 'Products - configurator')?></a></li>
                        <li><a href="/configurator/configurations"><?=$translator_1588459994->txt( 'Configurations - sidemenu btn')?></a></li>
                    </ul>
                <?}

                //uForms
                if($uSes->access(6)) {?>
                    <hr>
                    <h4><?=$translator_1588459994->txt( 'uForms'/*Формы*/)?></h4>
                    <ul>
                        <li><a href="<?=u_sroot?>uForms/admin_forms"><?=$translator_1588459994->txt( 'Forms'/*Формы*/)?></a></li>
                    </ul>
                <?}

                //uCAT
                if($this->uFunc->mod_installed('uCat') && $uSes->access(25)) {?>
                    <hr>
                    <h4><?=$translator_1588459994->txt( 'uCat'/*Каталог*/)?><?if($uSes->access(25)){?><small><a href="<?=u_sroot ?>uConf/settings_admin?mod=uCat"><span class="glyphicon glyphicon-cog pull-right " title="<?=$translator_1588459994->txt( 'uCat settings'/*Настройки каталога*/)?>"></span></a></small><?}?></h4>
                    <ul>
                        <li><a href="uCat/my_orders"><?=$translator_1588459994->txt( 'uCat orders'/*Заказы*/)?><!-- <span class="label label-primary">new</span>--></a></li>
                        <li><a href="uCat/sects"><?=$translator_1588459994->txt( 'uCat sects'/*Разделы*/)?></a></li>
                        <?if($this->uFunc->getConf('buy_button_show','uCat')){?><li><a href="uCat/admin_buy_orders"><?=$translator_1588459994->txt( 'uCat price requests'/*Запросы цены*/)?></a></li><?}?>
                        <?php if((int)$this->uFunc->getConf('articles_used', 'uCat')) {?>
                            <li><a href="uCat/articles"><?=$this->uFunc->getConf('arts_label', 'uCat')?></a></li>
                        <?}?>
                        <?php if((int)$this->uFunc->getConf('used_activation_token',"uCat")) {?>
                            <li><a href="uCat/evotor_admin"><?=$translator_1588459994->txt( 'uCat evotor'/*ЭВОТОР*/)?></a></li>
                        <?}?>
                        <li><a href="uCat/import"><?=$translator_1588459994->txt( 'uCat import'/*Импорт*/)?></a></li>
                        <li><a href="uCat/export">Экспорт</a></li>
                    </ul>
                <?}

                if($this->uFunc->mod_installed('obooking')) {
                    //obooking
                    /*if($uSes->access(25)) {*/?>
                        <hr>
                        <h4>Записи</h4>
                        <ul>
                            <li><a href="obooking/calendar">Расписание</a></li>
                            <li><a href="obooking/clients">Ученики</a></li>
                            <li><a href="obooking/managers">Наставники</a></li>
                            <li><a href="obooking/classes">Классы</a></li>
                            <li><a href="obooking/offices">Филиалы</a></li>
                        </ul>
                    <?/*}*/
                }

                if($this->uFunc->mod_installed('uSup')) {
                //uSupport
                    if($uSes->access(9)||$uSes->access(33)||$uSes->access(200)) {?>
                        <hr>
                        <h4><?=$translator_1588459994->txt( 'uSup'/*Техподдержка*/)?><?if($uSes->access(200)){?><small><a href="<?=u_sroot ?>uConf/settings_admin?mod=uSup"><span class="glyphicon glyphicon-cog pull-right " title="<?=$translator_1588459994->txt( 'uSup settings'/*Настройки техподдержки*/)?>"></span></a></small><?}?></h4>
                        <ul>
                            <?php if($uSes->access(8)||$uSes->access(9)){?><li><a href="<?=u_sroot?>uSupport/companies/"><?=$translator_1588459994->txt( 'uSup companies'/*Компании*/)?></a></li><?}?>
                            <?php if($uSes->access(9)) {?><li><a href="<?=u_sroot?>uSupport/requests"><?=$translator_1588459994->txt( 'uSup requests'/*Запросы*/)?></a></li><?}?>
                            <?php if($uSes->access(9)) {?><li><a href="<?=u_sroot?>uSupport/request_admin_feedbacks_list"><?=$translator_1588459994->txt( 'uSup feedback'/*Отзывы клиентов*/)?></a></li><?}?>
                            <?php if($uSes->access(27)) {?><li><a href="<?=u_sroot?>uSupport/reports"><?=$translator_1588459994->txt( 'uSup reports'/*Отчеты*/)?></a></li><?}?>
                            <li><a href="<?=u_sroot?>uKnowbase/records"><?=$translator_1588459994->txt( 'uKnowbase'/*База знаний*/)?></a> <?if($uSes->access(200)){?><small><a href="<?=u_sroot ?>uConf/settings_admin?mod=uKnowbase"><span class="glyphicon glyphicon-cog pull-right " title="<?=$translator_1588459994->txt( 'uKnowbase settings'/*Настройки базы знаний*/)?>"></span></a></small><?}?></li>
                        </ul>
                    <?php }
                }

                //uVIBLOG
                if($this->uFunc->mod_installed('uViblog') && $uSes->access(4)) {?>
                    <hr>
                    <h4><?=$this->uFunc->getConf('how_to_call', 'uViblog')?><?if($uSes->access(4)){?><small><a href="<?=u_sroot ?>uConf/settings_admin?mod=uViblog"><span class="glyphicon glyphicon-cog pull-right " title="<?=$translator_1588459994->txt( 'uViblog settings'/*Настройки Видеоленты*/)?>"></span></a></small><?}?></h4>
                    <ul>
                        <li><a href="<?=u_sroot ?>uViblog/admin_list"><?=$translator_1588459994->txt( 'uViblog records list'/*Список записей*/)?></a></li>
                        <li><a href="<?=u_sroot ?>uViblog/records"><?=$translator_1588459994->txt( 'uViblog page'/*Лента*/)?></a></li>
                    </ul>
                <?php }

                //uPEOPLE
                if($this->uFunc->mod_installed('uPeople') && $uSes->access(10)) {?>
                    <hr>
                    <h4><?=$this->uFunc->getConf('mod_title', 'uPeople')?><?if($uSes->access(10)){?><small><a href="<?=u_sroot ?>uConf/settings_admin?mod=uPeople"><span class="glyphicon glyphicon-cog pull-right " title="<?=$translator_1588459994->txt( 'uPeople settings'/*Настройки модуля*/)?>"></span></a></small><?}?></h4>
                    <ul>
                        <li><a href="<?=u_sroot?>uPeople/users_list_admin"><?=$translator_1588459994->txt( 'uPeople people list'/*Список людей*/)?></a></li>
                        <li><a href="<?=u_sroot?>uPeople/profile_fields_admin"><?=$translator_1588459994->txt( 'uPeople profile fields'/*Поля профиля*/)?></a></li>
                    </ul>
                <?php }

                //uSubscr
                if($this->uFunc->mod_installed('uSubscr') && $uSes->access(23)) {?>
                    <hr>
                    <h4><?=$translator_1588459994->txt( 'uSubscr'/*Рассылки*/)?></h4>
                    <ul>
                        <li><a href="<?=u_sroot?>uSubscr/records"><?=$translator_1588459994->txt( 'uSubscr articles'/*Статьи*/)?></a></li>
                        <li><a href="<?=u_sroot?>uSubscr/groups"><?=$translator_1588459994->txt( 'uSubscr subjects'/*Темы рассылок*/)?></a></li>
                        <li><a href="<?=u_sroot?>uSubscr/users"><?=$translator_1588459994->txt( 'uSubscr receivers'/*Получатели*/)?></a></li>
                        <li><a href="<?=u_sroot?>uSubscr/mailings"><?=$translator_1588459994->txt( 'uSubscr process'/*Процесс отправки*/)?></a></li>
                        <li><a href="<?=u_sroot?>uSubscr/subscribe"><?=$translator_1588459994->txt( 'uSubscr subscribe page'/*Страница подписки*/)?></a></li>
                    </ul>
                <?php }

                //USERS
                if($uSes->access(15)) {?>
                    <hr>
                    <h4><?=$translator_1588459994->txt( 'uAuth'/*Пользователи*/)?></h4>
                    <ul>
                        <li><a href="<?=u_sroot ?>uAuth/users_list_admin"><?=$translator_1588459994->txt("site's users"/*Пользователи сайта*/)?></a></li>
                    </ul>
                <?}
                ?>

                <p>&nbsp;</p>

                <a href="<?=u_sroot?>uAuth/logout" class="btn btn-primary btn-block" style="border-radius: 0;"><span class="glyphicon glyphicon-log-out" ></span> <?=$translator_1588459994->txt( 'Logout btn'/*Выход*/)?></a>

            </div>
        </div>
    </div>

    <?php
    include 'help/inline_help.php';
    if($uSes->access(7)) {
        include 'uNavi/inc/eip_dialogs.php';
    }
    ?>


    <?include 'creator/inline_create.php'?>
<?php } elseif($uSes->access(2)) {?>
    <div class="u235_panel_buttons">
        <div class="btn-group btn-group-sm">
            <a href="<?=u_sroot?>uAuth/profile" title="<?=$translator_1588459994->txt( 'my profile'/*Мой профиль*/)?>" class="btn btn-primary "><span class="icon-user"></span></a>
            <a href="<?=u_sroot?>uAuth/logout" title="<?=$translator_1588459994->txt( 'Logout btn'/*Выход*/)?>" class="btn btn-primary "><span class="glyphicon glyphicon-log-out"></span></a>
        </div>
    </div>
<?}?>

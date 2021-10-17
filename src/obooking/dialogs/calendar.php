<?php
require_once 'translator/translator.php';
$dialogs_calendar_translator = new \translator\translator(
    site_lang,
    'obooking/dialogs/calendar.php'
);
?>
<div class="modal fade" id="obooking_calendar_new_record_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_calendar_new_record_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="obooking_calendar_new_record_dgLabel"><?= $dialogs_calendar_translator->txt(
                    'New record'
                ) ?></h4>
            </div>
            <div class="modal-body" id="obooking_calendar_new_record_dg_body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger pull-left" onclick="obooking_calendar.delete_record_confirm()"><?= $dialogs_calendar_translator->txt(
                    'Delete'
                ) ?></button>
                <button type="button" class="btn btn-default" onclick="obooking_calendar.copy_record_init(this)" id="obooking_calendar_new_record_dg_copy_btn"><?= $dialogs_calendar_translator->txt(
                    'Copy'
                ) ?></button>
                <button type="button" class="btn btn-primary" id="obooking_calendar_new_record_dg_save_btn" onclick="obooking_calendar.new_record_save()"><?= $dialogs_calendar_translator->txt(
                    'Create'
                ) ?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="obooking_calendar_copy_record" tabindex="-1" role="dialog" aria-labelledby="obooking_calendar_copy_recordLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="obooking_calendar_copy_recordLabel"><?= $dialogs_calendar_translator->txt('Select dates to copy') ?></h4>
            </div>
            <div class="modal-body container-fluid">
                <div id="obooking_calendar_copy_record_date_form_group">
                    <input id="obooking_calendar_copy_record_date_input" type="hidden" value="">

                    <div class="col-md-6 col-xs-12 col-sm-12 col-lg-6">
                        <div id="obooking_calendar_copy_record_date_datepicker" data-date=""></div>
                    </div>
                    <div class="col-md-6 col-xs-12 col-sm-12 col-lg-6" id="obooking_calendar_copy_record_dates_container">
                        <p><?=$dialogs_calendar_translator->txt('Selected dates to copy')?></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <p><?=$dialogs_calendar_translator->txt('Choose dates in Calendar to make a copy')?></p>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="obooking_calendar_managers_list_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_calendar_managers_list_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-90">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="obooking_calendar_managers_list_dgLabel">Наставники</h4>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_manager_init()"><span class="icon-plus"></span> Создать наставника</button>
            </div>
            <div class="modal-body" id="obooking_calendar_managers_list_dg_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_manager_init()"><span class="icon-plus"></span> Создать наставника</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="obooking_calendar_administrators_list_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_calendar_administrators_list_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-90">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="obooking_calendar_administrators_list_dgLabel">Администраторы</h4>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_administrator_init()"><span class="icon-plus"></span> Создать администратора</button>
            </div>
            <div class="modal-body" id="obooking_calendar_administrators_list_dg_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_administrator_init()"><span class="icon-plus"></span> Создать администратора</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="obooking_calendar_clients_list_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_calendar_clients_list_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-90">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="obooking_calendar_clients_list_dgLabel">Ученики</h4>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_client_init()"><span class="icon-plus"></span> Создать ученика</button>
            </div>
            <div class="modal-body" id="obooking_calendar_clients_list_dg_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_client_init()"><span class="icon-plus"></span> Создать ученика</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="obooking_calendar_rec_types_list_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_calendar_rec_types_list_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-90">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="obooking_calendar_rec_types_list_dgLabel">Типы занятий</h4>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_rec_type_init()"><span class="icon-plus"></span> Создать тип занятия</button>
            </div>
            <div class="modal-body" id="obooking_calendar_rec_types_list_dg_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_rec_type_init()"><span class="icon-plus"></span> Создать тип занятия</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="obooking_calendar_offices_list_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_calendar_offices_list_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-90">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="obooking_calendar_offices_list_dgLabel">Филиалы</h4>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_office_init()"><span class="icon-plus"></span> Создать филиал</button>
            </div>
            <div class="modal-body" id="obooking_calendar_offices_list_dg_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_office_init()"><span class="icon-plus"></span> Создать филиал</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="obooking_calendar_classes_list_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_calendar_classes_list_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-90">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="obooking_calendar_classes_list_dgLabel">Классы</h4>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_office_init()"><span class="icon-plus"></span> Создать филиал</button>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_class_init()"><span class="icon-plus"></span> Создать класс</button>
            </div>
            <div class="modal-body" id="obooking_calendar_classes_list_dg_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_office_init()"><span class="icon-plus"></span> Создать филиал</button>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_class_init()"><span class="icon-plus"></span> Создать класс</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="obooking_calendar_client_payment_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_calendar_client_payment_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="obooking_calendar_client_payment_dgLabel">Оплата занятия</h4>
            </div>
            <div class="modal-body" id="obooking_calendar_client_payment_dg_body">
                <input type="hidden" id="obooking_calendar_client_payment_client_id">
                <input type="hidden" id="obooking_calendar_client_payment_rec_id">


                <div class="container-fluid highlight">
                    <label>Статус</label>
                    <select class="form-control" id="obooking_calendar_client_payment_status">
                        <option value="1">Посетил</option>
                        <option value="0">Прогулял</option>
                    </select>
                </div>

                <div class="container-fluid highlight">
                    <h3>Оплата деньгами. Баланс: <span class="form-control-static" id="obooking_calendar_client_payment_balance_input"></span><button type="button" class="btn btn-success pull-right" onclick="obooking_calendar.rec_payment_save()">Оплатить деньгами</button></h3>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="obooking_calendar_client_payment_input">Сумма к оплате</label>
                            <input type="text" class="form-control" id="obooking_calendar_client_payment_input">
                            <span class="help-block muted">Эта сумма списывается с баланса</span>
                        </div>
                        <div class="col-md-4">
                            <label for="obooking_calendar_client_payment_paid_input">Оплачено по факту</label>
                            <input type="text" class="form-control" id="obooking_calendar_client_payment_paid_input">
                            <span class="help-block muted">Эта сумма вносится учеником сейчас</span>
                        </div>
                        <div class="col-md-4">
                            <label for="obooking_calendar_client_payment_type_selectbox">Способ оплаты</label>
                            <select id="obooking_calendar_client_payment_type_selectbox" class="form-control">
                                <option value="0">Наличными</option>
                                <option value="1">Картой</option>
                                <option value="2">Онлайн</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="container-fluid highlight">
                    <h3>Оплата абонементом<button id="obooking_calendar_rec_payment_subscription_save_btn" type="button" class="btn btn-success pull-right" onclick="obooking_calendar.rec_payment_subscription_save()">Оплатить абонементом</button></h3>
                    <div class="row">
                        <div class="col-md-12">
                            <label>Осталось занятий по абонементу: </label>
                            <span class="form-control-static" id="obooking_calendar_client_payment_subscription_balance_input"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

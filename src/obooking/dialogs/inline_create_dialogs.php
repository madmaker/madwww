<div class="modal fade" id="obooking_inline_create_manager_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_manager_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Новый наставник</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="obooking_inline_create_manager_name_form_gr">
                    <label for="obooking_inline_create_manager_name">Имя</label>
                    <input type="text" class="form-control" id="obooking_inline_create_manager_name">
                    <div class="bs-callout bs-callout-danger" id="obooking_inline_create_manager_name_warning" style="display: none"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_manager_create()">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="obooking_inline_create_administrator_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_administrator_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Новый администратор</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="obooking_inline_create_administrator_name_form_gr">
                    <label for="obooking_inline_create_administrator_name">Имя</label>
                    <input type="text" class="form-control" id="obooking_inline_create_administrator_name">
                    <div class="bs-callout bs-callout-danger" id="obooking_inline_create_administrator_name_warning" style="display: none"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_administrator_create()">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="obooking_inline_create_client_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_client_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Новый ученик</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="obooking_inline_create_client_name_form_gr">
                    <label for="obooking_inline_create_client_name">Имя</label>
                    <input type="text" class="form-control" id="obooking_inline_create_client_name">
                    <div class="bs-callout bs-callout-danger" id="obooking_inline_create_client_name_warning" style="display: none"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_client_create()">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="obooking_inline_create_office_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_office_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Новый филиал</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="obooking_inline_create_office_name_form_gr">
                    <label for="obooking_inline_create_office_name">Название</label>
                    <input type="text" class="form-control" id="obooking_inline_create_office_name">
                    <div class="bs-callout bs-callout-danger" id="obooking_inline_create_office_name_warning" style="display: none"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_office_create()">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="obooking_inline_create_class_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_class_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Новый класс</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="obooking_inline_create_class_name_form_gr">
                    <label for="obooking_inline_create_class_name">Название</label>
                    <input type="text" class="form-control" id="obooking_inline_create_class_name">
                    <div class="bs-callout bs-callout-danger" id="obooking_inline_create_class_name_warning" style="display: none"></div>
                </div>
                <div class="form-group" id="obooking_inline_create_class_office_id_form_gr">
                    <label for="obooking_inline_create_class_office_id">Офис</label>
                    <select class="form-control" id="obooking_inline_create_class_office_id"></select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_class_create()">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="obooking_inline_create_rec_type_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_rec_type_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Новый тип записи</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="obooking_inline_create_rec_type_name_form_gr">
                    <label for="obooking_inline_create_rec_type_name">Название</label>
                    <input type="text" class="form-control" id="obooking_inline_create_rec_type_name">
                    <div class="bs-callout bs-callout-danger" id="obooking_inline_create_rec_type_name_warning" style="display: none"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_rec_type_create()">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="obooking_inline_create_add_new_card_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_add_new_card_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Новая клубная карта</h4>
            </div>
            <div class="modal-body" id="obooking_inline_create_add_new_card_dg_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.add_new_card_save()">Добавить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="obooking_inline_create_add_new_subscription_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_add_new_subscription_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Новый абонемент</h4>
            </div>
            <div class="modal-body" id="obooking_inline_create_add_new_subscription_dg_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.add_new_subscription_save()">Добавить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="obooking_inline_create_edit_cards_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_edit_cards_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Редактор клубных карт</h4>
            </div>
            <div class="modal-body" id="obooking_inline_create_edit_cards_dg_body"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="obooking_inline_create_edit_subscriptions_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_edit_subscriptions_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Редактор абонементов</h4>
            </div>
            <div class="modal-body" id="obooking_inline_create_edit_subscriptions_dg_body"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="obooking_inline_create_edit_selected_subscription_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_edit_selected_subscription_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Редактор абонемента</h4>
            </div>
            <div class="modal-body" id="obooking_inline_create_edit_selected_subscription_dg_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button id="obooking_inline_create_edit_selected_subscription_dg_submit_btn" type="button" class="btn btn-primary" onclick="obooking_inline_create.edit_selected_subscription_type_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="obooking_inline_create_edit_selected_card_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_edit_selected_card_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Редактор клубной карты</h4>
            </div>
            <div class="modal-body" id="obooking_inline_create_edit_selected_card_dg_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button id="obooking_inline_create_edit_selected_card_dg_submit_btn" type="button" class="btn btn-primary" onclick="obooking_inline_create.edit_selected_card_type_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="obooking_inline_create_new_course_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_new_course_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Новое направление</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="obooking_inline_create_new_course_dg_input_formGroup">
                    <label for="obooking_inline_create_new_course_dg_input">Название направления</label>
                    <input type="text" id="obooking_inline_create_new_course_dg_input" class="form-control" onkeyup="obooking_inline_create.new_course_reset_errors()">
                    <p id="obooking_inline_create_new_course_dg_input_hint" class="help-block"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button id="obooking_inline_create_new_course_dg_submit_btn" type="button" class="btn btn-primary" onclick="obooking_inline_create.new_course_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="obooking_inline_create_new_client_status_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_new_client_status_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Новый статус для ученика</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="obooking_inline_create_new_client_status_dg_input_formGroup">
                    <label for="obooking_inline_create_new_client_status_dg_input">Название статуса</label>
                    <input type="text" id="obooking_inline_create_new_client_status_dg_input" class="form-control" onkeyup="obooking_inline_create.new_client_status_reset_errors()">
                    <p id="obooking_inline_create_new_client_status_dg_input_hint" class="help-block"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button id="obooking_inline_create_new_client_status_dg_submit_btn" type="button" class="btn btn-primary" onclick="obooking_inline_create.new_client_status_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="obooking_inline_create_new_order_source_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_new_order_source_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Новый источник заявки</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="obooking_inline_create_new_order_source_dg_input_formGroup">
                    <label for="obooking_inline_create_new_order_source_dg_input">Название источника заявки</label>
                    <input type="text" id="obooking_inline_create_new_order_source_dg_input" class="form-control" onkeyup="obooking_inline_create.new_order_source_reset_errors()">
                    <p id="obooking_inline_create_new_order_source_dg_input_hint" class="help-block"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button id="obooking_inline_create_new_order_source_dg_submit_btn" type="button" class="btn btn-primary" onclick="obooking_inline_create.new_order_source_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="obooking_inline_create_new_order_how_did_find_out_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_new_order_how_did_find_out_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Новый источник откуда узнали</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="obooking_inline_create_new_order_how_did_find_out_dg_input_formGroup">
                    <label for="obooking_inline_create_new_order_how_did_find_out_dg_input">Название источника откуда узнали</label>
                    <input type="text" id="obooking_inline_create_new_order_how_did_find_out_dg_input" class="form-control" onkeyup="obooking_inline_create.new_order_how_did_find_out_reset_errors()">
                    <p id="obooking_inline_create_new_order_how_did_find_out_dg_input_hint" class="help-block"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button id="obooking_inline_create_new_order_how_did_find_out_dg_submit_btn" type="button" class="btn btn-primary" onclick="obooking_inline_create.new_order_how_did_find_out_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="obooking_inline_create_new_order_status_dg" tabindex="-1" role="dialog" aria-labelledby="obooking_inline_create_new_order_status_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Новый статус заявки</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="obooking_inline_create_new_order_status_dg_input_formGroup">
                    <label for="obooking_inline_create_new_order_status_dg_input">Название статуса заявки</label>
                    <input type="text" id="obooking_inline_create_new_order_status_dg_input" class="form-control" onkeyup="obooking_inline_create.new_order_status_reset_errors()">
                    <p id="obooking_inline_create_new_order_status_dg_input_hint" class="help-block"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button id="obooking_inline_create_new_order_status_dg_submit_btn" type="button" class="btn btn-primary" onclick="obooking_inline_create.new_order_status_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

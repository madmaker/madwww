<div class="modal fade" id="uEvents_new_event_dg" tabindex="-1" role="dialog" aria-labelledby="uEvents_new_event_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEvents_new_event_dgLabel"><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"New event - dg title"/*Новое событие*/)?></h4>
            </div>
            <div class="modal-body" id="uEvents_new_event_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Close - btn txt"/*Закрыть*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uEvents_inline_create.new_event_do()"><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Create - btn txt"/*Создать*/)?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uEvents_new_event_type_dg" tabindex="-1" role="dialog" aria-labelledby="uEvents_new_event_type_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEvents_new_event_type_dgLabel"><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"New event type - dg title"/*Новый тип событий*/)?></h4>
            </div>
            <div class="modal-body" id="uEvents_new_event_type_body">
                <div class="form-group">
                    <label><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Event type title - input label"/*Название типа событий*/)?></label>
                    <input type="text" class="form-control" placeholder="<?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Even type title - input placeholder"/*Семинар*/)?>" id="uEvents_new_event_type_title">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Close - btn txt"/*Закрыть*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uEvents_inline_create.new_event_type_do()"><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Create - btn txt"/*Создать*/)?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uEvents_new_event_header_dg" tabindex="-1" role="dialog" aria-labelledby="uEvents_new_event_header_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEvents_new_event_header_dgLabel"><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"New event header - dg title"/*Новый заголовок*/)?></h4>
            </div>
            <div class="modal-body" id="uEvents_new_event_header_body">
                <div class="form-group">
                    <label><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Event header - input label"/*Новый заголовок*/)?></label>
                    <input type="text" class="form-control" placeholder="<?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Event header - input placeholder"/*Заголовок*/)?>" id="uEvents_new_event_header_input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Close - btn txt"/*Закрыть*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uEvents_inline_create.add_header_do()"><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Create - btn txt"/*Создать*/)?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uEvents_edit_event_dg" tabindex="-1" role="dialog" aria-labelledby="uEvents_edit_event_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEvents_edit_event_dgLabel"><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Edit event or header - dg title"/*Редактировать событие или заголовок*/)?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="form-control" id="uEvents_edit_event_id_input">
                <div class="form-group">
                    <label><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Event title - input label"/*Название*/)?></label>
                    <input type="text" class="form-control" id="uEvents_edit_event_title_input">
                </div>
                <div class="form-group">
                    <label><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Event position - input label"/*Позиция*/)?></label>
                    <input type="text" class="form-control" id="uEvents_edit_event_pos_input">
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" value="" id="uEvents_edit_event_is_hedaer_input">
                        <span ><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Is header - input label"/*это заголовок?*/)?></span>
                    </label>
                </div>

                <div class="bs-callout bs-callout-primary"><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Event position - descr"/*<b>Позиция</b> позволяет откорректировать положение события относительно других в списке.<br>Чем меньше позиция, тем выше событие в списке.<br>Возможны также отрицательные значения.*/)?>
                </div>

                <div class="bs-callout bs-callout-primary"><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Event header - descr"/*Вы можете отметить событие <b>заголовком</b> - тогда этот элемент не будет отображаться, как событие, а будет отображаться как заголовок в списках*/)?></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="uEvents_edit_event_delete_header_btn" onclick="uEvents_inline_create.delete_event_header_do()"><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Delete header - btn txt"/*Удалить заголовок*/)?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Close - btn txt"/*Закрыть*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uEvents_inline_create.edit_element_do()"><?=$this->uInt->text(array('uEvents','inline_create_dialogs'),"Save - btn txt"/*Сохранить*/)?></button>
            </div>
        </div>
    </div>
</div>
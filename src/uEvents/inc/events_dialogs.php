<div class="modal fade" id="uEvents_event_type_title_edit_dg" tabindex="-1" role="dialog" aria-labelledby="uEvents_event_type_title_edit_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEvents_event_type_title_edit_dgLabel"><?=$this->uInt->text(array('uEvents','events'),"Type title editor - dg title"/*Редактор названия типа событий*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><?=$this->uInt->text(array('uEvents','events'),"Event type title - input label"/*Введите новое название*/)?></label>
                    <input type="text" id="uEvents_event_type_title_edit_title" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uInt->text(array('uEvents','events'),"Close - btn txt"/*Закрыть*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uEvents_events_admin.edit_title_do()"><?=$this->uInt->text(array('uEvents','events'),"Save - btn txt"/*Сохранить*/)?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uEvents_event_type_url_edit_dg" tabindex="-1" role="dialog" aria-labelledby="uEvents_event_type_url_edit_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEvents_event_type_url_edit_dgLabel"><?=$this->uInt->text(array('uEvents','events'),"URL of event type editor - dg title"/*Редактор URL типа событий*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><?=$this->uInt->text(array('uEvents','events'),"URL - input label"/*Введите новый URL*/)?></label>
                    <input type="text" id="uEvents_event_type_url_edit_url" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uInt->text(array('uEvents','events'),"Close - btn txt")?></button>
                <button type="button" class="btn btn-primary" onclick="uEvents_events_admin.edit_url_do()"><?=$this->uInt->text(array('uEvents','events'),"Save - btn txt")?></button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="uEvents_type_uploader_dg" tabindex="-1" role="dialog" aria-labelledby="uEvents_type_uploader_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEvents_type_uploader_dgLabel"><?=$this->uInt->text(array('uEvents','events'),"Files - dg title")?></h4>
                <div style="display: table; width: 100%;">
                    <div class="btn-group" style="display: table; float:right;">
                        <button type="button" id="uEvents_file_uploader_watch_btn" class="btn btn-default uEvents_file_uploader_btns" onclick="uEvents_events_admin.fManager_mode('watch')"><?=$this->uInt->text(array('uEvents','events'),"Watch - btn txt"/*Просмотр*/)?></button>
                        <button type="button" id="uEvents_file_uploader_insert_btn" class="btn btn-default active uEvents_file_uploader_btns" onclick="uEvents_events_admin.fManager_mode('insert')"><?=$this->uInt->text(array('uEvents','events'),"Insert - btn txt"/*Вставить*/)?></button>
                        <button type="button" id="uEvents_file_uploader_delete_btn" class="btn btn-default uEvents_file_uploader_btns" onclick="uEvents_events_admin.fManager_mode('delete')"><?=$this->uInt->text(array('uEvents','events'),"Delete - btn txt"/*Удалить*/)?></button>
                        <button type="button" id="uEvents_file_uploader_delete_all_btn" class="btn btn-danger uEvents_file_uploader_btns" onclick="uEvents_events_admin.fManager_delete_file('all');" style="display: none;"><?=$this->uInt->text(array('uEvents','events'),"Delete All - btn txt"/*Удалить Все*/)?></button>
                    </div>
                </div>
            </div>
            <div class="modal-body" id="uEvents_type_uploader_filelist"></div>
            <div class="modal-body" id="uEvents_type_uploader_body"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="uEvents_delete_type_dg" tabindex="-1" role="dialog" aria-labelledby="uEvents_delete_type_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEvents_delete_type_dgLabel"><?=$this->uInt->text(array('uEvents','events'),"Delete event type - dg title"/*Удалить тип событий?*/)?></h4>
            </div>
            <div class="modal-body" id="uEvents_delete_type_container"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uInt->text(array('uEvents','events'),"Cancel - btn txt"/*Отмена*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uEvents_events_admin.delete_type_do('move')"><?=$this->uInt->text(array('uEvents','events'),"Delete and move events - btn txt"/*Удалить и перенести события*/)?></button>
                <button type="button" class="btn btn-danger" onclick="uEvents_events_admin.delete_type_do('all')"><?=$this->uInt->text(array('uEvents','events'),"Delete event type and events attached - btn txt"/*Удалить вместе с событиями*/)?></button>
            </div>
        </div>
    </div>
</div>
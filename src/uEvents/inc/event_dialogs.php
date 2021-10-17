<div class="modal fade" id="uEvents_event_edit_title_dg" tabindex="-1" role="dialog" aria-labelledby="uEvents_event_edit_title_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEvents_event_edit_title_dgLabel"><?=$this->text("New event title - dg title"/*Введите новое название события*/)?></h4>
            </div>
            <div class="modal-body">
                <!--suppress HtmlFormInputWithoutLabel -->
                <input type="text" id="uEvents_event_edit_title_input" class="form-control">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt"/*Закрыть*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uEvents_event_admin.edit_title_save()"><?=$this->text("Save - btn txt"/*Сохранить*/)?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uEvents_event_edit_type_dg" tabindex="-1" role="dialog" aria-labelledby="uEvents_event_edit_type_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEvents_event_edit_type_dgLabel"><?=$this->text("Choose event type - dg title"/*Выберите новый тип события*/)?></h4>
            </div>
            <div class="modal-body" id="uEvents_event_edit_type_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button type="button" class="btn btn-primary" onclick="uEvents_event_admin.edit_type_save()"><?=$this->text("Save - btn txt")?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uEvents_event_uploader_dg" tabindex="-1" role="dialog" aria-labelledby="uEvents_event_uploader_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEvents_event_uploader_dgLabel"><?=$this->text("Files - dg title"/*Файлы*/)?></h4>
                <div style="display: table; width: 100%;">
                    <div class="btn-group" style="display: table; float:right;">
                        <button type="button" id="uEvents_file_uploader_watch_btn" class="btn btn-default uEvents_file_uploader_btns" onclick="uEvents_event_admin.fManager_mode('watch')"><?=$this->text("Watch - btn txt"/*Просмотр*/)?></button>
                        <button type="button" id="uEvents_file_uploader_insert_btn" class="btn btn-default active uEvents_file_uploader_btns" onclick="uEvents_event_admin.fManager_mode('insert')"><?=$this->text("Insert - btn txt"/*Вставить*/)?></button>
                        <button type="button" id="uEvents_file_uploader_delete_btn" class="btn btn-default uEvents_file_uploader_btns" onclick="uEvents_event_admin.fManager_mode('delete')"><?=$this->text("Delete - btn txt"/*Удалить*/)?></button>
                        <button type="button" id="uEvents_file_uploader_delete_all_btn" class="btn btn-danger uEvents_file_uploader_btns" onclick="uEvents_event_admin.fManager_delete_file('all');" style="display: none;"><?=$this->text("Delete All - btn txt"/*Удалить Все*/)?></button>
                    </div>
                </div>
            </div>
            <div class="modal-body" id="uEvents_event_uploader_filelist"></div>
            <div class="modal-body" id="uEvents_event_uploader_body"></div>
        </div>
    </div>
</div>



<div class="modal fade" id="uEvents_event_add_date_dg" tabindex="-1" role="dialog" aria-labelledby="uEvents_event_add_date_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEvents_event_add_date_dgLabel"><?=$this->text("Add date - dg title"/*Добавить дату*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="uEvents_event_add_date_date_input"><?=$this->text("Date - input label"/*Дата*/)?></label>
                    <div class="input-group date">
                        <input type="text" class="form-control" id="uEvents_event_add_date_date_input"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="uEvents_event_add_date_duration_input"><?=$this->text("Duration - input label"/*Продолжительность*/)?></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="uEvents_event_add_date_duration_input"><span class="input-group-addon"> <?=$this->text("Duration - input dimension"/*дней*/)?></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="uEvents_event_add_date_comment_input"><?=$this->text("Comment - input label"/*Комментарий*/)?></label>
                    <input type="text" class="form-control" id="uEvents_event_add_date_comment_input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button id="uEvents_event_add_date_btn" type="button" class="btn btn-primary"><?=$this->text("Add - btn txt"/*Добавить*/)?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uEvents_assign_form_dg" tabindex="-1" role="dialog" aria-labelledby="uEvents_assign_form_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEvents_assign_form_dgLabel"><?=$this->text("Assign form - dg title"/*Назначить форму*/)?></h4>
            </div>
            <div class="modal-body" id="uEvents_assign_form_cnt"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="uEvents_assign_code_dg" tabindex="-1" role="dialog" aria-labelledby="uEvents_assign_code_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEvents_assign_code_dgLabel"><?=$this->text("Assign code - dg title"/*Назначить код*/)?></h4>
            </div>
            <div class="modal-body" id="uEvents_assign_code_cnt">
                <label for="uEvents_assign_code_input"><?=$this->text("Code to assign - input label"/*Код для вставки*/)?></label>
                    <textarea rows="10" id="uEvents_assign_code_input" class="form-control"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button id="uEvents_event_add_code_btn" onclick="uEvents_event_admin.assign_code_do()" type="button" class="btn btn-primary"><?=$this->text("Add - btn txt"/*Добавить*/)?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uEvents_event_url" tabindex="-1" role="dialog" aria-labelledby="uEvents_event_url_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEvents_event_url_dgLabel"><?=$this->text("Event page url editor - dg name")?></h4>
            </div>
            <div class="modal-body" id="uEvents_event_url_cnt">
                <label for="uEvents_event_url_input"><?=$this->text("Fill the page url - label")?></label>
                <input type="text" id="uEvents_event_url_input" class="form-control">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button id="uEvents_event_add_code_btn" onclick="uEvents_event_admin.save_event_url()" type="button" class="btn btn-primary"><?=$this->text("Save - btn txt")?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uEvents_show_dates" tabindex="-1" role="dialog" aria-labelledby="uEvents_show_dates_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEvents_show_dates_dgLabel"><?=$this->text("Event show dates - dg name")?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <div class="col-md-6">
                        <label><?=$this->text("Event show dates - beginning - label")?></label>
                        <div id="uEvents_show_dates_show_begin_timestamp_datepicker"></div>
                        <input type="hidden" id="uEvents_show_dates_show_begin_timestamp">
                    </div>
                    <div class="col-md-6">
                        <label><?=$this->text("Event show dates - end - label")?></label>
                        <div id="uEvents_show_dates_show_end_timestamp_datepicker"></div>
                        <input type="hidden" id="uEvents_show_dates_show_end_timestamp">
                    </div>
                </div>
                <div class="bs-callout bs-callout-primary">

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button id="uEvents_event_add_code_btn" onclick="uEvents_event_admin.show_dates_save()" type="button" class="btn btn-primary"><?=$this->text("Save - btn txt")?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uForms_field_edit_dg" tabindex="-1" role="dialog" aria-labelledby="uForms_field_edit_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uForms_field_edit_dgLabel"><?=$uForms->text("Field editor - dg title"/*Редактор поля*/)?></h4>
            </div>
            <div class="modal-body" id="uForms_field_edit_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?=$uForms->text("Cancel - btn"/*Отмена*/)?></button>
                <button type="button" class="btn btn-success" onclick="uForms.edit_field_do(uForms.field_edited_ind);"><?=$uForms->text("Save - btn txt"/*Сохранить*/)?></button>
                <button type="button" class="btn btn-danger" id="btn-conf-del-field" onclick="uForms.del_field_exec(uForms.field_edited_ind);"><?=$uForms->text("Delete field - btn"/*Удалить поле*/)?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uForms_field_values_dg" tabindex="-1" role="dialog" aria-labelledby="uForms_field_values_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uForms_field_values_dgLabel"><?=$uForms->text("Field values - dg title"/*Значения поля*/)?></h4>
            </div>
            <div class="modal-body" id="uForms_field_values_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="uForms.add_value()"><?=$uForms->text("Add field value - btn"/*Добавить значение*/)?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$uForms->text("Done - btn"/*Готово*/)?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uForms_value_edit_dg" tabindex="-1" role="dialog" aria-labelledby="uForms_value_edit_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uForms_value_edit_dgLabel"><?=$uForms->text("Field value editor - dg title"/*Редактор значения*/)?></h4>
            </div>
            <div class="modal-body" id="uForms_value_edit_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="uForms.edit_value_do();"><?=$uForms->text("Save - btn"/*Сохранить*/)?></button>
                <button type="button" class="btn btn-danger" onclick="uForms.del_value();"><?=$uForms->text("Delete field value - btn"/*Удалить значение*/)?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uForms_value_delete_dg" tabindex="-1" role="dialog" aria-labelledby="uForms_value_delete_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uForms_value_delete_dgLabel"><?=$uForms->text("Delete field value - dg title"/*Удалить значение?*/)?></h4>
            </div>
            <div class="modal-body" id="uForms_value_delete_cnt">
                <p><?=$uForms->text("Are you're sure about deleting this field value - msg"/*Вы действительно хотите удалить это значение?*/)?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$uForms->text("Cancel - btn"/*Отмена*/)?></button>
                <button type="button" class="btn btn-danger" onclick="uForms.del_value_exec();"><?=$uForms->text("Delete - btn"/*Удалить*/)?></button>
            </div>
        </div>
    </div>
</div>
<!-- Modals -->
<div class="modal fade" id="uForms_form_edit_dg" tabindex="-1" role="dialog" aria-labelledby="uForms_form_edit_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uForms_form_edit_dgLabel"><?=$uForms->text("Form settings - dg title"/*Настройка формы*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><?=$uForms->text("Form title - label"/*Заголовок формы*/)?></label>
                    <input type="text" id="uForms_form_edit_dg_form_title" class="form-control">
                </div>
                <div class="form-group">
                    <label><?=$uForms->text("Form descr - label"/*Описание*/)?></label>
                    <textarea id="uForms_form_edit_dg_form_descr" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label><?=$uForms->text("Submit btn txt"/*Текст, который отображается на кнопке "Отправить форму"*/)?></label>
                    <input type="text" id="uForms_form_edit_dg_submit_btn_txt" class="form-control">
                </div>
                <div class="form-group">
                    <label><?=$uForms->text("Form result email - label"/*Сообщение об успешной отправке формы*/)?></label>
                    <textarea id="uForms_form_edit_dg_result_msg" class="form-control"></textarea>
                    <span class="help-block"><?=$uForms->text("Form result email - hint"/*Какой текст отображать посетителю, когда он успешно отправит форму? Не больше 255 символов.*/)?></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$uForms->text("Close - btn"/*Закрыть*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uForms.edit_form_do();"><?=$uForms->text("Save - btn txt"/*Сохранить*/)?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uForms_email_text_files_dg" tabindex="-1" role="dialog" aria-labelledby="uForms_email_text_files_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uForms_email_text_files_dgLabel"><?=$uForms->text("Uploaded files - dg title"/*Загруженные файлы*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="filelist" id="uForms_email_text_filelist"></div><div id="uploader_container"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary active" id="u235_files_dg_title_button_insert" onclick="uForms.fManager_mode('insert')"><?=$uForms->text("Insert file to editor - btn"/*Вставить в редактор*/)?></button>
                <button type="button" class="btn btn-default " id="u235_files_dg_title_button_watch" onclick="uForms.fManager_mode('watch')"><?=$uForms->text("View files - btn"/*Просмотр файлов*/)?></button>
                <button type="button" class="btn btn-default btn-danger" id="u235_files_dg_title_button_delete" onclick="uForms.fManager_mode('delete')"><?=$uForms->text("Delete files - btn"/*Удаление файлов*/)?></button>
                <button type="button" class="btn btn-default btn-danger" id="u235_files_dg_title_button_delete_all" onclick="uForms.fManager_delete_all()"><?=$uForms->text("Delete all files - btn"/*Удалить все*/)?></button>
            </div>
        </div>
    </div>
</div>

<div id="uForms_dinamic_dialog_create"></div>

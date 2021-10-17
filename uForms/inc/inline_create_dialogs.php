<div class="modal fade" id="uForms_common_create_form_dg" tabindex="-1" role="dialog" aria-labelledby="uForms_common_create_form_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uForms_common_create_form_dgLabel"><?=$this->uInt->text(array('uForms','inline_create_dialogs'),"New form - dg title"/*Новая форма*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><?=$this->uInt->text(array('uForms','inline_create_dialogs'),"Form title - label"/*Заголовок:*/)?></label>
                    <input type="text" id="uForms_common_create_form_title" class="form-control" onkeypress="u235_common.create_func_press_enter(event,'input','uForms_common.create_form_exec()')">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uInt->text(array('uForms','inline_create_dialogs'),"Close - btn"/*Закрыть*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uForms_common.create_form_exec();"><?=$this->uInt->text(array('uForms','inline_create_dialogs'),"Create form - btn"/*Создать*/)?></button>
            </div>
        </div>
    </div>
</div>
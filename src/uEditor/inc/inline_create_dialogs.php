<div class="modal fade" id="uEditor_common_create_page_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_common_create_page_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_common_create_page_dgLabel"><?=$this->uInt->text(array('uEditor','inline_create_dialogs'),"New art - dg title"/*Новая статья*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><?=$this->uInt->text(array('uEditor','inline_create_dialogs'),"Art title - input label"/*Заголовок:*/)?></label>
                    <input type="text" id="uEditor_common_create_page_title" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uInt->text(array('uEditor','inline_create_dialogs'),"Close - btn txt"/*Закрыть*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uEditor_common.create_page_exec();"><?=$this->uInt->text(array('uEditor','inline_create_dialogs'),"Create - btn txt"/*Создать*/)?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uEditor_common_new_rub_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_common_new_rub_dgLabel" aria-hidden="true" style="z-index:1053">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_common_new_rub_dgLabel"><?=$this->uInt->text(array('uEditor','inline_create_dialogs'),"New rubric - dg title"/*Новая рубрика*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><?=$this->uInt->text(array('uEditor','inline_create_dialogs'),"Rubric title - input label"/*Название рубрики:*/)?></label>
                    <input type="text" class="form-control" id="uEditor_common_new_rub_title">
                </div>
                <div class="bs-callout bs-callout-primary"><?=$this->uInt->text(array('uEditor','inline_create_dialogs'),"Rubrics description")?></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uInt->text(array('uEditor','inline_create_dialogs'),"Close - btn txt")?></button>
                <button type="button" class="btn btn-primary" onclick="uEditor_common.create_rubric_exec()"><?=$this->uInt->text(array('uEditor','inline_create_dialogs'),"Create - btn txt")?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uEditor_common_new_block_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_common_new_block_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_common_new_block_dgLabel"><?=$this->uInt->text(array('uEditor','inline_create_dialogs'),"New html-block"/*Новая вставка*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><?=$this->uInt->text(array('uEditor','inline_create_dialogs'),"Html-block title - input label"/*Название вставки:*/)?></label>
                    <input type="text" class="form-control" id="uEditor_common_new_block_title">
                </div>
                <div class="bs-callout bs-callout-primary"><?=$this->uInt->text(array('uEditor','inline_create_dialogs'),"Html-blocks description")?></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uInt->text(array('uEditor','inline_create_dialogs'),"Close - btn txt")?></button>
                <button type="button" class="btn btn-primary" onclick="uEditor_common.create_block_exec()"><?=$this->uInt->text(array('uEditor','inline_create_dialogs'),"Create - btn txt")?></button>
            </div>
        </div>
    </div>
</div>
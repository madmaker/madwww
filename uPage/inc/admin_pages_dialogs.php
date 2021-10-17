<div class="modal fade" id="uPage_admin_pages_dg" tabindex="-1" role="dialog" aria-labelledby="uPage_admin_pages_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uPage_admin_pages_dgLabel"><?=$uPages->text("New folder - dg title"/*Новая папка*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><?=$uPages->text("New folder title - input label"/*Название*/)?></label>
                    <input type="text" class="form-control" placeholder="<?=$uPages->text("New folder title - input label"/*Название*/)?>" id="uPage_admin_pages_input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$uPages->text("Close - dg btn")?></button>
                <button type="button" class="btn btn-primary" onclick="uPage_admin_pages.create_folder_do()"><?=$uPages->text("Create - dg btn")?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uPage_admin_pages_edit_folder_dg" tabindex="-1" role="dialog" aria-labelledby="uPage_admin_pages_edit_folder_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uPage_admin_pages_edit_folder_dgLabel"><?=$uPages->text("Rename folder - dg title"/*Переименовать папку*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><?=$uPages->text("New folder title - input label")?></label>
                    <input type="text" class="form-control" id="uPage_admin_pages_edit_folder_input">
                    <input type="hidden" id="uPage_admin_pages_edit_folder_id">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$uPages->text('Close - dg btn')?></button>
                <button type="button" class="btn btn-primary" onclick="uPage_admin_pages.rename_folder_do()"><?=$uPages->text('Save - dg btn')?></button>
            </div>
        </div>
    </div>
</div>
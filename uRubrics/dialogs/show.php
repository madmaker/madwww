<div class="modal fade" id="uRubrics_show_rubric_settings_dg" tabindex="-1" role="dialog" aria-labelledby="uRubrics_show_rubric_settings_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uRubrics_show_rubric_settings_dgLabel"><?=$uRubrics->text('News settings - dg name')?></h4>
            </div>
            <div class="modal-body" id="uRubrics_show_rubric_settings_dg_modal_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$uRubrics->text('Close')?></button>
                <button type="button" class="btn btn-primary" onclick="uRubrics.show.edit_rubric_settings_save()"><?=$uRubrics->text('Save')?></button>
            </div>
        </div>
    </div>
</div>
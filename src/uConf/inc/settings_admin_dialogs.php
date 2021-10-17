<div class="modal fade" id="uConf_settings_editor_dg" tabindex="-1" role="dialog" aria-labelledby="uConf_settings_editor_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uConf_settings_editor_dgLabel"><?=$uConf->translator->txt('Settings editor - dg title'/*Редактор настройки*/)?></h4>
            </div>
            <div class="modal-body" id="uConf_settings_editor_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$uConf->translator->txt('Close - btn txt'/*Закрыть*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uConf_settings_admin.save_field()"><?=$uConf->translator->txt('Save - btn txt'/*Сохранить*/)?></button>
            </div>
        </div>
    </div>
</div>

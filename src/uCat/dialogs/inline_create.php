<div class="modal fade" id="uCat_admin_common_new_item_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_admin_common_new_item_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_admin_common_new_item_dgLabel">Новый товар</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uCat_admin_common_new_item_text_info" style="display: none"></div>
                <div class="text-danger" id="uCat_admin_common_new_item_text_danger" style="display: none"></div>
                <div class="form-group">
                    <div class="form-group">
                        <label>Название товара</label>
                        <input id="uCat_admin_common_new_item_title" type="text" class="form-control" placeholder="Мой новый товар">
                    </div>
                    <div class="form-group">
                        <label class="control-label">В какой раздел прикрепить?</label>
                        <div class="input-group" id="uCat_admin_common_new_item_sects_list">Загрузка разделов. Подождите...</div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">В какую категорию прикрепить?</label>
                        <div class="input-group" id="uCat_admin_common_new_item_cats_list">Загрузка категорий. Подождите...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" id="uCat_admin_common_new_item_create_btn" class="btn btn-primary" onclick="uCat_common.create_item_do();">Создать</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_admin_common_new_item_done_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_admin_common_new_item_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="well well-lg bg-success">
                    <p>Товар создан</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="u235_common.countdown_btn_stop('uCat_admin_common_new_item_done_open_item_btn')">Закрыть</button>
                <button type="button" class="btn btn-success" id="uCat_admin_common_new_item_done_open_item_btn" onclick="uCat_common.open_last_created_item()">Открыть товар</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_admin_common_new_cat_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_admin_common_new_cat_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_admin_common_new_cat_dgLabel">Добавление категории каталога</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Название новой категории</label>
                    <input type="text" class="form-control" id="uCat_admin_common_new_cat_input">
                </div>
                <div class="form-group">
                    <label class="control-label">В какой раздел прикрепить?</label>
                    <div class="input-group" id="uCat_admin_common_new_cat_sects_list">Загрузка разделов. Подождите...</div>
                </div>
                <div class="bs-callout bs-callout-primary">После создания категории к ней нужно прикреплять товары.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" id="uCat_admin_common_new_cat_create_btn" class="btn btn-default btn-primary" onclick="uCat_common.create_cat_do();">Создать</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_admin_common_new_cat_done_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_admin_common_new_cat_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="well well-lg bg-success">
                    <p>Категория успешно создана.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="u235_common.countdown_btn_stop('uCat_admin_common_new_cat_done_open_cat_btn')">Закрыть</button>
                <button type="button" id="uCat_admin_common_new_cat_done_open_cat_btn" class="btn btn-success" onclick="uCat_common.open_last_created_cat()">Открыть категорию</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_admin_common_new_sect_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_admin_common_new_sect_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_admin_common_new_sect_dgLabel">Новый раздел</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uCat_admin_common_new_sect_text_info" style="display: none"></div>
                <div class="text-danger" id="uCat_admin_common_new_sect_text_danger" style="display: none"></div>
                <div class="form-group">
                    <label>Название раздела</label>
                    <input id="uCat_admin_common_new_sect_input" type="text" class="form-control" placeholder="Мой новый раздел">
                </div>
                <div class="bs-callout bs-callout-primary">После создания раздела к нему нужно прикреплять категории, а к ним - товары.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat_common.create_sect_do();">Создать</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_admin_common_new_sect_done_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_admin_common_new_sect_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="well well-lg bg-success">
                    <p>Раздел создан</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="u235_common.countdown_btn_stop('uCat_admin_common_new_sect_done_open_sect_btn')">Закрыть</button>
                <button type="button" class="btn btn-success" id="uCat_admin_common_new_sect_done_open_sect_btn" onclick="uCat_common.open_last_created_sect()">Открыть раздел</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_admin_common_new_art_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_admin_common_new_art_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_admin_common_new_art_dgLabel">Новая статья</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uCat_admin_common_new_art_text_info" style="display: none"></div>
                <div class="text-danger" id="uCat_admin_common_new_art_text_danger" style="display: none"></div>
                <div class="form-group">
                    <label>Заголовок статьи</label>
                    <input id="uCat_admin_common_new_art_input" type="text" class="form-control" placeholder="Моя новая статья">
                </div>
                <div class="bs-callout bs-callout-primary">После создания статьи ее можно прикрепить к товарам.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat_common.create_art_do();">Создать</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_admin_common_new_art_done_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_admin_common_new_art_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="well well-lg bg-success">
                    <p>Статья создана</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="u235_common.countdown_btn_stop('uCat_admin_common_new_art_done_open_art_btn')">Закрыть</button>
                <button type="button" class="btn btn-success" id="uCat_admin_common_new_art_done_open_art_btn" onclick="uCat_common.open_last_created_art()">Открыть статью</button>
            </div>
        </div>
    </div>
</div>
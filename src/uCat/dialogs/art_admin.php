<div class="modal fade" id="uCat_art_title_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_art_title_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_art_title_dgLabel">Заголовок</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Заголовок</label>
                    <input id="uCat_art_title_input" type="text" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat.art_title_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_art_author_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_art_author_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_art_author_dgLabel">Автор</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Автор</label>
                    <input id="uCat_art_author_input" type="text" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat.art_author_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_art_files_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_art_files_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_art_files_dgLabel">Загруженные файлы</h4>
            </div>
            <div class="modal-body">
                <div class="filelist" id="uCat_art_filelist"></div><div id="uploader_container"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary active" id="u235_files_dg_title_button_insert" onclick="uCat.fManager_mode('insert')">Вставить в редактор</button>
                <button type="button" class="btn btn-default " id="u235_files_dg_title_button_watch" onclick="uCat.fManager_mode('watch')">Просмотр файлов</button>
                <button type="button" class="btn btn-default btn-danger" id="u235_files_dg_title_button_delete" onclick="uCat.fManager_mode('delete')">Удаление файлов</button>
                <button type="button" class="btn btn-default btn-danger" id="u235_files_dg_title_button_delete_all" onclick="uCat.fManager_delete_all()">Удалить все</button>
            </div>
        </div>
    </div>
</div>

<?$art_avatar=$uCat->art_avatar->get_avatar("art_page",$uCat->art_id,$uCat->art->art_avatar_time)?>
<div class="modal fade" id="uCat_art_avatar_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_art_avatar_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_art_avatar_dgLabel">Главное изображение статьи</h4>
            </div>
            <div class="modal-body">
                <div id="uCat_art_avatar_img_container" class="img-thumbnail <?=!$art_avatar?'hidden':''?>">
                    <img id="uCat_art_avatar_img" src="<?=$art_avatar?$art_avatar:''?>">
                </div>
                <div id="uCat_art_avatar_uploader"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_attached_items_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_attached_items_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_attached_items_dgLabel">Открепить товары</h4>
                <div style="display: table; width: 100%;">
                    <div class="btn-group" style="display: table; float:right;">
                        <button type="button" class="btn btn-success" onclick="uCat.attach_items('attach');">Прикрепить</button>
                        <button type="button" class="btn btn-default" onclick="uCat_common.create_item();">Создать товар</button>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div id="uCat_attached_items_cnt" class="container-fluid"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_not_attached_items_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_not_attached_items_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_not_attached_items_dgLabel">Прикрепить товары</h4>
                <div style="display: table; width: 100%;">
                    <div class="btn-group" style="display: table; float:right;">
                        <button type="button" class="btn btn-danger" onclick="uCat.attach_items('unattach');">Открепить</button>
                        <button type="button" class="btn btn-default" onclick="uCat_common.create_item()">Создать товар</button>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div id="uCat_not_attached_items_cnt" class="container-fluid"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_art_delete_comfirm_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_art_delete_comfirm_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_art_delete_comfirm_dgLabel">Удалить статью?!?</h4>
            </div>
            <div class="modal-body">
                <div class="well bg-danger">
                    <p>Подтвердите, что хотите удалить статью.<br>Это действие нельзя отменить</p>
                    <p>Файлы статьи будут помещены в корзину</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-success" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-default btn-danger" onclick="uCat.delete_art_confirm()">Да, удаляем статью!</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_art_deleted_success_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_art_deleted_success_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="uCat_art_deleted_success_dgLabel">Статья удалена</h4>
            </div>
            <div class="modal-body">
                <div class="well well-lg bg-success">
                    <p>Статья успешно удалена</p>
                    <p>Файлы статьи помещены в корзину</p>
                </div>
                <div><h3>Что дальше?</h3>
                    <p><a href="<?=u_sroot?>">Главная страница</a></p>
                    <p><a href="<?=u_sroot?>uCat/sects">Разделы каталога</a></p>
                    <p><a href="<?=u_sroot?>uCat/articles">Статьи</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_art_new_item_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_art_new_item_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_art_new_item_dgLabel">Новый товар</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uCat_art_new_item_text_info" style="display: none"></div>
                <div class="text-danger" id="uCat_art_new_item_text_danger" style="display: none"></div>
                <div class="form-group">
                    <div class="form-group">
                        <label>Название товара</label>
                        <input id="uCat_art_new_item_title" type="text" class="form-control" placeholder="Мой новый товар">
                    </div>
                    <div class="form-group">
                        <label class="control-label">В какой раздел прикрепить?</label>
                        <div class="input-group" id="uCat_art_new_item_sects_list">Загрузка разделов. Подождите...</div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">В какую категорию прикрепить?</label>
                        <div class="input-group" id="uCat_art_new_item_cats_list">Загрузка категорий. Подождите...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" id="uCat_art_new_item_create_btn" class="btn btn-primary" onclick="uCat_common.create_item_do();">Создать</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_art_new_item_done_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_art_new_item_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="well well-lg bg-success">
                    <p>Товар создан</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="u235_common.countdown_btn_stop('uCat_art_new_item_done_open_item_btn')">Закрыть</button>
                <button type="button" class="btn btn-success" id="uCat_art_new_item_done_open_item_btn" onclick="uCat_common.open_last_created_item()">Открыть товар</button>
            </div>
        </div>
    </div>
</div>
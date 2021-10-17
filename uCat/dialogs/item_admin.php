<div class="modal fade" id="uCat_units_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_units_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_units_dgLabel">Единицы измерения</h4>
            </div>
            <div class="modal-body" id="uCat_units_modal_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-success" onclick="uCat.item_units_save()"><span class="icon-plus"></span> Создать новую</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_unit_editor_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_unit_editor_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_unit_editor_dgLabel">Редактор единицы измерения</h4>
                <form>
                    <div class="form-group">
                        <label for="uCat_unit_editor_unit_name_input">Название</label>
                        <input type="text" id="uCat_unit_editor_unit_name_input" class="form-control">
                    </div>
                    <div>
                        <button type="button" class="btn" id="uCat_unit_editor_default_btn" onclick="uCat_item_admin.item_units_set_as_default()"></button>
                    </div>
                </form>
            </div>
            <div class="modal-body" id="uCat_unit_editor_modal_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger pull-left" onclick="uCat_item_admin.item_units_delete()"><span class="icon-cancel"></span> Удалить</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-success" onclick="uCat_item_admin.item_units_update_name()"><span class="icon-ok"></span>Сохранить</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_item_url_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_url_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_url_dgLabel">URL товара</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uCat_item_url_text_info" style="display: none"></div>
                <div class="text-danger" id="uCat_item_url_text_danger" style="display: none"></div>
                <div class="form-group">
                    <label>URL товара</label>
                    <input id="uCat_item_url_input" type="text" class="form-control">
                    <span class="help-block">Например tovar_21, kniga, iphone6 и т.д.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat.item_url_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_item_keywords_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_keywords_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_keywords_dgLabel">Ключевые слова товара</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uCat_item_keywords_text_info" style="display: none"></div>
                <div class="text-danger" id="uCat_item_keywords_text_danger" style="display: none"></div>
                <div class="form-group">
                    <label>Ключевые слова товара</label>
                    <textarea id="uCat_item_keywords_input" class="form-control"></textarea>
                        <span class="help-block">Используется при поиске по каталогу и в поисковых системах (мета-тег keywords)<br>
                            Например Мощный, новый, удобный, 5000Вт, Генератор</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat.item_keywords_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_item_seo_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_seo_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_seo_dgLabel">SEO товара</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uCat_item_seo_text_info" style="display: none"></div>
                <div class="text-danger" id="uCat_item_seo_text_danger" style="display: none"></div>
                <div class="form-group">
                    <label>Тег Title для страницы товара</label>
                    <input type="text" id="uCat_item_seo_title_input" class="form-control">
                    <span class="help-block">Используется поисковыми системами. Название сайта дописывается автоматически</span>
                </div>
                <div class="form-group">
                    <label>Мета-тег Description для страницы товара</label>
                    <textarea id="uCat_item_seo_descr_input" class="form-control"></textarea>
                    <span class="help-block">Используется поисковыми системами</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat.item_seo_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_item_articles_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_articles_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_articles_dgLabel">Прикрепление статей к товару</h4>
            </div>
            <div class="modal-body">
                <div id="uCat_item_articles_dg_cnt" class="container-fluid"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-primary" onclick="uCat.getUnAttached_arts()">Показать неприкрепленные</button>
                <button type="button" class="btn btn-default btn-primary" onclick="uCat.getAttached_arts()">Показать прикрепленные</button>
                <button type="button" class="btn btn-default btn-success" onclick="uCat_common.create_art()">Создать статью</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_item_new_article_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_new_article_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_new_article_dgLabel">Новая статья</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Заголовок новой статьи</label>
                    <input class="form-control" type="text" id="uCat_item_new_article_input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-default btn-primary" onclick="uCat_common.create_art_do()">Создать</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_item_edit_article_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_edit_article_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_edit_article_dgLabel">Редактор статьи</h4>
            </div>
            <div class="modal-body">
                <h3>Заголовок статьи</h3>
                <div id="uCat_item_edit_article_title"></div>
                <div>
                    <h3>Основное изображение статьи</h3>
                    <div id="uCat_item_art_editor_thumbnail_container">
                            <div class="thumbnail">
                                <img src="" id="uCat_item_art_editor_thumbnail_container_img">
                            </div>
                        </div>
                    <div id="item_art_mainpic_uploader"></div>
                </div>
                <h3>Текст статьи<small><br>На странице товара отображается только <b>автоматически</b> урезанный текст без изображений и html-кода.<br>Полный текст статьи отображается на странице самой статьи</small></h3>
                <div id="uCat_item_edit_article_cnt"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_item_attached_cats_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_attached_cats_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-90">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_attached_cats_dgLabel">Родительские категории</h4>
                <div style="display: table; width: 100%;">
                    <div class="btn-group" style="display: table; float:right;">
                        <button type="button" class="btn btn-default" onclick="$('#uCat_new_cat_dg').modal('show')">Создать категорию</button>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" id="uCat_item_admin_cats_filter" class="form-control" placeholder="Фильтр" onkeyup="uCat.cats_filter()">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button"><span class="glyphicon glyphicon-search" onclick="uCat.cats_filter()"></span></button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div id="uCat_item_attached_cats_cnt" class="container-fluid"></div>
                    </div>
                    <div class="col-md-6">
                        <div id="uCat_item_not_attached_cats_cnt" class="container-fluid"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_new_cat_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_new_cat_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_new_cat_dgLabel">Добавление категории каталога</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Название новой категории</label>
                    <input type="text" class="form-control" id="uCat_new_cat_input">
                </div>
                <div class="form-group">
                    <div class="input-group" id="uCat_new_cat_sects_list">Загрузка разделов. Подождите...</div>
                </div>
                <div class="bs-callout bs-callout-default">Текущий товар будет автоматически прикреплен к новой категории, категория будет автоматически прикреплена к выбранному разделу</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" id="uCat_new_cat_create_btn" class="btn btn-default btn-primary" onclick="uCat_common.create_cat_do();">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_new_sect_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_new_sect_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_new_sect_dgLabel">Добавление раздела каталога</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Название нового раздела</label>
                    <input type="text" class="form-control" id="uCat_new_sect_input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-default btn-primary" onclick="uCat_common.create_sect()">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_item_fields_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_fields_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_fields_dgLabel">Характеристики товара</h4>
            </div>
            <div class="modal-body">
                <div id="uCat_item_fields_cnt"></div>
                <div class="bs-callout bs-callout-primary">Характеристики отображаются в разных местах на странице товара.<ul class="list-unstyled list-inline">
                <?mysqli_data_seek($uCat->q_fields_places,0);
                    while($place=$uCat->q_fields_places->fetch_object()) {
                        if($place->place_id=='1') continue;?>
                         <li><a href="javascript:void(0)" onclick="uCat.edit_fields(<?=$place->place_id?>)"><?=uString::sql2text($place->place_title,true)?></a></li>
                    <?}?>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-default btn-success" onclick="uCat.add_delete_fields('unattached')">Добавить характеристики</button>
                <button type="button" class="btn btn-default btn-danger" onclick="uCat.add_delete_fields('attached')">Удалить характеристики</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_item_attach_fields_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_attach_fields_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_attach_fields_dgLabel">Прикрепить/Убрать Характеристики</h4>
                <div style="display: table; width: 100%;">
                    <div class="btn-group" style="display: table; float:right;">
                        <button type="button" class="btn btn-default btn-primary" onclick="uCat.create_new_field()" >Создать новую</button>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="container-fluid" id="uCat_item_attach_fields_cnt"></div>
            </div>
        </div>
    </div>
</div>

<? include_once 'uCat/dialogs/new_field.php';?>

<div class="modal fade" id="uCat_edit_field_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_edit_field_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_edit_field_dgLabel">Редактор характеристики</h4>
            </div>
            <div class="modal-body">
                <div id="uCat_edit_field_cnt"></div>
                <div class="bs-callout bs-callout-primary">
                    <span>Учитывайте, что изменения характеристики отразятся во всех товарах, где эта характеристика отображается.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-default btn-primary" onclick="uCat.save_field()">Сохранить</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_show_field_values_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_show_field_values_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_show_field_values_dgLabel">Значения характеристики</h4>
            </div>
            <div class="modal-body">
                <div id="uCat_show_field_values_cnt"></div>
                <div class="bs-callout bs-callout-primary">
                    <span>Чтобы менять положение значений характеристик, добавляйте слева к значению пробел. Чем больше пробелов в начале, тем выше значение в списке.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-default btn-primary" onclick="uCat.save_field()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_edit_field_change_filter_notification_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_edit_field_change_filter_notification_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="uCat_edit_field_change_filter_notification_dgLabel">Отображение в фильтре изменено!</h4>
            </div>
            <div class="modal-body">
                <h3 class="text-warning">Обратите внимание!</h3>
                <p>Отображение характеристики в фильтре будет изменено, так как предыдущее не доступно для выбранного типа характеристики.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-primary" data-dismiss="modal">Я прочитал и принял к сведению</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_item_images_uploader_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_images_uploader_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_images_uploader_dgLabel">Изображения товара</h4>
            </div>
            <div class="modal-body">
                <div id="uCat_item_images_uploader_cnt">
                    <div id="uCat_item_images_uploader_filelist_cnt"></div>
                    <div id="uCat_item_images_uploader_uploader_cnt"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-default btn-primary active" id="uCat_item_images_uploader_btn_watch" onclick="uCat.item_images_mode('watch')">Режим просмотра</button>
                <button type="button" class="btn btn-default btn-danger" id="uCat_item_images_uploader_btn_delete" onclick="uCat.item_images_mode('delete')">Режим удаления</button>
                <button type="button" class="btn btn-default btn-danger" id="uCat_item_images_uploader_btn_delete_all" style="display:none" onclick="uCat.item_images_delete_all()">Удалить все</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_item_availabilities_editor_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_availabilities_editor_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_availabilities_editor_dgLabel">Доступность товаров - редактор</h4>
            </div>
            <div class="modal-body">
                <div id="uCat_item_availabilities_editor_cnt"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-default btn-success" onclick="jQuery('#uCat_item_availability_create_dg').modal('show').css('z-index',1051);">Добавить наличие</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_item_availability_create_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_availability_create_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_availability_create_dgLabel">Новая доступность товаров</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Название</label>
                    <input type="text" class="form-control" id="uCat_item_availability_create_avail_label">
                    <span class="help-block">Например, В наличии, Нет на складе</span>
                </div>
                <div class="form-group">
                    <label>Описание</label>
                    <textarea class="form-control" id="uCat_item_availability_create_avail_descr"></textarea>
                    <span class="help-block">Это описание пользователь увидит в качетсве дополнения к названию</span>
                </div>
                <div class="form-group">
                    <label>Тип доступности</label>
                    <div class="input-group">
                        <select class="form-control" id="uCat_item_availability_create_avail_type_id">
                            <?while($avail=$uCat->q_avail_types->fetch_object()) {?>
                                <option value="<?=$avail->avail_type_id?>"><?=uString::sql2text($avail->avail_type_title,true)?></option>
                            <?}?>
                        </select>
                        <span class="input-group-btn">
                            <button class="btn btn-default uTooltip" title="Посмотреть разъяснение по типам характеристик" type="button" onclick="jQuery('#uCat_item_availability_types_descr_dg').modal('show')"><span class="glyphicon glyphicon-question-sign"></span></button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-default btn-success" onclick="uCat.item_availability_create()">Создать</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_item_delete_comfirm_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_delete_comfirm_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_delete_comfirm_dgLabel">Удалить товар?!?</h4>
            </div>
            <div class="modal-body">
                <div class="well bg-danger">
                    <p>Подтвердите, что хотите удалить товар.<br>Это действие нельзя отменить</p>
                    <p>Файлы товара будут помещены в корзину</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-success" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-default btn-danger" onclick="uCat.delete_item_confirm()">Да, удаляем товар!</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_item_deleted_success_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_deleted_success_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="uCat_item_deleted_success_dgLabel">Товар удален</h4>
            </div>
            <div class="modal-body">
                <div class="well bg-success">Товар успешно удален</div>
                <p>Файлы товара помещены в корзину</p>
                <div><h3>Что дальше?</h3>
                <p><a href="<?=u_sroot?>">Главная страница</a></p>
                <p><a href="<?=u_sroot?>uCat/sects">Разделы каталога</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_new_art_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_new_art_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_new_art_dgLabel">Новая статья</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uCat_new_art_text_info" style="display: none"></div>
                <div class="text-danger" id="uCat_new_art_text_danger" style="display: none"></div>
                <div class="form-group">
                    <label>Заголовок статьи</label>
                    <input id="uCat_new_art_input" type="text" class="form-control" placeholder="Моя новая статья">
                </div>
                <div class="bs-callout bs-callout-success">Статья автоматически прикрепится к текущему товару</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat_common.create_art_do();">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_edit_avail_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_edit_avail_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_edit_avail_dgLabel">Редактор доступности</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="uCat_edit_avail_cnt"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat.item_availability_settings_save();">Сохранить</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_item_availability_types_descr_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_availability_types_descr_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_availability_types_descr_dgLabel">Разъяснение по типам доступностей</h4>
            </div>
            <div class="modal-body">
                <dl>
                    <dt>Можно купить</dt>
                    <dd>Товар доступен для покупки, выставления счета, заказа, оплаты и будет отгружен в кратчайшие сроки.</dd>
                    <dt>Не отображать на сайте</dt>
                    <dd>Товар не будет отображаться на сайте</dd>
                    <dt>Купить нельзя</dt>
                    <dd>Товар закончился. Купить нельзя. Но на сайте отображаться будет</dd>
                    <dt>Под заказ</dt>
                    <dd>Товара нет в наличии. Купить и забрать сейчас нельзя, но можно заказать</dd>
                    <dt>Осталось мало</dt>
                    <dd>Аналогично "Можно купить", но будет подсвечено особым образом (в соответствии с дизайном сайта)</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_item_type_edit_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_type_edit_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_type_edit_dgLabel">Тип товара</h4>
            </div>
            <div class="modal-body" id="uCat_item_type_edit_dg_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" disabled onclick="uCat_item_admin.edit_item_type_save()" id="uCat_item_type_edit_dg_save_btn">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_item_admin_variants_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_admin_variants_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-90">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_admin_variants_dgLabel">Варианты товара</h4>
            </div>
            <div class="modal-body" id="uCat_item_variants_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat_item_admin.new_variant_open_dg()">Добавить/Изменить варианты</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_new_item_variant_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_new_item_variant_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_new_item_variant_dgLabel">Добавить вариант к товару</h4>
            </div>
            <div class="modal-body" id="uCat_new_item_variant_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat_item_admin.new_variant_create()">Готово</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_edit_item_variant_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_edit_item_variant_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_edit_item_variant_dgLabel">Редактор вариант с опциями</h4>
            </div>
            <div class="modal-body" id="uCat_edit_item_variant_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat_item_admin.edit_variant_save()">Готово</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_new_item_variant_type_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_new_item_variant_type_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_new_item_variant_type_dgLabel">Новый вариант товаров</h4>
            </div>
            <div class="modal-body" id="uCat_new_item_variant_type_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat_item_admin.new_variant_type_create()">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_item_set_file_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_set_file_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_set_file_dgLabel">Файл для скачивания товара</h4>
            </div>
            <div class="modal-body" id="uCat_item_set_file_dg_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="uCat_item_admin.showFiles4item_file()">Задать файл</button>
                <button type="button" class="btn btn-danger" id="uCat_item_set_file_dg_detach_btn" onclick="uCat_item_admin.unset_file_do()">Открепить файл</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_item_variant_editor_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_variant_editor_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_variant_editor_dgLabel">Редактор варианта</h4>
            </div>
            <div class="modal-body" id="uCat_item_variant_editor_dg_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat_item_admin.variant_edit_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_items_types_editor_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_items_types_editor_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_items_types_editor_dgLabel">Редактор типов товаров</h4>
            </div>
            <div class="modal-body" id="uCat_items_types_editor_dg_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="uCat_item_admin.new_item_type()">Добавить тип товара</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="uCat_edit_item_type_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_edit_item_type_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_edit_item_type_dgLabel">Редактор типа товаров</h4>
            </div>
            <div class="modal-body" id="uCat_edit_item_type_dg_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" disabled onclick="uCat_item_admin.item_type_save()" id="uCat_edit_item_type_dg_save_btn">Сохранить</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="uCat_new_item_type_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_new_item_type_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_new_item_type_dgLabel">Новый тип товара</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="uCat_new_item_base_type_id">Базовый тип для нового типа товаров</label>
                    <select id="uCat_new_item_base_type_id" class="form-control">
                        <option value="0">Обычный товар</option>
                        <option value="1">Ссылка для скачивания</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="uCat_new_item_type_title">Название нового типа</label>
                    <input type="text" class="form-control" placeholder="Обычный товар" id="uCat_new_item_type_title">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="uCat_item_admin.new_item_type_save()">Создать</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_attach_option2item_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_attach_option2item_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_attach_option2item_dgLabel">Выберите опцию для добавления</h4>
            </div>
            <div class="modal-body" id="uCat_attach_option2item_modal_cnt"></div>
            <div class="modal-footer">
                <button id="uCat_create_new_option_btn" type="button" class="btn btn-default" onclick="uCat_item_admin.create_new_option()"><span class="icon-plus"></span> Создать новую опцию</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_option_editor_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_option_editor_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_option_editor_dgLabel">Редактор опции</h4>
            </div>
            <div class="modal-body" id="uCat_option_editor_modal_body"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_item_avatar_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_avatar_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_avatar_dgLabel">Загрузка изображения товара
                    <span class="pull-right">&nbsp;&nbsp;</span>
                    <span class="pull-right"><button type="button" class="btn btn-sm btn-danger" id="uCat_item_avatar_delete_btn" onclick="uCat_item_admin.delete_avatar()">Удалить изображение</button></span>
                </h4>
            </div>
            <div class="modal-body">
                <div id="uCat_item_avatar_img_container"></div>
                <div id="uCat_item_avatar_uploader"></div>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="uCat_yandex_market_settings_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_yandex_market_settings_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_yandex_market_settings_dgLabel">Настройки Яндекс Маркета для товара</h4>
            </div>
            <div class="modal-body" id="uCat_yandex_market_settings_dg_cnt"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_item_widgets_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_item_widgets_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_item_widgets_dgLabel">Виджеты</h4>
            </div>
            <div class="modal-body">
                <h3>Отметитье виджеты, в которых должен отображаться товар </h3>
                <div id="uCat_item_widgets_dg_list">
                    <!--0--><p><label><input type="checkbox" id="uCat_item_widgets_0"> Последние поступления</label></p>
                    <!--1--><p><label><input type="checkbox" id="uCat_item_widgets_1"> Новинки</label></p>
                    <!--2--><p><label><input type="checkbox" id="uCat_item_widgets_2"> Популярное</label></p>
                    <!--3--><p><label><input type="checkbox" id="uCat_item_widgets_3"> Товары со скидкой</label></p>
                    <!--4--><p><label><input type="checkbox" id="uCat_item_widgets_4"> Бирка "Хит"</label></p>
                    <!--5--><p><label><input type="checkbox" id="uCat_item_widgets_5"> Показывать подпись "От" рядом с ценой</label></p>
                    <!--6--><p><label><input type="checkbox" id="uCat_item_widgets_6"> Показывать в "Популярные товары"</label></p>
                    <!--7--><p><label><input type="checkbox" id="uCat_item_widgets_7"> Показывать в "Рекомендуем"</label></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat_item_admin.edit_widgets_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>
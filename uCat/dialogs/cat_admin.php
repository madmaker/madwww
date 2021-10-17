<?
require_once 'uCat/inc/cat_avatar.php';
$cat_avatar=new uCat_cat_avatar($this);
?>
<div class="modal fade" id="uCat_cat_title_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_cat_title_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_cat_title_dgLabel">Название категории</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Название категории</label>
                    <input id="uCat_cat_title_input" type="text" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat.cat_title_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_cat_url_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_cat_url_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_cat_url_dgLabel">URL категории</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uCat_cat_url_text_info" style="display: none"></div>
                <div class="text-danger" id="uCat_cat_url_text_danger" style="display: none"></div>
                <div class="form-group">
                    <label>URL категории</label>
                    <input id="uCat_cat_url_input" type="text" class="form-control">
                    <span class="help-block">Например tovar_21, kniga, iphone6 и т.д.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat.cat_url_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_cat_seo_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_cat_seo_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_cat_seo_dgLabel">SEO категории</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uCat_cat_seo_text_info" style="display: none"></div>
                <div class="text-danger" id="uCat_cat_seo_text_danger" style="display: none"></div>
                <div class="form-group">
                    <label>Тег Title для страницы категории</label>
                    <input type="text" id="uCat_cat_seo_title_input" class="form-control">
                    <span class="help-block">Используется поисковыми системами. Название сайта дописывается автоматически</span>
                </div>
                <div class="form-group">
                    <label>Мета-тег Description для страницы категории</label>
                    <textarea id="uCat_cat_seo_descr_input" class="form-control"></textarea>
                    <span class="help-block">Используется поисковыми системами</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat.cat_seo_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_cat_keywords_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_cat_keywords_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_cat_keywords_dgLabel">Ключевые слова категории</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uCat_cat_keywords_text_info" style="display: none"></div>
                <div class="text-danger" id="uCat_cat_keywords_text_danger" style="display: none"></div>
                <div class="form-group">
                    <label>Ключевые слова категории</label>
                    <textarea id="uCat_cat_keywords_input" class="form-control"></textarea>
                        <span class="help-block">Используется при поиске по каталогу и в поисковых системах (мета-тег keywords)<br>
                            Например Мощный, новый, удобный, 5000Вт, Генератор</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat.cat_keywords_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_cat_attached_sects_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_cat_attached_sects_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-90">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_cat_attached_sects_dgLabel">Родительские разделы</h4>
                <div style="display: table; width: 100%;">
                    <div class="btn-group" style="display: table; float:right;">
                        <button type="button" class="btn btn-default" onclick="uCat_common.create_sect()">Создать раздел</button>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" id="uCat_cats_sect_filter" class="form-control" placeholder="Фильтр" onkeyup="uCat.sects_filter()">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button"><span class="glyphicon glyphicon-search" onclick="uCat.sects_filter()"></span></button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div id="uCat_cat_attached_sects_cnt" class="container-fluid"></div>
                    </div>
                    <div class="col-md-6">
                        <div id="uCat_cat_not_attached_sects_cnt" class="container-fluid"></div>
                    </div>
                </div>
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
                <div class="bs-callout bs-callout-primary">Раздел будет автоматически прикреплен к текущей категории.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-default btn-primary" onclick="uCat_common.create_sect_do()">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_cat_attach_fields_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_cat_attach_fields_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_cat_attach_fields_dgLabel">Прикрепить/Убрать Характеристики</h4>
                <div style="display: table; width: 100%;">
                    <div class="btn-group" style="display: table; float:right;">
                        <button type="button" class="btn btn-default btn-success" onclick="uCat.add_delete_fields('unattached')" >Прикрепить</button>
                        <button type="button" class="btn btn-default btn-danger" onclick="uCat.add_delete_fields('attached')" >Открепить</button>
                        <button type="button" class="btn btn-default btn-default" onclick="uCat.create_new_field()" >Создать новую</button>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="container-fluid" id="uCat_cat_attach_fields_cnt"></div>
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

<div class="modal fade" id="uCat_cat_attached_items_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_cat_attached_items_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-90">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_cat_attached_items_dgLabel">Дочерние товары</h4>
                <div style="display: table; width: 100%;">
                    <div class="btn-group" style="display: table; float:right;">
                        <button type="button" class="btn btn-default" onclick="uCat_common.create_item();">Создать товар</button>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" id="uCat_admin_cats_item_filter" class="form-control" placeholder="Фильтр" onkeyup="uCat.items_filter();">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="uCat.items_filter()"><span class="glyphicon glyphicon-search"></span></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="uCat_cat_attached_items_cnt" class="container-fluid"></div>
                    </div>
                    <div class="col-md-6">
                        <div id="uCat_cat_not_attached_items_cnt" class="container-fluid"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_cat_new_item_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_cat_new_item_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_cat_new_item_dgLabel">Новый товар</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uCat_cat_new_item_text_info" style="display: none"></div>
                <div class="text-danger" id="uCat_cat_new_item_text_danger" style="display: none"></div>
                <div class="form-group">
                    <label>Название товара</label>
                    <input id="uCat_cat_new_item_title" type="text" class="form-control" placeholder="Мой новый товар">
                </div>
                <div class="bs-callout bs-callout-success">Товар автоматически прикрепится к этой категории!</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat_common.create_item_do();">Создать</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_cat_new_item_done_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_cat_new_item_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="well well-lg bg-success">
                    <p>Товар создан и прикреплен к этой категории</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="u235_common.countdown_btn_stop('uCat_cat_new_item_done_open_item_btn')">Закрыть</button>
                <button type="button" id="uCat_cat_new_item_done_open_item_btn" class="btn btn-success" onclick="uCat_common.open_last_created_item()">Открыть товар</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_cat_delete_comfirm_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_cat_delete_comfirm_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_cat_delete_comfirm_dgLabel">Удалить категорию?!?</h4>
            </div>
            <div class="modal-body">
                <div class="well bg-danger">
                    <p>Подтвердите, что хотите удалить категорию.<br>Это действие нельзя отменить</p>
                    <p>Файлы категории будут помещены в корзину</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-success" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-default btn-danger" onclick="uCat.delete_cat_confirm()">Да, удаляем категорию!</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_cat_deleted_success_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_cat_deleted_success_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="uCat_cat_deleted_success_dgLabel">Категория удалена</h4>
            </div>
            <div class="modal-body">
                <div class="well well-lg bg-success">
                    <p>Категория успешно удалена</p>
                    <p>Файлы категории помещены в корзину</p>
                </div>
                <div><h3>Что дальше?</h3>
                    <p><a href="<?=u_sroot?>">Главная страница</a></p>
                    <p><a href="<?=u_sroot?>uCat/sects">Разделы каталога</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_cat_avatar_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_cat_avatar_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_cat_avatar_dgLabel">Аватарка категории</h4>
            </div>
            <div class="modal-body">
                <div id="uCat_cat_avatar" class="img-thumbnail">
                    <img id="uCat_cat_avatar_img" src="<?=$cat_avatar->get_avatar("list_no_descr",$uCat->cat->cat_id,$uCat->cat->cat_avatar_time)?>">
                </div>
                <div id="uCat_cat_avatar_uploader"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_cat_sort_settings_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_cat_sort_settings_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_cat_sort_settings_dgLabel">Настройка сортировки</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>По какому полю сортировать по умолчанию?</label>
                    <select id="uCat_cat_sort_settings_select" class="form-control">
                        <option value="0" <?=$uCat->cat->def_sort_field=='0'?'selected':''?>>По умолчанию</option>
                        <option value="-1" <?=$uCat->cat->def_sort_field=='-1'?'selected':''?>>Название товара</option>
                        <option value="-2" <?=$uCat->cat->def_sort_field=='-2'?'selected':''?>>Цена товара</option>
                        <option value="-3" <?=$uCat->cat->def_sort_field=='-3'?'selected':''?>>ID товара</option>
                        <?for($i=0;$i<count($uCat->item_fields);$i++) {
                            $field=$uCat->item_fields_id2data[$uCat->item_fields[$i]];
                                echo '<option value="'.$field->field_id.'" '.($uCat->cat->def_sort_field==$field->field_id?'selected':'').'>'.uString::sql2text($field->field_title).' '.($field->sort_show!='1'?' (не отображается в сортировке - настройте)':'').'</option>';
                        }?>
                    </select>
                </div>
                <div class="form-group">
                    <label>В каком направлении сортировать по умолчанию?</label>
                    <select id="uCat_cat_sort_order_select" class="form-control">
                        <option value="0" <?=$uCat->cat->def_sort_order=='0'?'selected':''?>>По умолчанию</option>
                        <option value="1" <?=$uCat->cat->def_sort_order=='1'?'selected':''?>>А-Я</option>
                        <option value="2" <?=$uCat->cat->def_sort_order=='2'?'selected':''?>>Я-А</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat.sort_settings_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="uCat_yandex_market_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_yandex_market_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_yandex_market_dgLabel">Настройки Яндекс Маркета для категории</h4>
            </div>
            <div class="modal-body" id="uCat_yandex_market_dg_cnt"></div>
        </div>
    </div>
</div>
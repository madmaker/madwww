<?require_once 'uCat/inc/sect_avatar.php';
$sect_avatar=new uCat_sect_avatar($this);?>
<div class="modal fade" id="uCat_sect_title_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_sect_title_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_sect_title_dgLabel">Название раздела</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Название раздела</label>
                    <input id="uCat_sect_title_input" type="text" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat_cats_admin.sect_title_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_sect_seo_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_sect_seo_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_sect_seo_dgLabel">SEO раздела</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uCat_sect_seo_text_info" style="display: none"></div>
                <div class="text-danger" id="uCat_sect_seo_text_danger" style="display: none"></div>
                <div class="form-group">
                    <label>Тег Title для страницы раздела</label>
                    <input type="text" id="uCat_sect_seo_title_input" class="form-control">
                    <span class="help-block">Используется поисковыми системами. Название сайта дописывается автоматически</span>
                </div>
                <div class="form-group">
                    <label>Мета-тег Description для страницы раздела</label>
                    <textarea id="uCat_sect_seo_descr_input" class="form-control"></textarea>
                    <span class="help-block">Используется поисковыми системами</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat_cats_admin.sect_seo_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_sect_keywords_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_sect_keywords_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_sect_keywords_dgLabel">Ключевые слова раздела</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uCat_sect_keywords_text_info" style="display: none"></div>
                <div class="text-danger" id="uCat_sect_keywords_text_danger" style="display: none"></div>
                <div class="form-group">
                    <label>Ключевые слова раздела</label>
                    <textarea id="uCat_sect_keywords_input" class="form-control"></textarea>
                        <span class="help-block">Используется при поиске по каталогу и в поисковых системах (мета-тег keywords)<br>
                            Например Мощный, новый, удобный, 5000Вт, Генератор</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat_cats_admin.sect_keywords_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_sect_url_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_sect_url_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_sect_url_dgLabel">URL раздела</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uCat_sect_url_text_info" style="display: none"></div>
                <div class="text-danger" id="uCat_sect_url_text_danger" style="display: none"></div>
                <div class="form-group">
                    <label>URL раздела</label>
                    <input id="uCat_sect_url_input" type="text" class="form-control">
                    <span class="help-block">Например tovar_21, kniga, iphone6 и т.д.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat_cats_admin.sect_url_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_sect_avatar_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_sect_avatar_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_sect_avatar_dgLabel">Аватарка раздела</h4>
            </div>
            <div class="modal-body">
                <div id="uCat_sect_avatar" class="img-thumbnail">
                    <img id="uCat_sect_avatar_img" src="<?=$sect_avatar->get_avatar("sects_list",$uCat->sect->sect_id,$uCat->sect->sect_avatar_time)?>">
                </div>
                <div id="uCat_sect_avatar_uploader"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_sect_attached_cats_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_sect_attached_cats_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_sect_attached_cats_dgLabel">Дочерние категории</h4>
                <div style="display: table; width: 100%;">
                    <div class="btn-group" style="display: table; float:right;">
                        <button type="button" class="btn btn-default" onclick="uCat_common.create_cat()">Создать новую</button>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" id="uCat_sects_cat_filter" class="form-control" placeholder="Фильтр" onkeyup="uCat_cats_admin.cats_filter()">
                                <span class="input-group-btn">
                        <button class="btn btn-default" type="button"><span class="glyphicon glyphicon-search" onclick="uCat_cats_admin.cats_filter()"></span></button>
                    </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h3>Прикрепленные</h3>
                        <div id="uCat_sect_attached_cats_cnt" class="container-fluid"></div>
                    </div>
                    <div class="col-md-6">
                        <h3>Не прикрепленные</h3>
                        <div id="uCat_sect_not_attached_cats_cnt" class="container-fluid"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_sect_subsects_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_sect_subsects_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_sect_subsects_dgLabel">Дочерние подразделы</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" class="form-control" id="uCat_sects_subsects_filter" placeholder="Фильтр" onkeyup="uCat_cats_admin.sect_subsects_filter()">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="uCat_cats_admin.sect_subsects_filter()"><span class="glyphicon glyphicon-search"></span></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h3>Прикрепленные</h3>
                        <div id="uCat_sect_attached_subsects_cnt" class="container-fluid"></div>
                    </div>
                    <div class="col-md-6">
                        <h3>Не прикрепленные</h3>
                        <div id="uCat_sect_not_attached_subsects_cnt" class="container-fluid"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_sect_parent_sects_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_sect_parent_sects_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_sect_parent_sects_dgLabel">Родительские разделы</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" class="form-control" id="uCat_sects_parent_sects_filter" placeholder="Фильтр" onkeyup="uCat_cats_admin.sect_parent_sects_filter()">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="uCat_cats_admin.sect_parent_sects_filter()"><span class="glyphicon glyphicon-search"></span></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h3>Прикрепленные</h3>
                        <div id="uCat_sect_attached_parent_sects_cnt" class="container-fluid"></div>
                    </div>
                    <div class="col-md-6">
                        <h3>Не прикрепленные</h3>
                        <div id="uCat_sect_not_attached_parent_sects_cnt" class="container-fluid"></div>
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
                <h4 class="modal-title" id="uCat_new_cat_dgLabel">Новая категория</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Название категории</label>
                    <input type="text" class="form-control" id="uCat_new_cat_input">
                    <div class="bs-callout bs-callout-success">Категория будет автоматически прикреплена к этому разделу</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-default btn-primary" onclick="uCat_common.create_cat_do()">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_sect_view_style_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_sect_view_style_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_sect_view_style_dgLabel">Новая категория</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Стиль отображения раздела</label>
                    <select class="form-control" id="uCat_sect_view_style_select" onchange="uCat_cats_admin.sect_view_style_save()">
                        <option value="0" <?=$uCat->sect->show_cats_descr=='0'?'selected':''?>>Без описания категорий</option>
                        <option value="1" <?=$uCat->sect->show_cats_descr=='1'?'selected':''?>>С описаниями категорий</option>
                    </select>
                    <div class="bs-callout bs-callout-default">Как отображать список категорий: только названия и аватарки без описания или с описанием?</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_edit_cat_pos_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_edit_cat_pos_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_edit_cat_pos_dgLabel">Позиция категории</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Позиция</label>
                    <input type="text" class="form-control" id="uCat_edit_cat_pos_input">
                    <div class="bs-callout bs-callout-default">С помощью позиции Вы можете делать категорию выше или ниже остальных</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-default btn-primary" onclick="uCat.cat_pos_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_sect_delete_comfirm_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_sect_delete_comfirm_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_sect_delete_comfirm_dgLabel">Удалить раздел?!?</h4>
            </div>
            <div class="modal-body">
                <div class="well bg-danger">
                    <p>Подтвердите, что хотите удалить раздел.<br>Это действие нельзя отменить</p>
                    <p>Файлы раздела будут помещены в корзину</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-success" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-default btn-danger" onclick="uCat_cats_admin.delete_sect_confirm()">Да, удаляем раздел!</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uCat_sect_deleted_success_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_sect_deleted_success_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="uCat_sect_deleted_success_dgLabel">Раздел удален</h4>
            </div>
            <div class="modal-body">
                <div class="well well-lg bg-success">
                    <p>Раздел успешно удален</p>
                    <p>Файлы раздела помещены в корзину</p>
                </div>
                <div><h3>Что дальше?</h3>
                    <p><a href="<?=u_sroot?>">Главная страница</a></p>
                    <p><a href="<?=u_sroot?>uCat/sects">Разделы каталога</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
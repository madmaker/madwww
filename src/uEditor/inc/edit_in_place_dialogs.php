<div class="modal fade" id="uEditor_change_avatar_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_change_avatar_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_change_avatar_dgLabel"><?=$this->text("Basic page image - dg title"/*Основное изображение страницы*/)?></h4>
            </div>
            <div class="modal-body">
                <div id="uEditor_page_main_img_uploader"></div>

                <div class="checkbox">
                    <label>
                        <input id="uEditor_show_avatar_chbx" type="checkbox" <?=($this->page->show_avatar=='1')?'checked':''?> onchange="uEditor.save_show_avatar()"> <span><?=$this->text("Show image on current page - input label"/*Отображать изображение на этой странице?*/)?></span>
                        <span class="help-block"><?=$this->text("Show image on current page - input descr"/*Если снять, то на странице не будет отображаться, а будет только в виджетах и списках*/)?></span>
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input id="uEditor_show_avatars_on_pages_chbx" type="checkbox" <?=($this->uCore->uFunc->getConf("show_avatars_on_pages","content")=='1')?'checked':''?> onchange="uEditor.save_show_avatars_on_pages()"> <span><?=$this->text("Show image on pages - input label"/*Отображать изображения страниц на страницах статей?*/)?></span>
                            <span class="help-block"><?=$this->text("Show image on pages - input descr"/*Если снять, то основные изображения статей будут скрыты на всех страницах статей*/)?></span>
                    </label>
                </div>
                <!--<div class="form-group">
                    <label><?=$this->text("Basic image width on page - input label"/*Ширина изображения на этой странице*/)?></label>
                    <div class="input-group">
                        <input id="uEditor_avatar_page_width" type="text" value="400" class="form-control" onchange="uEditor.save_avatar_page_width()">
                        <span class="input-group-addon" id="basic-addon2">px</span>
                    </div>
                </div>
                <div class="form-group">
                    <label><?=$this->text("Basic image width on articles list - input label"/*Ширина изображения в списке страниц в рубрике (например список всех новостей)*/)?></label>
                    <div class="input-group">
                        <input id="uEditor_avatar_uRubrics_list_width" type="text" value="350" class="form-control" onchange="uEditor.save_avatar_uRubrics_list_width()">
                        <span class="input-group-addon" id="basic-addon2">px</span>
                    </div>
                </div>
                <div class="form-group">
                    <label><?=$this->text("Basic image width in widget - input label"/*Ширина изображения в виджете рубрики (например новости на главной)*/)?></label>
                    <div class="input-group">
                        <input id="uEditor_avatar_uRubrics_widget_width" type="text" value="350" class="form-control" onchange="uEditor.save_avatar_uRubrics_widget_width()">
                        <span class="input-group-addon" id="basic-addon2">px</span>
                    </div>
                </div>-->
                <div class="bs-callout bs-callout-primary"><?=$this->text("Basic image description")?></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button type="button" class="btn btn-danger" onclick="uEditor.delete_page_avatar()"><?=$this->text("Delete image - btn txt")?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uEditor_short_text_edit_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_short_text_edit_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-90">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_short_text_edit_dgLabel"><?=$this->text("Short text - dg title"/*Краткий текст*/)?></h4>
            </div>
            <div class="modal-body">
                <label><?=$this->text("short text - input label"/*Краткий текст:*/)?></label>
                <div class="page_short_text" id="page_short_text"><?=uString::sql2text($this->page->page_short_text,true)?></div>
                <div class="bs-callout bs-callout-primary"><?=$this->text("Short text - description"/*Краткий текст отображается в различных виджетах на сайте. Например в списке новостей на главной странице.*/)?></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uEditor_files_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_files_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-90">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_files_dgLabel"><?=$this->text("Uploaded files - dg title"/*Файлы, загруженные к статье*/)?></h4>
            </div>
            <div class="modal-body">
                <div id="uEditor_files_cnt">
                    <div id="uEditor_files_filelist"></div><div id="uEditor_files_uploader">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary active" id="uEditor_files_dg_title_button_insert" onclick="uEditor.fManager_mode('insert')"><?=$this->text("Insert to editor - btn txt"/*Вставить в редактор*/)?></button>
                    <button type="button" class="btn btn-default" id="uEditor_files_dg_title_button_watch" onclick="uEditor.fManager_mode('watch')"><?=$this->text("Preview - btn txt"/*Просмотр*/)?></button>
                    <button type="button" class="btn btn-danger" id="uEditor_files_dg_title_button_delete" onclick="uEditor.fManager_mode('delete')"><?=$this->text("Delete - btn txt"/*Удалить*/)?></button>
                    <button type="button" class="btn btn-danger" id="uEditor_files_dg_title_button_delete_all" onclick="uEditor.fManager_delete_all()"><?=$this->text("Delete all - btn txt"/*Удалить все*/)?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uEditor_page_title_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_page_title_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_page_title_dgLabel"><?=$this->text("Art title - dg title"/*Заголовок статьи*/)?></h4>
            </div>
            <div class="modal-body" id="uEditor_page_title_cnt">
                <div class="form-group">
                    <label><?=$this->text("Title - input label"/*Заголовок:*/)?></label>
                    <input id="uEditor_page_title" class="form-control" type="text" value="<?=htmlspecialchars(uString::sql2text($this->uCore->page['page_title'],true))?>">
                </div>
                <div class="checkbox">
                    <label>
                        <input id="uEditor_page_show_title_in_content" type="checkbox" <?=($this->uCore->page['page_show_title_in_content']=='1')?' checked ':''?>>  <span><?=$this->text("Show title on page - input label"/*Отображать заголовок на странице*/)?></span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button type="button" class="btn btn-primary" onclick="uEditor.save_page_title()"><?=$this->text("Save - btn txt")?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uEditor_page_url_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_page_url_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_page_url_dgLabel"><?=$this->text("Art url - dg title"/*URL страницы*/)?></h4>
            </div>
            <div class="modal-body" id="uEditor_page_url_cnt">
                <div class="form-group">
                    <label><?=$this->text("Current text id - label")/*ID этого текста:*/?></label>
                    <span class="form-control-static"><?=$this->uCore->page['page_id']?></span>
                </div>
                <div class="form-group">
                    <label><?=$this->text("Basic url - input label"/*Основной URL:*/)?></label>
                    <input id="uEditor_page_name"" class="form-control" type="text" value="<?=$this->uCore->page['page_name']?>">
                </div>
                <div class="form-group">
                    <label><?=$this->text("Beautiful url - input label"/*Красивый URL:*/)?></label>
                    <input id="uEditor_page_alias" class="form-control" type="text" value="<?=$this->page->page_alias?>">
                </div>
                <div class="bs-callout bs-callout-primary"><?=$this->text("Beautiful url - descr")?></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button type="button" class="btn btn-primary" onclick="uEditor.save_page_url()"><?=$this->text("Save - btn txt")?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uEditor_page_access_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_page_access_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_page_access_dgLabel"><?=$this->text("Article access - dg title"/*Доступ к статье*/)?></h4>
            </div>
            <div class="modal-body" id="uEditor_page_access_cnt">
                <div class="form-group">
                    <label><?=$this->text("Access - input label"/*Доступ:*/)?></label>
                    <select class="form-control" id="uEditor_page_access">
                        <option value="0" <?=(int)$this->uCore->page['page_access']==0?'selected':''?>><?=$this->text("Access 0"/*Все пользователи*/)?></option>
                        <option value="2" <?=(int)$this->uCore->page['page_access']==2?'selected':''?>><?=$this->text("Access 2"/*Только авторизованные*/)?></option>
                        <option value="11" <?=(int)$this->uCore->page['page_access']==11?'selected':''?>><?=$this->text("Access 11"/*Только НЕавторизованные*/)?></option>
                        </select>
                </div>
                <div class="bs-callout bs-callout-primary"><?=$this->text("Access - descr")?></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button type="button" class="btn btn-primary" onclick="uEditor.save_page_access()"><?=$this->text("Save - btn txt")?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uEditor_page_navi_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_page_navi_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_page_navi_dgLabel"><?=$this->text("Menu binding - dg title"/*Привязка к меню*/)?></h4>
            </div>
            <div class="modal-body" id="uEditor_page_navi_cnt">
                <div class="form-group">
                    <label for="uEditor_navi_parent_menu_id"><?=$this->text("Open menu - input label"/*Открыть меню:*/)?></label>
                    <select class="form-control" id="uEditor_navi_parent_menu_id">
                        <optgroup label="Выберите пункт меню"><option value="0">-------</option>
                            <?$menu_list=$this->uCore->uFunc->uMenu_list();
                            print_r($menu_list);
                            for($i=0;$menu_list[$i];$i++) {
                                if (!isset($menu_catIsset[$menu_list[$i]->cat_id])){?>
                        </optgroup><optgroup label="<?=$menu_list[$i]->cat_title?>">
                                    <?$menu_catIsset[$menu_list[$i]->cat_id]=true;
                                }?>

                            <option style="padding-left:'<?=$menu_list[$i]->indent?>px" value="<?=$menu_list[$i]->id?>"
                                <?=($this->page->navi_parent_menu_id==$menu_list[$i]->id)?' selected ':''?>><?=$menu_list[$i]->title?></option>
                            <?}?>
                        </optgroup>
                    </select>
                </div>
                <div class="bs-callout bs-callout-primary"><?=$this->text("Menu binding descr")?></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button type="button" class="btn btn-primary" onclick="uEditor.save_page_navi()"><?=$this->text("Save - btn txt")?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uEditor_page_timestamp_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_page_timestamp_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_page_timestamp_dgLabel"><?=$this->text("Page date - dg title"/*Дата и время страницы*/)?></h4>
            </div>
            <div class="modal-body" id="uEditor_page_timestamp_cnt">
                <div class="form-group">
                    <label><?=$this->text("Date - input label"/*Дата:*/)?></label>
                    <input id="uEditor_page_date" class="form-control" type="text" value="<?=date('d.m.Y',$this->page->page_timestamp);?>">
                </div>
                <div class="form-group">
                    <label><?=$this->text("Time - input label"/*Время:*/)?></label>
                    <input id="uEditor_page_time" class="form-control" type="text" value="<?=date('H:i',$this->page->page_timestamp);?>">
                </div>
                <div class="checkbox">
                    <label>
                        <input id="uEditor_page_date_show" type="checkbox" <?=($this->page->page_timestamp_show=='1')?' checked ':''?>>
                         <span><?=$this->text("Show date and time - input label"/*Показывать дату и время на странице*/)?></span>
                    </label>
                </div>
                <div class="bs-callout bs-callout-primary"><?=$this->text("Date and time - descr")?></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button type="button" class="btn btn-primary" onclick="uEditor.save_page_timestamp()"><?=$this->text("Save - btn txt")?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uEditor_seo_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_seo_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_seo_dgLabel">SEO</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><?=$this->text("meta-description - input label"/*Описание (мета-тег description)*/)?></label>
                    <textarea id="uEditor_meta_description" class="form-control"><?=(uString::sql2text($this->page->meta_description,1))?></textarea>
                </div>
                <div class="form-group">
                    <label><?=$this->text("meta-keywords - input label"/*Ключевые слова (мета-теш keywords)*/)?></label>
                    <textarea id="uEditor_meta_keywords" class="form-control"><?=(uString::sql2text($this->page->meta_keywords,1))?></textarea>
                </div>

                <div class="bs-callout bs-callout-primary"><?=$this->text("meta-tags description")?></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button type="button" class="btn btn-primary" onclick="uEditor.save_seo()"><?=$this->text("Save - btn txt")?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uEditor_uRubrics_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_uRubrics_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_uRubrics_dgLabel"><?=$this->text("Rubrics - dg title"/*Рубрики*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <h3><?=$this->text("Attached rubrics - list title"/*Прикрепленные рубрики*/)?></h3>
                            <div id="uEditor_uRubrics_attached"></div>
                        </div>
                        <div class="col-md-6">
                            <h3><?=$this->text("Unattached rubrics - list title"/*Неприкрепленные рубрики*/)?></h3>
                            <div id="uEditor_uRubrics_unattached"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default uTooltip" onclick="uEditor.getAttachedURubrics(); uEditor.getUnattachedURubrics();"><span class="icon-arrows-cw"></span></button>
                <button type="button" class="btn btn-success" onclick="uEditor_common.create_rubric()"><span class="glyphicon glyphicon-plus"></span> <?=$this->text("Create rubric - btn txt"/*Создать рубрику*/)?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uEditor_edit_rubric_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_edit_rubric_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_edit_rubric_dgLabel"><?=$this->text("Rubric editor - dg title"/*Редактор рубрики*/)?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="uEditor_edit_rubric_id">
                <div class="form-group">
                    <label><?=$this->text("Rubric title - input label"/*Название рубрики*/)?></label>
                    <input type="text" class="form-control" id="uEditor_edit_rubric_title">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button type="button" class="btn btn-primary" onclick="uEditor.edit_rubric_title_save()"><?=$this->text("Save - btn txt")?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uEditor_delete_rubric_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_delete_rubric_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_delete_rubric_dgLabel"><?=$this->text("Delete rubric - dg title"/*Удалить рубрику?*/)?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="uEditor_delete_rubric_id">
                <p class="bg-danger"><?=$this->text("Delete rubric - confirmation text"/*Вы действительно хотите удалить рубрику?*/)?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button type="button" class="btn btn-danger" onclick="uEditor.delete_rubric_exec()"><?=$this->text("Delete rubric - btn txt"/*Удалить*/)?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uEditor_uBlocks_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_uBlocks_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_uBlocks_dgLabel"><?=$this->text("HTML-blocks - dg title"/*Вставки*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <h3><?=$this->text("Attached html-blocks - list title"/*Прикрепленные вставки*/)?></h3>
                            <div id="uEditor_uBlocks_attached"></div>
                        </div>
                        <div class="col-md-6">
                            <h3><?=$this->text("Unattached html-blocks - list title"/*Неприкрепленные вставки*/)?></h3>
                            <div id="uEditor_uBlocks_unattached"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default uTooltip" onclick="uEditor.getAttachedUBlocks(); uEditor.getUnattachedUBlocks();"><span class="glyphicon glyphicon-refresh"></span></button>
                <button type="button" class="btn btn-success" onclick="uEditor_common.create_block()"><span class="glyphicon glyphicon-plus"></span> <?=$this->text("Create html-block - btn txt"/*Создать вставку*/)?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uEditor_delete_block_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_delete_block_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_delete_block_dgLabel"><?=$this->text("Delete html-block - dg title"/*Удалить вставку?*/)?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="uEditor_delete_block_id">
                <p class="bg-danger"><?=$this->text("Delete html-block - confirmation text"/*Вы действительно хотите удалить вставку?*/)?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button type="button" class="btn btn-danger" onclick="uEditor.delete_block_exec()"><?=$this->text("Delete html-block - btn txt"/*Удалить*/)?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uEditor_block_edit_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_block_edit_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_block_edit_dgLabel"><?=$this->text("Html-block editor - dg title"/*Редактор Вставки*/)?></h4>
            </div>
            <div class="modal-body" id="uEditor_block_edit_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button type="button" class="btn btn-primary" onclick="uEditor.edit_block_save()"><?=$this->text("Save - btn txt")?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uEditor_delete_page_confirm_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_delete_page_confirm_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_delete_page_confirm_dgLabel"><?=$this->text("Delete art - dg title"/*Удалить статью?*/)?></h4>
            </div>
            <div class="modal-body">
                <p class="well-lg bg-danger"><?=$this->text("Delete art - confirmation text")?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->text("Close - btn txt")?></button>
                <button type="button" class="btn btn-danger" onclick="uEditor.delete_page_exec()"><?=$this->text("Delete art - confirm btn txt"/*Да, удаляем*/)?></button>
            </div>
        </div>
    </div>
</div>
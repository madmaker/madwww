<div class="modal fade" id="uCat_new_field_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_new_field_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_new_field_dgLabel">Новая характеристика</h4>
            </div>
            <div class="modal-body">
                <div class="bs-callout bs-callout-success">Не создавайте без необходимости повторно одинаковые характеристики. В разных разделах можно использовать одну и ту же характеристику для разных товаров.<br>Таким образом характеристика "Цвет" может быть использована для товаров в разделах Электростанции, Книги, Автомобили и Телефоны одновременно!</div>


                <div role="tabpanel">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#uCat_new_field_main" aria-controls="home" role="tab" data-toggle="tab">Основные</a></li>
                        <li role="presentation"><a href="#uCat_new_field_view" aria-controls="uCat_new_field_view" role="tab" data-toggle="tab">Расширенные</a></li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="uCat_new_field_main">
                            <div class="row">
                                <div class="col-md-6">

                                    <div class="form-group" id="uCat_new_field_field_title_group">
                                        <label class="control-label">Название</label>
                                        <input id="uCat_new_field_field_title" class="form-control" type="text">
                                        <span id="uCat_new_field_field_title_helpBlock" class="help-block" style="display: none"></span>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Единицы измерения</label>
                                        <input id="uCat_new_field_field_units" class="form-control" type="text">
                                        <span class="help-block">Отображается в каталоге и фильтре</span>
                                    </div>

                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label>
                                                <input id="uCat_new_field_search_use" type="checkbox"> <span >Использовать при поиске?</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label>
                                                <input id="uCat_new_field_tablelist_show" type="checkbox"> <span >Показывать в табличном отображении?</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label>
                                                <input id="uCat_new_field_planelist_show" type="checkbox"> <span >Показывать в отображении списком и корзине?</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label>
                                                <input id="uCat_new_field_tileslist_show" type="checkbox"> <span >Показывать в плиточном отображении при наведении?</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label>
                                                <input id="uCat_new_field_tileslist_show_on_card" type="checkbox"> <span >Показывать в плиточном отображении сразу в карточке?</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label>
                                                <input id="uCat_new_field_sort_show" type="checkbox"> <span >Использовать для сортировки?</span>
                                            </label>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label class="control-label">Тип</label>
                                        <div class="input-group">
                                            <select id="uCat_new_field_field_type_id" class="form-control" onchange="uCat.create_new_field_change_type()">
                                                <?mysqli_data_seek($uCat->q_fields_types,0);
                                                while($type=$uCat->q_fields_types->fetch_object()) {
                                                    if($type->field_type_id=='0') continue;?>
                                                    <option value="<?=$type->field_type_id?>"><?=uString::sql2text($type->field_type_title)?></option>
                                                <?}?>
                                            </select>
                                        <span class="input-group-btn">
                                            <button class="btn btn-default uTooltip" title="Посмотреть разъяснение по типам характеристик" type="button" onclick="jQuery('#uCat_edit_field_types_explanation_dg').modal('show')"><span class="glyphicon glyphicon-question-sign"></span></button>
                                        </span>
                                        </div>
                                        <span class="help-block">Как хранить в базе данных, как отображать в товаре, как будет работать в фильтре. Влияет на скорость работы сайта!</span>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Как отображать в фильтре?</label>
                                        <div class="input-group">
                                            <select id="uCat_new_field_filter_type_id" class="form-control">
                                                <?mysqli_data_seek($uCat->q_fields_filter_types,0);
                                                while($filter=$uCat->q_fields_filter_types->fetch_object()) {?>
                                                    <option value="<?=$filter->filter_type_id?>"><?=uString::sql2text($filter->filter_type_title,true)?></option>
                                                <?}?>
                                            </select>
                            <span class="input-group-btn">
                                <button class="btn btn-default uTooltip" title="Посмотреть примеры отображения фильтров" type="button" onclick="jQuery('#uCat_edit_field_filter_examples_dg').modal('show')"><span class="glyphicon glyphicon-question-sign"></span></button>
                            </span>
                                        </div>
                                        <span class="help-block">Как отображать характеристику в фильтре?</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="uCat_new_field_view">

                            <div class="row">
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label class="control-label">Позиция относительно других характеристик</label>
                                        <input id="uCat_new_field_field_pos" type="text" class="form-control">
                                        <span class="help-block">Если число больше, чем у других, то будет отображаться ниже, если меньше - выше</span>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Где отображать на странице товара?</label>
                                        <select id="uCat_new_field_field_place_id" class="form-control">
                                            <?mysqli_data_seek($uCat->q_fields_places,0);
                                            while($place=$uCat->q_fields_places->fetch_object()){?>
                                                <option value="<?=$place->place_id?>"><?=uString::sql2text($place->place_title)?></option>
                                            <?}?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Где отображать название характеристики?</label>
                                        <select id="uCat_new_field_label_style_id" class="form-control">
                                            <?mysqli_data_seek($uCat->q_fields_label_styles,0);
                                            while($label_place=$uCat->q_fields_label_styles->fetch_object()) {?>
                                                <option value="<?=$label_place->label_style_id?>"><?=uString::sql2text($label_place->label_style_title,true)?></option>
                                            <?}?>
                                        </select>
                                        <span class="help-block">Слева от значения, над текстом и т.п.</span>
                                    </div>


                                </div>

                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label class="control-label">Комментарий</label>
                                        <textarea id="uCat_new_field_field_comment" class="form-control"></textarea>
                                        <span class="help-block">Комментарий для себя. Нигде не отображается</span>
                                    </div>

                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label>
                                                <input id="uCat_new_field_merge" type="checkbox"> <span >Склеивать?</span>
                                            </label>
                                        </div>
                                        <span class="help-block">Все характеристики с одинаковыми заголовками и позициями будут отображаться под одним общим заголовком (без заголовка для каждой)</span>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Эффект</label>
                                        <div class="input-group">
                                            <select id="uCat_new_field_field_effect_id" class="form-control">
                                                <?mysqli_data_seek($uCat->q_fields_effects,0);
                                                while($effect=$uCat->q_fields_effects->fetch_object()) {?>
                                                    <option value="<?=$effect->effect_id?>"><?=uString::sql2text($effect->effect_title,true)?></option>
                                                <?}?>
                                            </select>
                                            <span class="input-group-btn">
                                                <button class="btn btn-default uTooltip" title="Посмотреть разъяснение по эффектам характеристик" type="button" onclick="jQuery('#uCat_edit_field_effects_explanation_dg').modal('show')"><span class="glyphicon glyphicon-question-sign"></span></button>
                                            </span>
                                        </div>
                                        <span class="help-block">Какой эффект применять к характеристике при отображении на странице товара?</span>
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>

                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-default btn-primary" onclick="uCat.create_new_field_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_edit_field_types_explanation_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_edit_field_types_explanation_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_edit_field_types_explanation_dgLabel">Разъяснение типов характеристик</h4>
            </div>
            <div class="modal-body">
                <dl>
                    <dt>Целое число</dt>
                    <dd>Используйте для целочисленных значений таких как цена, артикул, числовая характеристика. У этого типа доступен фильтр "Диапазон" и "Флажок"</dd>
                    <dt>Дробное число</dt>
                    <dd>Аналогично типу "Целое число", но для дробных чисел.</dd>
                    <dt>Дата без отображающегося времени в формате ДД.ММ.ГГГГ. Доступен фильтр "Диапазон" и "Флажок"</dt>
                    <dt>Дата и время</dt>
                    <dd>Дата с отображаемым временем в формате ДД.ММ.ГГГГ ЧЧ:ММ. Доступен фильтр "Диапазон" и "Флажок"</dd>
                    <dt>Текст</dt>
                    <dd>Используйте для больших объемов текста, которые нужно отображать в несколько строк. Фильтры для таких характеристик недоступны.</dd>
                    <dt>Ссылка</dt>
                    <dd>Можете указать ссылку на любой файл или страницу внутри сайта или за его пределами. Можно указать адрес url, подпись и где открывать: в новой или текущей вкладке.</dd>
                    <dt>HTML-текст</dt>
                    <dd>Аналогично типу "Текст", но редактирование доступно во встроенном визуальном редакторе. Можно устанавливать формат текста, вставлять ссылки, заголовки, загружать файлы и т.п.<br>Не используйте этот тип без необходимости. Фильтры недоступны для этого типа.</dd>
                    <dt>Короткий текст</dt>
                    <dd>Короткий текст до 255 символов в длину. Используйте, если не планируется большое количество текста. Работает быстрее, отображается компактней, доступен фильтр "флажок"</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_edit_field_filter_examples_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_edit_field_filter_examples_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_edit_field_filter_examples_dgLabel">Примеры отображения в фильтре</h4>
            </div>
            <div class="modal-body">
                <h3>Без фильтра</h3>
                <p>Если выбрать этот пункт, то характеристика не будет отображаться в фильтре</p>
                <h3>Диапазон</h3>
                <div class="uCat_filter">
                    <input type="text" id="uCat_uCat_edit_field_filter_examples_slider_amount_field" class="form-control">
                    <div id="uCat_uCat_edit_field_filter_examples_slider"></div>
                </div>
                <h3>Флажок</h3>
                <div class="checkbox">
                    <label>
                        <input type="checkbox"> <span >Так отображается флажок</span>
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox"> <span >И еще один</span>
                    </label>
                </div>
                <div class="checkbox">
                    <label >
                        <input type="checkbox"> <span >Их будет несколько</span>
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox"> <span >Можно выбрать один или сразу много</span>
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox"> <span >Фильтр отобразить соответствующие</span>
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox"> <span >всем условиям</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uCat_edit_field_effects_explanation_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_edit_field_effects_explanation_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_edit_field_effects_explanation_dgLabel">Разъяснение эффектов характеристик</h4>
            </div>
            <div class="modal-body">
                <dl>
                    <dt>Листание книги</dt>
                    <dd>Работает с типами характеристик "HTML-текст".<br>Текст разбивается на страницы с помощью функции редактора "Вставить->Разрыв страницы".<br>Каждая страница будет отображаться на "Отдельной странице" и будет листаться с эффектом "Листание книги".</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
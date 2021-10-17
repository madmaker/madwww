<div class="modal fade" id="configurator_new_page_dg" tabindex="-1" role="dialog" aria-labelledby="configurator_new_page_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="configurator_new_page_dgLabel">Новая страница</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="configurator_new_page_page_name_form_group">
                    <label for="configurator_new_page_page_name">Название страницы</label>
                    <input type="text" class="form-control" id="configurator_new_page_page_name" placeholder="Тип космического двигателя">
                    <p class="help-block hidden"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="configurator.page_admin.new_page_save()">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="configurator_edit_page_pos_dg" tabindex="-1" role="dialog" aria-labelledby="configurator_edit_page_pos_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="configurator_edit_page_pos_dgLabel">Изменить положение страницы</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="configurator_edit_page_pos_page_name_form_group">
                    <label for="configurator_edit_page_pos_input">Положение страницы</label>
                    <input type="number" class="form-control" id="configurator_edit_page_pos_input">
                    <div class="bs-callout bs-callout-default">
                        Укажите число.<br>Страницы будут отображаться согласно положению: 0, 1, 2, 3, 4. <br>
                        Чтобы страница была раньше других, ее положение должно быть меньше, чтобы страница была позже - больше
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="configurator.page_admin.page_pos_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="configurator_new_sect_dg" tabindex="-1" role="dialog" aria-labelledby="configurator_new_sect_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="configurator_new_sect_dgLabel">Новый раздел</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="configurator_new_sect_sect_name_form_group">
                    <label for="configurator_new_sect_sect_name">Название раздела</label>
                    <input type="text" class="form-control" id="configurator_new_sect_sect_name" placeholder="Межпланетный двигатель">
                    <p class="help-block hidden"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="configurator.page_admin.new_sect_save()">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="configurator_edit_sect_pos_dg" tabindex="-1" role="dialog" aria-labelledby="configurator_edit_sect_pos_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="configurator_edit_sect_pos_dgLabel">Изменить положение раздела</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="form-control" id="configurator_edit_sect_pos_sect_id">
                <div class="form-group">
                    <label for="configurator_edit_sect_pos_input">Положение раздела</label>
                    <input type="text" class="form-control" id="configurator_edit_sect_pos_input">
                    <div class="bs-callout bs-callout-default">
                        Укажите число.<br>
                        Разделы будут отображаться согласно положению: 0, 1, 2, 3, 4.<br>
                        Чтобы раздел был выше других, его положение должно быть меньше, чтобы раздел был ниже - больше
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="configurator.page_admin.sect_pos_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="configurator_new_opt_dg" tabindex="-1" role="dialog" aria-labelledby="configurator_new_opt_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="configurator_new_opt_dgLabel">Новая опция</h4>
            </div>
            <div class="modal-body">
                    <input type="hidden" class="form-control" id="configurator_new_opt_sect_id">
                <div class="form-group" id="configurator_new_opt_opt_name_form_group">
                    <label for="configurator_new_opt_opt_name">Название опции</label>
                    <input type="text" class="form-control" id="configurator_new_opt_opt_name" placeholder="Межпланетный двигатель">
                    <p class="help-block hidden"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="configurator.page_admin.new_opt_save()">Создать</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="configurator_edit_opt_pos_dg" tabindex="-1" role="dialog" aria-labelledby="configurator_edit_opt_pos_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="configurator_edit_opt_pos_dgLabel">Изменить положение опции</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="form-control" id="configurator_edit_opt_pos_opt_id">
                <div class="form-group">
                    <label for="configurator_edit_opt_pos_input">Положение опции</label>
                    <input type="text" class="form-control" id="configurator_edit_opt_pos_input">
                    <div class="bs-callout bs-callout-default">
                        Укажите число.<br>
                        Опции будут отображаться согласно положению: 0, 1, 2, 3, 4.<br>
                        Чтобы опция была выше других, ее положение должно быть меньше, чтобы опция была ниже - больше
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="configurator.page_admin.opt_pos_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="configurator_edit_opt_style_dg" tabindex="-1" role="dialog" aria-labelledby="configurator_edit_opt_style_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="configurator_edit_opt_style_dgLabel">Изменить стиль отображения опции</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="form-control" id="configurator_edit_opt_style_opt_id">
                <div class="form-group">
                    <label for="configurator_edit_opt_style_input">Стиль отображения опции</label>
                    <select class="form-control" id="configurator_edit_opt_style_input">
                        <option value="0">Заголовок слева, описание  - справа</option>
                        <option value="1">Заголовок сверху, описание  - под заголовком</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="configurator.page_admin.opt_style_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="configurator_edit_opt_price_type_dg" tabindex="-1" role="dialog" aria-labelledby="configurator_edit_opt_price_type_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="configurator_edit_opt_price_type_dgLabel">Тип цены опции</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="form-control" id="configurator_edit_opt_price_type_opt_id">
                <div class="form-group">
                    <label for="configurator_edit_opt_price_type_input">Выберите тип цены</label>
                    <select class="form-control" id="configurator_edit_opt_price_type_input">
                        <option value="0">Стандартное оборудование</option>
                        <option value="1">Без изменения цены</option>
                        <option value="2">Данные о цене отсутствуют</option>
                        <option value="3">Увеличивает цену</option>
                        <option value="4">Заменяет базовую цену</option>
                    </select>
                    <div class="bs-callout bs-callout-default">
                        <dl>
                            <dt>Стандартное оборудование</dt>
                            <dd>Отображается текст "Стандартное оборудование" вместо цены - не влияет на цену</dd>
                            <dt>Не влияет на цену</dt>
                            <dd>Цена и подпись не отображается - не влияет на цену</dd>
                            <dt>Данные о цене отсутствуют</dt>
                            <dd>Отображается текст "Данные о цене" вместо цены - не влияет на цену</dd>
                            <dt>Увеличивает цену</dt>
                            <dd>При выборе этой опции, стоимость продукта увеличивается на цену опции</dd>
                            <dt>Заменяет базовую цену</dt>
                            <dd>При выборе этой опции заменяется базовая цена продукта</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="configurator.page_admin.opt_price_type_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="configurator_edit_opt_relations_dg" tabindex="-1" role="dialog" aria-labelledby="configurator_edit_opt_relations_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-90">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="configurator_edit_opt_relations_dgLabel">Взаимодействия опций</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="form-control" id="configurator_edit_opt_relations_opt_id">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="configurator_edit_opt_replacements_input">Заменяемые опции</label>
                                <input type="text" class="form-control" id="configurator_edit_opt_replacements_input">
                                <div class="bs-callout bs-callout-default">
                                    Укажите номера заменяемых опций через пробел.<br>
                                    Заменяемые опции - это те опции, которые заменяют друг друга. То есть, если выбрана одна опция, то все остальные заменяемые опции перестанут быть выбранными.<br>
                                    Такие опции применяются, например, для выбора двигателя в конфигураторе автомобиля: можно выбрать только один тип двигателя. Соответственно, при выборе одного варианта, все остальные перестанут быть выбранными.
                                    В каждой заменяемой опции нужно прописать все ее заменяемые опции.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="configurator_edit_opt_incompatibles_input">Несовместимые опции</label>
                                <input type="text" class="form-control" id="configurator_edit_opt_incompatibles_input">
                                <div class="bs-callout bs-callout-default">
                                    Укажите номера несовместимых опций через пробел.<br>
                                    При выборе этой опции, конфигуратор предложит отказаться от несовместимых опций.<br>
                                    Пока пользователь не откажется от всех несовместимых опций, он не сможет выбрать текущую опцию
                                </div>
                            </div>
                        </div>
                    </div><div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="configurator_edit_opt_removables_input">Удаляемые автоматически</label>
                                <input type="text" class="form-control" id="configurator_edit_opt_removables_input">
                                <div class="bs-callout bs-callout-default">
                                    Укажите номера опций которые, при выборе данной опции, убираются автоматически.<br>
                                    Это почти тоже самое, что несовместимые опции, но с одним отличием: для несовместимых конфигуратор предложит пользователю от них отказаться, а для удаляемых автоматически конфигуратор удалит эти опции молча
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="configurator_edit_opt_joinables_input">Добавляемые автоматически</label>
                                <input type="text" class="form-control" id="configurator_edit_opt_joinables_input">
                                <div class="bs-callout bs-callout-default">
                                    Укажите номера опций, которые, при выборе данной опции, будут добавлены автоматически<br>
                                    Работает также, как удаляемые автоматически, но только при выборе данной опции, добавляемые автоматически опции будут добавлены молча.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="configurator_edit_opt_required_input_">Требуемые опции</label>
                        </div>
                        <?for($i=1;$i<11;$i++) {?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="configurator_edit_opt_required_input_">Группа <?=$i?></label>
                                <input type="text" class="form-control" id="configurator_edit_opt_required_input_<?=$i?>">
                            </div>
                        </div>
                        <?}?>
                        <div class="col-md-12">
                            <div class="bs-callout bs-callout-default">
                                Укажите номера опций, без выбора которых данную опцию выбрать нельзя<br>
                                При выборе данной опции, конфигуратор предложит пользователю выбрать одну из предложенных опций.<br>
                                Пока пользователь не выберет требуемые опции, данную опцию он выбрать не сможет.<br>
                                Доступно 10 групп требуемых опций.<br>
                                В каждой группе можно прописать любое количество опций через пробел.<br>
                                Все опции из группы работают по принципу "или-или", то есть нужно, чтобы была выбрана хотя бы одна из опций в группе.<br>
                                Конфигуратор будет требовать, чтобы была выбрано хотя бы по одной опции из каждой группы
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="bs-callout bs-callout-primary">
                                Номера опций указаны слева от их названия, например #10<br>
                                Указывайте в это поле только цифры - номера опций, разделяя каждый номер пробелом.<br>
                                Порядок срабатывания правил взаимодействия:<br>
                                <ol>
                                  <li>Заменяемые опции</li>
                                  <li>Удаляемые автоматически</li>
                                  <li>Добавляемые автоматически</li>
                                  <li>Несовместимые опции</li>
                                  <li>Требуемые опции</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="configurator.page_admin.opt_relations_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="configurator_edit_must_choose_option_dg" tabindex="-1" role="dialog" aria-labelledby="configurator_edit_must_choose_option_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="configurator_edit_must_choose_option_dgLabel">Параметры страницы</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="form-control" id="configurator_edit_must_choose_option_opt_id">
                <div class="form-group">
                    <label for="configurator_edit_must_choose_option_input">Нужно выбрать хотя бы одну опцию</label>
                    <select class="form-control" id="configurator_edit_must_choose_option_input">
                        <option value="0">Нет</option>
                        <option value="1">Да</option>
                    </select>
                    <div class="bs-callout bs-callout-default">
                        Если выбрано "Нужно выбрать хотя бы одну опцию", то посетитель не сможет перейти к следующей странице, пока не выберет хотя бы одну опцию на этой странице
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="configurator.page_admin.must_choose_option_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="configurator_edit_required_options_selection_dg" tabindex="-1" role="dialog" aria-labelledby="configurator_edit_required_options_selection_dgLabel" aria-hidden="true"  data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="configurator_edit_required_options_selection_dgLabel"></h4>
            </div>
            <div class="modal-body" id="configurator_edit_required_options_selection_dg_content"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="configurator.page.try_again_opt_id=0">Отмена</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uSup_filter_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_filter_dgLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Закрыть</span></button>
                <h4 class="modal-title" id="myModalLabel">Фильтр</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-12">
                        <?if($uSupport->is_com_client||$uSupport->is_com_admin||$uSupport->is_operator||$uSupport->is_consultant) {?>
                            <label for="uSup_filter_com" class="control-label">По компаниям:</label>
                            <?if(count($uSupport->qCompList)>1) {?>
                                <select class="form-control" id="uSup_filter_com">
                                    <option value="no">Не фильтровать</option>
                                    <? /** @noinspection PhpUndefinedMethodInspection */
                                    for($i=0;$com=$uSupport->qCompList[$i];$i++) {?>
                                        <option value="<?=$com->com_id?>"><?=uString::sql2text($com->com_title)?></option>
                                    <?}?>
                                </select>
                            <?} else {?>
                                <input type="hidden" id="uSup_filter_com" value="no">
                                <p>На сайте нет компаний.</p>
                            <?}
                        } else {?>
                        <input type="hidden" id="uSup_filter_com" value="no">
                        <?}?>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="control-label" for="uSup_filter_date_start">Дата открытия от:</label>
                        <div class="input-group date">
                            <input class="form-control" id="uSup_filter_date_start"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="control-label" for="uSup_filter_date_stop">Дата открытия до:</label>
                        <div class="input-group date">
                            <input class="form-control" id="uSup_filter_date_stop"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="uSup.filter_reset()">Сбросить</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="uSup.load_requests()">Фильтровать</button>
            </div>
        </div>
    </div>
</div>

<div id="uSupport_requests_no_requests_text" style="display: none;">
    <div class="jumbotron">
        <h2>Запросов не найдено :(</h2>

        <h3>Выберите на верхней панели те запросы, которые есть:</h3>
        <ol>
            <li>Выберите нужный тип: Запросы или кейсы</li>
            <li>Выберите статус(ы): Открытые, Отвеченные, Выполненные, Закрытые</li>
            <li>Выберите ответственного: Ваши, Никому не назначенные, Назначенные другим специалистам</li>
        </ol>
        <p>У каждой кнопки вверху есть число - это количество таких запросов.<br>
            Нажмите одну кнопку или сразу несколько.</p>
    </div>
</div>

<div class="modal fade" id="uSupport_show_request_dg" tabindex="-1" role="dialog" aria-labelledby="uSupport_show_requestLabel">
    <div class="modal-dialog modal-90">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSupport_show_requestLabel">Просмотр запроса</h4>
            </div>
            <div class="modal-body" id="uSupport_show_request_cnt">

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uSupport_request_dg_close_confirm_dg" tabindex="-1" role="dialog" aria-labelledby="uSupport_request_dg_close_confirm_dgLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSupport_request_dg_close_confirm_dgLabel">Закрыть окно с запросом?</h4>
            </div>
            <div class="modal-body">
                <p>Похоже, что вы начали писать ответ на запрос.</p>
                <p>Точно закрыть окно? Изменения будут утеряны.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">Не закрывать</button>
                <button type="button" class="btn btn-danger" onclick="uSup_req_show_common.request_show_dg_close_confirm()">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uSup_new_request_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_new_request_dgLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSup_new_request_dgLabel">Новый запрос</h4>
            </div>
            <div class="modal-body">
                <?if($uSupport->is_operator||$uSupport->is_consultant||$uSupport->is_com_admin||$uSupport->is_com_client){?>
                <div class="form-group">
                    <label class="control-label" for="uSup_new_req_com_select">Компания:</label>
                    <select class="form-control" id="uSup_new_req_com_select" onchange="uSup.setUserList()"></select>
                </div>
                <?}
                else {?>
                    <input type="hidden" id="uSup_new_req_com_select" value="0">
                <?}
                if($uSupport->is_operator||$uSupport->is_consultant||$uSupport->is_com_admin){?>
                <div class="form-group">
                    <label class="control-label" for="uSup_new_request_user_select">Пользователь:</label>
                    <select class="form-control" id="uSup_new_request_user_select" disabled></select>
                </div>
                <?} else {?>
                    <input type="hidden" id="uSup_new_request_user_select" value="0">
                <?}?>
                <div class="form-group">
                    <label class="control-label" for="uSup_new_request_subject">Тема:</label>
                    <input class="form-control" id="uSup_new_request_subject" onclick="uSup.new_request_edited_init()">
                </div>
                <div class="form-group">
                    <label class="control-label" for="uSup_new_request_text">Текст запроса:</label>
                    <textarea class="form-control" id="uSup_new_request_text" style="min-height:150px; " onclick="uSup.new_request_edited_init()"></textarea>
                </div>

                <div id="uSup_new_request_uploader"></div>
                <div id="uSup_new_request_filelist" class="uSupport_filelist"></div>
            </div>
            <div class="modal-footer">
                <?$terms_link=$terms_link_closer="";
                $terms_page_id=(int)$uSupport->uFunc->getConf("privacy_terms_text_id","content",1);
                if($terms_page_id) {
                    $txt_obj=$uSupport->uFunc->getStatic_data_by_id($terms_page_id,"page_name");
                    if($txt_obj) {
                        $terms_link = '<a target="_blank" href="' . u_sroot . 'page/' . $txt_obj->page_name . '">';
                        $terms_link_closer = "</a>";
                    }
}?>
                <p><?=$terms_link?>Нажимая на кнопку "Отправить", вы даете согласие на обработку своих персональных данных<?=$terms_link_closer?></p>

                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="uSup_new_request_save_btn" onclick="uSup.new_request_save()">Отправить</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="uSup_new_request_delete_files_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_new_request_delete_files_dgLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSup_new_request_delete_files_dgLabel">Удалить файлы?</h4>
            </div>
            <div class="modal-body">
                Вы действительно хотите удалить отмеченные файлы?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-danger" onclick="uSup.delete_files()">Удалить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uSup_new_request_dg_close_confirm_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_new_request_dg_close_confirm_dgLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSup_new_request_dg_close_confirm_dgLabel">Закрыть окно с запросом?</h4>
            </div>
            <div class="modal-body">
                <p>Похоже, что вы начали новый запрос.</p>
                <p>Точно закрыть окно? Изменения будут утеряны.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">Не закрывать</button>
                <button type="button" class="btn btn-danger" onclick="uSup.new_request_dg_close_confirm()">Закрыть</button>
            </div>
        </div>
    </div>
</div>
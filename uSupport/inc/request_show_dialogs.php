<div class="modal fade" id="uSup_open_req_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_open_req_dgLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSup_open_req_dgLabel">Решение в базе знаний</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted">Нажмите на решение - оно откроется в новой вкладке</p>
                <div id="uSup_open_req_solutions"></div>
            </div><?if($this->access(8)||$this->access(9)) {?>
            <div class="modal-footer">
                <button id="uSup_uKnowbase_btn2" onclick="uSup_req_show_common.set_solution()" class="btn btn-default" data-dismiss="modal">Изменить решение</button>
            </div>
            <?}?>
        </div>
    </div>
</div>

<div class="modal fade" id="uSupport_change_status_dg" tabindex="-1" role="dialog" aria-labelledby="uSupport_change_status_dgLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSupport_change_status_dgLabel">Изменить статус?</h4>
            </div>
            <div class="modal-body" id="uSupport_change_status_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="uSup_req_show_common.changeReqStatus_send()">Продолжаем</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uSupport_request_show_delete_files_dg" tabindex="-1" role="dialog" aria-labelledby="uSupport_request_show_delete_files_dgLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSupport_request_show_delete_files_dgLabel">Удалить файлы?</h4>
            </div>
            <div class="modal-body">
                Вы действительно хотите удалить отмеченные файлы?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-danger" onclick="uSup_req_show_common.delete_files_exec()">Удалить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uSup_send_feedback_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_send_feedback_dgLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSup_send_feedback_dgLabel">Оценка качества техподдержки</h4>
            </div>
            <div class="modal-body" id="uSup_send_feedback_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="uSup_send_feedback_send_btn" onclick="uSup_req_show_common.send_feedback_do()">Отправить</button>
            </div>
        </div>
    </div>
</div>

<?if($uSupport->is_consultant||$uSupport->is_operator||$uSupport->is_com_admin){?>
    <div class="modal fade" id="uSup_set_rec_id_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_set_rec_id_dgLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uSup_set_rec_id_dgLabel">Назначить решение к запросу</h4>
                </div>
                <div style="display: table; width: 100%;">
                    <div class="btn-group" style="display: table; float:right;">
                        <button type="button" class="btn btn-success" onclick="uKnowbase_inline_create.new_record_init();">Создать новое</button>
                        <button type="button" class="btn btn-warning" onclick="uSup_req_show_common.set_no_solution();">Без решения</button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="text-info" id="uSup_set_rec_id_text_info" style="display: none"></div>
                    <div class="text-danger" id="uSup_set_rec_id_text_danger" style="display: none"></div>
                    <p class="text-muted">Нажмите на решение, чтобы назначить его или убрать</p>
                    <div id="uSup_set_rec_id_solutions"></div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="uSup_change_req_subject_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_change_req_subject_dgLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uSup_change_req_subject_dgLabel">Смена темы запроса</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="uSup_change_req_subject_input">Новая тема запроса</label>
                        <input id="uSup_change_req_subject_input" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary" onclick="uSup_req_show_common.change_subject_save();">Сохранить</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uSup_change_cat_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_change_cat_dgLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uSup_change_cat_dgLabel">Сменить категорию запроса</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group" id="uSup_change_cat_cnt"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-default" onclick="uSup_req_show_common.requests_cats_editor()">Редактор категорий</button>
                    <button type="button" class="btn btn-primary" onclick="uSup_req_show_common.changeCat_do();">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="uSup_requests_cats_editor_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_requests_cats_editor_dgLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uSup_requests_cats_editor_dgLabel">Редактор категорий</h4>
                </div>
                <div class="modal-body" id="uSup_requests_cats_editor_cnt"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary" onclick="uSup_req_show_common.request_new_cat()">Создать</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uSup_category_editor_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_category_editor_dgLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uSup_category_editor_dgLabel">Редактор категории</h4>
                </div>
                <div class="modal-body" id="uSup_category_editor_cnt"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary" onclick="uSup_req_show_common.requests_cat_editor_save()">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="uSup_cat_delete_confirm_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_cat_delete_confirm_dgLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uSup_cat_delete_confirm_dgLabel">Удалить категорию?</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="uSup_cat_delete_confirm_cat_id">
                    <p class="well-lg bg-danger">Вы действительно хотите удалить выбранную категорию?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" onclick="uSup_req_show_common.requests_cat_delete_proceed()">Удалить</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="uSup_new_cat_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_new_cat_dgLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uSup_new_cat_dgLabel">Новая категория</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="uSup_new_cat_input">Название категории</label>
                        <input class="form-control" id="uSup_new_cat_input">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary" onclick="uSup_req_show_common.request_new_cat_proceed()">Создать</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uSupport_set_cons_dg" tabindex="-1" role="dialog" aria-labelledby="uSupport_set_cons_dgLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uSupport_set_cons_dgLabel">Назначить ответственного</h4>
                </div>
                <div class="modal-body" id="uSupport_set_cons_cnt"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary" onclick="uSup_req_show_common.setCons_send();">Назначить</button>
                </div>
            </div>
        </div>
    </div>
<div class="modal fade bs-example-modal-lg" id="uSup_log_time_dg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Записать потраченное время</h4>
            </div>
            <div class="modal-body">

                <p class="text-danger" id="uSup_log_time_err" style="display: none;"></p>
                <p class="text-info" id="uSup_log_time_info" style="display:none;">Изменения сохраняются...</p>

                <?if($this->access(8)) {?>
                <div class="form-group">
                    <label class="control-label" for="uSup_log_time_user_id">Консультант</label>
                    <select id="uSup_log_time_user_id" name="uSup_log_time_user_id" class="form-control">
                        <?for($i=0;$user=$uSupport->q_cons_list[$i];$i++) {
                            $user_name=uString::sql2text($user->firstname.' '.$user->secondname.' '.$user->lastname,1);
                            if($user->user_id==$uSupport->uSes->get_val("user_id")) $user_name=$user_name.' (Я)';
                            ?>
                            <option <?if($user->user_id==$uSupport->uSes->get_val("user_id")) echo 'selected';?>  value="<?=$user->user_id?>"><?=$user_name?></option>
                        <?}?>
                    </select>
                </div>
                <?} else {?>
                <input type="hidden" id="uSup_log_time_user_id" value="<?=$uSupport->uSes->get_val("user_id")?>">
                <?}?>

                <label class="control-label" for="uSup_log_time_hours">Потраченное время</label>
                <div class="row form-group">
                    <div class="col-xs-4">
                        <input id="uSup_log_time_hours" name="uSup_log_time_hours" class="form-control" value="0">
                        <span class="help-inline">часов</span>
                    </div>
                    <div class="col-xs-4">
                        <!--suppress HtmlFormInputWithoutLabel -->
                        <input id="uSup_log_time_minutes" class="form-control" value="0">
                        <span class="help-inline">минут</span>
                    </div>
                </div>
                <label class="control-label" for="uSup_log_time_hours">Дата и время</label>
                <div class="row form-group">
                    <div class="col-xs-4">
                        <div class="input-group date">
                            <!--suppress HtmlFormInputWithoutLabel -->
                            <input class="form-control" id="uSup_log_time_date"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                        </div>
                        <span class="help-inline">дата</span>
                    </div>
                    <div class="col-xs-4">
                        <div class="input-group clockpicker" data-placement="left" data-align="top" data-autoclose="true">
                            <!--suppress HtmlFormInputWithoutLabel -->
                            <input class="form-control" id="uSup_log_time_time">
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-time"></span>
                            </span>
                        </div>
                        <span class="help-inline">время</span>
                    </div>
                </div>
                <label class="control-label" for="uSup_log_time_comment">Комментарий:</label>
                <div class="row form-group">
                    <div class="col-xs-12">
                        <textarea id="uSup_log_time_comment" name="uSup_log_time_comment" class="form-control"></textarea>
                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="uSup_req_show_common.save_time()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uSup_delete_time_log_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_delete_time_log_dgLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSup_delete_time_log_dgLabel">Удалить запись?</h4>
            </div>
            <div class="modal-body">
                <p class="bg-danger">Вы действительно хотите удалить выбранную запись?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-danger" onclick="uSup_req_show_common.del_time_log()">Удалить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uSup_set_no_solution_reason_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_set_no_solution_reason_dgLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSup_set_no_solution_reason_dgLabel">Решение не требуется</h4>
            </div>
            <div class="modal-body">
                <p>Вы можете отметить этот запрос, как не требующий решения.</p>
                <div class="form-group">
                    <label class="control-label" for="uSup_set_no_solution_reason">Причина:</label>
                    <textarea class="form-control" id="uSup_set_no_solution_reason" name="uSup_set_no_solution_reason" placeholder="Причина"></textarea>
                    <p class="help-block">Напишите почему этот запрос не требует добавления решения в базу знаний.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uSup_req_show_common.set_no_solution_do()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uSup_show_no_solution_reason_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_show_no_solution_reason_dgLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSup_show_no_solution_reason_dgLabel">Причина запроса без решения</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Консультант:</label>
                    <p class="form-control-static" id="uSup_show_now_solution_consultant"></p>
                    <label>Причина:</label>
                    <p class="form-control-static" id="uSup_show_now_solution_reason"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uSup_req_show_common.set_solution(uSup_req_show_common.tic_id)">Изменить</button>
            </div>
        </div>
    </div>
</div>

    <div class="modal fade" id="uSup_request_show_feedback_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_request_show_feedback_dgLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uSup_request_show_feedback_dgLabel">Отзыв о техподдержке</h4>
                </div>
                <div class="modal-body" id="uSup_request_show_feedback_cnt"></div>
            </div>
        </div>
    </div>
<?}?>
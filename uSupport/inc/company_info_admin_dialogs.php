<div class="modal fade" id="uSup_newUser_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_newUser_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSup_newUser_dgLabel">Прикрепить пользователя</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uSup_newUser_text_info" style="display: none"></div>
                <div class="text-danger" id="uSup_newUser_text_danger" style="display: none"></div>
                <div class="form-group">
                    <? if(count($uSup->unattachedUsers_ar)>1) {?>
                        <label>Пользователи, зарегистрированные на сайте</label>
                        <div class="input-group">
                            <input type="text" id="uSup_newUser_filter" class="form-control" placeholder="Фильтр" onkeyup="uSup.com_new_user_filter()">
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button"><span class="glyphicon glyphicon-search" onclick="uSup.com_new_user_filter()"></span></button>
                            </span>
                        </div>
                        <table class="table table-condensed table-striped table-hover" id="uSup_newUserId">
                            <? for($i=0;$user=$uSup->unattachedUsers_ar[$i];$i++) {?>
                                <tr>
                                    <td><?=$user->user_id?></td>
                                    <td><?=$user->firstname?> <?=$user->secondname?> <?=$user->lastname?> #<?=$user->user_id?></td>
                                    <td><button onclick="uSup.new_user(<?=$user->user_id?>)" class="btn btn-success btn-xs uTooltip" title="Добавить пользователя в компанию"><span class="glyphicon glyphicon-plus"></span></button></td>
                                </tr>
                            <?}?>
                        </table>
                        <span class="help-block">Выберите пользователя сайта, чтобы добавить его в компанию</span>
                    <?}
                    else {?>
                        <p>Нет ни одного пользователя, неприкрепленного к компании</p>
                    <?}?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uSup_newUserByEmail_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_newUserByEmail_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSup_newUserByEmail_dgLabel">Новый пользователь</h4>
            </div>
            <div class="modal-body">
                <div class="text-info" id="uSup_newUserByEmail_text_info" style="display: none"></div>
                <div class="text-danger" id="uSup_newUserByEmail_text_danger" style="display: none"></div>
                <p class="text-muted">Если пользователь зарегистрирован, то он будет сразу добавлен в компанию и уведомлен об этом.<br>
                    Если не зарегистрирован, то будет создан и будет отправлено уведомление с логином и паролем</p>
                <div class="form-group form-icon">
                    <label class="control-label">e-mail *:</label>
                    <input type="text" id="uSup_newUserByEmail_email" class="form-control">
                    <span id="uSup_newUserByEmail_email_icon_proc" class='icon-spin4 animate-spin input-icon hidden'></span>
                    <span id="uSup_newUserByEmail_email_icon_done" class='icon-ok input-icon hidden'></span>
                </div>
                <div class="form-group">
                    <label>Имя *:</label>
                    <input type="text" id="uSup_newUserByEmail_firstname" class="form-control">
                </div>
                <div class="form-group">
                    <label>Отчество:</label>
                    <input type="text" id="uSup_newUserByEmail_secondname" class="form-control">
                </div>
                <div class="form-group">
                    <label>Фамилия *:</label>
                    <input type="text" id="uSup_newUserByEmail_lastname" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="jQuery('#uSup_newUserByEmail_dg').modal('hide');">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uSup.new_user_by_email();">Добавить пользователя</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uSup_com_info_del_admin_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_com_info_del_admin_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSup_com_info_del_admin_dgLabel">Убрать админа?</h4>
            </div>
            <div class="modal-body">
                <p>Вы действительно хотите убрать этого пользователя из администраторов поддержки данной компании?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-danger" onclick="uSup.del_user_do('admin');">Убрать</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uSup_com_info_del_user_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_com_info_del_user_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSup_com_info_del_user_dgLabel">Убрать пользователя?</h4>
            </div>
            <div class="modal-body">
                <p>Вы действительно хотите убрать этого пользователя из данной компании?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-danger" onclick="uSup.del_user_do('user');">Убрать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uSup_newDomain_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_newDomain_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSup_newDomain_dgLabel">Новый домен компании</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Доменное имя</label>
                    <input type="text" id="uSup_newDomainName" class="form-control">
                    <span class="help-block">Например <?=$this->uFunc->getConf('site_domain','content')?></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uSup.new_domain();">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uSup_delDomnain_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_delDomnain_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSup_delDomnain_dgLabel">Убрать домен</h4>
            </div>
            <div class="modal-body">
                <p>Вы действительно хотите убрать этот почтовый домен из данной компании?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-danger" onclick="uSup.del_domain_do();">Удалить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uSup_com_info_com_title_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_com_info_com_title_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSup_com_info_com_title_dgLabel">Изменить название компании</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Новое название</label>
                    <input type="text" id="uSup_com_info_com_title_input" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uSup.change_title_do()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uSup_remove_com_confirm_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_remove_com_confirm_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uSup_remove_com_confirm_dgLabel">Удалить компанию?</h4>
            </div>
            <div class="modal-body">
                <p class="bg-danger">Компания будет удалена безвозвратно, пользователи и запросы будут откреплены от нее.<br><br>Это действие нельзя будет отменить!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-danger" onclick="uSup.remove_com_confirm();">Подтверждаю</button>
            </div>
        </div>
    </div>
</div>
<?php
require_once 'processors/classes/uFunc.php';

class uSup_reports {
    private $uCore;
    public $q_cons,$q_comps,$q_time;
    private function get_consultants() {
        if(!$this->q_cons=$this->uCore->query("uAuth","SELECT DISTINCT
        `u235_users`.`user_id`,
        `firstname`,
        `secondname`,
        `lastname`
        FROM
        `u235_users`,
        `u235_usersinfo`,
        `u235_usersinfo_groups`
        WHERE
        (`u235_usersinfo_groups`.`group_id`='4' OR `u235_usersinfo_groups`.`group_id`='5') AND
        `u235_usersinfo_groups`.`user_id`=`u235_users`.`user_id` AND
        `u235_usersinfo_groups`.`site_id`='".site_id."' AND
        `u235_usersinfo`.`status`='active' AND
        `u235_usersinfo`.`user_id`=`u235_users`.`user_id` AND
        `u235_usersinfo`.`site_id`='".site_id."' AND
        `u235_users`.`status`='active'
        ORDER BY
        `firstname` ASC
        ")) $this->uCore->error(10);
    }
    private function get_companies() {
        if(!$this->q_comps=$this->uCore->query("uSup","SELECT
        `com_id`,
        `com_title`
        FROM
        `u235_comps`
        WHERE
        (`com_status` IS NULL OR `com_status`='') AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(20);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        $this->uFunc=new \processors\uFunc($this->uCore);

        $this->get_consultants();
        $this->get_companies();

        $this->uFunc->incJs(u_sroot.'uSupport/js/reports.js');
        $this->uFunc->incJs(u_sroot."js/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js");
        $this->uFunc->incJs(u_sroot."js/bootstrap-datepicker/dist/locales/bootstrap-datepicker.ru.min.js");
        $this->uFunc->incCss(u_sroot."js/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css");
    }
}
$uSup=new uSup_reports($this);

ob_start();?>
<h1 class="page-header"><?=$this->page['page_title']?></h1>

<div class="row">
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12">
                <h4>Даты запросов</h4>
                <div class="form-group">
                    <select class="form-control" id="uSup_reports_fast_range_select" onchange="uSup.set_fast_range();">
                        <option class="text-muted" value="default">За все время</option>
                        <option value="today">Сегодня</option>
                        <option value="yesterday">Вчера</option>
                        <option value="current_week">Текущая неделя (пн-вс)</option>
                        <option value="last_week">Прошлая неделя (пн-вс)</option>
                        <option value="current_month">Текущий месяц</option>
                        <option value="last_month">Прошлый месяц</option>
                        <option value="last_7_days">Последние 7 дней</option>
                        <option value="last_15_days">Последние 15 дней</option>
                        <option value="last_30_days">Последние 30 дней</option>
                    </select>
                </div>
            </div>
            <div class="form-group col-md-6">
                <label>Дата, от</label>
                <div class="input-group date">
                    <input type="text" class="form-control" id="uSup_reports_date_from"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                </div>
            </div>
            <div class="form-group col-md-6">
                <label>Дата, до включительно</label>
                <div class="input-group date">
                    <input type="text" class="form-control" id="uSup_reports_date_to"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                </div>
            </div>
            <div class="form-group col-md-12">
                <h5>Что должно попадать под выбранные даты?</h5>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="uSup_reports_date_filter_request_open">
                         <span>Дата открытия запроса</span>
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="uSup_reports_date_filter_request_changed">
                         <span>Дата изменения/закрытия запроса</span>
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="uSup_reports_date_filter_time_logged">
                         <span>Дата списания времени на запрос</span>
                    </label>
                    <span class="text-muted"> - если выбрать, то будут отображены ТОЛЬКО те запросы, где списано время</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-12">
                <h4>Статусы запросов</h4>
                <div class="checkbox">
                    <label><input type="checkbox"  id="uSup_reports_req_status_closed">
                        <span>Закрытые</span>
                    </label>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox"  id="uSup_reports_req_status_done">
                        <span>Выполненные</span>
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox"  id="uSup_reports_req_status_open">
                        <span >Открытые</span>
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox"  id="uSup_reports_req_status_answered">
                        <span>Отвеченные</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h4>Списанное время</h4>
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox"  id="uSup_reports_detalized">
                            <span>Детализировать потраченное на запрос время</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox"  id="uSup_reports_time_spent">
                            <span>Отображать только те запросы, где списано время</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group" id="uSup_cons_group">
            <h4>Кому назначены кейсы</h4>
            <button type="button" class="btn btn-default btn-xs" id="uSup_cons_group_sel_all_btn" onclick="uSup.sel_all_cons()">Выбрать всех</button>
            <?while($user=$uSup->q_cons->fetch_object()) {?>
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="uSup_cons_<?=$user->user_id?>">
                    <span><?=uString::sql2text($user->firstname)?> <?=uString::sql2text($user->secondname)?> <?=uString::sql2text($user->lastname)?></span>
                </label>
            </div>
            <?}?>
            <p class="text-muted">Помните, что назначить можно только <B>кейс</B>. Если выбрать консультанта, то отобразятся только назначенные ему <b>кейсы</b>.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group" id="uSup_comps_group">
            <label>Компании</label>
            <button type="button" id="uSup_comps_group_sel_all_btn" class="btn btn-default btn-xs" onclick="uSup.sel_all_comps()">Выбрать все</button>
            <?while($com=$uSup->q_comps->fetch_object()) {?>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="uSup_comp_<?=$com->com_id?>">
                        <span><?=uString::sql2text($com->com_title)?></span>
                    </label>
                </div>
            <?}?>
        </div>
    </div>
</div>
<button type="button" class="btn btn-primary" onclick="uSup.load_report()">Получить отчет</button>

<div id="uSup_time_report"></div>

<?$this->page_content=ob_get_contents();
ob_end_clean();
include "templates/u235/template.php";

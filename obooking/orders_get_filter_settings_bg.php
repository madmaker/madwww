<?php
namespace obooking;

use PDO;
use uSes;
use uString;

require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class orders_get_filter_settings_bg {
    /**
     * @var common
     */
    private $obooking;

    private function check_ses_var() {
        if(!isset($_SESSION["obooking"])) {
            $_SESSION["obooking"] = [];
        }

        if(!isset($_SESSION["obooking"]["filter"])) {
            $_SESSION["obooking"]["filter"] = [];
        }
        if(!isset($_SESSION["obooking"]["filter"]["offices"])) {
            $_SESSION["obooking"]["filter"]["offices"] = [];
        }
        if(!isset($_SESSION["obooking"]["filter"]["timestamps"])) {
            $_SESSION["obooking"]["filter"]["timestamps"] = [];
        }
        if(!isset($_SESSION["obooking"]["filter"]["next_contact_dates"])) {
            $_SESSION["obooking"]["filter"]["next_contact_dates"] = [];
        }
        if(!isset($_SESSION["obooking"]["filter"]["trial_dates"])) {
            $_SESSION["obooking"]["filter"]["trial_dates"] = [];
        }
        if(!isset($_SESSION["obooking"]["filter"]["timestamps"]["start"])) {
            $_SESSION["obooking"]["filter"]["timestamps"]["start"] = 0;
        }
        if(!isset($_SESSION["obooking"]["filter"]["next_contact_dates"]["start"])) {
            $_SESSION["obooking"]["filter"]["next_contact_dates"]["start"] = 0;
        }
        if(!isset($_SESSION["obooking"]["filter"]["trial_dates"]["start"])) {
            $_SESSION["obooking"]["filter"]["trial_dates"]["start"] = 0;
        }
        if(!isset($_SESSION["obooking"]["filter"]["timestamps"]["end"])) {
            $_SESSION["obooking"]["filter"]["timestamps"]["end"] = 0;
        }
        if(!isset($_SESSION["obooking"]["filter"]["next_contact_dates"]["end"])) {
            $_SESSION["obooking"]["filter"]["next_contact_dates"]["end"] = 0;
        }
        if(!isset($_SESSION["obooking"]["filter"]["trial_dates"]["end"])) {
            $_SESSION["obooking"]["filter"]["trial_dates"]["end"] = 0;
        }
        if(!isset($_SESSION["obooking"]["filter"]["statuses"])) {
            $_SESSION["obooking"]["filter"]["statuses"] = [];
        }
        if(!isset($_SESSION["obooking"]["filter"]["courses"])) {
            $_SESSION["obooking"]["filter"]["courses"] = [];
        }
        if(!isset($_SESSION["obooking"]["filter"]["sources"])) {
            $_SESSION["obooking"]["filter"]["sources"] = [];
        }
        if(!isset($_SESSION["obooking"]["filter"]["how_did_find_outs"])) {
            $_SESSION["obooking"]["filter"]["how_did_find_outs"] = [];
        }
        if(!isset($_SESSION["obooking"]["filter"]["managers"])) {
            $_SESSION["obooking"]["filter"]["managers"] = [];
        }

        if(!isset($_SESSION["obooking"]["sort"])) {
            $_SESSION["obooking"]["sort"] = [];
        }
        if(!isset($_SESSION["obooking"]["sort"]["fields"])) {
            $_SESSION["obooking"]["sort"]["fields"] = [];
        }
        if(!isset($_SESSION["obooking"]["sort"]["order"])) {
            $_SESSION["obooking"]["sort"]["order"] = [];
        }
    }

    private function office_is_filtered(/*int*/$office_id) {
        $this->check_ses_var();
        if(!isset($_SESSION["obooking"]["filter"]["offices"][$office_id])) {
            $_SESSION["obooking"]["filter"]["offices"][$office_id] = 0;
        }

        return $_SESSION["obooking"]["filter"]["offices"][$office_id];
    }
    private function timestamp_is_filtered(/*start|end*/$point) {
        $this->check_ses_var();
        if(!$_SESSION["obooking"]["filter"]["timestamps"][$point]) {
            return "";
        }

        return date('d.m.Y',$_SESSION["obooking"]["filter"]["timestamps"][$point]);
    }
    private function next_contact_date_is_filtered(/*start|end*/$point) {
        $this->check_ses_var();
        if(!$_SESSION["obooking"]["filter"]["next_contact_dates"][$point]) {
            return "";
        }

        return date('d.m.Y',$_SESSION["obooking"]["filter"]["next_contact_dates"][$point]);
    }
    private function trial_date_is_filtered(/*start|end*/$point) {
        $this->check_ses_var();
        if(!$_SESSION["obooking"]["filter"]["trial_dates"][$point]) {
            return "";
        }

        return date('d.m.Y',$_SESSION["obooking"]["filter"]["trial_dates"][$point]);
    }
    private function status_is_filtered(/*int*/$status_id) {
        $this->check_ses_var();
        if(!isset($_SESSION["obooking"]["filter"]["statuses"][$status_id])) {
            $_SESSION["obooking"]["filter"]["statuses"][$status_id] = 0;
        }

        return $_SESSION["obooking"]["filter"]["statuses"][$status_id];
    }
    private function course_is_filtered(/*int*/$course_id) {
        $this->check_ses_var();
        if(!isset($_SESSION["obooking"]["filter"]["courses"][$course_id])) {
            $_SESSION["obooking"]["filter"]["courses"][$course_id] = 0;
        }

        return $_SESSION["obooking"]["filter"]["courses"][$course_id];
    }
    private function source_is_filtered(/*int*/$source_id) {
        $this->check_ses_var();
        if(!isset($_SESSION["obooking"]["filter"]["sources"][$source_id])) {
            $_SESSION["obooking"]["filter"]["sources"][$source_id] = 0;
        }

        return $_SESSION["obooking"]["filter"]["sources"][$source_id];
    }
    private function how_did_find_out_is_filtered(/*int*/$how_did_find_out_id) {
        $this->check_ses_var();
        if(!isset($_SESSION["obooking"]["filter"]["how_did_find_outs"][$how_did_find_out_id])) {
            $_SESSION["obooking"]["filter"]["how_did_find_outs"][$how_did_find_out_id] = 0;
        }

        return $_SESSION["obooking"]["filter"]["how_did_find_outs"][$how_did_find_out_id];
    }
    private function manager_is_filtered(/*int*/$manager_id) {
        $this->check_ses_var();
        if(!isset($_SESSION["obooking"]["filter"]["managers"][$manager_id])) {
            $_SESSION["obooking"]["filter"]["managers"][$manager_id] = 0;
        }

        return $_SESSION["obooking"]["filter"]["managers"][$manager_id];
    }

    private function toggle_office_filter(/*int*/$office_id) {
        if(!isset($_SESSION["obooking"]["filter"]["offices"][$office_id])) {
            $_SESSION["obooking"]["filter"]["offices"][$office_id] = 0;
        }
        $_SESSION["obooking"]["filter"]["offices"][$office_id]=1-$_SESSION["obooking"]["filter"]["offices"][$office_id];

        return $_SESSION["obooking"]["filter"]["offices"][$office_id];
    }
    private function toggle_timestamp_filter($start_date,$end_date) {
        if($start_date==="0") {
            $start_timestamp = 0;
        }
        else {
            $timestamp_ar=explode('.',$start_date);
            $start_timestamp = strtotime($timestamp_ar[1]."/".$timestamp_ar[0]."/".$timestamp_ar[2]);
        }

        if($end_date==="0") {
            $end_timestamp = 0;
        }
        else {
            $timestamp_ar=explode('.',$end_date);
            $end_timestamp = strtotime($timestamp_ar[1]."/".$timestamp_ar[0]."/".$timestamp_ar[2]);
        }

        $_SESSION["obooking"]["filter"]["timestamps"]["start"]=$start_timestamp;
        $_SESSION["obooking"]["filter"]["timestamps"]["end"]=$end_timestamp;
    }
    private function toggle_next_contact_date_filter($start_date,$end_date) {
        if($start_date==="0") {
            $start_timestamp = 0;
        }
        else {
            $timestamp_ar=explode('.',$start_date);
            $start_timestamp = strtotime($timestamp_ar[1]."/".$timestamp_ar[0]."/".$timestamp_ar[2]);
        }

        if($end_date==="0") {
            $end_timestamp = 0;
        }
        else {
            $timestamp_ar=explode('.',$end_date);
            $end_timestamp = strtotime($timestamp_ar[1]."/".$timestamp_ar[0]."/".$timestamp_ar[2]);
        }

        $_SESSION["obooking"]["filter"]["next_contact_dates"]["start"]=$start_timestamp;
        $_SESSION["obooking"]["filter"]["next_contact_dates"]["end"]=$end_timestamp;
    }
    private function toggle_trial_date_filter($start_date,$end_date) {
        if($start_date==="0") {
            $start_trial_date = 0;
        }
        else {
            $trial_date_ar=explode('.',$start_date);
            $start_trial_date = strtotime($trial_date_ar[1]."/".$trial_date_ar[0]."/".$trial_date_ar[2]);
        }

        if($end_date==="0") {
            $end_trial_date = 0;
        }
        else {
            $trial_date_ar=explode('.',$end_date);
            $end_trial_date = strtotime($trial_date_ar[1]."/".$trial_date_ar[0]."/".$trial_date_ar[2]);
        }

        $_SESSION["obooking"]["filter"]["trial_dates"]["start"]=$start_trial_date;
        $_SESSION["obooking"]["filter"]["trial_dates"]["end"]=$end_trial_date;
    }
    private function toggle_status_filter(/*int*/$status_id) {
        if(!isset($_SESSION["obooking"]["filter"]["statuses"][$status_id])) {
            $_SESSION["obooking"]["filter"]["statuses"][$status_id] = 0;
        }
        $_SESSION["obooking"]["filter"]["statuses"][$status_id]=1-$_SESSION["obooking"]["filter"]["statuses"][$status_id];

        return $_SESSION["obooking"]["filter"]["statuses"][$status_id];
    }
    private function toggle_course_filter(/*int*/$course_id) {
        if(!isset($_SESSION["obooking"]["filter"]["courses"][$course_id])) {
            $_SESSION["obooking"]["filter"]["courses"][$course_id] = 0;
        }
        $_SESSION["obooking"]["filter"]["courses"][$course_id]=1-$_SESSION["obooking"]["filter"]["courses"][$course_id];

        return $_SESSION["obooking"]["filter"]["courses"][$course_id];
    }
    private function toggle_source_filter(/*int*/$source_id) {
        if(!isset($_SESSION["obooking"]["filter"]["sources"][$source_id])) {
            $_SESSION["obooking"]["filter"]["sources"][$source_id] = 0;
        }
        $_SESSION["obooking"]["filter"]["sources"][$source_id]=1-$_SESSION["obooking"]["filter"]["sources"][$source_id];

        return $_SESSION["obooking"]["filter"]["sources"][$source_id];
    }
    private function toggle_how_did_find_out_filter(/*int*/$how_did_find_out_id) {
        if(!isset($_SESSION["obooking"]["filter"]["how_did_find_outs"][$how_did_find_out_id])) {
            $_SESSION["obooking"]["filter"]["how_did_find_outs"][$how_did_find_out_id] = 0;
        }
        $_SESSION["obooking"]["filter"]["how_did_find_outs"][$how_did_find_out_id]=1-$_SESSION["obooking"]["filter"]["how_did_find_outs"][$how_did_find_out_id];

        return $_SESSION["obooking"]["filter"]["how_did_find_outs"][$how_did_find_out_id];
    }
    private function toggle_manager_filter(/*int*/$manager_id) {
        if(!isset($_SESSION["obooking"]["filter"]["managers"][$manager_id])) {
            $_SESSION["obooking"]["filter"]["managers"][$manager_id] = 0;
        }
        $_SESSION["obooking"]["filter"]["managers"][$manager_id]=1-$_SESSION["obooking"]["filter"]["managers"][$manager_id];

        return $_SESSION["obooking"]["filter"]["managers"][$manager_id];
    }

    private function print_offices_dg() {
        ob_start();
        $offices_stm=$this->obooking->get_offices("office_id,office_name");
        ?>
        <div class="input-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="obooking_orders_filter_offices_dg_row_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_orders.filter_offices_list_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"><span class="icon-search" onclick="obooking_orders.filter_offices_list_filter()"></span></button>
            </span>
        </div>

        <table class="table table-condensed table-hover" id="obooking_orders_filter_offices_dg_list">
            <?php
            while($office=$offices_stm->fetch(PDO::FETCH_OBJ)) {
                $office->office_id=(int)$office->office_id;
                $is_filtered=$this->office_is_filtered($office->office_id);?>
                <tr
                        id="obooking_orders_filter_offices_dg_row_<?=$office->office_id?>"
                        class="<?=$is_filtered?'bg-success':''?>"
                        data-office_id="<?=$office->office_id?>"
                        data-is_filtered="<?=$is_filtered?>"
                >
                    <td style="cursor: pointer" onclick="obooking_orders.toggle_office2filter(<?=$office->office_id?>)">#<?=$office->office_id?></td>
                    <td class="obooking_orders_filter_offices_dg_list_office_name" style="cursor: pointer" onclick="obooking_orders.toggle_office2filter(<?=$office->office_id?>)"><?=$office->office_name?></td>
                </tr>
            <?}?>
        </table>
        <?php
        return ob_get_clean();
    }
    private function print_timestamps_dg() {
        ob_start();
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <label for="obooking_orders_filter_timestamps_dg_start_timestamp">Дата с</label>
                    <input id="obooking_orders_filter_timestamps_dg_start_timestamp" type="text" class="form-control" placeholder="<?=date('d.m.Y')?>" onblur="obooking_orders.toggle_timestamp2filter('start')" value="<?=$this->timestamp_is_filtered("start")?>">
                </div>
                <div class="col-md-6">
                    <label for="obooking_orders_filter_timestamps_dg_end_timestamp">Дата по</label>
                    <input id="obooking_orders_filter_timestamps_dg_end_timestamp" type="text" class="form-control" placeholder="<?=date('d.m.Y')?>" onblur="obooking_orders.toggle_timestamp2filter('end')" value="<?=$this->timestamp_is_filtered("end")?>">
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    private function print_next_contact_dates_dg() {
        ob_start();
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <label for="obooking_orders_filter_next_contact_dates_dg_start_timestamp">Дата с</label>
                    <input id="obooking_orders_filter_next_contact_dates_dg_start_timestamp" type="text" class="form-control" placeholder="<?=date('d.m.Y')?>" onblur="obooking_orders.toggle_next_contact_date2filter('start')" value="<?=$this->next_contact_date_is_filtered("start")?>">
                </div>
                <div class="col-md-6">
                    <label for="obooking_orders_filter_next_contact_dates_dg_end_timestamp">Дата по</label>
                    <input id="obooking_orders_filter_next_contact_dates_dg_end_timestamp" type="text" class="form-control" placeholder="<?=date('d.m.Y')?>" onblur="obooking_orders.toggle_next_contact_date2filter('end')" value="<?=$this->next_contact_date_is_filtered("end")?>">
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    private function print_trial_dates_dg() {
        ob_start();
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <label for="obooking_orders_filter_trial_dates_dg_start_trial_date">Дата с</label>
                    <input id="obooking_orders_filter_trial_dates_dg_start_trial_date" type="text" class="form-control" placeholder="<?=date('d.m.Y')?>" onblur="obooking_orders.toggle_trial_date2filter('start')" value="<?=$this->trial_date_is_filtered("start")?>">
                </div>
                <div class="col-md-6">
                    <label for="obooking_orders_filter_trial_dates_dg_end_trial_date">Дата по</label>
                    <input id="obooking_orders_filter_trial_dates_dg_end_trial_date" type="text" class="form-control" placeholder="<?=date('d.m.Y')?>" onblur="obooking_orders.toggle_trial_date2filter('end')" value="<?=$this->trial_date_is_filtered("end")?>">
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    private function print_statuses_dg() {
        ob_start();
        $statuses_stm=$this->obooking->get_order_statuses("status_id,status_name");
        ?>
        <div class="input-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="obooking_orders_filter_statuses_dg_row_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_orders.filter_statuses_list_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"><span class="icon-search" onclick="obooking_orders.filter_statuses_list_filter()"></span></button>
            </span>
        </div>

        <table class="table table-condensed table-hover" id="obooking_orders_filter_statuses_dg_list">
            <?php
            while($status=$statuses_stm->fetch(PDO::FETCH_OBJ)) {
                $status->status_id=(int)$status->status_id;
                $is_filtered=$this->status_is_filtered($status->status_id);?>
                <tr
                        id="obooking_orders_filter_statuses_dg_row_<?=$status->status_id?>"
                        class="<?=$is_filtered?'bg-success':''?>"
                        data-status_id="<?=$status->status_id?>"
                        data-is_filtered="<?=$is_filtered?>"
                >
                    <td style="cursor: pointer" onclick="obooking_orders.toggle_status2filter(<?=$status->status_id?>)">#<?=$status->status_id?></td>
                    <td class="obooking_orders_filter_statuses_dg_list_status_name" style="cursor: pointer" onclick="obooking_orders.toggle_status2filter(<?=$status->status_id?>)"><?=$status->status_name?></td>
                </tr>
            <?}?>
        </table>
        <?php
        return ob_get_clean();
    }
    private function print_courses_dg() {
        ob_start();
        $courses_stm=$this->obooking->get_courses("course_id,course_name");
        ?>
        <div class="input-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="obooking_orders_filter_courses_dg_row_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_orders.filter_courses_list_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"><span class="icon-search" onclick="obooking_orders.filter_courses_list_filter()"></span></button>
            </span>
        </div>

        <table class="table table-condensed table-hover" id="obooking_orders_filter_courses_dg_list">
            <?php
            while($course=$courses_stm->fetch(PDO::FETCH_OBJ)) {
                $course->course_id=(int)$course->course_id;
                $is_filtered=$this->course_is_filtered($course->course_id);?>
                <tr
                        id="obooking_orders_filter_courses_dg_row_<?=$course->course_id?>"
                        class="<?=$is_filtered?'bg-success':''?>"
                        data-course_id="<?=$course->course_id?>"
                        data-is_filtered="<?=$is_filtered?>"
                >
                    <td style="cursor: pointer" onclick="obooking_orders.toggle_course2filter(<?=$course->course_id?>)">#<?=$course->course_id?></td>
                    <td class="obooking_orders_filter_courses_dg_list_course_name" style="cursor: pointer" onclick="obooking_orders.toggle_course2filter(<?=$course->course_id?>)"><?=$course->course_name?></td>
                </tr>
            <?}?>
        </table>
        <?php
        return ob_get_clean();
    }
    private function print_sources_dg() {
        ob_start();
        $sources_stm=$this->obooking->get_order_sources("source_id,source_name");
        ?>
        <div class="input-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="obooking_orders_filter_sources_dg_row_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_orders.filter_sources_list_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"><span class="icon-search" onclick="obooking_orders.filter_sources_list_filter()"></span></button>
            </span>
        </div>

        <table class="table table-condensed table-hover" id="obooking_orders_filter_sources_dg_list">
            <?php
            while($source=$sources_stm->fetch(PDO::FETCH_OBJ)) {
                $source->source_id=(int)$source->source_id;
                $is_filtered=$this->source_is_filtered($source->source_id);?>
                <tr
                        id="obooking_orders_filter_sources_dg_row_<?=$source->source_id?>"
                        class="<?=$is_filtered?'bg-success':''?>"
                        data-source_id="<?=$source->source_id?>"
                        data-is_filtered="<?=$is_filtered?>"
                >
                    <td style="cursor: pointer" onclick="obooking_orders.toggle_source2filter(<?=$source->source_id?>)">#<?=$source->source_id?></td>
                    <td class="obooking_orders_filter_sources_dg_list_source_name" style="cursor: pointer" onclick="obooking_orders.toggle_source2filter(<?=$source->source_id?>)"><?=$source->source_name?></td>
                </tr>
            <?}?>
        </table>
        <?php
        return ob_get_clean();
    }
    private function print_how_did_find_outs_dg() {
        ob_start();
        $how_did_find_outs_stm=$this->obooking->get_order_how_did_find_outs("how_did_find_out_id,how_did_find_out_name");
        ?>
        <div class="input-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="obooking_orders_filter_how_did_find_outs_dg_row_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_orders.filter_how_did_find_outs_list_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"><span class="icon-search" onclick="obooking_orders.filter_how_did_find_outs_list_filter()"></span></button>
            </span>
        </div>

        <table class="table table-condensed table-hover" id="obooking_orders_filter_how_did_find_outs_dg_list">
            <?php
            while($how_did_find_out=$how_did_find_outs_stm->fetch(PDO::FETCH_OBJ)) {
                $how_did_find_out->how_did_find_out_id=(int)$how_did_find_out->how_did_find_out_id;
                $is_filtered=$this->how_did_find_out_is_filtered($how_did_find_out->how_did_find_out_id);?>
                <tr
                        id="obooking_orders_filter_how_did_find_outs_dg_row_<?=$how_did_find_out->how_did_find_out_id?>"
                        class="<?=$is_filtered?'bg-success':''?>"
                        data-how_did_find_out_id="<?=$how_did_find_out->how_did_find_out_id?>"
                        data-is_filtered="<?=$is_filtered?>"
                >
                    <td style="cursor: pointer" onclick="obooking_orders.toggle_how_did_find_out2filter(<?=$how_did_find_out->how_did_find_out_id?>)">#<?=$how_did_find_out->how_did_find_out_id?></td>
                    <td class="obooking_orders_filter_how_did_find_outs_dg_list_how_did_find_out_name" style="cursor: pointer" onclick="obooking_orders.toggle_how_did_find_out2filter(<?=$how_did_find_out->how_did_find_out_id?>)"><?=$how_did_find_out->how_did_find_out_name?></td>
                </tr>
            <?}?>
        </table>
        <?php
        return ob_get_clean();
    }
    private function print_managers_dg() {
        ob_start();
        $managers_stm=$this->obooking->get_managers("manager_id,manager_name,manager_lastname,manager_phone,manager_email");
        ?>
        <div class="input-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="obooking_orders_filter_managers_dg_row_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_orders.filter_managers_list_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"><span class="icon-search" onclick="obooking_orders.filter_managers_list_filter()"></span></button>
            </span>
        </div>

        <table class="table table-condensed table-hover" id="obooking_orders_filter_managers_dg_list">
            <?php
            while($manager=$managers_stm->fetch(PDO::FETCH_OBJ)) {
                $manager->manager_id=(int)$manager->manager_id;
                $is_filtered=$this->manager_is_filtered($manager->manager_id);?>
                <tr
                        id="obooking_orders_filter_managers_dg_row_<?=$manager->manager_id?>"
                        class="<?=$is_filtered?'bg-success':''?>"
                        data-manager_id="<?=$manager->manager_id?>"
                        data-is_filtered="<?=$is_filtered?>"
                >
                    <td style="cursor: pointer" onclick="obooking_orders.toggle_manager2filter(<?=$manager->manager_id?>)">#<?=$manager->manager_id?></td>
                    <td class="obooking_orders_filter_managers_dg_list_manager_name" style="cursor: pointer" onclick="obooking_orders.toggle_manager2filter(<?=$manager->manager_id?>)"><?=$manager->manager_name?></td>
                    <td class="obooking_orders_filter_managers_dg_list_manager_lastname" style="cursor: pointer" onclick="obooking_orders.toggle_manager2filter(<?=$manager->manager_id?>)"><?=$manager->manager_lastname?></td>
                    <td class="obooking_orders_filter_managers_dg_list_manager_phone" style="cursor: pointer" onclick="obooking_orders.toggle_manager2filter(<?=$manager->manager_id?>)"><?=$manager->manager_phone?></td>
                    <td class="obooking_orders_filter_managers_dg_list_manager_email" style="cursor: pointer" onclick="obooking_orders.toggle_manager2filter(<?=$manager->manager_id?>)"><?=$manager->manager_email?></td>
                </tr>
            <?}?>
        </table>
        <?php
        return ob_get_clean();
    }

    public function get_filtered_offices() {
        $this->check_ses_var();
        return $_SESSION["obooking"]["filter"]["offices"];
    }
    public function get_filtered_timestamps($point) {
        $this->check_ses_var();
        return $_SESSION["obooking"]["filter"]["timestamps"][$point];
    }
    public function get_filtered_next_contact_dates($point) {
        $this->check_ses_var();
        return $_SESSION["obooking"]["filter"]["next_contact_dates"][$point];
    }
    public function get_filtered_trial_dates($point) {
        $this->check_ses_var();
        return $_SESSION["obooking"]["filter"]["trial_dates"][$point];
    }
    public function get_filtered_statuses() {
        $this->check_ses_var();
        return $_SESSION["obooking"]["filter"]["statuses"];
    }
    public function get_filtered_courses() {
        $this->check_ses_var();
        return $_SESSION["obooking"]["filter"]["courses"];
    }
    public function get_filtered_sources() {
        $this->check_ses_var();
        return $_SESSION["obooking"]["filter"]["sources"];
    }
    public function get_filtered_how_did_find_outs() {
        $this->check_ses_var();
        return $_SESSION["obooking"]["filter"]["how_did_find_outs"];
    }
    public function get_filtered_managers() {
        $this->check_ses_var();
        return $_SESSION["obooking"]["filter"]["managers"];
    }

    public function get_sort_order() {
        $this->check_ses_var();
        return $_SESSION["obooking"]["sort"]["order"];
    }
    public function field_is_sorted($field) {
        if(isset($_SESSION["obooking"]["sort"]["fields"][$field])) {
            return $_SESSION["obooking"]["sort"]["fields"][$field];
        }

        return false;
    }

    private function reduce_sort_order_counter_for_next_fields($order) {
        $found=false;
        $initial_order=$order;

        while(isset($_SESSION["obooking"]["sort"]["order"][$order+1])){
            $found=true;
            $field=$_SESSION["obooking"]["sort"]["order"][$order+1]["field"];
            $_SESSION["obooking"]["sort"]["fields"][$field]["order"]--;
            $_SESSION["obooking"]["sort"]["order"][$order]=$_SESSION["obooking"]["sort"]["order"][$order+1];
            unset($_SESSION["obooking"]["sort"]["order"][$order+1]);
            $order++;
        }

        if(!$found) {
            unset($_SESSION["obooking"]["sort"]["order"][$initial_order]);
        }
    }
    private function setup_sort_order($field) {
        $this->check_ses_var();
        if(!isset($_SESSION["obooking"]["sort"]["fields"][$field])) {
            $order=count($_SESSION["obooking"]["sort"]["order"]);
            $_SESSION["obooking"]["sort"]["fields"][$field]=array(
                    "direction"=>"ASC",
                    "order"=>$order
            );
            $_SESSION["obooking"]["sort"]["order"][$order]=array(
                "field"=>$field,
                "direction"=>$_SESSION["obooking"]["sort"]["fields"][$field]["direction"]
            );
        }
        else if($_SESSION["obooking"]["sort"]["fields"][$field]["direction"]==="ASC") {
            $order=$_SESSION["obooking"]["sort"]["fields"][$field]["order"];
            $_SESSION["obooking"]["sort"]["fields"][$field]["direction"]=$_SESSION["obooking"]["sort"]["order"][$order]["direction"]="DESC";
        }
        else {
            $order=$_SESSION["obooking"]["sort"]["fields"][$field]["order"];
            $this->reduce_sort_order_counter_for_next_fields($order);
            unset($_SESSION["obooking"]["sort"]["fields"][$field]);
        }
    }

    public function prepare_filters() {
        if(isset($_POST["reset_filter"])) {
            unset($_SESSION["obooking"]["filter"]);
            print json_encode(array(
                'status'=>'done'
            ));
            exit;
        }
        if(isset($_POST['sort'])) {
            if(!isset($_POST['field'])) {
                print json_encode(array(
                    'status'=>'error',
                    'msg'=>'wrong data 1581893725'
                ));
                exit;
            }

            $field=$_POST["field"];
            if(
                    $field!=="timestamp"&&
                    $field!=="office_name"&&
                    $field!=="next_contact_date"&&
                    $field!=="trial_date"&&
                    $field!=="client_name"&&
                    $field!=="status_name"&&
                    $field!=="course_name"&&
                    $field!=="manager_name"&&
                    $field!=="source_name"&&
                    $field!=="how_did_find_out_name"
            ) {
                print json_encode(array(
                    'status'=>'error',
                    'msg'=>'wrong data 1581894181'
                ));
                exit;
            }

            $this->setup_sort_order($field);

            print json_encode(array(
                'status'=>'done'
            ));
            exit;
        }

        if (isset($_POST["data"])) {
            $data=$_POST["data"];
            if($data==="toggle office") {
                if(!isset($_POST['office_id'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'wrong data 1581693182'
                    ));
                    exit;
                }
                $office_id=(int)$_POST['office_id'];
                $this->toggle_office_filter($office_id);
                print json_encode(array(
                    'status'=>'done',
                    'office_id'=>$office_id,
                    'is_filtered'=>$_POST['is_filtered']
                ));
                exit;
            }
            if($data==="toggle timestamp") {
                if(!isset($_POST['start_timestamp'],$_POST['end_timestamp'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'wrong data 1581916755'
                    ));
                    exit;
                }
                if($_POST['start_timestamp']!=='0'&&!uString::isDate($_POST['start_timestamp'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'start date is wrong'
                    ));
                    exit;
                }
                if($_POST['end_timestamp']!=='0'&&!uString::isDate($_POST['end_timestamp'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'end date is wrong'
                    ));
                    exit;
                }
                $this->toggle_timestamp_filter($_POST['start_timestamp'],$_POST['end_timestamp']);
                print json_encode(array(
                    'status'=>'done',
                    'start_timestamp'=>$_POST['start_timestamp'],
                    'end_timestamp'=>$_POST['end_timestamp']
                ));
                exit;
            }
            if($data==="toggle trial_date") {
                if(!isset($_POST['start_trial_date'],$_POST['end_trial_date'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'wrong data 1581916755'
                    ));
                    exit;
                }
                if($_POST['start_trial_date']!=='0'&&!uString::isDate($_POST['start_trial_date'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'start date is wrong'
                    ));
                    exit;
                }
                if($_POST['end_trial_date']!=='0'&&!uString::isDate($_POST['end_trial_date'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'end date is wrong'
                    ));
                    exit;
                }
                $this->toggle_trial_date_filter($_POST['start_trial_date'],$_POST['end_trial_date']);
                print json_encode(array(
                    'status'=>'done',
                    'start_trial_date'=>$_POST['start_trial_date'],
                    'end_trial_date'=>$_POST['end_trial_date']
                ));
                exit;
            }
            if($data==="toggle next_contact_date") {
                if(!isset($_POST['start_next_contact_date'],$_POST['end_next_contact_date'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'wrong data 15819167551'
                    ));
                    exit;
                }
                if($_POST['start_next_contact_date']!=='0'&&!uString::isDate($_POST['start_next_contact_date'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'start next_contact_date is wrong'
                    ));
                    exit;
                }
                if($_POST['end_next_contact_date']!=='0'&&!uString::isDate($_POST['end_next_contact_date'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'end next_contact_date is wrong'
                    ));
                    exit;
                }
                $this->toggle_next_contact_date_filter($_POST['start_next_contact_date'],$_POST['end_next_contact_date']);
                print json_encode(array(
                    'status'=>'done',
                    'start_next_contact_date'=>$_POST['start_next_contact_date'],
                    'end_next_contact_date'=>$_POST['end_next_contact_date']
                ));
                exit;
            }
            if($data==="toggle status") {
                if(!isset($_POST['status_id'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'wrong data 1581693182'
                    ));
                    exit;
                }
                $status_id=(int)$_POST['status_id'];
                $this->toggle_status_filter($status_id);
                print json_encode(array(
                    'status'=>'done',
                    'status_id'=>$status_id,
                    'is_filtered'=>$_POST['is_filtered']
                ));
                exit;
            }
            if($data==="toggle course") {
                if(!isset($_POST['course_id'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'wrong data 1581693182'
                    ));
                    exit;
                }
                $course_id=(int)$_POST['course_id'];
                $this->toggle_course_filter($course_id);
                print json_encode(array(
                    'status'=>'done',
                    'course_id'=>$course_id,
                    'is_filtered'=>$_POST['is_filtered']
                ));
                exit;
            }
            if($data==="toggle source") {
                if(!isset($_POST['source_id'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'wrong data 1581693182'
                    ));
                    exit;
                }
                $source_id=(int)$_POST['source_id'];
                $this->toggle_source_filter($source_id);
                print json_encode(array(
                    'status'=>'done',
                    'source_id'=>$source_id,
                    'is_filtered'=>$_POST['is_filtered']
                ));
                exit;
            }
            if($data==="toggle how_did_find_out") {
                if(!isset($_POST['how_did_find_out_id'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'wrong data 1581693182'
                    ));
                    exit;
                }
                $how_did_find_out_id=(int)$_POST['how_did_find_out_id'];
                $this->toggle_how_did_find_out_filter($how_did_find_out_id);
                print json_encode(array(
                    'status'=>'done',
                    'how_did_find_out_id'=>$how_did_find_out_id,
                    'is_filtered'=>$_POST['is_filtered']
                ));
                exit;
            }
            if($data==="toggle manager") {
                if(!isset($_POST['manager_id'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'wrong data 1581693182'
                    ));
                    exit;
                }
                $manager_id=(int)$_POST['manager_id'];
                $this->toggle_manager_filter($manager_id);
                print json_encode(array(
                    'status'=>'done',
                    'manager_id'=>$manager_id,
                    'is_filtered'=>$_POST['is_filtered']
                ));
                exit;
            }
//            else {
                print json_encode(array(
                    'status'=>'error',
                    'msg'=>'wrong data'
                ));
                exit;
//            }
        }

        $offices_dg_content=$this->print_offices_dg();
        $timestamps_dg_content=$this->print_timestamps_dg();
        $trial_dates_dg_content=$this->print_trial_dates_dg();
        $next_contact_dates_dg_content=$this->print_next_contact_dates_dg();
        $statuses_dg_content=$this->print_statuses_dg();
        $courses_dg_content=$this->print_courses_dg();
        $sources_dg_content=$this->print_sources_dg();
        $how_did_find_outs_dg_content=$this->print_how_did_find_outs_dg();
        $managers_dg_content=$this->print_managers_dg();
        print json_encode(array(
            'status'=>'done',
            'offices_dg_content'=>$offices_dg_content,
            'timestamps_dg_content'=>$timestamps_dg_content,
            'trial_dates_dg_content'=>$trial_dates_dg_content,
            'next_contact_dates_dg_content'=>$next_contact_dates_dg_content,
            'statuses_dg_content'=>$statuses_dg_content,
            'courses_dg_content'=>$courses_dg_content,
            'sources_dg_content'=>$sources_dg_content,
            'how_did_find_outs_dg_content'=>$how_did_find_outs_dg_content,
            'managers_dg_content'=>$managers_dg_content
        ));
        exit;
    }

    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print 'forbidden';
            exit;
        }
        $obooking=new common($uCore);
        $user_id=(int)$uSes->get_val('user_id');
        $is_admin=$obooking->is_admin($user_id);

        if(!$is_admin) {
            print 'forbidden';
            exit;
        }

        $this->obooking=new common($uCore);
    }
}
if($this->mod==='obooking'&&$this->page_name==='orders_get_filter_settings_bg') {
    $obooking=new orders_get_filter_settings_bg($this);
    $obooking->prepare_filters();
}

<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class edit_order_dg_loader_bg {
    /**
     * @var common
     */
    public $obooking;
    /**
     * @var int
     */
    public $edit_mode;
    /**
     * @var int
     */
    public $order_id;
    public $order_info;
    /**
     * @var uFunc
     */
    private $uFunc;

    public function get_courses() {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT
            courses.course_id,
            course_name,
            order_id
            FROM 
            courses
            JOIN
            order_courses oc on courses.course_id = oc.course_id AND
            order_id=:order_id
            WHERE
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':order_id', $this->order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1589067248'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_managers() {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT
            managers.manager_id,
            manager_name,
            manager_lastname,
            order_id
            FROM 
            managers
            JOIN
            order_managers oc on managers.manager_id = oc.manager_id AND
            order_id=:order_id
            WHERE
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':order_id', $this->order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1589067244'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_order_sources() {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            source_id,
            source_name
            FROM 
            order_sources 
            WHERE 
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1589067258'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_order_how_did_find_outs() {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            how_did_find_out_id,
            how_did_find_out_name
            FROM 
            order_how_did_find_outs 
            WHERE 
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1589067236'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_order_statuses() {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            status_id,
            status_name
            FROM 
            order_statuses 
            WHERE 
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1580859718'/*.$e->getMessage()*/);}
        return 0;
    }

    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print 'forbidden';
            exit;
        }
        $this->obooking=new common($uCore);
        $user_id=(int)$uSes->get_val('user_id');
        $is_admin=$this->obooking->is_admin($user_id);

        if(!$is_admin) {
            print 'forbidden';
            exit;
        }

        $this->uFunc=new uFunc($uCore);

        $this->edit_mode=0;
        if(isset($_POST["order_id"])) {
            $this->order_id=(int)$_POST["order_id"];

            $this->order_info=$this->obooking->order_id2data($this->order_id,'office_id,
            trial_date,
            next_contact_date,
            client_name,
            phone,
            email,
            status_id,
            manager_id,
            comment,
            source_id, 
            how_did_find_out_id,
            client_id
            ');

            if($this->order_info) {
                $this->edit_mode=1;
                $this->order_info->office_id=(int)$this->order_info->office_id;
                $this->order_info->trial_date=(int)$this->order_info->trial_date;
                $this->order_info->next_contact_date=(int)$this->order_info->next_contact_date;
                $this->order_info->status_id=(int)$this->order_info->status_id;
                $this->order_info->manager_id=(int)$this->order_info->manager_id;
                $this->order_info->source_id=(int)$this->order_info->source_id;
                $this->order_info->how_did_find_out_id=(int)$this->order_info->how_did_find_out_id;
                $this->order_info->client_id=(int)$this->order_info->client_id;
            }
        }
        else {
            $this->order_id = $this->obooking->register_new_temp_order();
        }
    }
}
$obooking=new edit_order_dg_loader_bg($this);?>

<input type="hidden" id="obooking_edit_order_dg_client_id" value="<?=$obooking->order_info->client_id?>">

<div class="container-fluid">
    <input type="hidden" id="obooking_edit_order_dg_order_id" value="<?=$obooking->order_id?>">
    <div class="form-group">
        <label for="obooking_edit_order_dg_office_id">Филиал</label>
        <select id="obooking_edit_order_dg_office_id" class="form-control">
            <option value="0">Не выбран</option>
            <?$offices_stm=$obooking->obooking->get_offices("office_id,office_name",site_id);
            while($office=$offices_stm->fetch(PDO::FETCH_OBJ)) {
                $office->office_id=(int)$office->office_id?>
               <option value="<?=$office->office_id?>" <?if($obooking->edit_mode && $obooking->order_info->office_id === $office->office_id) {
                   print " selected ";
               } ?>><?=$office->office_name?></option>
            <?}?>
        </select>
    </div>

    <div class="form-group" id="obooking_edit_order_dg_trial_date_form_group">
        <label for="obooking_edit_order_dg_trial_date">Дата пробного занятия</label>
        <input id="obooking_edit_order_dg_trial_date" type="text" class="form-control" value="<?php

        if ($obooking->edit_mode && $obooking->order_info->trial_date) {
            print date('d.m.Y', $obooking->order_info->trial_date);
        }

        ?>">
        <div id="obooking_edit_order_dg_trial_date_help_block" class="help-block">В формате дд.мм.гггг, например 30.01.2020</div>
    </div>

    <div class="form-group" id="obooking_edit_order_dg_next_contact_date_form_group">
        <label for="obooking_edit_order_dg_next_contact_date">Дата следующего контакта</label>
        <input id="obooking_edit_order_dg_next_contact_date" type="text" class="form-control" value="<?php

        if ($obooking->edit_mode && $obooking->order_info->next_contact_date) {
            print date('d.m.Y', $obooking->order_info->next_contact_date);
        }

        ?>">
        <div id="obooking_edit_order_dg_next_contact_date_help_block" class="help-block">В формате дд.мм.гггг, например 30.01.2020</div>
    </div>

    <?if($obooking->order_info&&$obooking->order_info->client_id) {?>
        <div class="form-group">
            <label for="obooking_edit_order_dg_client_name">Ученик</label>
            <div>
                <a title="Открыть карточку ученика" href="/obooking/clients/<?=$obooking->order_info->client_id?>" target="_blank"><span class="icon-users"></span></a> <?=$obooking->order_info->client_name?><br>
                <?php
                if(uString::isPhone($obooking->order_info->phone)) {
                    print '<a title="Позвонить" href="tel:'.$obooking->order_info->phone.'"><span class="icon-phone-1"></span></a> ';
                }
                print $obooking->order_info->phone;
                print '<br>';

                if(uString::isEmail($obooking->order_info->email)) {
                    print '<a title="Отправить email" href="mailto:'.$obooking->order_info->email.'"><span class="icon-mail-alt"></span></a> ';
                }

                print $obooking->order_info->email;
                print '<br>';
                ?>
            </div>
        </div>
    <?}
    else {?>
    <div class="form-group" id="obooking_edit_order_dg_client_name_form_group">
        <label for="obooking_edit_order_dg_client_name">ФИО ученика</label>
        <input id="obooking_edit_order_dg_client_name" type="text" class="form-control"  value="<?php

        if ($obooking->edit_mode) {
            print $obooking->order_info->client_name;
        }

        ?>">
        <div id="obooking_edit_order_dg_client_name_help_block" class="help-block"></div>
    </div>

    <div class="form-group" id="obooking_edit_order_dg_phone_form_group">
        <label for="obooking_edit_order_dg_phone">Телефон</label>
        <input id="obooking_edit_order_dg_phone" type="text" class="form-control" value="<?php

        if ($obooking->edit_mode) {
            print $obooking->order_info->phone;
        }

        ?>">
        <div id="obooking_edit_order_dg_phone_help_block" class="help-block"></div>
    </div>

    <div class="form-group" id="obooking_edit_order_dg_email_form_group">
        <label for="obooking_edit_order_dg_email">Email</label>
        <input id="obooking_edit_order_dg_email" type="text" class="form-control" value="<?php

        if ($obooking->edit_mode) {
            print $obooking->order_info->email;
        }

        ?>">
        <div id="obooking_edit_order_dg_email_help_block" class="help-block"></div>
    </div>
        <div class="bs-callout bs-callout-primary"><p><b>Если клиент с таким телефоном или Email существует</b>:<br>
                Заявка будет прикреплена к нему, а Email, Телефон и ФИО будут взяты с карточки клиента</p>
            <p><b>Если клиента с таким Email или телефоном не существует</b>:<br>
                Будет создан новый клиент
            </p>
        </div>
    <?}?>

    <div class="form-group">
        <label for="obooking_edit_order_dg_order_status" onclick="obooking_inline_edit.edit_order_statuses_list_init(1)">Статус <span class="icon-pencil"></span> </label>
        <?$order_statusesStm=$obooking->get_order_statuses();?>
        <select id="obooking_edit_order_dg_order_status" class="form-control">
            <option id="obooking_edit_order_dg_order_status_option_0" value="0" <?if($obooking->edit_mode && $obooking->order_info->status_id === 0) {
                print " selected ";
            } ?>>Новая</option><?php
            while($order_status=$order_statusesStm->fetch(PDO::FETCH_OBJ)) {
                $order_status->status_id=(int)$order_status->status_id;
                ?>
                <option id="obooking_edit_order_dg_order_status_option_<?=$order_status->status_id?>" value="<?=$order_status->status_id?>" <?if($obooking->edit_mode && $obooking->order_info->status_id === $order_status->status_id) {
                    print " selected ";
                } ?>><?=$order_status->status_name?></option>
            <?}?>
        </select>
    </div>

    <div class="form-group">
        <label for="obooking_edit_order_dg_course" onclick="obooking_inline_edit.edit_courses_list_init(1)">Направления <span class="icon-pencil"></span></label>
        <table class="table table-hover table-condensed" id="obooking_edit_order_dg_courses_list">
        <?$coursesStm=$obooking->get_courses();
        while($course=$coursesStm->fetch(PDO::FETCH_OBJ)) {
                $course->course_id=(int)$course->course_id;
                ?>
                <tr
                        id="obooking_edit_order_dg_course_<?=$course->course_id?>"
                        data-course_id="<?=$course->course_id?>"
                        data-is_added="1"
                >
                    <td class="obooking_edit_order_dg_courses_list_course_name"><?=$course->course_name?></td>
                    <td><em class="icon-cancel text-danger" title="Убрать направление" style="cursor:pointer;" onclick="obooking_inline_edit.toggle_course2order($(this).parent())"></em></td>
                </tr>
            <?}?>
        </table>
    </div>

    <div class="form-group">
        <label for="obooking_edit_order_dg_manager" onclick="obooking_inline_edit.edit_managers_list_init(1)">Наставники <span class="icon-pencil"></span></label>
        <table class="table table-hover table-condensed" id="obooking_edit_order_dg_managers_list">
            <?$managersStm=$obooking->get_managers();
            while($manager=$managersStm->fetch(PDO::FETCH_OBJ)) {
                $manager->manager_id=(int)$manager->manager_id;
                ?>
                <tr
                        id="obooking_edit_order_dg_manager_<?=$manager->manager_id?>"
                        data-manager_id="<?=$manager->manager_id?>"
                        data-is_added="1"
                >
                    <td class="obooking_edit_order_dg_managers_list_manager_name"><?=$manager->manager_name?> <?=$manager->manager_lastname?> </td>
                    <td><em class="icon-cancel text-danger" title="Убрать менеджера" style="cursor:pointer;" onclick="obooking_inline_edit.toggle_manager2order($(this).parent())"></em></td>
                </tr>
            <?}?>
        </table>
    </div>

    <div class="form-group">
        <label for="obooking_edit_order_dg_order_source" onclick="obooking_inline_edit.edit_order_sources_list_init(1)">Источник <span class="icon-pencil"></span> </label>
        <?$order_sourcesStm=$obooking->get_order_sources();?>
        <select id="obooking_edit_order_dg_order_source" class="form-control">
            <option id="obooking_edit_order_dg_order_source_option_0" value="0" <?if($obooking->edit_mode && $obooking->order_info->source_id === 0) {
                print " selected ";
            } ?>>Не выбран</option>
            <?php
            while($order_source=$order_sourcesStm->fetch(PDO::FETCH_OBJ)) {
                $order_source->source_id=(int)$order_source->source_id;
                ?>
                <option id="obooking_edit_order_dg_order_source_option_<?=$order_source->source_id?>" value="<?=$order_source->source_id?>" <?if($obooking->edit_mode && $obooking->order_info->source_id === $order_source->source_id) {
                    print " selected ";
                } ?>><?=$order_source->source_name?></option>
            <?}?>
        </select>
    </div>

    <div class="form-group">
        <label for="obooking_edit_order_dg_order_how_did_find_out" onclick="obooking_inline_edit.edit_order_how_did_find_outs_list_init(1)">Откуда узнали <span class="icon-pencil"></span> </label>
        <?$order_how_did_find_outsStm=$obooking->get_order_how_did_find_outs();?>
        <select id="obooking_edit_order_dg_order_how_did_find_out" class="form-control">
            <option id="obooking_edit_order_dg_order_how_did_find_out_option_0" value="0" <?if($obooking->edit_mode && $obooking->order_info->how_did_find_out_id === 0) {
                print " selected ";
            } ?>>Не выбрано</option>
            <?php
            while($order_how_did_find_out=$order_how_did_find_outsStm->fetch(PDO::FETCH_OBJ)) {
                $order_how_did_find_out->how_did_find_out_id=(int)$order_how_did_find_out->how_did_find_out_id;
                ?>
                <option id="obooking_edit_order_dg_order_how_did_find_out_option_<?=$order_how_did_find_out->how_did_find_out_id?>" value="<?=$order_how_did_find_out->how_did_find_out_id?>" <?if($obooking->edit_mode && $obooking->order_info->how_did_find_out_id === $order_how_did_find_out->how_did_find_out_id) {
                    print " selected ";
                } ?>><?=$order_how_did_find_out->how_did_find_out_name?></option>
            <?}?>
        </select>
    </div>

    <div class="form-group">
        <label for="obooking_edit_order_dg_comment">Коммент</label>
        <textarea id="obooking_edit_order_dg_comment" class="form-control"> <?if($obooking->edit_mode) {print $obooking->order_info->comment;}?></textarea>
    </div>
</div>

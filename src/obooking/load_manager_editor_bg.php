<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class load_manager_editor_bg {
    private $manager_id;
    private $uFunc;
    private function check_data() {
        if(!isset($_POST["manager_id"])) {
            $this->uFunc->error(10);
        }
        $this->manager_id=(int)$_POST["manager_id"];
    }
    private function get_manager_data($manager_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            manager_name, 
            manager_lastname, 
            manager_phone, 
            manager_email, 
            manager_status, 
            manager_specialization, 
            manager_birthdate, 
            manager_balance, 
            manager_comment, 
            manager_vk_id/*,
            manager_bank_card_number*/
            FROM 
            managers 
            WHERE 
            manager_id=:manager_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':manager_id', $manager_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            $manager_data=$stm->fetch(PDO::FETCH_OBJ);
            $manager_data->manager_id=$manager_id;
            return $manager_data;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return 0;
    }
    private function print_editor($manager_data) {?>
        <input type="hidden" id="obooking_inline_edit_manager_manager_id" value="<?=$manager_data->manager_id?>">


        <div class="row">
            <div class="col-md-4">
                <h4><a href="javascript:void(0)" onclick="obooking_inline_edit.edit_manager_schedule_init()">Открыть график работы</a></h4>
            </div>
            <div class="col-md-4">
                <h4><a href="javascript:void(0)">График занятий</a></h4>
            </div>
            <div class="col-md-4">
                <h4 class="pull-right"><a href="javascript:void(0)">Баланс:</a> 1052 р.</h4>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group" id="obooking_inline_edit_manager_manager_name_form_group">
                    <label for="obooking_inline_edit_manager_manager_name">Имя</label>
                    <input class="form-control" id="obooking_inline_edit_manager_manager_name" type="text" value="<?=htmlspecialchars($manager_data->manager_name)?>">
                    <span class="help-block" id="obooking_inline_edit_manager_manager_name_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_manager_manager_lastname_form_group">
                    <label for="obooking_inline_edit_manager_manager_lastname">Фамилия</label>
                    <input class="form-control" id="obooking_inline_edit_manager_manager_lastname" type="text" value="<?=htmlspecialchars($manager_data->manager_lastname)?>">
                    <span class="help-block" id="obooking_inline_edit_manager_manager_lastname_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_manager_manager_phone_form_group">
                    <label for="obooking_inline_edit_manager_manager_phone">Телефон</label>
                    <input class="form-control" id="obooking_inline_edit_manager_manager_phone" type="tel" value="<?=htmlspecialchars($manager_data->manager_phone)?>">
                    <span class="help-block" id="obooking_inline_edit_manager_manager_phone_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_manager_manager_email_form_group">
                    <label for="obooking_inline_edit_manager_manager_email">E-mail</label>
                    <input class="form-control" id="obooking_inline_edit_manager_manager_email" type="email" value="<?=htmlspecialchars($manager_data->manager_email)?>">
                    <span class="help-block" id="obooking_inline_edit_manager_manager_email_help_block"></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group" id="obooking_inline_edit_manager_manager_status_form_group">
                    <label for="obooking_inline_edit_manager_manager_status">Статус</label>
                    <select class="form-control" id="obooking_inline_edit_manager_manager_status">
                        <?=$manager_data->manager_status=(int)$manager_data->manager_status?>
                        <option value="0" <?=$manager_data->manager_status===0?'selected':''?>>Работает</option>
                        <option value="1" <?=$manager_data->manager_status===1?'selected':''?>>Уволен</option>
                    </select>
                    <span class="help-block" id="obooking_inline_edit_manager_manager_status_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_manager_manager_specialization_form_group">
                    <label for="obooking_inline_edit_manager_manager_specialization">Специализация</label>
                    <input class="form-control" id="obooking_inline_edit_manager_manager_specialization" type="text" value="<?=htmlspecialchars($manager_data->manager_specialization)?>">
                    <span class="help-block" id="obooking_inline_edit_manager_manager_specialization_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_manager_manager_birthdate_form_group">
                    <label for="obooking_inline_edit_manager_manager_birthdate">Дата рождения</label>
                    <input class="form-control" id="obooking_inline_edit_manager_manager_birthdate" type="text" value="<?=(int)$manager_data->manager_birthdate?(date("d.m.Y",$manager_data->manager_birthdate)):/*0*/""?>">
                    <?/*<div id="obooking_inline_edit_manager_manager_birthdate_datepicker" <?=(int)$manager_data->manager_birthdate?('data-date="'.date("d.m.Y",$manager_data->manager_birthdate).'"'):''?>></div>*/?>
                    <span class="text-muted" style="color: gray">В формате дд.мм.гггг, например 01.01.1990</span>
                    <span class="help-block" id="obooking_inline_edit_manager_manager_birthdate_help_block"></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group" id="obooking_inline_edit_manager_manager_comment_form_group">
                    <label for="obooking_inline_edit_manager_manager_comment">Характеристики</label>
                    <textarea class="form-control" id="obooking_inline_edit_manager_manager_comment"><?=htmlspecialchars($manager_data->manager_comment)?></textarea>
                    <span class="help-block" id="obooking_inline_edit_manager_manager_comment_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_manager_manager_vk_id_form_group">
                    <label for="obooking_inline_edit_manager_manager_vk_id">VK</label>
                    <input class="form-control" id="obooking_inline_edit_manager_manager_vk_id" type="text" value="<?=htmlspecialchars($manager_data->manager_vk_id)?>">
                    <span class="help-block" id="obooking_inline_edit_manager_manager_vk_id_help_block"></span>
                </div>
                <!--<div class="form-group" id="obooking_inline_edit_manager_manager_bank_card_number_form_group">
                    <label for="obooking_inline_edit_manager_manager_bank_card_number">Номер банковской карты</label>
                    <input class="form-control" id="obooking_inline_edit_manager_manager_bank_card_number" type="number" value="<?/*=htmlspecialchars($manager_data->manager_bank_card_number)*/?>">
                    <span class="help-block" id="obooking_inline_edit_manager_manager_bank_card_number_help_block"></span>
                </div>-->
            </div>
        </div>
    <?}

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

        $this->uFunc=new uFunc($uCore);

        $this->check_data();
        $manager_data=$this->get_manager_data($this->manager_id);
        if(!$manager_data) {
            die("error");
        }
        $this->print_editor($manager_data);
    }
}
new load_manager_editor_bg($this);

<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once 'processors/uSes.php';
require_once 'processors/classes/uFunc.php';
require_once 'obooking/classes/common.php';

class load_administrator_editor_bg {
    private $administrator_id;
    private $uFunc;
    private function check_data() {
        if(!isset($_POST['administrator_id'])) {
            $this->uFunc->error(1587009958);
        }
        $this->administrator_id=(int)$_POST['administrator_id'];
    }
    private function get_administrator_data($administrator_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('SELECT 
            administrator_name, 
            administrator_lastname, 
            administrator_phone, 
            administrator_email, 
            administrator_status, 
            administrator_birthday, 
            administrator_balance, 
            administrator_comment, 
            administrator_vk_id
            FROM 
            administrators 
            WHERE 
            administrator_id=:administrator_id AND
            site_id=:site_id
            ');
            $site_id=site_id;
            $stm->bindParam(':administrator_id', $administrator_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            $administrator_data=$stm->fetch(PDO::FETCH_OBJ);
            $administrator_data->administrator_id=$administrator_id;
            return $administrator_data;
        }
        catch(PDOException $e) {$this->uFunc->error('1587009954'.$e->getMessage(),1);}
        return 0;
    }
    private function print_editor($administrator_data) {?>
        <input type="hidden" id="obooking_inline_edit_administrator_administrator_id" value="<?=$administrator_data->administrator_id?>">


        <div class="row">
            <div class="col-md-4">
                <h4 class="pull-right"><a href="javascript:void(0)">Баланс:</a> 0 р.</h4>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group" id="obooking_inline_edit_administrator_administrator_name_form_group">
                    <label for="obooking_inline_edit_administrator_administrator_name">Имя</label>
                    <input class="form-control" id="obooking_inline_edit_administrator_administrator_name" type="text" value="<?=htmlspecialchars($administrator_data->administrator_name)?>">
                    <span class="help-block" id="obooking_inline_edit_administrator_administrator_name_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_administrator_administrator_lastname_form_group">
                    <label for="obooking_inline_edit_administrator_administrator_lastname">Фамилия</label>
                    <input class="form-control" id="obooking_inline_edit_administrator_administrator_lastname" type="text" value="<?=htmlspecialchars($administrator_data->administrator_lastname)?>">
                    <span class="help-block" id="obooking_inline_edit_administrator_administrator_lastname_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_administrator_administrator_phone_form_group">
                    <label for="obooking_inline_edit_administrator_administrator_phone">Телефон</label>
                    <input class="form-control" id="obooking_inline_edit_administrator_administrator_phone" type="tel" value="<?=htmlspecialchars($administrator_data->administrator_phone)?>">
                    <span class="help-block" id="obooking_inline_edit_administrator_administrator_phone_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_administrator_administrator_email_form_group">
                    <label for="obooking_inline_edit_administrator_administrator_email">E-mail</label>
                    <input class="form-control" id="obooking_inline_edit_administrator_administrator_email" type="email" value="<?=htmlspecialchars($administrator_data->administrator_email)?>">
                    <span class="help-block" id="obooking_inline_edit_administrator_administrator_email_help_block"></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group" id="obooking_inline_edit_administrator_administrator_status_form_group">
                    <label for="obooking_inline_edit_administrator_administrator_status">Статус</label>
                    <select class="form-control" id="obooking_inline_edit_administrator_administrator_status">
                        <?=$administrator_data->administrator_status=(int)$administrator_data->administrator_status?>
                        <option value="0" <?=$administrator_data->administrator_status===0?'selected':''?>>Работает</option>
                        <option value="1" <?=$administrator_data->administrator_status===1?'selected':''?>>Уволен</option>
                    </select>
                    <span class="help-block" id="obooking_inline_edit_administrator_administrator_status_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_administrator_administrator_birthday_form_group">
                    <label for="obooking_inline_edit_administrator_administrator_birthday">Дата рождения</label>
                    <input class="form-control" id="obooking_inline_edit_administrator_administrator_birthday" type="text" value="<?=(int)$administrator_data->administrator_birthday?(date('d.m.Y',$administrator_data->administrator_birthday)):/*0*/ '' ?>">
                    <span class="text-muted" style="color: gray">В формате дд.мм.гггг, например 01.01.1990</span>
                    <span class="help-block" id="obooking_inline_edit_administrator_administrator_birthday_help_block"></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group" id="obooking_inline_edit_administrator_administrator_comment_form_group">
                    <label for="obooking_inline_edit_administrator_administrator_comment">Характеристики</label>
                    <textarea class="form-control" id="obooking_inline_edit_administrator_administrator_comment"><?=htmlspecialchars($administrator_data->administrator_comment)?></textarea>
                    <span class="help-block" id="obooking_inline_edit_administrator_administrator_comment_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_administrator_administrator_vk_id_form_group">
                    <label for="obooking_inline_edit_administrator_administrator_vk_id">VK</label>
                    <input class="form-control" id="obooking_inline_edit_administrator_administrator_vk_id" type="text" value="<?=htmlspecialchars($administrator_data->administrator_vk_id)?>">
                    <span class="help-block" id="obooking_inline_edit_administrator_administrator_vk_id_help_block"></span>
                </div>
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
        $administrator_data=$this->get_administrator_data($this->administrator_id);
        if(!$administrator_data) {
            die('error');
        }
        $this->print_editor($administrator_data);
    }
}
new load_administrator_editor_bg($this);

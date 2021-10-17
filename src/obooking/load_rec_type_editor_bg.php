<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class load_rec_type_editor_bg {
    private $rec_type_id;
    private $uFunc;

    private function check_data() {
        if(!isset($_POST["rec_type_id"])) {
            $this->uFunc->error(10);
        }
        $this->rec_type_id=(int)$_POST["rec_type_id"];
    }
    private function get_rec_type_data($rec_type_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            rec_type_name,
            rec_type_price,
            rec_type_price_without_card,
            rec_type_duration
            FROM 
            rec_types
            WHERE 
            rec_type_id=:rec_type_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':rec_type_id', $rec_type_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            if(!$rec_type_data=$stm->fetch(PDO::FETCH_OBJ)) {
                return 0;
            }
            $rec_type_data->rec_type_id=(int)$rec_type_id;
            return $rec_type_data;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return 0;
    }
    private function print_editor($rec_type_data) {

        $duration_minutes = $rec_type_data->rec_type_duration / 60;
        for ($hours = 0; $duration_minutes > 60; $hours++) {
            $duration_minutes -= 60;
        }
        $rec_type_duration=$hours . ":" . (int)$duration_minutes;?>
        <input type="hidden" id="obooking_inline_edit_rec_type_id" value="<?=$rec_type_data->rec_type_id?>">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group" id="obooking_inline_edit_rec_type_name_form_group">
                    <label for="obooking_inline_edit_rec_type_name">Название</label>
                    <input class="form-control" id="obooking_inline_edit_rec_type_name" type="text" value="<?=htmlspecialchars($rec_type_data->rec_type_name)?>">
                    <span class="help-block" id="obooking_inline_edit_rec_type_name_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_rec_type_duration_form_group">
                    <label for="obooking_inline_edit_rec_type_duration">Длительность</label>
                    <input class="form-control" id="obooking_inline_edit_rec_type_duration" type="text" value="<?=htmlspecialchars($rec_type_duration)?>">
                    <span class="help-block" id="obooking_inline_edit_rec_type_duration_help_block"></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group" id="obooking_inline_edit_rec_type_price_form_group">
                    <label for="obooking_inline_edit_rec_type_price">Стомиость с клубной картой</label>
                    <input class="form-control" id="obooking_inline_edit_rec_type_price" type="text" value="<?=htmlspecialchars($rec_type_data->rec_type_price)?>">
                    <span class="help-block" id="obooking_inline_edit_rec_type_price_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_rec_type_price_without_card_card_form_group">
                    <label for="obooking_inline_edit_rec_type_price_without_card">Стомиость без карты</label>
                    <input class="form-control" id="obooking_inline_edit_rec_type_price_without_card" type="text" value="<?=htmlspecialchars($rec_type_data->rec_type_price_without_card)?>">
                    <span class="help-block" id="obooking_inline_edit_rec_type_price_without_card_card_help_block"></span>
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
        if(!$rec_type_data=$this->get_rec_type_data($this->rec_type_id)) {
            die("error");
        }

        $this->print_editor($rec_type_data);
    }
}
new load_rec_type_editor_bg($this);

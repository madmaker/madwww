<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class load_office_editor_bg {
    private $office_id;
    private $uFunc;
    private function check_data() {
        if(!isset($_POST["office_id"])) {
            $this->uFunc->error(10);
        }
        $this->office_id=(int)$_POST["office_id"];
    }
    private function get_office_data($office_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            office_name
            FROM 
            offices
            WHERE 
            office_id=:office_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':office_id', $office_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            if(!$office_data=$stm->fetch(PDO::FETCH_OBJ)) {
                return 0;
            }
            $office_data->office_id=(int)$office_id;
            return $office_data;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return 0;
    }
    private function print_editor($office_data) {?>
        <input type="hidden" id="obooking_inline_edit_office_id" value="<?=$office_data->office_id?>">


        <div class="row">
            <div class="col-md-12">
                <h4><a href="obooking/calendar?office=<?=$office_data->office_id?>" target="_blank">Открыть график занятий</a></h4>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group" id="obooking_inline_edit_office_name_form_group">
                    <label for="obooking_inline_edit_office_name">Название</label>
                    <input class="form-control" id="obooking_inline_edit_office_name" type="text" value="<?=htmlspecialchars($office_data->office_name)?>">
                    <span class="help-block" id="obooking_inline_edit_office_name_help_block"></span>
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
        if(!$office_data=$this->get_office_data($this->office_id)) {
            die("error");
        }

        $this->print_editor($office_data);
    }
}
new load_office_editor_bg($this);

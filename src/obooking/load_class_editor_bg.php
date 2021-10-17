<?php
namespace obooking;
use PDO;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class load_class_editor_bg {
    private $obooking;
    private $class_id;
    private $uFunc;
    private function check_data() {
        if(!isset($_POST["class_id"])) {
            $this->uFunc->error(10);
        }
        $this->class_id=(int)$_POST["class_id"];
    }
    private function print_editor($class_data) {
        $class_data->office_id=(int)$class_data->office_id?>
        <input type="hidden" id="obooking_inline_edit_class_id" value="<?=$class_data->class_id?>">


        <div class="row">
            <div class="col-md-12">
                <h4><a  href="obooking/calendar?office=<?=$class_data->office_id?>" target="_blank">Открыть график занятий</a></h4>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group" id="obooking_inline_edit_class_name_form_group">
                    <label for="obooking_inline_edit_class_name">Название</label>
                    <input class="form-control" id="obooking_inline_edit_class_name" type="text" value="<?=htmlspecialchars($class_data->class_name)?>">
                    <span class="help-block" id="obooking_inline_edit_class_name_help_block"></span>
                </div>
            </div>
            <div class="col-md-6">
                <label for="obooking_inline_edit_office_id" class="control-label">Филиал</label>
                <?$q_offices=$this->obooking->get_offices();?>
                <select id="obooking_inline_edit_office_id" class="form-control">
                    <?php
                    for($i=0;$office[$i]=$q_offices->fetch(PDO::FETCH_OBJ);$i++) {
                        $office[$i]->office_id=(int)$office[$i]->office_id;
                        $office_id2i[$office[$i]->office_id]=$i;
                        ?>
                        <option <?=$class_data->office_id===$office[$i]->office_id?"selected":""?> value="<?=$office[$i]->office_id?>" <?=$office[$i]->office_id===$class_data->office_id?"selected":""?>><?=$office[$i]->office_name?></option>
                    <?}?>
                </select>
            </div>
        </div>
    <?}

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

        $this->check_data();
        if(!$class_data=$this->obooking->get_class_info("class_name,class_id,office_id",$this->class_id)) {
            die("error");
        }

        $this->print_editor($class_data);
    }
}
new load_class_editor_bg($this);

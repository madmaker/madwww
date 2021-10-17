<?php
namespace uCat\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class admin_get_list_of_options_bg {
    public $item_id;
    private $uSes;
    private $uFunc;
    private $uCore;
    private function check_data() {
        if(!isset($_POST['item_id'])) $this->uFunc->error(10);
        $this->item_id=(int)$_POST['item_id'];
    }
    private function get_options() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            option_id,
            option_name 
            FROM 
            variant_options 
            WHERE 
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return 0;
    }
    private function if_option_is_attached($option_id,$item_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            option_id 
            FROM 
            items_options 
            WHERE 
            option_id=:option_id AND
            item_id=:item_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            if($stm->fetch(PDO::FETCH_OBJ)) return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        return 0;
    }
    private function print_options($options_obj) {
        ob_start();?>
        <table class="table table-condensed table-hover" id="uCat_options4attachment_list">
        <?while($option=$options_obj->fetch(PDO::FETCH_OBJ)) {?>
            <tr id="uCat_attach_option2item_tr_"<?=$option->option_id?> style="cursor: pointer">
                <td onclick="uCat_item_admin.option_editor(<?=$option->option_id?>)"><?=$option->option_name?></td>
                <td><?
                    if($this->if_option_is_attached($option->option_id,$this->item_id)) {?>
                        <button type="button" class="btn btn-danger btn-sm" id="uCat_attach_option2item_btn_<?=$option->option_id?>" onclick="uCat_item_admin.attach_option2item(<?=$option->option_id?>,'unattach')"><span class="icon-cancel"></span> Убрать</button>
                    <?}
                    else {?>
                        <button type="button" class="btn btn-success btn-sm" id="uCat_attach_option2item_btn_<?=$option->option_id?>" onclick="uCat_item_admin.attach_option2item(<?=$option->option_id?>,'attach')"><span class="icon-plus"></span> Добавить</button>
                    <?}
                    ?></td>
            </tr>
        <?}?>
        </table>
        <div class="bs-callout bs-callout-primary">
            Неиспользуемые опции удаляются автоматически
        </div>

        <?$cnt=ob_get_contents();
        ob_end_clean();

        return $cnt;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $options_obj=$this->get_options();
        $cnt=$this->print_options($options_obj);

        echo "{
        'status':'done',
        'cnt':'".rawurlencode($cnt)."'
        }";
        exit;
    }
}
new admin_get_list_of_options_bg($this);
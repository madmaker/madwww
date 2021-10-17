<?php
namespace uCat\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uCat\common;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "uCat/classes/common.php";

class admin_item_edit_variant_with_options_show_dg_bg{
    public $var_id;
    public $var_info;
    private $uFunc;
    private $uSes;
    private $uCore;
    public $uCat,$q_variants_types,$item_id;

    private function check_data() {
        if(!isset($_POST['var_id'])) $this->uFunc->error(10);
        $this->var_id=(int)$_POST['var_id'];

        if(!$this->var_info=$this->uCat->var_id2data($this->var_id)) $this->uFunc->error(15);
        $this->item_id=(int)$this->var_info->item_id;
    }
    public function option_id2var_value($option_id,$var_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            value_id 
            FROM 
            variants_options_values 
            WHERE 
            option_id=:option_id AND
            var_id=:var_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return (int)$qr->value_id;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_attached_options() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            variant_options.option_id,
            option_name
            FROM 
            variant_options
            JOIN 
            items_options
            ON
            items_options.option_id=variant_options.option_id AND
            items_options.site_id=variant_options.site_id
            WHERE 
            item_id=:item_id AND
            variant_options.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        return 0;
    }
    public function option_id2values($option_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            value_id,
            `value`
            FROM 
            option_values 
            WHERE 
            option_id=:option_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        return 0;
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);
        
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");
        $this->check_data();

        $this->q_variants_types=$this->uCat->get_variants_types();
    }
}
$uCat=new admin_item_edit_variant_with_options_show_dg_bg($this);

$opts_obj=$uCat->get_attached_options();?>
<div id="uCat_new_vartiant_with_options_form">
    <input type="hidden" id="uCat_new_vartiant_with_options_form_var_id" value="<?=$uCat->var_id?>">
<? /** @noinspection PhpUndefinedMethodInspection */
while($option=$opts_obj->fetch(PDO::FETCH_OBJ)) {
    $var_value=$uCat->option_id2var_value($option->option_id,$uCat->var_id)?>
<div class="form-group" id="uCat_option_form_group_<?=$option->option_id?>">
    <label ><?=$option->option_name?></label>
    <div class="input-group">
        <select data-option_id="<?=$option->option_id?>" class="form-control selectpicker eip_input"><?$vals_obj=$uCat->option_id2values($option->option_id);
            while($value=$vals_obj->fetch(PDO::FETCH_OBJ)) {?>
                <option value="<?=$value->value_id?>" <?=($var_value==(int)$value->value_id?"selected":"")?>><?=$value->value?></option>
            <?}?>
        </select>
        <span class="input-group-btn">
            <button class="btn btn-default uTooltip" title="Редактировать опцию и список значений" type="button" onclick="uCat_item_admin.option_editor(<?=$option->option_id?>)"><span class="icon-pencil"></span></button>
        </span>
    </div><!-- /input-group -->
</div>
<?}?>
</div>
<button id="uCat_attach_new_option_btn" type="button" class="btn btn-primary" onclick="uCat_item_admin.attach_option2item_open_dg()"><span class="icon-plus"></span> Добавить/убрать опцию</button>

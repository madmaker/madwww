<?php
namespace uCat\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class admin_load_option_editor_bg {
    private $editor_content;
    private $option_values_obj;
    private $option_obj;
    private $option_id;
    private $uSes;
    private $uFunc;
    private $uCore;
    private function check_data() {
        if(!isset($_POST['option_id'])) $this->uFunc->error(10);
        $this->option_id=(int)$_POST['option_id'];
    }
    private function get_option_data() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            option_name,
            option_type,
            option_display_style
            FROM 
            variant_options 
            WHERE 
            option_id=:option_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $this->option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return 0;
    }
    private function get_option_values() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            value_id,
            value,
            color
            FROM 
            option_values 
            WHERE
            option_id=:option_id AND 
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $this->option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        return 0;
    }
    private function build_editor() {
        $this->option_obj->option_type=(int)$this->option_obj->option_type;
        $this->option_obj->option_display_style=(int)$this->option_obj->option_display_style;
        ob_start();?>

        <div class="col-md-4">
            <div class="form-group">
                <label for="uCat_option_name_input">Название опции</label>
                <input id="uCat_option_name_input" type="text" class="eip_input" value="<?=addslashes($this->option_obj->option_name)?>" onblur="uCat_item_admin.save_option_name(<?=$this->option_id?>)">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="uCat_option_type_selectbox">Тип опции</label>
                <select id="uCat_option_type_selectbox" class="eip_input" onchange="uCat_item_admin.save_option_type(<?=$this->option_id?>)">
                    <option value="0" <?=$this->option_obj->option_type===0?"selected":""?>>Текст</option>
                    <option value="1" <?=$this->option_obj->option_type===1?"selected":""?>>Цвет</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="uCat_option_display_style_selectbox">Стиль отображения</label>
                <select id="uCat_option_display_style_selectbox" class="eip_input" onchange="uCat_item_admin.save_option_display_style(<?=$this->option_id?>)">
                    <option value="0" <?=$this->option_obj->option_display_style===0?"selected":""?>>Таблицей</option>
                    <option value="1" <?=$this->option_obj->option_display_style===1?"selected":""?>>В строку</option>
                </select>
            </div>
        </div>
        <label>Доступные значения опции</label>
        <table class="table table-condensed table-hover" id="uCat_option_values_list">
            <tr>
                <th>Текст</th>
            <?if($this->option_obj->option_type===1/*color*/) {?>
                <th>Цвет</th>
            <?}?>
            </tr>
            <?while ($value=$this->option_values_obj->fetch(PDO::FETCH_OBJ)) {?>
                <tr id="uCat_option_value_tr_<?=$value->value_id?>" style="cursor: pointer">
                    <td id="uCat_option_value_td_<?=$value->value_id?>" onclick="uCat_item_admin.option_value_editor_init(<?=$value->value_id?>)"><?=$value->value?></td>
                    <?if($this->option_obj->option_type===1/*color*/) {?>
                        <td id="uCat_option_color_td_<?=$value->value_id?>" onclick="uCat_item_admin.option_color_editor_init(<?=$value->value_id?>)" style="background: <?=$value->color?>"><?=$value->color?></td>
                    <?}?>
                </tr>
            <?}?>
        </table>
        <button type="button" id="uCat_option_new_value_btn" class="btn btn-success btm-sm" onclick="uCat_item_admin.add_option_value(<?=$this->option_id?>)"><span class="icon-plus"></span> Добавить значение</button>

        <div class="bs-callout bs-callout-primary">
            Неиспользуемые значения удаляются автоматически
        </div>
    <?
        $this->editor_content=ob_get_contents();
        ob_end_clean();
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();
        if(!$this->option_obj=$this->get_option_data()) $this->uFunc->error(40);
        $this->option_values_obj=$this->get_option_values();
        $this->build_editor();
        echo "{
        'status':'done',
        'editor_content':'".rawurlencode($this->editor_content)."'
        }";
        exit;
    }
}
new admin_load_option_editor_bg($this);
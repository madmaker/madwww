<?php
namespace uCat\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "lib/simple_html_dom.php";
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class admin_item_get_field_values_inline_editor_ajax{
    private $field_id;
    private $fields_ar;
    private $uFunc;
    private $uSes;
    private $uCore,$place_id,$item_id,$items_fields_q_select,$item;
    private function check_data() {
        if(!isset($_POST['field_id'])) $this->uFunc->error(10);
        $this->field_id=(int)$_POST['field_id'];
    }
    private function get_field_values($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            field_".$this->field_id." AS value
            FROM
            u235_items
            WHERE
            field_".$this->field_id."!='' AND
            field_".$this->field_id." IS NOT NULL AND
            site_id=:site_id
            ORDER BY
            field_".$this->field_id." ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        return 0;
    }
    private function print_values($values) {?>
        <table class="table table-condensed table-striped"><?
        while($value=$values->fetch(PDO::FETCH_OBJ)) {
            $value_converted=uString::sql2text($value->value,1);
            $value_converted_trimmed=ltrim($value_converted);
            $indent=mb_strlen($value_converted)-mb_strlen($value_converted_trimmed);
            $value_whitespaces=mb_substr($value_converted,0,$indent);
            ?>
           <tr>
               <td><?=str_replace(" ",'<span class="icon-up-open"></span>',$value_whitespaces).$value_converted_trimmed?></td>
               <td><button onclick="uCat.use_field_value(<?=$this->field_id?>,'<?=rawurlencode($value_converted)?>')">Использовать</button></td>
           </tr>
        <?}
        ?></table>
    <?}
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(25)) die('forbidden');
        
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();
        $values=$this->get_field_values();
        $this->print_values($values);
    }
}
new admin_item_get_field_values_inline_editor_ajax($this);
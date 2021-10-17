<?php
namespace uCat\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class show_item_units_bg {
    private $units_list_ar;
    private $uFunc;
    private $uSes;
    private $item_id;
    private $item_unit_id;
    private $uCore;
    private function check_data() {
        if(!isset($_POST['item_id'])) $this->uFunc->error(10);
        $this->item_id=(int)$_POST['item_id'];
        //get item's unit_id
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            unit_id 
            FROM 
            u235_items 
            WHERE 
            item_id=:item_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(20);
            $this->item_unit_id=(int)$qr->unit_id;
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }

    private function get_units_list() {
        $this->units_list_ar=array();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            unit_id,
            unit_name,
            `default`
            FROM 
            units 
            WHERE 
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            for($i=0; $qr=$stm->fetch(PDO::FETCH_OBJ); $i++) {
                $this->units_list_ar[$i]['unit_id']=$qr->unit_id;
                $this->units_list_ar[$i]['unit_name']=$qr->unit_name;
                $this->units_list_ar[$i]['default']=(int)$qr->default;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
    }
    private function print_units_list() {
        $cnt='<table class="table table-condensed table-hover">';
        $units_count=count($this->units_list_ar);
        for($i=0;$i<$units_count;$i++) {
            $cnt.='<tr ';
            if($this->item_unit_id===(int)$this->units_list_ar[$i]["unit_id"]) $cnt.=' class="bg-primary" ';
                $cnt.='>
                    <td><button class="btn '.(($this->item_unit_id===(int)$this->units_list_ar[$i]["unit_id"])?'btn-primary':'btn-default').'" onclick="uCat_item_admin.item_units_select_unit('.$this->units_list_ar[$i]["unit_id"].')"><span class="icon-ok" title="Использовать"></span> </button></td>
                    <td>'.$this->units_list_ar[$i]["unit_name"].'</td>
                    <td><button class="btn btn-default" onclick="uCat_item_admin.item_units_open_editor('.$this->units_list_ar[$i]["unit_id"].',\''.rawurlencode($this->units_list_ar[$i]["unit_name"]).'\','.$this->units_list_ar[$i]['default'].')"><span class="icon-pencil" title="Редактировать"></span></button></td>
                    </tr>';
        }
        $cnt.='</table>';

        echo "{
        'status':'done',
        'cnt':'".rawurlencode($cnt)."'
        }";
        exit;
    }

//    public function text($str) {
//        return $this->uCore->text(array('uPage','setup_uPage_page'),$str);
//    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->get_units_list();
        $this->print_units_list();

//        $this->uCore->uInt_js('uPage','setup_uPage_page');
    }
}
new show_item_units_bg($this);
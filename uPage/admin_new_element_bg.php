<?php
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once 'uPage/inc/common.php';

class uPage_admin_new_element {
    public $uPage;
    public $uSes;
    public $uFunc;
    private $uCore,$element,$handler;
    private function check_data() {
        if(!isset($_POST['element'],$_POST['handler'])) $this->uFunc->error(10,1);
        $this->element=$_POST['element'];
        $this->handler=$_POST['handler'];
        if(!uString::isDigits($this->handler)) $this->uFunc->error(20,1);
        if($this->element=='row') {
            if(!isset($_POST['row_pos'])) $this->uFunc->error(30,1);
            $_POST['row_pos']=(int)$_POST['row_pos'];

            $this->new_row();
        }
        if($this->element=='col') {
            if(!isset($_POST['col_pos'],$_POST['md'],$_POST['lg'],$_POST['sm'],$_POST['xs'])) $this->uFunc->error(50,1);
            if(!uString::isDigits($_POST['col_pos'])) $this->uFunc->error(60,1);
            $this->new_col();
        }
        else $this->uFunc->error(70,1);
    }

    //ROWS
    private function new_row($site_id=site_id) {
        if(!isset($_POST["row_template_id"])) $this->uFunc->error(80,1);
        $row_template_id=(int)$_POST["row_template_id"];

        if(!$row_template_data=$this->uPage->row_template_id2data($row_template_id,"page_id,site_id")) $this->uFunc->error(85,1);
        $page_id=(int)$row_template_data->page_id;
        $page_data=$this->uPage->page_id2data($this->handler,"page_title,text_folder_id");
        $page_data=(array)$page_data;
        $page_data["page_id"]=$this->handler;
        $page_data["text_folder_id"]=$this->uPage->define_text_folder_id($this->handler,$page_data["page_title"],$page_data["text_folder_id"]);
        $src_site_id=(int)$row_template_data->site_id;
        if(!$rows_of_page_stm=$this->uPage->get_rows_of_page($page_id,"*",$src_site_id))  $this->uFunc->error(90,1);

        /** @noinspection PhpUndefinedMethodInspection */
        $added_rows_ids_ar=[];
        $added_rows_js_ar=[];
        /** @noinspection PhpUndefinedMethodInspection */
        for($row_pos=$_POST['row_pos']; $row=$rows_of_page_stm->fetch(PDO::FETCH_OBJ);) {
            $row_pos=$this->uPage->define_new_row_pos($row_pos,$this->handler);

            $row=$this->uPage->copy_row($page_data,$row,$row_pos,$src_site_id,$site_id);
            $added_rows_ids_ar[]=$row->row_id;
            $added_rows_js_ar[]=$this->uPage->build_row_js4page_builder($row,1);
        }

        //Достаем все row с row_id и row_pos, чтобы передать браузеру информацию об изменениях
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            row_id,
            row_pos
            FROM
            u235_rows
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->handler,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/,1);}


        $result='{';
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        while($row=$stm->fetch(PDO::FETCH_OBJ)) $result.='"row_'.$row->row_id.'":"'.$row->row_pos.'",';
        //,
//        "row_id":"'.$row_id.'",
//        "row_content_centered":"'.$row_content_centered.'",
//        "row_pos":"'.$row_pos.'"
        $result.='"added_rows_ids":"';
        for($i=0;$i<count($added_rows_ids_ar);$i++) {
            $result.=$added_rows_ids_ar[$i].",";
        }
        $result.='",';
        for($i=0;$i<count($added_rows_js_ar);$i++) {
            $result.='"row_js_'.$added_rows_ids_ar[$i].'":"'.rawurlencode($added_rows_js_ar[$i]).'",';
        }
        $result.='"status":"done"
        }';

        $this->clear_cache();
        die($result);
    }

    //COLS
    private function new_col() {
        $md=(int)$_POST['md'];
        if($md>12||$md<0) $md=12;

        $lg=(int)$_POST['lg'];
        if($lg>12||$lg<0) $lg=12;

        $sm=(int)$_POST['sm'];
        if($sm>12||$sm<0) $sm=12;

        $xs=(int)$_POST['xs'];
        if($xs>12||$xs<0) $xs=12;

        $col_pos=$this->uPage->define_new_col_pos($_POST['col_pos'],$this->handler);

        //get new col id
        $col_id=$this->uPage->get_new_col_id();

        //save new col
        $this->uPage->create_col($col_id,$this->handler,$col_pos,$lg,$md,$sm,$xs);

        //Достаем все col с col_id и col_pos, чтобы передать браузеру информацию об изменениях
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            col_id,
            col_pos
            FROM
            u235_cols
            WHERE
            row_id=:row_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $this->handler,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('150'/*.$e->getMessage()*/,1);}


        $result='{';
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        while($col=$stm->fetch(PDO::FETCH_OBJ)) $result.='"col_'.$col->col_id.'":"'.$col->col_pos.'",';

        $result.='"status":"done",
        "row_id":"'.$this->handler.'",
        "col_id":"'.$col_id.'",
        "col_pos":"'.$col_pos.'",
        "lg_width":"'.$lg.'",
        "md_width":"'.$md.'",
        "sm_width":"'.$sm.'",
        "xs_width":"'.$xs.'"
        }';

        $this->clear_cache();

        die($result);
    }
    private function clear_cache() {
        if($this->element=='col') {
            //clear cache
            include_once "uPage/inc/common.php";
            $uPage_common=new \uPage\common($this->uCore);
            $page_id=$uPage_common->get_page_id('row',$this->handler);
        }
        else $page_id=$this->handler;

        $this->uPage->clear_cache($page_id);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uPage=new \uPage\common($this->uCore);
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->check_data();
    }
}
$uPage=new uPage_admin_new_element($this);
<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uPage\common;

class uCat_sale{
    private $uFunc;
    private $uPage;
    private $uCore;

    private function create_default_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO
            el_config_uCat_sale (
            cols_els_id,
            site_id
            ) VALUES (
            :cols_els_id,
            :site_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat_sale 10'/*.$e->getMessage()*/);}
    }
    public function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            items_number,
            dots_style,
            item_title_lines
            FROM
            el_config_uCat_sale
            WHERE
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$conf=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->create_default_el_settings($cols_els_id);
                $conf=$this->get_el_settings($cols_els_id);
            }
            return $conf;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat_sale 20'/*.$e->getMessage()*/);}
        return 0;
    }

    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!$el_settings=$this->get_el_settings($el->cols_els_id,$source_site_id)) return 0;

        //attach sects to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'uCat_sale',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO el_config_uCat_sale (
            cols_els_id,
            site_id,
            items_number,
            dots_style,
            item_title_lines
            ) VALUES (
            :cols_els_id,
            :site_id,
            :items_number,
            :dots_style,
            :item_title_lines
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':items_number', $el_settings->items_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_style', $el_settings->dots_style,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_title_lines', $el_settings->item_title_lines,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat_sale 30'/*.$e->getMessage()*/);}
        return $cols_els_id;
    }

    public function attach_el2col($col_id,$el_id) {
        $el_pos=$this->uPage->define_new_el_pos($col_id);
        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();
        //attach sects to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'uCat_sale',$col_id,$el_id);

        echo '{';
        echo $res[0].'}';
        exit;
    }

    public function load_el_content($cols_els_id,$site_id=site_id) {
        if(!isset($this->uCat)) {
            require_once "uCat/classes/common.php";
            $this->uCat=new \uCat\common($this->uCore);
        }
        $cnt=$this->uCat->sale_items_widget($cols_els_id);

        $conf=$this->get_el_settings($cols_els_id,$site_id);

        echo json_encode(array(
            "status"=>"done",

            "items_number"=>$conf->items_number,
            "dots_style"=>$conf->dots_style,
            "item_title_lines"=>$conf->item_title_lines,

            "cols_els_id"=>$cols_els_id,
            "cnt"=>$cnt
        ));
    }

    public function save_el_conf($cols_els_id) {
        $items_number=20;
        if(isset($_POST['items_number'])) $items_number=(int)$_POST['items_number'];
        if($items_number<1) $items_number=1;
        if($items_number>50) $items_number=50;

        $dots_style=0;
        if(isset($_POST['dots_style'])) $dots_style=(int)$_POST['dots_style'];
        if($dots_style<1) $dots_style=0;
        if($dots_style>16) $dots_style=0;

        $item_title_lines=2;
        if(isset($_POST['item_title_lines'])) $item_title_lines=(int)$_POST['item_title_lines'];
        if($item_title_lines<1) $item_title_lines=1;
        if($item_title_lines>5) $item_title_lines=5;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_uCat_sale
                SET 
                items_number=:items_number,
                item_title_lines=:item_title_lines,
                dots_style=:dots_style
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
                ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':items_number', $items_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_title_lines', $item_title_lines,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_style', $dots_style,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat_sale  40'/*.$e->getMessage()*/);}

        $page_id=$this->uPage->get_page_id('el',$cols_els_id);
        $this->uPage->clear_cache($page_id);

        exit('{
            "cols_els_id":"'.$cols_els_id.'",
            "status":"done"
            }');
    }

    function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=&$this->uPage->uCore;
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=$this->uPage->uFunc;
    }
}
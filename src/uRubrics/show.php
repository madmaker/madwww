<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "uRubrics/classes/common.php";

class show{
    public $uSes;
    public $display_style;
    public $pages_limit;
    public $uRubrics;
    public $common_ar;
    private $uFunc;
    private $uCore;
    public $rubric_name;
    public $rubric_id;
    public $pages_count;
    public $curPage;

    public function text($str) {
        return $this->uCore->text(array('uRubrics','show'),$str);
    }

    private function error(/** @noinspection PhpUnusedParameterInspection */$code) {
        header('Location: '.u_sroot);
        die();
    }
    private function checkData() {
        if(!isset($this->uCore->url_prop[1])) $this->error(1);
        $this->rubric_id=$this->uCore->url_prop[1];
        if(!uString::isDigits($this->rubric_id)) $this->error(2);

        if(!$rubric_data=$this->uRubrics->rubric_id2data($this->rubric_id,"
        rubric_name,
        pages_limit,
        display_style
        ")) $this->error(3);

        $this->rubric_name=$rubric_data->rubric_name;
        $this->pages_limit=(int)$rubric_data->pages_limit;
        $this->display_style=(int)$rubric_data->display_style;

        $this->curPage=0;
        if(isset($_GET['page'])) {
            if(uString::isDigits($_GET['page'])) $this->curPage=$_GET['page'];
        }
    }
    public function insertPageNums($pages_count,$cur_page=0,$rows_limit=20) {
        $cnt='';
        $pageNumber=ceil($pages_count/$rows_limit);
        if($pageNumber>1) {
        $cnt='<ul class="pagination">';
        $butNum=4;//number of buttons before and after
            $start=0;
            $end=$pageNumber;
            if($pageNumber>$butNum*2) {
                $start=($this->curPage-$butNum)<0?0:($this->curPage-$butNum);
                $end=($this->curPage+$butNum)>$pageNumber?$pageNumber:($this->curPage+$butNum);
                if(($start+$end)<$pageNumber) $end=($start+$butNum*2)<$pageNumber?$start+$butNum*2:$pageNumber;
            }
            if($start>0) {
                $page_number=$start-1;
                $cnt.="<li><a href=\"uRubrics/show/$this->rubric_id?page=$page_number\">&laquo;</a></li>";
            }
            for($i=$start;$i<$end;$i++) {
                $active_class=$this->curPage==$i?'class="active"':'';
                $page_number=$i+1;
                $cnt.="<li $active_class><a href=\"uRubrics/show/$this->rubric_id?page=$i\">$page_number</a></li>";
            }
            if($end<$pageNumber) {
                $cnt.="<li><a href=\"uRubrics/show/$this->rubric_id?page=$end\">&raquo;</a></li>";
            }
            $cnt.='</ul>';
        }
        return $cnt;
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new uCore();
        $this->uSes=new uSes($this->uCore);
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uRubrics=new \uRubrics\common($this->uCore);

        $this->checkData();

        $this->uFunc->incCss(u_sroot.'uRubrics/css/uRubrics.min.css');
        $this->uFunc->incCss(u_sroot.'templates/site_'.site_id.'/css/uRubrics/uRubrics.css');

        $this->uCore->page['page_title']=$this->rubric_name;
        $this->uCore->uBc->add_info->html='<li><a href="/uRubrics/show/'.$this->rubric_id.'">'.$this->rubric_name.'</a></li>';

        $this->uCore->uInt_js("uRubrics","show");

        if($this->uSes->access(7)&&!isset($_POST['no_html'])) {
            $this->uFunc->incJs("uRubrics/js/show.min.js");
        }

        $this->pages_count=$this->uRubrics->get_number_of_pages_of_rubric($this->rubric_id);
        $pages_limit=$this->pages_limit===0?20:$this->pages_limit;
        $start_point=$this->pages_limit===0?$this->curPage*20:0;
        $stm_pages=$this->uRubrics->get_pages_of_rubric($this->rubric_id,$pages_limit,$start_point);
        $stm_texts=$this->uRubrics->get_texts_of_rubric($this->rubric_id,$pages_limit,$start_point);
        /** @noinspection PhpUndefinedMethodInspection */
        $pages_ar=$stm_pages->fetchAll(PDO::FETCH_OBJ);
        /** @noinspection PhpUndefinedMethodInspection */
        $text_ar=$stm_texts->fetchAll(PDO::FETCH_OBJ);


        $this->common_ar=array_merge($pages_ar,$text_ar);
    }
}
$uRubrics=new show($this);

ob_start();
if($uRubrics->uSes->access(7)&&!isset($_POST['no_html'])) {
    require_once "uRubrics/dialogs/show.php";?>
    <script type="text/javascript">
        if(typeof uRubrics==="undefined") uRubrics={};
        if(typeof uRubrics.show==="undefined") uRubrics.show={};

        uRubrics.show.rubric_id=<?=$uRubrics->rubric_id?>;
    </script>
<?}?>

    <div class="uPage_rubrics_arts_column container-fluid" id="uRubrics_show_<?=$uRubrics->rubric_id?>">
        <h1 class="page-header"><?=uString::sql2text($uRubrics->rubric_name,1);?></h1>
        <?
        if($uRubrics->pages_count>20&&$uRubrics->pages_limit===0) {
            print $uRubrics->insertPageNums($uRubrics->pages_count,$uRubrics->curPage);
        }
        $common_ar_count=count($uRubrics->common_ar);
        if($uRubrics->display_style<4) $col_number=$uRubrics->display_style+1;
        else $col_number=3;

        if($uRubrics->display_style<4) $uRubrics->uRubrics->print_rubric_columns($uRubrics->common_ar,$col_number);
        elseif($uRubrics->display_style===4) $uRubrics->uRubrics->print_rubric_tiles($uRubrics->common_ar,$col_number)?>

        <?
        if($uRubrics->pages_count>20&&$uRubrics->pages_limit===0) {
            print $uRubrics->insertPageNums($uRubrics->pages_count,$uRubrics->curPage);
        }
        ?>
    </div>
<?
$this->page_content=ob_get_contents();
ob_end_clean();

if(!isset($_POST["no_html"])) include "templates/template.php";
else echo $this->page_content;

<?php
namespace uRubrics\admin;
use PDO;
use processors\uFunc;
use uRubrics\common;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uRubrics/classes/common.php";

class news_list {
    public $uRubrics;
    private $uFunc;
    public $uSes;
    private $uCore;
    public function text($str) {
        return $this->uCore->text(array('uRubrics','rubrics_list'),$str);
    }



    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
        $this->uRubrics=new common($this->uCore);

        $this->uCore->uInt_js('uPage','setup_uPage_page');
    }
}
$uRubrics=new news_list($this);
ob_start();?>
<div class="uRubrics_list">
    <?if($rubrics_stm=$uRubrics->uRubrics->get_site_rubrics()) {
        /** @noinspection PhpUndefinedMethodInspection */
        while($rubric=$rubrics_stm->fetch(PDO::FETCH_OBJ)) {
            $rubric->display_style=(int)$rubric->display_style;
            $rubrics_pages_stm=$uRubrics->uRubrics->get_pages_of_rubric($rubric->rubric_id,$rubric->pages_limit_on_news_list);
            /** @noinspection PhpUndefinedMethodInspection */
            $rubrics_pages_ar=$rubrics_pages_stm->fetchAll(PDO::FETCH_OBJ);
            $rubrics_pages_ar_count=count($rubrics_pages_ar);
            if($rubrics_pages_ar_count||$uRubrics->uSes->access(7)) {?>
                <h2><a href="uRubrics/show/<?= $rubric->rubric_id ?>"><?= $rubric->rubric_name ?></a></h2>
                <?
                if($rubric->display_style<4) $uRubrics->uRubrics->print_rubric_columns($rubrics_pages_ar,$rubric->display_style+1);
                else $uRubrics->uRubrics->print_rubric_tiles($rubrics_pages_ar,3);
            }
        }
    }?>
</div>
<?$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/template.php';
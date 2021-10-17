<?php
namespace dashboard;
use dashboard\admin\common;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

require_once "dashboard/classes/common.php";

class home {
    public $grant;
    public $dashboard;
    public $uFunc;
    private $uSes;
    private $uCore;
    private function check_data() {

    }

    public function text($str) {
        return $this->uCore->text(array('dashboard','home'),$str);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);

        $this->grant=0;
        if(!$this->uSes->access(7)) return 0;//TODO-nik87 сюда прописать всех админов
        $this->grant=1;

        $this->uFunc=new uFunc($this->uCore);
        $this->dashboard=new common($this->uCore);

        $this->check_data();

        $this->uFunc->incJs(staticcontent_url.'js/lib/Numeral-js/numeral.min.js');

        $this->uFunc->incJs("/js/chartjs/Chart.min.js");
        $this->uFunc->incCss("/js/chartjs/Chart.min.css");


//        $this->uFunc->incJs("dashboard/js/utils.min.js");
        $this->uFunc->incJs("dashboard/js/home.js");
        $this->uFunc->incJs("js/chartjs/doughnut_text.min.js");


//        $this->uCore->uInt_js('uPage','setup_uPage_page');
        return 1;
    }
}
$dashboard=new home($this);
ob_start();
if($dashboard->grant) {?>
<div class="dashboard container">
    <div class="row">
        <div class="col-md-12">
            <div id="canvas-holder">
                <canvas id="chart-area"></canvas>
                <script type="text/javascript">
                    if(typeof dashboard==="undefined") {var dashboard;dashboard={};}
                    if(typeof dashboard.home==="undefined") dashboard.home={};
                    dashboard.home.disk_sizes=[];
                    dashboard.home.disk_sizes_labels=[];
                    dashboard.home.disk_sizes_colors=[];
                    dashboard.home.allowed_space=0;
                <?
                $disk_space_ar=$dashboard->dashboard->get_site_disk_space();
//                print_r($disk_space_ar);
                $disk_space_ar_count=count($disk_space_ar);
                $total_used_space=0;
                $allowed_space=$dashboard->dashboard->get_allowed_disk_space();
                for($i=0;$i<$disk_space_ar_count;$i++) {
                    $dirsize=$disk_space_ar[$i]["dirsize"]/1024/1024/1024;
                    $total_used_space+=$dirsize;?>
                    dashboard.home.disk_sizes[<?=$i?>]=<?=number_format($dirsize,2,".","")?>;
                    dashboard.home.disk_sizes_labels[<?=$i?>]="<?=$disk_space_ar[$i]["dirname"]?>";
                    dashboard.home.disk_sizes_colors[<?=$i?>]="#<?=$dashboard->uFunc->getColor($i)?>";
                <?}
                $free_space=$allowed_space-$total_used_space;?>

                    dashboard.home.disk_sizes[<?=$i?>]=<?=number_format($free_space,2,".","")?>;
                    percentage="<?=number_format(100/$allowed_space*$total_used_space,2,".","")."%"?>";
                    chartname="<?=$dashboard->text("Disk space used")?>: <?=number_format($total_used_space,2,"."," ")?><?=$dashboard->text("GB of total")?> <?=$allowed_space?><?=$dashboard->text("GB")?>";
                    dashboard.home.disk_sizes_labels[<?=$i?>]="<?=$free_space>0?"FREE":"OVERSIZE"?>";
                    dashboard.home.disk_sizes_colors[<?=$i?>]="<?=$free_space>0?"white":"red"?>";
                    percentage_color="<?=$free_space>0?"black":"red"?>";

                </script>
            </div>
        </div>
    </div>
</div>

    <script>

    </script>
<?}
else {//TODO-nik87 авторизацию тут предлагать.?>
    QQQQQ
<?}

$this->page_content=ob_get_contents();
ob_end_clean();
include 'templates/u235/template.php';

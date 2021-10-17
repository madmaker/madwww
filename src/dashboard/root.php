<?php
namespace dashboard;
use dashboard\admin\common;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

require_once "dashboard/classes/common.php";

class root {
    public $grant;
    public $dashboard;
    public $uFunc;
    private $uSes;
    private $uCore;
    private function check_data() {

    }

    public function text($str) {
        return $this->uCore->text(array('dashboard','root'),$str);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);

        $this->grant=0;
        if(!$this->uSes->access(17)) return 0;
        $this->grant=1;

        $this->uFunc=new uFunc($this->uCore);
        $this->dashboard=new common($this->uCore);

        $this->check_data();

        $this->uFunc->incJs('/js/Numeral-js/numeral.min.js');

        $this->uFunc->incJs("/js/chartjs/Chart.min.js");
        $this->uFunc->incCss("/js/chartjs/Chart.min.css");


//        $this->uFunc->incJs("dashboard/js/utils.min.js");
        $this->uFunc->incJs("dashboard/js/root.js");
        $this->uFunc->incJs("js/chartjs/doughnut_text.min.js");


//        $this->uCore->uInt_js('uPage','setup_uPage_page');
        return 1;
    }
}
$dashboard=new root($this);
ob_start();
if($dashboard->grant) {
    $sites_stm = $dashboard->dashboard->get_sites();?>
    <script type="text/javascript">
        if (typeof dashboard === "undefined") {var dashboard;dashboard = {};}
        if (typeof dashboard.root === "undefined") dashboard.root = {};
        dashboard.root.disk_sizes = [];
        dashboard.root.disk_sizes_labels = [];
        dashboard.root.disk_sizes_colors = [];
        dashboard.root.allowed_space = [];
        dashboard.root.disk_size_percentage = [];
        dashboard.root.disk_size_percentage_color = [];
        dashboard.root.disk_size_chartname = [];
        dashboard.root.sites_number=0;
    </script>
    <?
    for ($site_i=0;$site = $sites_stm->fetch(\PDO::FETCH_OBJ);$site_i++) {
        $site_id=$site->site_id;
        $site_name=$site->site_name;?>
        <div class="dashboard container">
            <div class="row">
                <div class="col-md-12">
                    <h3>#<?=$site_id?> <?=$site_name?></h3>
                    <p>uDrive DB size: <?=number_format($dashboard->dashboard->get_site_uDrive_db_space($site_id)/1024/1024/1024,2,"."," ")?>Gb</p>
                    <div id="canvas-holder_<?=$site_i?>">
                        <canvas id="dashboard_disk_size_<?=$site_i?>_chart-area"></canvas>
                        <script type="text/javascript">
                            dashboard.root.disk_sizes[<?=$site_i?>] = [];
                            dashboard.root.disk_sizes_labels[<?=$site_i?>] = [];
                            dashboard.root.disk_sizes_colors[<?=$site_i?>] = [];
                            dashboard.root.allowed_space[<?=$site_i?>] = 0;
                            <?

                            $disk_space_ar = $dashboard->dashboard->get_site_disk_space($site_id);
                            //                print_r($disk_space_ar);
                            $disk_space_ar_count = count($disk_space_ar);
                            $total_used_space = 0;
                            $allowed_space = $dashboard->dashboard->get_allowed_disk_space($site_id);
                            for($i = 0;$i < $disk_space_ar_count;$i++) {
                                $dirsize = $disk_space_ar[$i]["dirsize"] / 1024 / 1024 / 1024;
                                $total_used_space += $dirsize;?>
                                dashboard.root.disk_sizes[<?=$site_i?>][<?=$i?>] =<?=number_format($dirsize, 2, ".", "")?>;
                                dashboard.root.disk_sizes_labels[<?=$site_i?>][<?=$i?>] = "<?=$disk_space_ar[$i]["dirname"]?>";
                                dashboard.root.disk_sizes_colors[<?=$site_i?>][<?=$i?>] = "#<?=$dashboard->uFunc->getColor($i)?>";
                            <?}
                            $free_space = $allowed_space - $total_used_space;?>

                            dashboard.root.disk_sizes[<?=$site_i?>][<?=$i?>] =<?=number_format($free_space, 2, ".", "")?>;
                            dashboard.root.disk_size_percentage[<?=$site_i?>] = "<?=number_format(100 / $allowed_space * $total_used_space, 2, ".", "") . "%"?>";
                            dashboard.root.disk_size_chartname[<?=$site_i?>] = "<?=$dashboard->text("Disk space used")?>: <?=number_format($total_used_space, 2, ".", " ")?><?=$dashboard->text("GB of total")?> <?=$allowed_space?><?=$dashboard->text("GB")?>";
                            dashboard.root.disk_sizes_labels[<?=$site_i?>][<?=$i?>] = "<?=$free_space > 0 ? "FREE" : "OVERSIZE"?>";
                            dashboard.root.disk_sizes_colors[<?=$site_i?>][<?=$i?>] = "<?=$free_space > 0 ? "white" : "red"?>";
                            dashboard.root.disk_size_percentage_color[<?=$site_i?>] = "<?=$free_space > 0 ? "black" : "red"?>";

                        </script>
                    </div>
                </div>
            </div>
        </div>
    <?
    }?>
    <script type="text/javascript">
        dashboard.root.sites_number=<?=$site_i?>;
    </script>
<?}
else {//TODO-nik87 авторизацию тут предлагать.?>
    QQQQQ
<?}

$this->page_content=ob_get_contents();
ob_end_clean();
include 'templates/u235/template.php';
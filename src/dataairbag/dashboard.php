<?php
namespace dataairbag;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "dataairbag/classes/dataairbag.php";

class dashboard {
    /**
     * @var int
     */
    public $hostsAr;
    /**
     * @var dataairbag
     */
    public $dataairbag;
    /**
     * @var int
     */
    public $print_data;
    /**
     * @var int
     */
    private $user_id;
    /**
     * @var uFunc
     */
    private $uFunc;
    /**
     * @var uSes
     */
    private $uSes;
    private $uCore;

    private function get_hosts_list() {
        $this->user_id=$this->uSes->get_val("user_id");
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT 
            host_id,
            status,
            arch,
            hostname,
            platform,
            `release`,
            totalmem,
            type,
            hash,
            `key`
            FROM 
            hosts 
            WHERE 
            user_id=:user_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('10'.$e->getMessage());}
        return 0;
    }

    public function host_status2text($status) {
        if($status==="") return 'Зарегистрирован на сервере, но не зарегистрирован в личном кабинете';
        elseif($status==="registered") return 'Зарегистрирован';
        return "";
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) /** @noinspection PhpFullyQualifiedNameUsageInspection */ $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);

        ob_start();
        if(!$this->uSes->access(2)) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">Вы не авторизованы</h1>
                <p>Пожалуйста, авторизуйтесь</p>
                <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
            </div>';
            return 0;
        }
        else $this->print_data=1;

        $this->uFunc=new uFunc($this->uCore);

        $this->dataairbag=new dataairbag($this->uCore);

        $this->hostsAr=$this->get_hosts_list();

        return 1;
    }
}
$da=new dashboard($this);


if($da->print_data) {?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-header">Мои компьютеры<small><br>Выберите компьютер, чтобы увидеть файлы и папки для резервного копирования</small></h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-striped">
                <?foreach ($da->hostsAr as $host) {
                    $online_status=$da->dataairbag->checkOnline($host->hash,$host->key);?>
                    <tr>
                        <td><a href="/dataairbag/fileList/<?=$host->host_id?>"><?=$host->host_id?></a></td>
                        <td><a href="/dataairbag/fileList/<?=$host->host_id?>"><?=$host->arch?></a></td>
                        <td><a href="/dataairbag/fileList/<?=$host->host_id?>"><?=$host->hostname?></a></td>
                        <td><a href="/dataairbag/fileList/<?=$host->host_id?>"><?=$host->platform?></a></td>
<!--                        <td>--><?//=$host->release?><!--</td>-->
<!--                        <td>--><?//=$host->totalmem?><!--</td>-->
<!--                        <td>--><?//=$host->type?><!--</td>-->
                        <td><a href="/dataairbag/fileList/<?=$host->host_id?>"><?=$da->host_status2text($host->status)?></a></td>
                        <td><?=$online_status?></td>
                    </tr>
                <?}?>
            </table>
        </div>
    </div>
</div>

<?}
$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/template.php';

<?php
namespace dataairbag;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class fileVersions {
    /**
     * @var int
     */
    public $print_data;
    /**
     * @var int
     */
    public $versionsStm;
    public $hostname;
    public $fileName;
    public $type;
    /**
     * @var uFunc
     */
    private $uFunc;
    /**
     * @var uSes
     */
    private $uSes;
    /**
     * @var int
     */
    private $file_id;
    private $uCore;
    private function check_data() {
        if(!isset($this->uCore->url_prop[1])) {
            header('Location: '.u_sroot.'dataairbag/dashboard');
            exit;
        }
        $this->file_id=(int)$this->uCore->url_prop[1];
    }

    private function getFileVersions($file_id) {
        $user_id=$this->uSes->get_val("user_id");

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT 
            fileName,
            files.type as type,
            hostname,
            birthtime
            FROM 
            files 
            JOIN 
            hosts
            ON
            files.host_id=hosts.host_id
            WHERE 
            user_id=:user_id AND
            file_id=:file_id
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $file_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) {
                header('Location: '.u_sroot.'dataairbag/dashboard');
                exit;
            }

            $this->hostname=$qr->hostname;
            $this->fileName=$qr->fileName;
            $this->type=(int)$qr->type;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT 
            version,
            timestamp,
            fileName,
            size,
            mtime,
            ctime
            FROM 
            file_versions 
            WHERE 
            file_id=:file_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $file_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return 0;
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
                <p><!--suppress HtmlUnknownAnchorTarget --><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
            </div>';
            return 0;
        }
        else $this->print_data=1;


        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();

        $this->versionsStm=$this->getFileVersions($this->file_id);

        return 1;
    }
}
$da=new fileVersions($this);
if($da->print_data) {?>
    <div class="container-fluid">
        <h1 class="page-header">Версии <?=$da->type===0?'<span class="icon-doc"></span>':'<span class="icon-folder"></span>'?><?=$da->fileName?> <small><br>на хосте <?=$da->hostname?></small></h1>

        <table class="table table-hover">
            <tr>
                <th>#</th>
                <th>Дата загрузки</th>
                <th>Имя</th>
                <th>Размер</th>
                <th>Изменено содержимое</th>
                <th>Изменена информация <span class="icon-question" title="Изменено название файла, папка, любая другая информация кроме содержимого файла"></span> </th>
                <th></th>
            </tr>
            <?
            /** @noinspection PhpUndefinedMethodInspection */
            while($ver=$da->versionsStm->fetch(PDO::FETCH_OBJ)) {?>
            <tr>
                <td><?=$ver->version?></td>
                <td><?=date("d.m.Y H:i",$ver->timestamp)?></td>
                <td><?=$ver->fileName?></td>
                <td><?$ver->size = (int)$ver->size;
                    if ($ver->size > 1099511627776) {
                        print number_format($ver->size / 1099511627776, 2, '.', ' ');
                        print " Тбайт";
                    } elseif ($ver->size > 1073741824) {
                        print number_format($ver->size / 1073741824, 2, '.', ' ');
                        print " Гбайт";
                    } elseif ($ver->size > 1048576) {
                        print number_format($ver->size / 1048576, 2, '.', ' ');
                        print " Мбайт";
                    } elseif ($ver->size > 1024) {
                        print number_format($ver->size / 1024, 2, '.', ' ');
                        print " Кбайт";
                    } else {
                        print $ver->size;
                        print " байт";
                    }?></td>
                <td><?=date("d.m.Y H:i",$ver->mtime)?></td>
                <td><?=date("d.m.Y H:i",$ver->ctime)?></td>
            </tr>
            <?}?>
        </table>
    </div>
<?}

$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/template.php';

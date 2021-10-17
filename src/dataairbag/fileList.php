<?php
namespace dataairbag;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "dataairbag/classes/dataairbag.php";

class fileList {
    public $hostname;
    /**
     * @var int
     */
    public $host_id;
    /**
     * @var uSes
     */
    public $uSes;/**
 * @var dataairbag
 */public $dataairbag;
    /**
     * @var int
     */
    public $root_folder_files_ar;
    /**
     * @var string
     */
    public $pathOfInode;
    /**
     * @var int
     */
    public $print_data;
    /**
     * @var int
     */
    private $parentInode;
    /**
     * @var int
     */
    private $hash;
    private $key;
    public $online_status;
    /**
     * @var uFunc
     */
    private $uFunc;
    private $uCore;
    private function check_data() {
        if(!isset($this->uCore->url_prop[1])) {
            header('Location: '.u_sroot.'dataairbag/dashboard');
            exit;
        }
        $host_id=(int)$this->uCore->url_prop[1];
        $user_id=$this->uSes->get_val("user_id");

        if(!$qr=$this->dataairbag->hostBelongs2User($host_id,$user_id,"hostname,`key`,hash")) {
            header('Location: '.u_sroot.'dataairbag/dashboard');
            exit;
        }

        $parentInode=0;

        if(isset($this->uCore->url_prop[2])) {
            $folder_file_id=(int)$this->uCore->url_prop[2];
            if($result=$this->dataairbag->file_id2data($folder_file_id,$host_id,"inode")) $parentInode=$result->inode;
        }

        $this->hostname=$qr->hostname;
        $this->host_id=$host_id;
        $this->hash=$qr->hash;
        $this->key=$qr->key;
        $this->parentInode=$parentInode;
    }

    public function getFileVersions($file_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT 
            timestamp 
            FROM 
            file_versions 
            WHERE 
            host_id=:host_id AND
            file_id=:file_id
            ORDER BY version ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $this->host_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $file_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
        return 0;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
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

        $this->check_data();

        $this->online_status=$this->dataairbag->checkOnline($this->hash,$this->key);

        $this->root_folder_files_ar=$this->dataairbag->getFilesFromFolder($this->host_id,$this->parentInode);
        $this->pathOfInode=$this->dataairbag->getPathOfInode($this->host_id,$this->parentInode,1);

        $this->uFunc->incJs("/dataairbag/js/fileList.min.js");
        return 1;
    }
}
$da=new fileList($this);

if($da->print_data) { ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-header">#<?= $da->host_id ?> <?= $da->hostname ?>
                    <small><?= $da->online_status ?><?
                        if ($da->online_status !== "online") { ?><br><span class="text-danger">Компьютер оффлайн. Показаны архивные результаты</span> <?
                        } ?></small>
                </h1>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <p><strong><?= $da->pathOfInode ?></strong></p>
                <table class="table table-striped table-condensed">
                    <tr>
                        <th></th>
                        <th>Имя</th>
                        <th></th>
                        <th>Размер</th>
                        <th>Модифицирован</th>
                        <th>Изменен</th>
                        <th>Создан</th>
                        <th>Загружен</th>
                        <th></th>
                    </tr>
                    <?
                    foreach ($da->root_folder_files_ar as $file) {
                        $link = '/dataairbag/fileList/' . $da->host_id . '/' . $file->file_id; ?>
                        <tr>
                            <td><a href="<?= $link ?>"><?
                                    $file->type = (int)$file->type;
                                    if ($file->type === 0) { ?><span class="icon-doc"></span><?
                                    } elseif ($file->type === 1) { ?><span class="icon-folder"></span><?
                                    } else { ?><span class="icon-file-unknown"></span><?
                                    } ?>
                                </a>
                            </td>
                            <td><a href="<?= $link ?>"><?= $file->fileName ?></a></td>
                            <td><?
                                $file->status = (int)$file->status;
                                if ($file->status === 1 || $file->status === 2) { ?><a
                                        title="Начинает храниться в DataAirbag"><span class="icon-upload-cloud"></span></a><?
                                } elseif ($file->status === 3) { ?><a title="Хранится в DataAirbag"
                                                                      class="text-success"><span
                                            class="icon-upload-cloud"></span></a><?
                                } elseif ($file->status === 4 || $file->status === 5) { ?><a
                                        title="Отменяется сохранение в DataAirbag" class="text-danger"><span
                                            class="icon-cloud"></span></a><?
                                } elseif ($file->status === 6) { ?><a title="Больше не сохраняется в DataAirbag"
                                                                      class="text-danger"><span
                                            class="icon-cloud"></span></a><?
                                } else { ?><a title="Не загружен в DataAirbag"><span class="icon-cloud"></span></a><?
                                } ?></td>
                            <td><?
                                $file->size = (int)$file->size;
                                if ($file->size > 1099511627776) {
                                    print number_format($file->size / 1099511627776, 2, '.', ' ');
                                    print " Тбайт";
                                } elseif ($file->size > 1073741824) {
                                    print number_format($file->size / 1073741824, 2, '.', ' ');
                                    print " Гбайт";
                                } elseif ($file->size > 1048576) {
                                    print number_format($file->size / 1048576, 2, '.', ' ');
                                    print " Мбайт";
                                } elseif ($file->size > 1024) {
                                    print number_format($file->size / 1024, 2, '.', ' ');
                                    print " Кбайт";
                                } else {
                                    print $file->size;
                                    print " байт";
                                }
                                ?></td>
                            <td><?
                                $mtime = (int)$file->mtime / 1000;
                                print date("d.m.Y H:i:s", $mtime);
                                ?></td>
                            <td><?
                                $ctime = (int)$file->ctime / 1000;
                                print date("d.m.Y H:i:s", $ctime);
                                ?></td>
                            <td><?
                                $birthtime = (int)$file->birthtime / 1000;
                                print date("d.m.Y H:i:s", $birthtime);
                                ?></td>
                            <td><a href="/dataairbag/fileVersions/<?=$file->file_id?>"><?
                                $lastUploadTimestamp = (int)$file->lastUploadTimestamp;
                                if (!$lastUploadTimestamp) { ?>-<?
                                } else {
                                    print date("d.m.Y H:i:s", $lastUploadTimestamp);
                                    if ($versionsAr = $da->getFileVersions($file->file_id)) {
                                        if ($versionsNumber = count($versionsAr)) {
                                            print "<br>Всего " . $versionsNumber . " раз";
                                        }
                                    }
                                }
                                    ?></a></td>
                            <td>
                                <button
                                        type="button"

                                        class="btn <?
                                        if ($file->status === 0 || $file->status === 4 || $file->status === 5 || $file->status === 6) { ?>btn-primary <?
                                        } else { ?>btn-danger<?
                                        }
                                        ?> btn-xs"

                                        id="changeFileStatus_btn_<?= $file->file_id ?>"

                                        data-btn_action="<?
                                        if ($file->status === 0 || $file->status === 4 || $file->status === 5 || $file->status === 6) { ?>backup<?
                                        } else { ?>cancel<?
                                        } ?>"

                                        onclick="<?
                                        if ($file->status === 0 || $file->status === 4 || $file->status === 5 || $file->status === 6) { ?>
                                                dataairbag.fileList.changeFileStatus(<?=$da->host_id?>,<?=$file->file_id?>,this);
                                        <? } else { ?>dataairbag.fileList.changeFileStatus(<?=$da->host_id?>,<?=$file->file_id?>,this);<?
                                        }
                                        ?>"

                                ><?
                                    if ($file->status === 0 || $file->status === 4 || $file->status === 5 || $file->status === 6) { ?>Начать хранить<?
                                    } else { ?>Не хранить<?
                                    }
                                    ?></button>
                            </td>
                        </tr>
                    <?
                    } ?>
                </table>
            </div>
        </div>
    </div>

    <?
}
$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/template.php';

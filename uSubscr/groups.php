<?php
namespace uSubscr\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

class channels {
    public $uFunc;
    private $uCore;
    public $q_grs;
    public function get_groups() {
        if(isset($_POST['list'])) {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSubscr")->prepare("SELECT
            u235_groups.gr_id,
            gr_title
            FROM
            u235_groups
            WHERE
            (
            status IS NULL OR 
            status='active'
            ) AND
            site_id=:site_id
            ORDER BY
            gr_id ASC
            ");
        }
        else {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSubscr")->prepare("SELECT
            gr_id,
            gr_title,
            status
            FROM
            u235_groups
            WHERE
            site_id=:site_id
            ORDER BY
            gr_id ASC
            ");
        }

        try {
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
        return 0;
    }
    private function del_dropped_grs() {
            $dropped_gr_lifetime=time()-604800;//1 week

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSubscr")->prepare("DELETE FROM
            u235_groups
            WHERE
            timestamp<:timestamp AND
            status='deleted' AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $dropped_gr_lifetime,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);

        $this->del_dropped_grs();
    }
}
$uSubscr=new channels($this);

if(isset($_POST['list'])) {
    $stm=$uSubscr->get_groups();
    echo '{';
    /** @noinspection PhpUndefinedMethodInspection */
    for($i=0; $data=$stm->fetch(PDO::FETCH_OBJ); $i++) {
        echo '"gr_id_'.$i.'":"'.$data->gr_id.'",
        "gr_title_'.$i.'":"'.rawurlencode(uString::sql2text($data->gr_title)).'",';
    }
    echo '"status":"done"}';
}
else {
    $this->uFunc->incJs(u_sroot . $this->mod . '/js/' . $this->page_name . '.js');
    ob_start();
    ?>
    <h1><?= $this->page['page_title'] ?></h1>
    <div style="float: right; display: table">
        <div class="btn-group">
            <button class="btn btn-default btn-sm" id="uSubscr_watchBtn" onclick="uSubscr.switchMode('watch');">
                Просмотр
            </button>
            <button class="btn btn-default btn-sm" id="uSubscr_createBtn" onclick="uSubscr.new_gr_dg();">Новая группа
            </button>
            <button class="btn btn-default btn-sm" id="uSubscr_restoreBtn">Восстановить</button>
            <button class="btn btn-default btn-sm" id="uSubscr_deleteBtn">Удалить</button>
        </div>
    </div>

    <p class="clearfix">&nbsp;</p>

    <div class="uSubscr row">
        <div id="uSubscr_list" class="col-md-12"></div>
    </div>

    <script type="text/javascript">
        if(typeof uSubscr==="undefined") {
            uSubscr = {};


            uSubscr.gr_id = [];
            uSubscr.gr_title = [];
            uSubscr.status = [];
            uSubscr.gr_show = [];
            uSubscr.gr_sel = [];
            uSubscr.gr_id2index = [];
        }
        <?$stm=$uSubscr->get_groups();
        /** @noinspection PhpUndefinedMethodInspection */for($i = 0;$data = $stm->fetch(PDO::FETCH_OBJ);$i++) {?>
        i =<?=$i?>;
        uSubscr.gr_id[i] =<?=$data->gr_id?>;
        uSubscr.gr_title[i] = "<?=rawurlencode(uString::sql2text($data->gr_title))?>";
        uSubscr.status[i] = "<?=$data->status?>";
        uSubscr.gr_show[i] = true;
        uSubscr.gr_sel[i] = false;
        uSubscr.gr_id2index[uSubscr.gr_id[i]] = i;
        <?}?>
    </script>

    <div class="modal fade" id="uSubscr_new_gr_dg" tabindex="-1" role="dialog" aria-labelledby="uSubscr_new_gr_dgLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span
                                aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uSubscr_new_gr_dgLabel">Создать группу</h4>
                </div>
                <div class="modal-body">
                    <div class="text-info" id="uSubscr_new_gr_text_info" style="display: none"></div>
                    <div class="text-danger" id="uSubscr_new_gr_text_danger" style="display: none"></div>
                    <div class="form-group">
                        <label for="uSubscr_new_gr_title">Название группы:</label>
                        <input type="text" id="uSubscr_new_gr_title" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" onclick="uSubscr.new_gr()">Создать</button>
                </div>
            </div>
        </div>
    </div>


    <? $this->page_content = ob_get_contents();
    ob_end_clean();
    include "templates/u235/template.php";
}
?>
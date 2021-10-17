<?php
class uSubscr_records {
    private $uCore;
    public $q_records;
    private function getRecords() {
            if(!$this->q_records=$this->uCore->query("uSubscr","SELECT
            `rec_id`,
            `rec_title`,
            `status`
            FROM
            `u235_records`
            WHERE
            `site_id`='".site_id."'
            ORDER BY
            `rec_id` ASC
            ")) $this->uCore->error(1);
    }
    private function del_droped_recs() {
            $droped_rec_lifetime=time()-604800;//1 week

            if(!$query=$this->uCore->query("uSubscr","SELECT
             `rec_id`
             FROM
             `u235_records`
             WHERE
             `timestamp`<'".$droped_rec_lifetime."' AND
             `status`='deleted' AND
             `site_id`='".site_id."'
             ")) $this->uCore->error(2);
            while($rec=$query->fetch_object()) {
                @uFunc::rmdir($_SERVER['DOCUMENT_ROOT'].'/'.$this->uCore->mod.'/files/'.site_id.'/'.$rec->rec_id);
                if(!$this->uCore->query("uSubscr","DELETE FROM
                `u235_records_files`
                WHERE
                `rec_id`='".$rec->rec_id."' AND
                `site_id`='".site_id."'
                ")) $this->uCore->error(3);
            }
            if(!$this->uCore->query("uSubscr","DELETE FROM
            `u235_records`
            WHERE
            `timestamp`<'".$droped_rec_lifetime."' AND
            `status`='deleted' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(4);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        $this->del_droped_recs();

        $this->getRecords();
    }
}
$uSubscr=new uSubscr_records($this);

$this->uFunc->incJs(u_sroot.'uSubscr/js/'.$this->page_name.'.js');

ob_start();
?>
<h1><?=$this->page['page_title']?></h1>
<div style="float: right; display: table">
    <div class="btn-group">
        <button class="btn btn-default btn-sm" id="uSubscr_watchBtn" onclick="uSubscr.switchMode('watch');">Просмотр</button>
        <button class="btn btn-default btn-sm" id="uSubscr_createBtn" onclick="uSubscr.new_rec_dg()">Новая запись</button>
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
            uSubscr.rec_id = [];
            uSubscr.rec_title = [];
            uSubscr.status = [];
            uSubscr.rec_show = [];
            uSubscr.rec_sel = [];
            uSubscr.rec_id2index = [];
        }

        <? for($i=0;$data=$uSubscr->q_records->fetch_object();$i++) {?>
        i=<?=$i?>;
        uSubscr.rec_id[i]=<?=$data->rec_id?>;
        uSubscr.rec_title[i]="<?=rawurlencode(uString::sql2text($data->rec_title))?>";
        uSubscr.status[i]="<?=$data->status?>";
        uSubscr.rec_show[i]=true;
        uSubscr.rec_sel[i]=false;
        uSubscr.rec_id2index[uSubscr.rec_id[i]]=i;
        <?}?>
    </script>

    <div class="modal fade" id="uSubscr_new_rec_dg" tabindex="-1" role="dialog" aria-labelledby="uSubscr_new_rec_dgLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uSubscr_new_rec_dgLabel">Создать новость</h4>
                </div>
                <div class="modal-body">
                    <div class="text-info" id="uSubscr_new_rec_text_info" style="display: none"></div>
                    <div class="text-danger" id="uSubscr_new_rec_text_danger" style="display: none"></div>
                    <div class="form-group">
                        <label>Заголовок записи:</label>
                        <input type="text" id="uSubscr_new_rec_title" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" onclick="uSubscr.new_record()">Создать</button>
                </div>
            </div>
        </div>
    </div>

<? $this->page_content=ob_get_contents();
ob_end_clean();
include "templates/u235/template.php";
?>

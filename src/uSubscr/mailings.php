<?php
class uSubscr_mailing {
    private $uCore;
    public $q_mailing,$status2txt;
    private function get_mailing() {
            if(!$this->q_mailing=$this->uCore->query("uSubscr","SELECT DISTINCT
            `m_id`,
            `u235_mailing`.`timestamp`,
            `u235_mailing`.`status`,
            `progress`,
            `rec_title`
            FROM
            `u235_mailing`,
            `u235_records`
            WHERE
            `u235_mailing`.`site_id`='".site_id."' AND
            `u235_records`.`site_id`='".site_id."' AND
            `u235_records`.`rec_id`=`u235_mailing`.`rec_id`
            ORDER BY
            `m_id` DESC
            ")) $this->uCore->error(1);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->status2txt['']='';
        $this->status2txt['preparing']='Подготовка к отправке';
        $this->status2txt['running']='В процессе';
        $this->status2txt['finished']='Завершена';
        $this->status2txt['stopped']='Остановлена';

        $this->get_mailing();
    }
}
$uSubscr=new uSubscr_mailing($this);

$this->uFunc->incJs(u_sroot.'uSubscr/js/'.$this->page_name.'.js');

ob_start();
?>
<h1><?=$this->page['page_title']?></h1>

<div class="uSubscr row">
    <div id="uSubscr_list" class="col-md-12"></div>
</div>

    <script type="text/javascript">
        if(typeof uSubscr==="undefined") {
            uSubscr = {};

            uSubscr.m_id = [];
            uSubscr.rec_title = [];
            uSubscr.timestamp = [];
            uSubscr.status = [];
            uSubscr.progress = [];
            uSubscr.m_id2index = [];
        }

        <? for($i=0;$mailing=$uSubscr->q_mailing->fetch_object();$i++) {?>
        i=<?=$i?>;
        uSubscr.m_id[i]=<?=$mailing->m_id?>;
        uSubscr.rec_title[i]="<?=rawurlencode(uString::sql2text($mailing->rec_title))?>";
        uSubscr.timestamp[i]="<?=date('d.m.Y H:i:s',$mailing->timestamp)?>";
        uSubscr.status[i]="<?=$uSubscr->status2txt[$mailing->status]?>";
        uSubscr.progress[i]=<?=$mailing->progress?>;
        uSubscr.m_id2index[uSubscr.m_id[i]]=i;
        <?}?>
    </script>

<? $this->page_content=ob_get_contents();
ob_end_clean();
include "templates/u235/template.php";
?>

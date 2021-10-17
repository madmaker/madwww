<?php
class uSubscr_subscribe {
    private $uCore;
    public $page_title,$page_html,$q_groups;
    private function get_groups() {
        //get all groups
        if(!$this->q_groups=$this->uCore->query("uSubscr","SELECT
        `gr_id`,
        `gr_title`
        FROM
        `u235_groups`
        WHERE
        (`status` IS NULL OR `status`='active') AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(6);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        $this->get_groups();
	 }
}
$uSubscr=new uSubscr_subscribe($this);

//$this->uFunc->incJs(u_sroot.'js/u235/uString.js');
$this->uFunc->incJs(u_sroot.'uSubscr/js/'.$this->page_name.'.js');
ob_start();?>

<div class="uSubscr">
    <h1 class="page-header"><?=$this->page['page_title']?></h1>

    <div class="row">
        <div class="col-md-6">
            <?while($gr=$uSubscr->q_groups->fetch_object()) {?>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="uSubscr_gr_id_<?=$gr->gr_id?>">
                        <span><?=uString::sql2text($gr->gr_title)?></span>
                    </label>
                </div>
                <?}?>
            <p class="text-muted">Выберите новости, на которые хотите подписаться</p>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label>Ваше имя:</label>
                <input type="text" placeholder="Иванов Иван" class="form-control" id="uSubscr_user_name">
            </div>
            <div class="form-group">
                <label>Ваш email:</label>
                <input type="text" placeholder="my@email.com" class="form-control" id="uSubscr_user_email">
            </div>
            <button type="button" class="btn btn-primary" onclick="uSubscr.form_submit()">Подписаться</button>
        </div>
    </div>

    <script type="text/javascript">
        if(typeof uSubscr==="undefined") {
            uSubscr = {};
            uSubscr.gr_id = [];
        }
    <?mysqli_data_seek($uSubscr->q_groups,0);
    for($i=0;$gr=$uSubscr->q_groups->fetch_object();$i++) {?>
    uSubscr.gr_id[<?=$i?>]=<?=$gr->gr_id?>;
    <?}?>
    </script>


</div>

<?$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/u235/template.php";

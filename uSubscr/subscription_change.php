<?php
class uSubscr_subscription_change {
    private $uCore;
    public $user_id,$user_name,$hash,$page_title,$page_html,$q_groups,$assigned_groups_ar,$err;
    private function err() {
        $this->err=true;

        return false;
    }
    private function check_data() {
        if(!isset($this->uCore->url_prop[1],$this->uCore->url_prop[2])) return $this->err();
        $this->user_id=$this->uCore->url_prop[1];
        $this->hash=$this->uCore->url_prop[2];

        if(!uString::isDigits($this->user_id)) return $this->err();
        if(!uString::isHash($this->hash)) return $this->err();

        //delete old hashes
        $hash_lifetime=1296000;//15 days
        if(!$this->uCore->query("uSubscr","DELETE FROM
        `u235_mailing_changes_appr`
        WHERE
        `timestamp`<'".(time()-$hash_lifetime)."'
        ")) $this->uCore->error(1);

        //check hash
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `user_id`
        FROM
        `u235_mailing_changes_appr`
        WHERE
        `user_id`='".$this->user_id."' AND
        `hash`='".$this->hash."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(2);
        if(!mysqli_num_rows($query)) {
            if(!$query=$this->uCore->query("uSubscr","SELECT
            `user_id`
            FROM
            `u235_mailing_results`
            WHERE
            `user_id`='".$this->user_id."' AND
            `hash`='".$this->hash."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(3);
            if(!mysqli_num_rows($query)) return $this->err();
        }

        return true;
    }
    private function get_user() {
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `user_name`
        FROM
        `u235_users`
        WHERE
        `user_id`='".$this->user_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
        if(!mysqli_num_rows($query)) return $this->err();
        $user=$query->fetch_object();
        $this->user_name=uString::sql2text($user->user_name);
    }
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
        ")) $this->uCore->error(5);

        //get assigned groups
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `gr_id`
        FROM
        `u235_users_groups`
        WHERE
        `user_id`='".$this->user_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(6);
        while($gr=$query->fetch_object()) {
            $this->assigned_groups_ar[$gr->gr_id]=1;
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->err=false;

        if($this->check_data()) {
            $this->get_user();
            $this->get_groups();
        }
	 }
}
$uSubscr=new uSubscr_subscription_change($this);

//$this->uFunc->incJs(u_sroot.'js/u235/uString.js');
$this->uFunc->incJs(u_sroot.'uSubscr/js/'.$this->page_name.'.js');
ob_start();?>

<div class="uSubscr"><?if(!$uSubscr->err) {?>
    <h1 class="page-header"><?=$this->page['page_title']?></h1>

    <div class="row">
        <div class="col-md-6">
            <?while($gr=$uSubscr->q_groups->fetch_object()) {?>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="uSubscr_gr_id_<?=$gr->gr_id?>" <?=isset($uSubscr->assigned_groups_ar[$gr->gr_id])?' checked ':''?>>
                         <span><?=uString::sql2text($gr->gr_title)?></span>
                    </label>
                </div>
                <?}?>
            <p class="text-muted">Выберите новости, которые хотите получать</p>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label>Ваше имя:</label>
                <input type="text" class="form-control" id="uSubscr_user_name" value="<?=$uSubscr->user_name?>">
            </div>
            <button type="button" class="btn btn-primary" onclick="uSubscr.form_submit()">Сохранить</button>
            <button type="button" class="btn btn-danger" onclick="uSubscr.form_unsubscribe()">Отписаться от всех новостей!</button>
        </div>
    </div>

    <script type="text/javascript">
        if(typeof uSubscr==="undefined") {
            uSubscr={};
            uSubscr.gr_id=[];
        }

    <?mysqli_data_seek($uSubscr->q_groups,0);
    for($i=0;$gr=$uSubscr->q_groups->fetch_object();$i++) {?>
    uSubscr.gr_id[<?=$i?>]=<?=$gr->gr_id?>;
    <?}?>
        uSubscr.user_id=<?=$uSubscr->user_id?>;
        uSubscr.hash="<?=$uSubscr->hash?>";
    </script>
<?}
    else {?>
        <h1 class="page-header">Страница не найдена</h1>
        <p>Такой страницы не существует.</p>
        <p>Возможно ссылка просто устарела.</p>
    <?}?>
</div>

<?$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/u235/template.php";

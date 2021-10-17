<?php
require_once "processors/classes/uFunc.php";
require_once "uSupport/classes/common.php";

class uSup_request_change_com_id_bg {
    public $uSup;
    public $uFunc;
    public $com_title;
    public $request_title;
    private $uCore,
        $link_lifetime,$com,
        $user_id,$com_id,$hash;
    public $tic_id,$when;
    private function error($err) {
        $this->uCore->page_content='<h1>Ошибка '.$err.'</h1><p>Ссылка устарела.<br>Если вы получили письмо только что, то обратитесь к администрации сайта.</p>';
        include "templates/template.php";
        die();
    }
    private function check_data() {
        if(!isset(
        $this->uCore->url_prop[1],
        $this->uCore->url_prop[2],
        $this->uCore->url_prop[3],
        $this->uCore->url_prop[4],
        $this->uCore->url_prop[5]
        )) $this->uFunc->error(10);

        $this->user_id=$this->uCore->url_prop[1];
        $this->tic_id=$this->uCore->url_prop[2];
        $this->com_id=$this->uCore->url_prop[3];
        $this->hash=$this->uCore->url_prop[4];
        $this->when=$this->uCore->url_prop[5];

        if(!uString::isDigits($this->user_id)) $this->error(20);
        if(!uString::isDigits($this->tic_id)) $this->error(30);
        if(!uString::isDigits($this->com_id)) $this->error(40);
        if(!uString::isHash($this->hash)) $this->error(50);
        if($this->when!='default') $this->when='now';
    }
    private function delete_old_request_change_links() {
        if(!$query=$this->uCore->query("uSup","DELETE FROM
        `u235_requests_change_com_links`
        WHERE
        `timestamp`<'".(time()-$this->link_lifetime)."'
        ")) $this->uFunc->error(60);
    }
    private function check_request_change_link() {
        //delete old records
        $this->delete_old_request_change_links();
        //check if current record exists
        if(!$query=$this->uCore->query("uSup","SELECT
        `tic_id`
        FROM
        `u235_requests_change_com_links`
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `com_id`='".$this->com_id."' AND
        `user_id`='".$this->user_id."' AND
        `hash`='".$this->hash."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(70);
        if(mysqli_num_rows($query)) return true;
        return false;
    }
    private function change_req_com() {
        //get old com_id
        if(!$request_info=$this->uSup->req_id2info($this->tic_id,"company_id,escalated,tic_subject")) return 0;
        $com_id=(int)$request_info->company_id;
        $escalated=(int)$request_info->escalated;
        $this->request_title=uString::sql2text($request_info->tic_subject,1);

        //get old company info
        if(!$company_info=$this->uSup->com_id2com_info($com_id,"com_title,two_level")) return 0;
        $two_level=(int)$company_info->two_level;
        $this->com_title=uString::sql2text($company_info->com_title,1);

        //get new company info
        if(!$company_info=$this->uSup->com_id2com_info($this->com_id,"two_level")) return 0;
        $two_level_new=(int)$company_info->two_level;


        if(!$this->uCore->query("uSup","UPDATE
        `u235_requests`
        SET
        `company_id`='".$this->com_id."'
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(120);

        if($this->when=='default') {
            if(!$this->uCore->query("uSup","UPDATE
            `u235_com_users`
            SET
            `default_com`='1'
            WHERE
            `user_id`='".$this->user_id."' AND
            `com_id`='".$this->com_id."' AND
            `site_id`='".site_id."'
            ")) $this->uFunc->error(130);
        }


        if(!$escalated&&$two_level&&!$two_level_new) {//if request in old company was internal but new company has one level only - we should notify help desk operators
            $q_operators=$this->uSup->get_operators("firstname,email");
            while($oper=$q_operators->fetch(PDO::FETCH_OBJ)) {
                $this->uSup->new_msg_cons_notification($this->tic_id,$oper->email,$oper->firstname,site_id);
            }
            //update request to escalated
            $this->uSup->escalate_request(1,$this->tic_id,site_id);
        }
        elseif(!$two_level&&$two_level_new) {//if old company was not two level but new is two level - we should notify company's admins
            $q_com_admins_list=$this->uSup->get_com_admins_to_notify_about_requests("user_id",$this->com_id);
            $q_admin_ids=" (1=0";
            while($admin=$q_com_admins_list->fetch(PDO::FETCH_OBJ)) {
                $q_admin_ids.=" OR user_id=".$admin->user_id." ";
            }
            $q_admin_ids.=" )";

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT 
                        firstname,
                        email
                        FROM 
                        u235_users 
                        WHERE 
                        ".$q_admin_ids."
                        ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('140'/*.$e->getMessage()*/);}
            $com_admins=$stm;

            while($oper=$com_admins->fetch(PDO::FETCH_OBJ)) {
                $this->uSup->new_msg_cons_notification($this->tic_id,$oper->email,$oper->firstname,site_id);
            }
            //update request to not escalated
            $this->uSup->escalate_request(0,$this->tic_id,site_id);
        }

    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSup=new uSupport\common($this->uCore);
        $this->uFunc=new \processors\uFunc($this->uCore);

        $this->link_lifetime=7200;//2 hours

        $this->check_data();
        if($this->check_request_change_link()) $this->change_req_com();
    }
}
$uSup=new uSup_request_change_com_id_bg($this);
ob_start();?>

<h1>Копмания успешно изменена</h1>
<p>В запросе <a href="<?=u_sroot.$this->mod?>/request_show/<?=$uSup->tic_id?>"><?=$uSup->request_title?></a> компания изменена на компанию <?=$uSup->com_title?></p>
<?if($uSup->when=='default') {?>
<p>Ваша компания по умолчанию изменена на <?=$uSup->com_title?></p>
<?}?>

<?$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/template.php";

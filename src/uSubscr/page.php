<?php
class uSubscr_rec_editor{
    private $uCore,$m_id,$hash,$user_id;
    public $rec_title,$rec_html,$user_name,$error;
    private function checkData() {
        if(!isset($_GET['m_id'],$_GET['user_id'],$_GET['hash'])) return false;
        $this->m_id=$_GET['m_id'];
        $this->user_id=$_GET['user_id'];
        $this->hash=$_GET['hash'];

        if(!uString::isDigits($this->m_id)) return false;
        if(!uString::isDigits($this->user_id)) return false;
        if(!uString::isHash($this->hash)) return false;

        return true;
    }
    private function get_rec() {
        //get rec
        if(!$query=$this->uCore->query("uSubscr","SELECT DISTINCT
        `rec_title`,
        `rec_html`
        FROM
        `u235_records`,
        `u235_mailing`
        WHERE
        `m_id`='".$this->m_id."' AND
        `u235_mailing`.`rec_id`=`u235_records`.`rec_id` AND
        `u235_mailing`.`site_id`='".site_id."' AND
        `u235_records`.`site_id`='".site_id."'
        LIMIT 1
        ")) $this->uCore->error(1);
        if(!mysqli_num_rows($query)) return false;
        $rec=$query->fetch_object();

        $this->rec_title=uString::sql2text($rec->rec_title);
        $this->rec_html=uString::sql2text($rec->rec_html,true);

        //get user_name
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `user_name`
        FROM
        `u235_users`,
        `u235_mailing_results`
        WHERE
        `m_id`='".$this->m_id."' AND
        `hash`='".$this->hash."' AND
        `u235_mailing_results`.`user_id`='".$this->user_id."' AND
        `u235_mailing_results`.`site_id`='".site_id."' AND
        `u235_users`.`user_id`=`u235_mailing_results`.`user_id` AND
        `u235_users`.`site_id`='".site_id."'
        LIMIT 1
        ")) $this->uCore->error(2);
        if(!mysqli_num_rows($query)) return false;
        $user=$query->fetch_object();
        $this->user_name=uString::sql2text($user->user_name);

        $this->rec_html=str_replace('{user_name}',$user->user_name,$this->rec_html);

        return true;
    }
    private function update_state() {
        if(!$this->uCore->query("uSubscr","UPDATE
        `u235_mailing_results`
        SET
        `result`='read'
        WHERE
        `m_id`='".$this->m_id."' AND
        `user_id`='".$this->user_id."' AND
        `hash`='".$this->hash."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->error=false;

        if($this->checkData()) {
            if($this->get_rec()) {
                $this->update_state();
            }
            else $this->error=true;
        }
        else $this->error=true;
    }
}
$uSubscr=new uSubscr_rec_editor($this);
ob_start();

if(!$uSubscr->error) {
$this->uFunc->incCss(u_sroot.'uSubscr/css/default.min.css');

$this->page['page_title']=$uSubscr->rec_title;?>
    <h1 class="page-header"><?=$uSubscr->rec_title?></h1>
    <div><?=uString::sql2text($uSubscr->rec_html,true)?></div>

<?}
else {?>
    <h1 class="page-header">Страница не найдена</h1>
    <p>Такой страницы не существует</p>
<?}
$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/u235/template.php";

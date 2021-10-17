<?
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uSupport_new_request_get_id {
    public $uFunc;
    public $uSes;
    private $uCore, $tic_id;
    private function check_access() {
        //consultant or operator
        if($this->uCore->access(9)) return true;
        if($this->uCore->access(8)) return true;

        //check if user is client of any company
        if(!$query=$this->uCore->query("uSup","SELECT
        `user_id`
        FROM
        `u235_com_users`
        WHERE
        `user_id`='".$this->uSes->get_val("user_id")."' AND
        `site_id`='".site_id."'
        LIMIT 1
        ")) $this->uCore->error(3);
        if(mysqli_num_rows($query)) return true;

        //check if we can receive request from users not in companies
        if($this->uCore->uFunc->getConf("receive_only_from_comps_users","uSup")=='0') return true;

        return false;
    }
    private function make_tmp_req() {
        //Создаем пустой тикет. На случай, если пользователь будет грузить файл, мы уже будем знать id тикета, к которому прикреплять
        if(!$query=$this->uCore->query("uSup","SELECT
        `tic_id`
        FROM
        `u235_requests`
         WHERE
         `site_id`='".site_id."'
        ORDER BY
        `tic_id`
        DESC LIMIT 1")) $this->uCore->error(1);
        if(mysqli_num_rows($query)>0) {
            $qr=$query->fetch_object();
            $this->tic_id=$qr->tic_id+1;
        }
        else $this->tic_id=1;
        if(!$this->uCore->query("uSup","INSERT INTO
        `u235_requests` (
        `tic_id`,
        `tic_opened_timestamp`,
        `tic_status`,
        `company_id`,
        `user_id`,
        `site_id`
        ) VALUES (
        '".$this->tic_id."',
        '".time()."',
        'new',
        '0',
        '".$this->uSes->get_val("user_id")."',
        '".site_id."'
        )")) $this->uCore->error(2);
    }
    public function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        if(!$this->uCore->access(2)) die('{"status":"forbidden"}');
        if(!$this->check_access()) die('{"status":"forbidden"}');

        $this->make_tmp_req();

        $ses_hack['id']='0';
        $ses_hack['hash']='';

        if(isset($_POST['require_ses_hack'])) {
            if($_POST['require_ses_hack']=='1') {
                $ses_hack=$this->uCore->uFunc->sesHack();
            }
        }

        echo '{
        "status":"done",
        "tic_id":"'.$this->tic_id.'",
        "ses_hack_id":"'.$ses_hack['id'].'",
        "ses_hack_hash":"'.$ses_hack['hash'].'"
        }';
    }
}
$uSup=new uSupport_new_request_get_id($this);

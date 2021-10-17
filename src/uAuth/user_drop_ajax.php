<?
class uAuth_user_drop_ajax {
    private $uCore;
    private $status;
    function __construct(&$uCore) {
        $this->uCore=&$uCore;

        if(!$this->uCore->access(16)) die('forbidden');

        $this->checkData();
        $this->run();
    }
    private function checkData() {
        if(!isset($_POST['ids'],$_POST['action'])) $this->uCore->error(1);
        $action=&$_POST['action'];
        if($action=='delete') $this->status='banned';
        else $this->status='active';
    }
    private function update_pageStatus($ids_list) {
        if(!$this->uCore->query("uAuth","UPDATE
        `u235_usersinfo`
        SET
        `status`='".$this->status."'
        WHERE
        `site_id`='".site_id."' AND
        (".$ids_list.")
        ")) $this->uCore->error(2);
    }
    private function run() {
        $idsAr=explode("#", $_POST['ids']);
        $ids_count=count($idsAr);

        $ids_list='1=0';
        for($i=1;$i<$ids_count;$i++) {
            $cat_id=$idsAr[$i];
            if(!uString::isDigits($cat_id)) $this->uCore->error(3);
            $ids_list.=" OR `user_id`='".$cat_id."'";
        }
        //update status to new
        $this->update_pageStatus($ids_list);

        echo 'done';
    }
}
$uAuth=new uAuth_user_drop_ajax($this);

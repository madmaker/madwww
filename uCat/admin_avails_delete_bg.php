<?php
class uCat_admin_avails_delete_bg {
    private $uCore,$avail_id;

    private function check_data() {
        if(!isset($_POST['avail_id'])) $this->uCore->error(1);
        $this->avail_id=$_POST['avail_id'];
        if(!uString::isDigits($this->avail_id)) $this->uCore->error(2);

        $this->check_ifAvailIsUsed();
    }
    private function check_ifAvailIsUsed() {
        if(!$query=$this->uCore->query('uCat',"SELECT
        `item_avail`
        FROM
        `u235_items`
        WHERE
        `item_avail`='".$this->avail_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(2);
        if(mysqli_num_rows($query)) die("{'status' : 'error', 'msg' : 'avail_id_used'}");
    }
    private function update_availStatus() {
        if(!$this->uCore->query('uCat',"DELETE FROM
        `u235_items_avail_values`
        WHERE
        `avail_id`='".$this->avail_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(2);
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;

        if(!$this->uCore->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();

        //update status to new
        $this->update_availStatus();

        echo "{'status' : 'done'}";
    }
}
$uCat=new uCat_admin_avails_delete_bg ($this);

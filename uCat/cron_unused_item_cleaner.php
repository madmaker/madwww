<?php
class uCat_cron_unused_items_cleaner{
    private $uCore,
        $secret,$lifetime,
        $q_items_update;
    private function check_data() {
        if(!isset($_POST['uSecret'])) $this->uCore->error(1);
        if($this->secret!=$_POST['uSecret']) $this->uCore->error(2);
    }

    private function delete_item($item) {
        if(!$this->uCore->query("uCat","DELETE FROM
        `u235_items`
        WHERE
        `item_id`='".$item->item_id."' AND
        `site_id`='".$item->site_id."'
        ")) $this->uCore->error(3);

        if(!$this->uCore->query("uCat","DELETE FROM
        `u235_items_files`
        WHERE
        `item_id`='".$item->item_id."' AND
        `site_id`='".$item->site_id."'
        ")) $this->uCore->error(3);

        if(!$this->uCore->query("uCat","DELETE FROM
        `u235_items_pictures`
        WHERE
        `item_id`='".$item->item_id."' AND
        `site_id`='".$item->site_id."'
        ")) $this->uCore->error(3);

        uFunc::rmdir($this->uCore->mod.'/items_files/'.$item->site_id.'/'.$item->item_id);
        uFunc::rmdir($this->uCore->mod.'/item_avatars/'.$item->site_id.'/'.$item->item_id);
        uFunc::rmdir($this->uCore->mod.'/item_pictures/'.$item->site_id.'/'.$item->item_id);
    }
    private function update_items_usage_check_timestamp() {
        if(!$query=$this->uCore->query("uCat","UPDATE
            `u235_items`
            SET
            `usage_check_timestamp`='".time()."'
            WHERE
            ".$this->q_items_update
        )) $this->uCore->error(4);
        //echo $this->q_items_update;
    }
    private function check_items($item) {
        if(!$query=$this->uCore->query("uCat","SELECT
        `item_id`
        FROM
        `u235_cats_items`
        WHERE
        `item_id`='".$item->item_id."' AND
        `site_id`='".$item->site_id."'
        ")) $this->uCore->error(5);
        if(!mysqli_num_rows($query)) {//item is UNused
            //echo 'item:'.$item->item_id.'. site:'.$item->site_id.'<br>';
            $this->delete_item($item);
        }
    }
    private function get_items() {
        //get last 100 items
        if(!$query=$this->uCore->query("uCat","SELECT
        `item_id`,
        `site_id`
        FROM
        `u235_items`
        WHERE
        usage_check_timestamp<".(time()-$this->recheck_time)."
        ORDER BY
        `usage_check_timestamp` ASC
        LIMIT 100
        ")) $this->uCore->error(6);
        while($item=$query->fetch_object()) {
            //echo 'item:'.$item->item_id.'. site:'.$item->site_id.'<br>'; exit;
            if(time()>($this->startime+$this->lifetime)) {
                $this->update_items_usage_check_timestamp();
                exit;
            }
            $this->check_items($item);
            if(isset($this->q_items_update)) $this->q_items_update.=' OR ';
            else $this->q_items_update='';

            $this->q_items_update.=" (`item_id`='".$item->item_id."' AND `site_id`='".$item->site_id."') ";
        }
        if(!mysqli_num_rows($query)) exit;
        //echo '<h1>one more time</h1>';
        $this->get_items();
    }
    private function clean_items() {
        $this->get_items();
        $this->update_items_usage_check_timestamp();
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;

        $this->secret='JKLkljsdf789ljk12jkl1230987897alkjlklsdf832k2js72ioLKs72jkkjsdfkljsdfkj2kjslk';
        $this->startime=time();
        $this->lifetime=20;//20 seconds
        $this->recheck_time=1209600;//14 days

        $this->check_data();

        $this->clean_items();
    }
}
$uCat=new uCat_cron_unused_items_cleaner($this);

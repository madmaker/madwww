<?php
require_once 'uPage/inc/common.php';
class uPage_admin_delete_row {
    private $uCore,$row_id,$page_id;
    private function check_data() {
        if(!isset($_POST['row_id'])) $this->uCore->error(1);
        $this->row_id=$_POST['row_id'];
        if(!uString::isDigits($this->row_id)) $this->uCore->error(2);

        include_once "uPage/inc/common.php";
        $uPage_common=new \uPage\common($this->uCore);
        $this->page_id=$uPage_common->get_page_id('row',$this->row_id);
    }
    private function delete_row() {
        if(!$this->uCore->query("uPage","DELETE FROM
        `u235_rows`
        WHERE
        `row_id`='".$this->row_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);

        //get row's cols
        if(!$query=$this->uCore->query("uPage","SELECT
        `col_id`
        FROM
        `u235_cols`
        WHERE
        `row_id`='".$this->row_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
        //delete row's cols
        if(!$this->uCore->query("uPage","DELETE FROM
        `u235_cols`
        WHERE
        `row_id`='".$this->row_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(5);

        while($col=$query->fetch_object()) {//delete all cols_els of this row
            if(!$this->uCore->query("uPage","DELETE FROM
            `u235_cols_els`
            WHERE
            `col_id`='".$col->col_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(6);
        }
    }
    private function clear_cache() {
        $this->uPage->clear_cache($this->page_id);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uPage=new \uPage\common($this->uCore);

        if(!$this->uCore->access(7)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->delete_row();
        $this->clear_cache();

        die('{
        "status":"done"
        }');
    }
}
$uPage=new uPage_admin_delete_row($this);

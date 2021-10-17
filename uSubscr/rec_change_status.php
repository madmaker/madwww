<?php
class uSubscr_rec_change_status {
    private $uCore,$field,$action,$executed_ids;

    private function checkData() {
        if(!isset($_POST['ids'],$_POST['action'])) $this->uCore->error(1);
        $this->action=$_POST['action'];
        if($this->action=='active'||$this->action=='deleted') $this->field='status';
        else $this->uCore->error(2);
    }
    private function make_query() {
        $idsAr=explode("#", $_POST['ids']);
        $ids_count=count($idsAr);

        $ids_list='1=0';
        $this->executed_ids='';
        for($i=1;$i<$ids_count;$i++) {
            $rec_id=$idsAr[$i];
            if(!uString::isDigits($rec_id)) $this->uCore->error(3);
            $ids_list.=" OR `rec_id`='".$rec_id."'";
            $this->executed_ids.="'rec_".$rec_id."':'1',";
        }
        $this->update_status($ids_list);
    }
    private function update_status($ids_list) {
        if(!$this->uCore->query('uSubscr',"UPDATE
        `u235_records`
        SET
        `".$this->field."`='".$this->action."',
        `timestamp`='".time()."'
        WHERE
        (".$ids_list.") AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(6);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(23)) die("{'status' : 'forbidden'}");

        $this->checkData();
        $this->make_query();
        echo "{
        'status' : 'done',
        ".$this->executed_ids."
        'field' : '".$this->field."',
        'action' : '".$this->action."'
        }";
    }
}
$uSubscr=new uSubscr_rec_change_status($this);

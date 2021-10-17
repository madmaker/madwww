<?php
class uSubscr_rec_edit_save {
    private $uCore,$rec_id,$field,$html;
    private function check_data() {
        if(!isset($_POST['rec_id'],$_POST['field'],$_POST['html'])) $this->uCore->error(1);
        if(!uString::isDigits($_POST['rec_id'])) $this->uCore->error(2);
        $this->rec_id=$_POST['rec_id'];

        $this->field=$_POST['field'];
        if($this->field!='rec_title'&&$this->field!='rec_html') $this->uCore->error(3);


        $_POST['html']=trim($_POST['html']);
        if($this->field=='rec_title'&&empty($_POST['html'])) die("{'status' : 'error', 'msg' : 'rec_title'}");

        $this->html=$_POST['html'];
    }
    private function update_rec() {
        if(!$this->uCore->query("uSubscr","UPDATE
        `u235_records`
        SET
        `".$this->field."`='".uString::text2sql($this->html)."'
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(23)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->update_rec();
        echo "{
        'status' : 'done',
        'rec_id' : '".$this->rec_id."',
        'html' : '".rawurlencode($this->html)."'
        }";
    }
}
new uSubscr_rec_edit_save($this);

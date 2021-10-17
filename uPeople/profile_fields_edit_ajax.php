<?php
class uPeople_profile_fields_edit_ajax {
    private $uCore,
    $field_id,$new_field_id,$field,$value;

    private function check_data() {
        if(!isset($_POST['field'],$_POST['value'],$_POST['field_id'])) $this->uCore->error(1);
        $this->field_id=&$_POST['field_id'];
        $this->field=&$_POST['field'];
        $this->value=$_POST['value'];

        if(!uString::isDigits($this->field_id)) $this->uCore->error(2);
    }
    private function set_data() {
        if($this->field=='label') {
            $this->value=uString::text2sql($this->value);
        }
        else if($this->field=='field_comment') {
            $this->value=uString::text2sql($this->value);
        }
        else if($this->field=='show_on_list') {
            if($this->value!='1')$this->value=0;
        }
        else if($this->field=='show_on_page') {
            if($this->value!='1')$this->value=0;
        }
        elseif($this->field=='field_type') {
            if(!uString::isDigits($this->value)) $this->uCore->error(3);
            if($this->value<1||$this->value>3) $this->uCore->error(4);
        }
        elseif($this->field=='sort') {
            if(!uString::isDigits($this->value)) {
                $_POST['value']=$this->value=0;
            }
        }
        else $this->uCore->error(5);
    }
    private function get_new_field_id() {
        $field_limit=1000;//field columns limit
        $field_type_id2sql[1]='tinytext';
        $field_type_id2sql[2]='longtext';
        $field_type_id2sql[3]='longtext';
        //get all site's fields for all sites
        if(!$query=$this->uCore->query("uPeople","SELECT DISTINCT
        `field_id`
        FROM
        `u235_fields`
        ")) $this->uCore->error(6);
        while($field=$query->fetch_object()) {
            $field_exists[$field->field_id]=true;
        }
        for($i=0;$i<$field_limit;$i++) {
            if(!isset($field_exists[$i])) {
                if(!$query=$this->uCore->query("uPeople","SHOW
                columns
                FROM
                `u235_people`
                WHERE
                field='field_".$i."'
                ")) $this->uCore->error(7);
                if(mysqli_num_rows($query)) {
                    $field=$query->fetch_object();
                    if($field_type_id2sql[$this->value]==$field->Type) {
                        $new_field_id=$i;
                        break;
                    }
                }
                else {
                    $new_field_id=-1;
                    break;
                }
            }
        }

        if(!isset($new_field_id)) die("{'status' : 'error', 'msg' : 'limit_exceeded'}");//user trying to make too many fields
        elseif($new_field_id<0) {//we have to make new column
            $new_field_id=$i;
            if(!$this->uCore->query("uPeople","ALTER TABLE
            `u235_people`
            ADD
            field_".$new_field_id."
            ".$field_type_id2sql[$this->value]."
            NULL")) $this->uCore->error(8);
        }
        return $new_field_id;
    }
    private function update_db() {
        if($this->field=='field_type') {
            $this->new_field_id=$this->get_new_field_id();
            if(!$this->uCore->query("uPeople","UPDATE
            `u235_fields`
            SET
            `".$this->field."`='".$this->value."',
            `field_id`='".$this->new_field_id."'
            WHERE
            `site_id`='".site_id."' AND
            `field_id`='".$this->field_id."'
            ")) $this->uCore->error(9);

            //update user's fields
            if(!$this->uCore->query("uPeople","UPDATE
            `u235_people`
            SET
            `field_".$this->new_field_id."`=`field_".$this->field_id."`
            WHERE
            `site_id` = '".site_id."'
            ")) $this->uCore->error(10);

            if(!$this->uCore->query("uPeople","UPDATE
            `u235_people`
            SET
            `field_".$this->field_id."`=''
            WHERE
            `site_id` = '".site_id."'
            ")) $this->uCore->error(11);
        }
        else {
            $this->new_field_id=0;
            if(!$this->uCore->query("uPeople","UPDATE
            `u235_fields`
            SET
            `".$this->field."`='".$this->value."'
            WHERE
            `site_id`='".site_id."' AND
            `field_id`='".$this->field_id."'
            ")) $this->uCore->error(12);
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(10)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->set_data();
        $this->update_db();

        echo "{
        'status' : 'done',
        'field_id' : '".$this->field_id."',
        'new_field_id' : '".$this->new_field_id."',
        'field':'".$this->field."',
        'value':'".rawurlencode($_POST['value'])."'
        }";
    }
}
$uPeople=new uPeople_profile_fields_edit_ajax($this);

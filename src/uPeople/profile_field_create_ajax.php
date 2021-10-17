<?
class uPeople_profile_field_create_ajax {
    private $uCore,
        $label,$sort,$field_type,$field_id;
    private function check_data() {
        if(!isset($_POST['label'],$_POST['sort'],$_POST['field_type'])) $this->uCore->error(1);

        $this->label=$_POST['label'];
        if(strlen($this->label)<2) die("{'status' : 'label'}");
        $this->label=uString::text2sql($this->label);

        $this->sort=$_POST['sort'];

        $this->field_type=$_POST['field_type'];

        if(empty($this->label)) die("{'status' : 'label'}");
        if(!uString::isDigits($this->sort)) $this->sort=0;
        if($this->field_type!='1'&&$this->field_type!='2'&&$this->field_type!='3') $this->uCore->error(2);
    }
    private function add_field() {
        $field_limit=1000;//field columns limit
        $field_type_id2sql[1]='tinytext';
        $field_type_id2sql[2]='longtext';
        $field_type_id2sql[3]='longtext';
        //get all site's fields
        if(!$query=$this->uCore->query("uPeople","SELECT
        `field_id`
        FROM
        `u235_fields`
        WHERE
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
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
                ")) $this->uCore->error(4);
                if(mysqli_num_rows($query)) {
                    $field=$query->fetch_object();
                    if($field_type_id2sql[$this->field_type]==$field->Type) {
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
            ".$field_type_id2sql[$this->field_type]."
            NULL")) $this->uCore->error(5);
        }
        $this->field_id=$new_field_id;


        //Create Field
        if(!$this->uCore->query('uPeople',"INSERT INTO `u235_fields` (
        `field_id`,
        `label`,
        `sort`,
        `field_type`,
        `site_id`
        ) VALUES (
        '".$this->field_id."',
        '".$this->label."',
        '".$this->sort."',
        '".$this->field_type."',
        '".site_id."'
        )")) $this->uCore->error(6);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(10)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->add_field();

        echo "{
        'status'        :'success',
        'field_id'       :'".$this->field_id."',
        'label'     :'".rawurlencode($this->label)."',
        'sort'    :'".$this->sort."',
        'field_type'      :'".$this->field_type."'
        }";
    }
}
$uPeople=new uPeople_profile_field_create_ajax($this);

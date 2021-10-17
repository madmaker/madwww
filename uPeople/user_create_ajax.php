<?
class uPeople_user_create_ajax {
    private $uCore,
        $firstname,$secondname,$lastname,$user_id;
    private function checkData() {
        if(!isset($_POST['firstname'],$_POST['secondname'],$_POST['lastname'])) $this->uCore->error(1);

        $this->firstname=$_POST['firstname'];
        if(strlen($this->firstname)<2) die("{'status' : 'firstname'}");
        $this->firstname=uString::text2sql($this->firstname);

        $this->secondname=$_POST['secondname'];
        $this->secondname=uString::text2sql($this->secondname);

        $this->lastname=$_POST['lastname'];
        $this->lastname=uString::text2sql($this->lastname);

        //Get last user id
        if(!$query=$this->uCore->query('uPeople',"SELECT
        `user_id`
        FROM
        `u235_people`
        ORDER BY
        `user_id`
        DESC LIMIT 1
        ")) $this->uCore->error(6);
        $qR=$query->fetch_object();
        if(mysqli_num_rows($query)>0) $this->user_id=(int)$qR->user_id+1;
        else $this->user_id=1;
    }
    private function addUser() {
        if(!$this->uCore->query('uPeople',"DELETE FROM
        `u235_people_groups`
        WHERE
        `user_id`='".$this->user_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(7);

        //Create User
        if(!$this->uCore->query('uPeople',"INSERT INTO `u235_people` (
        `user_id`,
        `firstname`,
        `secondname`,
        `lastname`,
        `site_id`
        ) VALUES (
        '".$this->user_id."',
        '".$this->firstname."',
        '".$this->secondname."',
        '".$this->lastname."',
        '".site_id."'
        )")) $this->uCore->error(8);
    }
    private function get_users_count() {
        if(!$query=$this->uCore->query("uPeople","SELECT
        COUNT(*)
        FROM
        `u235_people`
        WHERE
        (`status`='' OR `status` IS NULL) AND
        `site_id`='".site_id."'
        ")) $this->uCore->erro(9);
        $qr=$query->fetch_assoc();
        return $qr['COUNT(*)'];
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(10)) die("{'status' : 'forbidden'}");

        $this->checkData();
        $this->addUser();

        echo "{
        'status'        :'success'
        }";
    }
}
$uPeople=new uPeople_user_create_ajax($this);

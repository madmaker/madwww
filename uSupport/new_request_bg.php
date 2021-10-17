<?
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uSupport/classes/common.php";
require_once "uAuth/classes/common.php";

class uSup_new_request_admin {
    public $uFunc;
    public $uSes;
    public $uSup;
    public $uAuth;
    public $two_level;
    private $uCore,
        $tic_subject,$msg_text,$tic_id,$user_id,$com_id,
        $owner_name,$owner_email,$msg_id,
        $is_com_client,$is_com_admin,$is_consultant,$is_operator;
    private function check_data() {
        if(!isset($_POST['tic_subject'],$_POST['msg_text'],$_POST['tic_id'],$_POST['user_id'],$_POST['com_id'])) $this->uFunc->error(10);

        $this->tic_subject=uString::text2sql($_POST['tic_subject']);//convert subject to safe format
        $this->msg_text=uString::text2sql($_POST['msg_text']);//convert text to safe format
        $this->tic_id=$_POST['tic_id'];
        if(!uString::isDigits($this->tic_id)) $this->uFunc->error(20); //request id must be an integer
        if(!uString::isDigits($_POST['user_id'])) $this->uFunc->error(30); //user id must be an integer
        $this->user_id=(int)$_POST['user_id'];
        $this->com_id=$_POST['com_id'];
        if(!uString::isDigits($this->com_id)) $this->uFunc->error(40); //company id must be an integer
        $this->com_id=(int)$this->com_id;

        //check if company exists and get some comp info
        if($this->com_id) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uSup")->prepare("SELECT 
                two_level 
                FROM 
                u235_comps 
                WHERE
                com_id=:com_id AND
                site_id=:site_id
                ");
                $site_id = site_id;
                /** @noinspection PhpUndefinedMethodInspection */
                $stm->bindParam(':com_id', $this->com_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */
                $stm->execute();
            } catch (PDOException $e) {
                $this->uFunc->error('50'/*.$e->getMessage()*/);
            }
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            if (!$qr = $stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(60);
            $this->two_level = (int)$qr->two_level;
        }
        else {
            $this->two_level=0;
        }

    }
    private function check_access(){
        $this->is_com_client=$this->is_com_admin=$this->is_consultant=$this->is_operator=false;

        //consultant or operator
        if($this->uSes->access(9)) return $this->is_operator=true;
        if($this->uSes->access(8)) return $this->is_consultant=true;

        //if company isset: must be client of this company
        if($this->com_id) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uSup")->prepare("SELECT
                admin
                FROM
                u235_com_users
                WHERE
                user_id=:user_id AND
                com_id=:com_id AND
                site_id=:site_id
                ");
                $site_id = site_id;
                $user_id = $this->uSes->get_val("user_id");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            } catch (PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedMethodInspection */
            if ($com = $stm->fetch(PDO::FETCH_OBJ)) {
                if ((int)$com->admin) {//is admin of request's company
                    $this->is_com_admin = true;

                    //if current user is author
                    if((int)$this->user_id==(int)$this->uSes->get_val("user_id")) return 1;

                    //Company admin may create request only for users of this company
                    try {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $stm=$this->uFunc->pdo("uSup")->prepare("SELECT 
                        user_id 
                        FROM 
                        u235_com_users 
                        WHERE
                        user_id=:user_id AND
                        site_id=:site_id
                        ");
                        $site_id=site_id;
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                    }
                    catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}
                    if($stm->fetch(PDO::FETCH_OBJ)) return 1;

                    return 0;

                }
                else {//client of this company
                    $this->is_com_client = true;

                    //company client may open request only for himself
                    $this->user_id=(int)$this->uSes->get_val("user_id");
                    return 1;
//                    if((int)$this->user_id==(int)$this->uSes->get_val("user_id")) return 1;

                    return 0;//client makes request not for himself
                }
            }
            else return 0;//not user of this company
        }

        //Not operator, not consultant, not user of company

        if(!$this->user_id) $this->user_id=$this->uSes->get_val("user_id");
        if($this->user_id!=$this->uSes->get_val("user_id")) return 0;//current user is not author of request

        //check if we can receive request from users not in companies
        if(!(int)$this->uCore->uFunc->getConf("receive_only_from_comps_users","uSup")) return 1;

        return 0;//we can't proceed this request
    }
    private function check_if_tic_owner_exists() {
        //Check if user with that id is exists and not banned. Take user name
        if(!$user_data=$this->uAuth->user_id2user_data("firstname,lastname,email,status",$this->user_id)) return 0;
        if(!$usersinfo_data=$this->uAuth->user_id2usersinfo("user_id,status",$this->user_id)) return 0;

        if($user_data->status!=$usersinfo_data->status&&$user_data->status!="active") return 0;

        $this->owner_name=uString::sql2text($user_data->firstname.' '.$user_data->lastname,1);
        $this->owner_email=$user_data->email;

        return 1;
    }
    private function get_new_msg_id() {
        //Take the last msg id
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uSup","SELECT
        `msg_id`
        FROM
        `u235_msgs`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `msg_id` DESC
        LIMIT 1
        ")) $this->uFunc->error(120);
        if(mysqli_num_rows($query)>0) {
            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$query->fetch_object();
            $this->msg_id=$qr->msg_id+1;
        }
        else $this->msg_id=1;
    }

    private function write_req2db() {
        $escalated=1;

        if($this->two_level) {
            if($this->is_com_admin||$this->is_com_client) $escalated=0;
        }
        //Write out new request to db
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("UPDATE
            u235_requests
            SET
            company_id=:company_id,
            user_id=:user_id,
            cons_id=0,
            tic_opened_timestamp=:timestamp,
            tic_changed_timestamp=:timestamp,
            tic_subject=:tic_subject,
            tic_status='req_open',
            escalated=:escalated
            WHERE
            tic_id=:tic_id AND
            site_id=:site_id
            ");
            $timestamp=time();
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_subject', $this->tic_subject,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':escalated', $escalated,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':company_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $this->tic_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('130'/*.$e->getMessage()*/);}
    }
    private function write_new_msg2db() {
        //Write out new msg to db
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->query("uSup","INSERT INTO
        `u235_msgs` (
        `tic_id`,
        `msg_id`,
        `msg_text`,
        `msg_sender`,
        `msg_timestamp`,
        `msg_status`,
        `site_id`
        ) VALUES (
        '".$this->tic_id."',
        '".$this->msg_id."',
        '".$this->msg_text."',
        '".$this->user_id."',
        '".time()."',
        '1',
        '".site_id."'
        )")) $this->uFunc->error(140);

        //Set msg_id for files uploaded to new request
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->query("uSup","UPDATE
        `u235_msgs_files`
        SET
        `msg_id`='".$this->msg_id."'
        WHERE
        `msg_id`='0' AND
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(150);
    }

    private function send_notification($request_id) {
        //Send notification to request's author
        $this->uSup->request_is_received_notification($request_id,0,site_id);

        //send notification to consultants
        //get company two-level support config
        $two_level = 0;
        if($this->com_id) {
            if ($com_info = $this->uSup->com_id2com_info($this->com_id, "com_title,two_level"))
                $two_level = (int)$com_info->two_level;
        }


        if($two_level) {//Если 2-уровневая поддержка для этой компании.
            if($this->is_operator||$this->is_consultant) {//If current user is operator or consultant
                $admins = $this->uSup->get_operators("u235_users.user_id");
            }
            else {// Тогда отправлять надо админам, а не операторам
                $admins = $this->uSup->get_com_admins_to_notify_about_requests("user_id", $this->com_id);
            }
        }
        else {//Если 1-уровневая поддержка, то отправляем операторам
            $admins = $this->uSup->get_operators("u235_users.user_id");
        }

        /** @noinspection PhpUndefinedMethodInspection */
        while($qr=$admins->fetch(PDO::FETCH_OBJ)) {
            $user=$this->uAuth->user_id2user_data($qr->user_id,"firstname,secondname,email");
            $this->uSup->new_request_cons_notification($user->firstname,$user->secondname,$user->email,$request_id,site_id);
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uSup=new \uSupport\common($this->uCore);
        $this->uAuth=new \uAuth\common($this->uCore);

        $this->check_data();
        if(!$this->check_access()) die('{"status":"forbidden"}');

        $this->check_if_tic_owner_exists();

        $this->get_new_msg_id();

        $this->write_req2db();
        $this->write_new_msg2db();

        $this->uSup->make_msg_hash($this->msg_id,site_id);

        $this->send_notification($this->tic_id);

        echo '{"status":"done"}';
    }
}
new uSup_new_request_admin($this);
<?
require_once 'uAuth/inc/avatar.php';
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uSupport/classes/common.php";

class uSupport_new_msg_bg {
    public $uFunc;
    public $uSes;
    public $is_operator;
    public $is_consultant;
    public $is_com_admin;
    public $escalated;
    public $uSup;
    private $uCore,$user_id, $msg_id, $new_msg_id, $msg_text, $msg_hash, $tic_id, $company_id, $tic_subject, $tic_status, $author_name,$cons_id,$tic_author_id,$author_avatar_timestamp,$filelist,
        $support_email,$support_email_from,$use_smtp,$smtp_settings,
        $user_avatar;

    private function check_data(){
        if(!isset($_POST['msg_id'],$_POST['msg_text'])) $this->uFunc->error(10);
        if(!uString::isDigits($_POST['msg_id'])) $this->uFunc->error(20);
        $this->msg_id=&$_POST['msg_id'];
        $this->msg_text=$_POST['msg_text'];
    }

    private function check_access() {
        $this->is_operator=$this->is_consultant=$this->is_com_admin=0;

        $this->user_id=$this->uSes->get_val("user_id");

        //get tic_id of current msg
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uSup","SELECT
        `tic_id`
        FROM
        `u235_msgs`
        WHERE
        `msg_status`='0' AND
        `msg_id`='".$this->msg_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(30);
        if(!mysqli_num_rows($query)) return false;//message either is not found or it's status is not new
        /** @noinspection PhpUndefinedMethodInspection */
        $qr=$query->fetch_object();
        $this->tic_id=$qr->tic_id;

        //get company_id and other info of current request
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            company_id,
            tic_subject,
            tic_status,
            cons_id,
            user_id,
            escalated
            FROM
            u235_requests
            WHERE
            tic_id=:tic_id AND
            tic_status!='req_closed' AND
            tic_status!='case_closed' AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $this->tic_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        if(!$req=$stm->fetch(PDO::FETCH_OBJ)) return false;//request either is not found or status is closed

        $this->company_id=$req->company_id;
        $this->tic_subject=uString::sql2text($req->tic_subject);
        $this->cons_id=$req->cons_id;
        $this->tic_author_id=$req->user_id;
        $this->tic_status=$req->tic_status;
        $this->escalated=(int)$req->escalated;

        //consultant or operator
        if($this->uSes->access(9)) return $this->is_consultant=1;
        if($this->uSes->access(8)) return $this->is_operator=1;

        if($req->user_id==$this->uSes->get_val("user_id")) {//current user is author of msg
            if($this->company_id!='0') {
                //check if user is client of request's company
                /** @noinspection PhpUndefinedMethodInspection */
                if(!$query=$this->uCore->query("uSup","SELECT
                `user_id`
                FROM
                `u235_com_users`
                WHERE
                `user_id`='".$this->uSes->get_val("user_id")."' AND
                `com_id`='".$this->company_id."' AND
                `site_id`='".site_id."'
                LIMIT 1
                ")) $this->uFunc->error(50);
                if(mysqli_num_rows($query)) return true;
            }

            //check if we can receive request from users not in companies
            if($this->uCore->uFunc->getConf("receive_only_from_comps_users","uSup")=='0') return true;
        }
        else {
            if($this->company_id!='0') {
                //check if user is admin of request's company
                /** @noinspection PhpUndefinedMethodInspection */
                if(!$query=$this->uCore->query("uSup","SELECT
                `user_id`
                FROM
                `u235_com_users`
                WHERE
                `user_id`='".$this->uSes->get_val("user_id")."' AND
                `com_id`='".$this->company_id."' AND
                `admin`='1' AND
                `site_id`='".site_id."'
                LIMIT 1
                ")) $this->uFunc->error(60);
                if(mysqli_num_rows($query)) return $this->is_com_admin=1;
            }
        }

        return 0;
    }
    private function get_consultant_info() {
        //GET support consultant email
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT
            firstname,
            secondname,
            email
            FROM
            u235_users
            JOIN 
            u235_usersinfo
            ON
            u235_users.user_id=u235_usersinfo.user_id AND
            u235_usersinfo.status=u235_users.status
            WHERE
            u235_users.user_id=:user_id AND
            u235_users.status='active' AND
            u235_usersinfo.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->cons_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}

        return $stm;
    }
    private function get_admins_info() {
        //GET support com_admins emails
        $stm=$this->uSup->get_com_admins_to_notify_about_requests("user_id",$this->company_id);

        $q_user_id="(1=0 ";
        /** @noinspection PhpUndefinedMethodInspection */
        while($admin=$stm->fetch(PDO::FETCH_OBJ)) {
            $q_user_id.=" OR u235_users.user_id='".$admin->user_id."' ";
        }
        $q_user_id.=")";
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT DISTINCT
            firstname,
            secondname,
            email
            FROM
            u235_users
            JOIN 
            u235_usersinfo
            ON
            u235_users.user_id=u235_usersinfo.user_id AND
            u235_usersinfo.status=u235_users.status
            WHERE
            " .$q_user_id. "  AND 
            u235_users.status='active' AND
            u235_usersinfo.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/);}

        return $stm;
    }

    private function get_new_msg_id(){
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uSup","SELECT
        `msg_id`
        FROM
        `u235_msgs`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `msg_id` DESC
        LIMIT 1")) $this->uFunc->error(130);
        if(mysqli_num_rows($query)>0) {
            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$query->fetch_object();
            $this->new_msg_id=$qr->msg_id+1;
        }
        else $this->new_msg_id=1;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("INSERT INTO
            u235_msgs (
            tic_id,
            msg_id,
            msg_sender,
            msg_timestamp,
            msg_status,
            site_id
            ) VALUES (
            :tic_id,
            :msg_id,
            :msg_sender,
            :msg_timestamp,
            0,
            :site_id
            )");
            $site_id=site_id;
            $msg_sender=$this->uSes->get_val("user_id");
            $msg_timestamp=time();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $this->tic_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_id', $this->new_msg_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_sender', $msg_sender,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_timestamp', $msg_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('140'/*.$e->getMessage()*/);}
    }

    private function saveMsg(){
        //Записываем в БД новое сообщение
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->query("uSup","UPDATE `u235_msgs` SET
        `msg_text`='".uString::text2sql($this->msg_text)."',
        `msg_sender`='".$this->user_id."',
        `msg_timestamp`='".time()."',
        `msg_status`='1'
        WHERE
        `msg_id`='".$this->msg_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(145);
    }
    private function updateReqStatus() {
        if($this->is_consultant||$this->is_operator||($this->is_com_admin&&$this->tic_author_id!=$this->uSes->get_val("user_id"))) {//consultant or operator or com_admin (and not an author of request)
            if(strpos($this->tic_status,'req')===0) $status='req_answered';
            else $status='case_answered';
        }
        else {
            if(strpos($this->tic_status,'req')===0) $status='req_open';
            else $status='case_open';
        }

        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->query("uSup","UPDATE
        `u235_requests`
        SET
        `tic_status`='".$status."',
        `tic_changed_timestamp`='".time()."'
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(150);
    }
    public function getMsgFiles($msg_id) {
        //Достаём список файлов, прикреплённых к сообщению
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uSup","SELECT
        `file_id`,
        `filename`,
        `file_size`,
        `file_mime`,
        `timestamp`
        FROM
        `u235_msgs_files`
        WHERE
        `msg_id`='".$msg_id."' AND
        `site_id`='".site_id."'
        ORDER BY
        `file_id` ASC
        ")) $this->uFunc->error(160);

        $files2email_html='';
        if(mysqli_num_rows($query)>0) {
            $files2email_html.='<p><strong>Прикрепленные файлы:</strong></p>
            <div class="files">';
            /** @noinspection PhpUndefinedMethodInspection */
            while($files=$query->fetch_object()) {
                if(!strpos('_'.$files->file_mime,'image')) {
                    $filename=uString::sql2text($files->filename);
                    $files2email_html.='<a href="'.u_sroot.'uSupport/file/'.$files->file_id.'" target="_blank">'.$filename.'</a><br>';
                }
            }
            $files2email_html.='</div>
            <div class="images">';
            mysqli_data_seek($query,0);
            /** @noinspection PhpUndefinedMethodInspection */
            while($files=$query->fetch_object()) {
                if(strpos('_'.$files->file_mime,'image')) {
                    $filename=uString::sql2text($files->filename);
                    $files2email_html.='<a href="'.u_sroot.'uSupport/file/'.$files->file_id.'/img.jpg?'.$files->timestamp.'" title="'.$filename.'"><img src="'.u_sroot.'uSupport/file/'.$files->file_id.'/sm/'.$this->msg_id.'/'.$this->msg_hash.'/'.$files->timestamp.'.jpg" alt="'.$filename.'" class="img-thumbnail" />
                    </a>&nbsp;';
                }
            }
            $files2email_html.='</div>';
        }

        $this->filelist='';
        if(mysqli_num_rows($query)>0) {
            mysqli_data_seek($query,0);
            $this->filelist.='<div class="files">
                    <h4>Прикрепленные файлы:</h4>
                    <ul class="files">';
            /** @noinspection PhpUndefinedMethodInspection */
            while($files=$query->fetch_object()) {
                if(!strpos('_'.$files->file_mime,'image')) {
                    $filename=uString::sql2text($files->filename);
                    $this->filelist.='<li title="'.$filename.', '.$files->file_size.' байт" class="uTooltip">
                                    <a href="'.u_sroot.'uSupport/file/'.$files->file_id.'" target="_blank" class="img-thumbnail">'.$filename.'</a>
                                    <a href="'.u_sroot.'uSupport/file/'.$files->file_id.'?download" class="btn btn-link uTooltip" title="Скачать файл"><span class="glyphicon glyphicon-download"></span></a>
                                </li>';
                }
            }
            $this->filelist.='</ul>
                    <ul class="images">';
            mysqli_data_seek($query,0);
            /** @noinspection PhpUndefinedMethodInspection */
            while($files=$query->fetch_object()) {
                if(strpos('_'.$files->file_mime,'image')) {
                    $filename=uString::sql2text($files->filename);
                    $this->filelist.='<li title="'.$filename.', '.$files->file_size.' байт" class="uTooltip">
                                    <a class="fancybox" rel="gallery1" href="'.u_sroot.'uSupport/file/'.$files->file_id.'/img.jpg?'.$files->timestamp.'" title="'.$filename.'">
                                        <img src="'.u_sroot.'uSupport/file/'.$files->file_id.'/sm?'.$files->timestamp.'" alt="'.$filename.'" class="img-thumbnail" />
                                    </a>
                                </li>';
                }
            }
            $this->filelist.='</ul>
                </div>';
        }
        return $files2email_html;
    }

    private function sendInvoice($request_id,$company_id) {
        if($this->is_operator||$this->is_consultant||($this->is_com_admin&&$this->tic_author_id!=$this->uSes->get_val("user_id"))) {//if message is from consultant
            $this->uSup->new_msg_author_notification($request_id,site_id);
        }
        else {//if message from client
            //get company info
            if($qr = $this->uSup->com_id2com_info($company_id,"com_title,two_level",site_id)) $two_level=(int)$qr->two_level;
            else $two_level=0;

            if($this->escalated||!$two_level) {//request is escalated to company
                if($this->cons_id=='0') $stm=$this->uSup->get_operators("firstname,secondname,email",site_id);
                else $stm=$this->get_consultant_info();
            }
            else {//request is still internal
                $stm=$this->get_admins_info();
            }

            /** @noinspection PhpUndefinedMethodInspection */
            while($suOper=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->uSup->new_msg_cons_notification($request_id,$suOper->email,$suOper->firstname,site_id);
            }
        }

    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uSup=new uSupport\common($this->uCore);

        $this->check_data();
        if(!$this->check_access()) die('{"status":"forbidden"}');

        $this->support_email=$this->uCore->uFunc->getConf("support_email","uSup");
        $this->support_email_from=$this->uCore->uFunc->getConf("support_email_fromname","uSup");
        $this->use_smtp=$this->uCore->uFunc->getConf('smtp_use_madwww_server','uSup')=='0';
        if($this->use_smtp) {
            //port
            //server_name
            //use_ssl
            //user_name
            //password
            $this->smtp_settings['server_name']=$this->uCore->uFunc->getConf('smtp_server_name','uSup');
            $this->smtp_settings['port']=$this->uCore->uFunc->getConf('smtp_port','uSup');
            $this->smtp_settings['user_name']=$this->uCore->uFunc->getConf('smtp_user_name','uSup');
            $this->smtp_settings['password']=$this->uCore->uFunc->getConf('smtp_password','uSup');
            $this->smtp_settings['use_ssl']=$this->uCore->uFunc->getConf('smtp_use_ssl','uSup')=='1';
        }
        else $this->smtp_settings[0]=0;

        $this->saveMsg();
        $this->updateReqStatus();
        $this->msg_hash=$this->uSup->make_msg_hash($this->msg_id);
        $this->sendInvoice($this->tic_id,$this->company_id);
        $this->getMsgFiles($this->msg_id);
        $this->get_new_msg_id();

        $this->user_avatar=new uAuth_avatar($this->uCore);
        $author_avatar=$this->user_avatar->get_avatar('uSup_com_users_list',$this->uSes->get_val("user_id"),$this->author_avatar_timestamp);

        echo '{
        "status":"done",
        "author_name":"'.rawurlencode($this->author_name).'",
        "author_avatar_src":"'.rawurlencode($author_avatar).'",
        "msg_text":"'.rawurlencode(nl2br($this->msg_text)).'",
        "msg_time":"'.date('d.m.Y H:i',time()).'",
        "filelist":"'.rawurlencode($this->filelist).'",
        "new_msg_id":"'.$this->new_msg_id.'"
        }';
    }
}

new uSupport_new_msg_bg($this);
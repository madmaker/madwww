<?php
class uSubscr_new_user{
    private $uCore,$user_name,$user_email,$user_id;
    private function check() {
        if(!isset($_POST['user_name'],$_POST['user_email'])) $this->uCore->error(1);
        $this->user_name=$_POST['user_name'];
        $this->user_email=trim($_POST['user_email']);

        if(!uString::isEmail($this->user_email)) die("{'status' : 'error', 'msg' : 'email'}");

        //check if email exists
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `user_id`
        FROM
        `u235_users`
        WHERE
        `user_email`='".$this->user_email."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(2);
        if(mysqli_num_rows($query)) die("{'status' : 'error', 'msg' : 'email_exists'}");
    }
    private function check_quota() {
        //get quota
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `site_user_limit`,
        `users_count`
        FROM
        `u235_limits`
        WHERE
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
        if(!mysqli_num_rows($query)) {
            if(!$query=$this->uCore->query("uSubscr","INSERT INTO
            `u235_limits` (
            `site_user_limit`,
            `users_count`,
            `site_id`
            ) VALUES (
            '50',
            '0',
            '".site_id."'
            )
            ")) $this->uCore->error(3);

            if(!$query=$this->uCore->query("uSubscr","SELECT
        `site_user_limit`,
        `users_count`
        FROM
        `u235_limits`
        WHERE
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
        }
        $quota=$query->fetch_object();

        if($quota->site_user_limit<=$quota->users_count&&$quota->site_user_limit!='0') {//limit exceeded
            //get last user created timestamp
            if(!$query=$this->uCore->query("uSubscr","SELECT
            `timestamp`
            FROM
            `u235_users`
            WHERE
            `site_id`='".site_id."'
            ORDER BY
            `timestamp` ASC
            LIMIT 1
            ")) $this->uCore->error(4);
            if(mysqli_num_rows($query)) {
                $qr=$query->fetch_object();
                $timestamp=$qr->timestamp;
            }
            else $timestamp=0;

            //limits (users - days after first created)
            //500-0-10
            //1000-10-30
            //5000-30-60
            //50000-60-90
            //100000-90-120
            //0-120-n

            $days_gone=time()-$timestamp;
            $days_gone=$days_gone/24/60/60;

            if($days_gone<30&&$days_gone>=10) $limit=1000;
            elseif($days_gone<60&&$days_gone>=30) $limit=5000;
            elseif($days_gone<90&&$days_gone>=60) $limit=50000;
            elseif($days_gone<120&&$days_gone>=90) $limit=100000;
            elseif($days_gone>=120) $limit=0;
            else $limit=500;//less than 10 days

            if($quota->site_user_limit<$limit) {//we can extend limit
                //set new limit
                if(!$query=$this->uCore->query("uSubscr","UPDATE
                `u235_limits`
                SET
                `site_user_limit`='".$limit."'
                WHERE
                `site_id`='".site_id."'
                ")) $this->uCore->error(5);
            }
            else {//we can't extend limit
                die("{'status' : 'error', 'msg' : 'limit_exceeded'}");
            }
        }
    }
    private function get_new_user_id() {
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `user_id`
        FROM
        `u235_users`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `user_id` DESC
        LIMIT 1
        ")) $this->uCore->error(6);
        if(mysqli_num_rows($query)) {
            $qr=$query->fetch_object();
            $this->user_id=$qr->user_id+1;
        }
        else $this->user_id=1;
    }
    private function write2db() {
        if(!$this->uCore->query("uSubscr","INSERT INTO
        `u235_users` (
        `user_id`,
        `user_name`,
        `user_email`,
        `admin_made`,
        `timestamp`,
        `site_id`
        ) VALUES (
        '".$this->user_id."',
        '".uString::text2sql($this->user_name)."',
        '".$this->user_email."',
        '1',
        '".time()."',
        '".site_id."'
        )
        ")) $this->uCore->error(7);

        //get total user's number
        if(!$query=$this->uCore->query("uSubscr","SELECT
        COUNT(`user_id`)
        FROM
        `u235_users`
        WHERE
        `admin_made`='1' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(8);
        $qr=$query->fetch_assoc();

        //update total user's count
        if(!$this->uCore->query("uSubscr","UPDATE
        `u235_limits`
        SET
        `users_count`='".$qr["COUNT(`user_id`)"]."'
        ")) $this->uCore->error(9);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(23)) die("{'status' : 'forbidden'}");

        $this->check();
        $this->check_quota();

        $this->get_new_user_id();

        $this->write2db();

        echo  "{
        'status' : 'done',
        'user_id' : '".$this->user_id."',
        'user_name':'".rawurlencode($this->user_name)."',
        'user_email':'".rawurlencode($this->user_email)."'
        }";
    }
}
$uSubscr=new uSubscr_new_user($this);

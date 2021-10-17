<?php
class uSubscr_users_importer {
    private $uCore,$site_id;
    private function check_data() {
        if(file_exists('uSubscr/inc/import.csv')) return 1;
        return 0;
    }
    private function check_if_user_exists($email) {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `user_id`
        FROM
        `u235_users`
        WHERE
        `user_email`='".$email."' AND
        `site_id`='".$this->site_id."'
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(901);
        return mysqli_num_rows($query);
    }
    private function get_new_user_id() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `user_id`
        FROM
        `u235_users`
        WHERE
        `site_id`='".$this->site_id."'
        ORDER BY
        `user_id` DESC
        LIMIT 1
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(902);
        if(mysqli_num_rows($query)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$query->fetch_object();
            return $qr->user_id+1;
        }
        return 1;
    }
    private function add_user($user,$email) {
        $user_id=$this->get_new_user_id();
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->query("uSubscr","INSERT INTO
        `u235_users` (
        `user_id`,
        `user_name`,
        `user_email`,
        `unsubscribed`,
        `subscription_after_approve`,
        `timestamp`,
        `admin_made`,
        `site_id`
        ) VALUES (
        '".$user_id."',
        '".uString::text2sql($user)."',
        '".$email."',
        '0',
        '0',
        '".time()."',
        '1',
        '".$this->site_id."'
        )
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(903);
    }
    private function read_file($test=0) {
        $file_contents=file_get_contents('uSubscr/inc/import.csv');
        $line_by_line=explode("\n",$file_contents);
        for($skiped=$added=$i=0;$i<count($line_by_line);$i++) {
            $user_ar=explode(',',$line_by_line[$i]);
//            $user=trim($user_ar[0]).' '.trim($user_ar[1]);//Если имя получателя в 1-й и 2-й колонке
//            $email=trim($user_ar[2]);//Если email в 3-й колонке
            $user=trim($user_ar[0]);//Если имя получателя в 1-й колонке
            $email=trim($user_ar[1]);//Если email во 2-й колонке
            if($test) echo '<p><b>user</b>: '.$user.' <b>email</b>: '.$email.'</p>';
            if(uString::isEmail($email)) {
                if(!$this->check_if_user_exists($email)) {
                    if($test) echo '<p class="text-success">ADDED</p>';
                    $added++;
                    if(!$test) $this->add_user($user,$email);
                }
                else {
                    $skiped++;
                    if($test) echo '<p class="text-danger">SKIPED</p>';
                }
            }
            else {
                if($test) echo '<h1 class="bg-danger">NOT AN EMAIL</h1>';
            }
        }
        echo '<div class="bs-callout bs-callout-primary">
        <p class="text-success">Добавлено: '.$added.'</p>
        <p class="text-danger">Пропущено: '.$skiped.'</p>
        </div>';
    }

    private function trim_users_names_and_emails() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `user_id`,
        `user_name`,
        `user_email`,
        `site_id`
        FROM
        `u235_users`
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(904);
        /** @noinspection PhpUndefinedMethodInspection */
        while($user=$query->fetch_object()) {
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->uCore->query("uSubscr","UPDATE
            `u235_users`
            SET
            `user_name`='".trim($user->user_name)."',
            `user_email`='".trim($user->user_email)."'
            WHERE
            `user_id`='".$user->user_id."' AND
            `site_id`='".$user->site_id."'
            ")) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(905);
        }
    }

    private function select_users($start_id,$site_id) {
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `user_id`
        FROM
        `u235_users`
        WHERE
        `user_id`>".$start_id." AND
        `site_id`='".$site_id."'
        ")) $this->uCore->error(906);
        return $query;
    }
    private function add_user2group($user_id,$gr_id,$site_id) {
        //check if user is not added yet
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `user_id`
        FROM
        `u235_users_groups`
        WHERE
        `user_id`='".$user_id."' AND
        `gr_id`='".$gr_id."' AND
        `site_id`='".$site_id."'
        ")) $this->uCore->error(907);
        if(!mysqli_num_rows($query)) {
            if(!$this->test_mode) {
                if(!$this->uCore->query("uSubscr","INSERT INTO
                `u235_users_groups` (
                `user_id`,
                `gr_id`,
                `site_id`
                ) VALUES (
                '".$user_id."',
                '".$gr_id."',
                '".$site_id."'
                )
                ")) $this->uCore->error(908);
            }
            echo '<p class="text-success">Добавлен '.$user_id.' в '.$gr_id.' на сайте '.$site_id.'</p>';
            return 1;
        }
        else echo '<p class="text-danger">ПРОПУЩЕН '.$user_id.' в '.$gr_id.' на сайте '.$site_id.'</p>';
        return 0;
    }
    private function add2groups($start_user_id,$gr_id,$site_id) {
        //select users
        $added=$skiped=0;
        $q_users=$this->select_users($start_user_id,$site_id);
        while($user=$q_users->fetch_object()) {
            if($this->add_user2group($user->user_id,$gr_id,$site_id)) $added++;
            else $skiped++;
        }
        echo '<div class="bs-callout bs-callout-primary">
        <h3>Пользователи успешно добавлены в группы</h3>
        <p>Добавлено: '.$added.'</p>
        <p>Пропущено: '.$skiped.'</p>
        </div>';
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        $this->test_mode=1;//1 - отрабатывает без добавления в базу, 1 - добавляет в базу

        $this->site_id=4;

        $add2group_start_user_id=3996;//Пользователь, с которого начинать добавлять в группу
        $add2group_gr_id=8;//id группы

        //$this->trim_users_names_and_emails();//Запускать это, если нужно поправить email и имена

        if(!$this->check_data()) return 0;
        //$this->read_file($this->test_mode);//Запускать это, если нужно импортировать
        //$this->add2groups($add2group_start_user_id,$add2group_gr_id,$this->site_id);//Запускать, если нужно добавлять пользователей в группы

        return 1;
    }
}
$uSubscr_importer=new uSubscr_users_importer($this);

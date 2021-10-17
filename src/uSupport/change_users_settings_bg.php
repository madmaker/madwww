<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uSupport_change_users_settings {
    public $uSes;
    public $uFunc;
    private $uCore;
    private function save_settings() {
        $settings_ar=array(
            'show_opened',
            'show_answered',
            'show_done',
            'show_closed',
            'show_requests',
            'show_cases',
            'show_mine',
            'show_others',
            'show_assigned2me',
            'show_assigned2others',
            'show_unassigned',
            'show_internal',
            'show_escalated'
        );
        $user_id=$this->uSes->get_val("user_id");
        for($i=0;$i<count($settings_ar);$i++) {
            if(isset($_POST[$settings_ar[$i]])) {
                //get cur value
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
                    ".$settings_ar[$i]."
                    FROM
                    u235_users_settings
                    WHERE
                    user_id=:user_id
                    ");
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}

                /** @noinspection PhpUndefinedVariableInspection */
                /** @noinspection PhpUndefinedMethodInspection */
                if(!$setting=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(20);
                $setting_name=$settings_ar[$i];

                if($setting->$setting_name=='1') $val=0;
                else $val=1;

                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uSup")->prepare("UPDATE
                    u235_users_settings
                    SET
                    ".$settings_ar[$i]."=:val
                    WHERE
                    user_id=:user_id
                    ");

                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':val', $val,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

                $_SESSION['uSupport']['users_settings'][$settings_ar[$i]]=$val;

                die("{
                'status' : 'done',
                'setting_name':'".$settings_ar[$i]."',
                $settings_ar[$i]:'".$val."'
                }");
            }
        }
        die("{'status' : 'forbidden'}");
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        
        if(!$this->uSes->access(2)) die("{'status' : 'forbidden'}");

        $this->save_settings();
    }
}
$uSupport=new uSupport_change_users_settings($this);
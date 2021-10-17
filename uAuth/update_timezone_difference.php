<?php
if(!isset($_SESSION['SESSION']['timezone_difference'])) {
    if(isset($_POST['timestamp'])) {
        //check for how much hours cur timestamp differs from servers timestamp - make time zone correction
        if(uString::isDigits($_POST['timestamp'])) {
            $dif=$difference=time()-$_POST['timestamp'];
            if($dif<0) $dif=$dif*(-1);
            if($dif>86400) $dif=0;
            $_SESSION['SESSION']['timezone_difference']=$difference;

            if(!$this->query("uSes","UPDATE
             `u235_list`
             SET
             `timezone_difference`='".$_SESSION['SESSION']['timezone_difference']."'
             WHERE
             `sesId`='".$_SESSION['SESSION']['sesId']."'
             ")) $this->uCore->error(1);
        }
    }
}

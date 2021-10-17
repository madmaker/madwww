<?php
namespace uLeadGen;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
//require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';

class callback_save_bg {
    public $uFunc;
    public $phone_number;
    public $notification_email;
    public $purifier;
    private $uCore;

    private function return_error() {
        print "{'status' : 'error'}";
        exit;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);

        if(!isset($_POST['phone_number'])) $this->return_error();
        $this->phone_number=$_POST['phone_number'];

        $config = \HTMLPurifier_Config::createDefault();
        $this->purifier = new \HTMLPurifier($config);

        $this->phone_number=$this->purifier->purify(htmlspecialchars(trim($_POST['phone_number'])));
        if(site_id!=46) $this->phone_number='+7 '.$this->phone_number;

        $this->notification_email=$this->uFunc->getConf("invoice_emails","content",1);
        $emails_ar=explode(',',$this->notification_email);

        $msg="<h3>Посетитель сайта заказал обратный звонок</h3>
<p><b>Телефон: </b> ".$this->phone_number."</p>";

        uFunc::slack("Посетитель сайта заказал обратный звонок: ".$this->phone_number);

        print "{";
        for($i=0;$i<count($emails_ar);$i++) {
            $email=$emails_ar[$i];
            if(uString::isEmail($email)) {
                $this->uFunc->sendMail($msg,"Заказ обратного звонка",$email);
//                print "'email".$i."':'".$email."',";
            }
        }


        print "'status':'done'
        }";
    }
}
/*$newClass=*/new callback_save_bg($this);

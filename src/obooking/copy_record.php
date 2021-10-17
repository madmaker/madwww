<?php
namespace obooking;
use uSes;
use uString;

require_once "processors/uSes.php";
require_once 'obooking/classes/common.php';

class copy_record
{
    private $obooking;

    public function __construct(&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print json_encode([
                'status' => 'forbidden'
            ]);
            exit;
        }

        $user_id=(int)$uSes->get_val('user_id');

        $this->obooking = new common($uCore);

        $is_admin=$this->obooking->is_admin($user_id);

        if(!$is_admin) {
            print json_encode([
                'status' => 'forbidden'
            ]);
            exit;
        }

        $recInfo=$this->check_data();
        $srcRecId=$recInfo['rec_id'];
        $timestamp=$recInfo['timestamp'];
        $date=$recInfo['date'];
        $time=$recInfo['time'];
        $class_id=$recInfo['class_id'];
        $copyAssignedClients=$recInfo['copyAssignedClients'];

        $targetRecId=$this->obooking->copy_record($srcRecId,$timestamp,$class_id,0,$copyAssignedClients);

        print json_encode([
            'status' => 'success',
            'rec_id' => $targetRecId,
            'date' => $date,
            'time' => $time
        ]);
    }

    private function check_data($site_id = site_id) {
        //check that variables are received
        if (!isset(
            $_POST['recId'],
            $_POST['date'],
            $_POST['dateFormat']
        )) {
            print json_encode([
                'status' => 'error',
                'msg' => 'wrong request',
                'POST' => $_POST
            ]);
            exit();
        }

        //check rec_id format
        if (!uString::isDigits($_POST['recId'])) {
            print json_encode([
                'status' => 'error',
                'msg' => 'wrong record',
            ]);
            exit();
        }

        //save record for future use
        $rec_id = (int) $_POST['recId'];

        //check dateFormat
        if($_POST['dateFormat']==='dd.mm.yyyy') {
            if(!$dateCheckResult=uString::isDate($_POST['date'])) {
                print json_encode([
                    'status' => 'error',
                    'msg' => 'wrong date',
                ]);
                exit();
            }

            $dateAr=explode('.',$_POST['date']);
            $dd=$dateAr[0];
            $mm=$dateAr[1];
            $yyyy=$dateAr[2];
        }
        elseif($_POST['dateFormat']==='mm/dd/yyyy') {
            if(!$dateCheckResult=uString::isDate_en($_POST['date'])) {
                print json_encode([
                    'status' => 'error',
                    'msg' => 'wrong date',
                ]);
                exit();
            }

            $dateAr=explode('/',$_POST['date']);
            $mm=$dateAr[0];
            $dd=$dateAr[1];
            $yyyy=$dateAr[2];
        }
        elseif($_POST['dateFormat']==='dd-mm-yyyy') {
            if(!$dateCheckResult=uString::isDate_en($_POST['date'])) {
                print json_encode([
                    'status' => 'error',
                    'msg' => 'wrong date',
                ]);
                exit();
            }

            $dateAr=explode('-',$_POST['date']);
            $dd=$dateAr[0];
            $mm=$dateAr[1];
            $yyyy=$dateAr[2];
        }
        else {
            print json_encode([
                'status' => 'error',
                'msg' => 'wrong date format',
            ]);
            exit();
        }

        //get record's date, class_id,  duration
        if (
            !($rec_data = $this->obooking->get_record_info(
                'class_id,timestamp,duration',
                $rec_id,
                $site_id
            ))
        ) {
            print json_encode([
                'status' => 'error',
                'msg' => 'record is not found',
            ]);
            exit();
        }

        $duration=(int)$rec_data->duration;
        $class_id=(int)$rec_data->class_id;

        $srcTimestamp=(int)$rec_data->timestamp;
        $hours = (int)date('G',$srcTimestamp);//Hours without leading zero
        $minutes = (int)date('i',$srcTimestamp);//Minutes with leading zero

        $destTimestamp=strtotime($mm.'/'.$dd.'/'.$yyyy.' '.$hours.':'.$minutes.':00');

        if(isset($_POST['time']) && uString::isTime($_POST['time'])) {
            $timeAr=explode(':',$_POST['time']);
            $destTimestamp=strtotime($mm.'/'.$dd.'/'.$yyyy.' '.$timeAr[0].':'.$timeAr[1].':00');
        }

        $time=date('H:i',$destTimestamp);

        if(!$this->obooking->time_is_free($class_id,$destTimestamp,$duration)) {
            print json_encode([
                'status' => 'error',
                'msg' => 'occupied',
                'date' => $_POST['date'],
                'time' => $time
            ]);
            exit();
        }

        $copyAssignedClients=1;
        if(isset($_POST['copyAssignedClients'])) {
            $_POST['copyAssignedClients']=(int)$_POST['copyAssignedClients'];

            if($_POST['copyAssignedClients']===0) {
                $copyAssignedClients = 0;
            }
        }

        if($copyAssignedClients!==1) {
            $copyAssignedClients = 0;
        }

        return array(
            'rec_id'=>$rec_id,
            'duration'=>$duration,
            'timestamp'=>$destTimestamp,
            'date'=>$_POST['date'],
            'time'=>$time,
            'class_id'=>$class_id,
            'copyAssignedClients'=>$copyAssignedClients
        );
    }


}
new copy_record($this);

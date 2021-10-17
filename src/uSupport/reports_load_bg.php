<?php
namespace uSupport;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uAuth/classes/common.php";
require_once "uSupport/classes/common.php";

class reports_load_bg {
    public $uFunc;
    public $uSes;
    public $uAuth;
    public $uSup;
    private $uCore,$q_status,
        $user_id2data_ar,$com_id2title_ar,
        $q_comps,$q_cons,$q_time_from,$q_time_to;
    public $format,$q_time,$status2label,$time_spent,
        $report_filename,
    $detalized,
$time_total;
    private function check_data() {
        $this->format='html';
        if(isset($this->uCore->url_prop[1])) {
            if($this->uCore->url_prop[1]=='pdf') $this->format='pdf';
            elseif($this->uCore->url_prop[1]=='xl') $this->format='xl';
        }

        if(!isset(
        $_GET['date_from'],
        $_GET['date_to'],
        $_GET['date_filter_open'],
        $_GET['date_filter_changed'],
        $_GET['date_filter_time_logged'],
        $_GET['status_closed'],
        $_GET['status_done'],
        $_GET['status_open'],
        $_GET['status_answered'],
        $_GET['detalized'],
        $_GET['time_spent'],
        $_GET['comps'],
        $_GET['cons']
        )) $this->uFunc->error(10);


        if(!empty($_GET['date_from'])) @$timestamp_from=strtotime($_GET['date_from']);
        if(!empty($_GET['date_to'])) @$timestamp_to=strtotime($_GET['date_to'])+86400;//+24 h to include finish date
        $this->q_time_from='';
        $this->q_time_to='';
        if(isset($timestamp_from)) if(uString::isDigits($timestamp_from)) {
            if($_GET['date_filter_open']=='true') $this->q_time_from=" tic_opened_timestamp>=".(int)$timestamp_from." AND ";
            if($_GET['date_filter_changed']=='true') $this->q_time_from=" tic_changed_timestamp>=".(int)$timestamp_from." AND ";
            if($_GET['date_filter_time_logged']=='true') $this->q_time_from=" u235_requests_time.timestamp>=".(int)$timestamp_from." AND ";

            if($this->q_time_from=='') $this->q_time_from=" (tic_opened_timestamp>=".(int)$timestamp_from." OR tic_changed_timestamp>=".(int)$timestamp_from.") AND ";
        }
        if(isset($timestamp_to)) if(uString::isDigits($timestamp_to)) {
            if($_GET['date_filter_open']=='true') $this->q_time_to=" tic_opened_timestamp<=".(int)$timestamp_to." AND ";
            if($_GET['date_filter_changed']=='true') $this->q_time_to=" tic_changed_timestamp<=".(int)$timestamp_to." AND ";
            if($_GET['date_filter_time_logged']=='true') $this->q_time_to=" u235_requests_time.timestamp<=".(int)$timestamp_to." AND ";

            if($this->q_time_to=='') $this->q_time_to=" (tic_opened_timestamp<=".(int)$timestamp_to." OR tic_changed_timestamp<=".(int)$timestamp_to.") AND ";
        }


        if($_GET['status_closed']=='true'&&
            $_GET['status_done']=='false'&&
            $_GET['status_open']=='false'&&
            $_GET['status_answered']=='false') $this->q_status=" (
        tic_status='req_closed' OR
        tic_status='case_closed'
        ) AND ";
        elseif($_GET['status_closed']=='false'&&
                $_GET['status_done']=='true'&&
                $_GET['status_open']=='false'&&
                $_GET['status_answered']=='false'
        ) $this->q_status=" (tic_status='case_done') AND ";
        elseif($_GET['status_closed']=='false'&&
                $_GET['status_done']=='false'&&
                $_GET['status_open']=='true'&&
                $_GET['status_answered']=='false'
        ) $this->q_status=" (
        tic_status='req_open' OR
        tic_status='req_processing' OR
        tic_status='case_open' OR
        tic_status='case_processing'
        ) AND ";
        elseif($_GET['status_closed']=='false'&&
                $_GET['status_done']=='false'&&
                $_GET['status_open']=='false'&&
                $_GET['status_answered']=='true'
        ) $this->q_status=" (tic_status='req_answered' OR
        tic_status='case_answered'
        ) AND ";
        elseif($_GET['status_closed']=='true'&&
                $_GET['status_done']=='true'&&
                $_GET['status_open']=='false'&&
                $_GET['status_answered']=='false'
        ) $this->q_status=" (tic_status='req_closed' OR
        tic_status='case_done' OR
        tic_status='case_closed'
        ) AND ";
        elseif($_GET['status_closed']=='true'&&
                $_GET['status_done']=='false'&&
                $_GET['status_open']=='true'&&
                $_GET['status_answered']=='false'
        ) $this->q_status=" (
        tic_status='req_open' OR
        tic_status='req_processing' OR
        tic_status='req_closed' OR
        tic_status='case_open' OR
        tic_status='case_processing' OR
        tic_status='case_closed'
        ) AND ";
        elseif($_GET['status_closed']=='true'&&
                $_GET['status_done']=='false'&&
                $_GET['status_open']=='false'&&
                $_GET['status_answered']=='true'
        ) $this->q_status=" (tic_status='req_answered' OR
        tic_status='req_closed' OR
        tic_status='case_answered' OR
        tic_status='case_closed'
        ) AND ";
        elseif($_GET['status_closed']=='false'&&
                $_GET['status_done']=='true'&&
                $_GET['status_open']=='true'&&
                $_GET['status_answered']=='false'
        ) $this->q_status=" (tic_status='req_open' OR
        tic_status='req_processing' OR
        tic_status='case_open' OR
        tic_status='case_processing' OR
        tic_status='case_done'
        ) AND ";
        elseif($_GET['status_closed']=='false'&&
                $_GET['status_done']=='true'&&
                $_GET['status_open']=='false'&&
                $_GET['status_answered']=='true'
        ) $this->q_status=" (tic_status='req_answered' OR
        tic_status='case_answered' OR
        tic_status='case_done'
        ) AND ";
        elseif($_GET['status_closed']=='false'&&
                $_GET['status_done']=='false'&&
                $_GET['status_open']=='true'&&
                $_GET['status_answered']=='true'
        ) $this->q_status=" (
        tic_status='req_open' OR
        tic_status='req_answered' OR
        tic_status='req_processing' OR
        tic_status='case_open' OR
        tic_status='case_answered' OR
        tic_status='case_processing'
        ) AND ";
        else $this->q_status=" (
        tic_status='req_open' OR
        tic_status='req_answered' OR
        tic_status='req_processing' OR
        tic_status='req_closed' OR
        tic_status='case_open' OR
        tic_status='case_answered' OR
        tic_status='case_processing' OR
        tic_status='case_done' OR
        tic_status='case_closed'
        ) AND ";



        $this->detalized=$_GET['detalized']=='true';
        if($_GET['time_spent']=='true') $this->time_spent=" tic_time_spent!='0' AND "; else $this->time_spent="";


        $comps=explode(',',$_GET['comps']);
        for($i=0;$i<count($comps);$i++) {
            $comps[$i]=str_replace("uSup_comp_","",$comps[$i]);
            if(uString::isDigits($comps[$i])) {
                $this->q_comps.=" OR company_id=".(int)$comps[$i]." ";
            }
        }
        $this->q_comps=substr($this->q_comps,3);
        if(!empty($this->q_comps)) $this->q_comps=' ('.$this->q_comps.') AND ';

        $cons=explode(',',$_GET['cons']);
        for($i=0;$i<count($cons);$i++) {
            $cons[$i]=str_replace("uSup_cons_","",$cons[$i]);
            if(uString::isDigits($cons[$i])) {
                $this->q_cons.=" OR cons_id=".(int)$cons[$i]." ";
            }
        }
        $this->q_cons=substr($this->q_cons,3);
        if(!empty($this->q_cons)) $this->q_cons=' ('.$this->q_cons.') AND ';

    }
    private function set_status_labels() {
        $this->status2label['req_open']='Запрос открыт';
        $this->status2label['req_answered']='Есть ответ на запрос';
        $this->status2label['req_processing']='Запрос рассматривается';
        $this->status2label['req_closed']='Запрос закрыт';

        $this->status2label['case_open']='Кейс открыт';
        $this->status2label['case_answered']='Кейс отвечен';
        $this->status2label['case_processing']='Кейс рассматривается';
        $this->status2label['case_closed']='Кейс закрыт';
        $this->status2label['case_done']='Кейс выполнен';
    }
    private function get_time() {
        if($_GET['date_filter_time_logged']=='true') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT DISTINCT
                SUM(time_spent),
                u235_requests.tic_id,
                company_id,
                u235_requests.user_id,
                cons_id,
                tic_opened_timestamp,
                tic_changed_timestamp,
                tic_subject,
                tic_status,
                tic_cat,
                tic_time_spent
                FROM
                u235_requests
                JOIN
                u235_requests_time
                ON
                u235_requests.tic_id=u235_requests_time.tic_id AND
                u235_requests.site_id=u235_requests_time.site_id
                WHERE
                ".
                    $this->q_status."
                ".$this->time_spent."
                ".$this->q_comps."
                ".$this->q_cons."
                ".$this->q_time_from."
                ".$this->q_time_to./**@lang mysql*/" 
                u235_requests.site_id=:site_id
                GROUP BY 
                tic_id
                ORDER BY
                company_id ASC,
                tic_changed_timestamp ASC
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                /** @noinspection PhpStatementHasEmptyBodyInspection */
                for($i=0; $this->q_time[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT 
                SUM(DISTINCT tic_time_spent)
                FROM
                u235_requests
                JOIN 
                u235_requests_time
                ON
                u235_requests.tic_id=u235_requests_time.tic_id AND
                u235_requests.site_id=u235_requests_time.site_id
                WHERE
                ".$this->q_status."
                ".$this->time_spent."
                ".$this->q_comps."
                ".$this->q_cons."
                ".$this->q_time_from."
                ".$this->q_time_to./** @lang mysql */"
                u235_requests.site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$stm->fetch(PDO::FETCH_ASSOC);
            $this->time_total=$qr['SUM(DISTINCT tic_time_spent)'];
        }
        else {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
                tic_id,
                company_id,
                user_id,
                cons_id,
                tic_opened_timestamp,
                tic_changed_timestamp,
                tic_subject,
                tic_status,
                tic_cat,
                tic_time_spent
                FROM
                u235_requests
                WHERE
                ".$this->q_status."
                ".$this->time_spent."
                ".$this->q_comps."
                ".$this->q_cons."
                ".$this->q_time_from."
                ".$this->q_time_to./** @lang mysql */"
                u235_requests.site_id=:site_id
                ORDER BY
                company_id ASC,
                tic_changed_timestamp ASC
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpStatementHasEmptyBodyInspection */
                /** @noinspection PhpUndefinedMethodInspection */
                for($i=0; $this->q_time[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
            }
            catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT 
                SUM(tic_time_spent)
                FROM
                u235_requests
                WHERE
                ".$this->q_status."
                ".$this->time_spent."
                ".$this->q_comps."
                ".$this->q_cons."
                ".$this->q_time_from."
                ".$this->q_time_to./** @lang text */"
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$stm->fetch(PDO::FETCH_ASSOC);
            $this->time_total=$qr['SUM(tic_time_spent)'];
        }
    }
    public function tic_id2time($tic_id) {
        if($_GET['date_filter_time_logged']=='true') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
                time_spent,
                comment,
                timestamp,
                user_id
                FROM
                u235_requests_time
                WHERE
                ".$this->q_time_from."
                ".$this->q_time_to./** @lang mysql */"
                tic_id=:tic_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $tic_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
        }
        else {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
                time_spent,
                comment,
                timestamp,
                user_id
                FROM
                u235_requests_time
                WHERE
                tic_id=:tic_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $tic_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
        }
        /** @noinspection PhpUndefinedVariableInspection */
        return $stm;
    }
    public function user_id2data($user_id) {
        if(!isset($this->user_id2data_ar[$user_id])) {
            if($user=$this->uAuth->user_id2user_data($user_id,"firstname,secondname,lastname")) {
                $this->user_id2data_ar[$user_id]=uString::sql2text($user->firstname.' '.$user->secondname.' '.$user->lastname);
            }
            else $this->user_id2data_ar[$user_id]='';
        }
        return $this->user_id2data_ar[$user_id];
    }
    public function com_id2title($com_id) {
        if(!isset($this->com_id2title_ar[$com_id])) {
            if($com=$this->uSup->com_id2com_info($com_id,"com_title")) {
                $this->com_id2title_ar[$com_id]=uString::sql2text($com->com_title);
            }
            else $this->com_id2title_ar[$com_id]='';
        }
        return $this->com_id2title_ar[$com_id];
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uAuth=new \uAuth\common($this->uCore);
        $this->uSup=new common($this->uCore);
        
        if(!$this->uSes->access(27)) die('forbidden');

        if(isset($_GET['filename'])) $this->report_filename=uString::text2filename(uString::rus2eng(rawurldecode(trim($_GET['filename']))));
        else $this->report_filename='Отчет';
        if(!strlen($this->report_filename))$this->report_filename='Отчет';

        $this->check_data();
        $this->set_status_labels();
        $this->get_time();
    }
}
$uSup=new reports_load_bg($this);

if($uSup->format=='pdf'||$uSup->format=='html') {
ob_start();
?>
<h2>Отчет<?if($uSup->format=='html'){?> <small>
        <button onclick="uSup.load_report_file('<?=rawurlencode(u_sroot.'uSupport/'.$this->page_name.'/xl'.str_replace('/'.'uSupport/'.$this->page_name,'',$_SERVER['REQUEST_URI']))?>')" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-save"></span> Excel</button>
        <button onclick="uSup.load_report_file('<?=rawurlencode(u_sroot.'uSupport/'.$this->page_name.'/pdf'.str_replace('/'.'uSupport/'.$this->page_name,'',$_SERVER['REQUEST_URI']))?>')" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-save"></span> PDF</button>
        <span><small>Имя файла:</small></span>
        <input id="uSup_report_filename" type="text" placeholder="<?=$uSup->report_filename?>" class="form-control input-sm" style="width:auto; display: inherit;">
    </small><?}?></h2>
    <?$hours=floor($uSup->time_total/60)?>
    <p>Всего списано времени: <?=$hours.':'.($uSup->time_total-($hours*60))?></p>
<table class="table table-condensed table-striped">
    <tr>
        <th>Запрос, Потраченное время<?=($uSup->detalized)?'<br>Сотрудник':''?></th>
        <th>Статус</th>
        <th>Автор</th>
        <th>Дата открытия запроса<?=($uSup->detalized)?'<br>Дата отметки':''?></th>
        <th>Потраченное время</th>
        <th>Дата закрытия/изменения запроса<?=($uSup->detalized)?'<br>Комментарий':''?></th>
    </tr>
<?$last_com_id=0;
for($i=0;$time=$uSup->q_time[$i];$i++) {
    if($_GET['date_filter_time_logged']=='true') {
        $time_spent_field="SUM(time_spent)";
        $time->tic_time_spent=$time->$time_spent_field;
    }
    ?>
    <?if($last_com_id!=$time->company_id) {
        $last_com_id=$time->company_id;?>
    <tr><th colspan="5"><h3><?=$uSup->com_id2title($time->company_id)?></h3></th></tr>
    <?}

    $hours=floor($time->tic_time_spent/60);
    $minutes=$time->tic_time_spent-$hours*60;
    if(strlen($minutes)<2) $minutes='0'.$minutes;
    ?>
    <tr <?=$uSup->detalized?'class="info"':''?>>
        <td><?if($uSup->format=='html') echo '<a href="'.u_sroot.'uSupport/request_show/'.$time->tic_id.'" target="_blank">'?>#<?=$time->tic_id?> <b><?=uString::sql2text($time->tic_subject)?></b><?if($uSup->format=='html') echo '</a>'?><br>
            <?if($uSup->format=='html') echo '<a href="'.u_sroot.'uAuth/profile/'.$time->cons_id.'" target="_blank">'?><?=$uSup->user_id2data($time->cons_id)?><?if($uSup->format=='html') echo '</a>'?></td>
        <td><?=$uSup->status2label[$time->tic_status]?></td>
        <td><?if($uSup->format=='html') echo '<a href="'.u_sroot.'uAuth/profile/'.$time->user_id.'" target="_blank">'?><?=$uSup->user_id2data($time->user_id)?><?if($uSup->format=='html') echo '</a>'?></td>
        <td><?=date('d.m.Y H:i:s',
                $time->tic_opened_timestamp+
                $_SESSION['SESSION']['timezone_difference_isset_always'])?></td>
        <td><span class="glyphicon glyphicon-time"></span> <?=$hours.':'.$minutes?></td>
        <td><?=date('d.m.Y H:i:s',$time->tic_changed_timestamp+$_SESSION['SESSION']['timezone_difference_isset_always'])?></td>
    </tr>
    <?$query=$uSup->tic_id2time($time->tic_id);
    if($uSup->detalized) {
        /** @noinspection PhpUndefinedMethodInspection */
        while($time_log=$query->fetch(PDO::FETCH_OBJ)) {
            $hours_spent=floor($time_log->time_spent/60);
            $minutes_spent=$time_log->time_spent-$hours_spent*60;
            if(strlen($minutes_spent)<2) $minutes_spent='0'.$minutes_spent;
            ?>
            <tr>
                <td colspan="3"><?if($uSup->format=='html') echo '<a href="'.u_sroot.'uAuth/profile/'.$time->user_id.'" target="_blank">'?><?=$uSup->user_id2data($time_log->user_id)?><?if($uSup->format=='html') echo '</a>'?></td>
                <td><?=date('d.m.Y H:i:s',$time_log->timestamp+$_SESSION['SESSION']['timezone_difference_isset_always'])?></td>
                <td><?=$hours_spent.':'.$minutes_spent?></td>
                <td><?=uString::sql2text($time_log->comment)?></td>
            </tr>
        <?}?>
    <?}?>
<?}?>
</table><?
$html=ob_get_contents();
ob_end_clean();
}
if($uSup->format=='pdf') {
    include('lib/MPDF/mpdf.php');
    $mpdf=new \mPDF('utf-8', 'A4-L');
    $stylesheet = file_get_contents('lib/MPDF/mpdf.css');
    $mpdf->WriteHTML($stylesheet,1);
    /** @noinspection PhpUndefinedVariableInspection */
    $mpdf->WriteHTML($html,2);
    $mpdf->Output($uSup->report_filename.'.pdf','I');
    exit;
}
elseif($uSup->format=='xl') {
    include('lib/phpexcel/PHPExcel.php');
    $doc = new \PHPExcel();

    $table=array();
    $table[0]=array("Запрос\nПотраченное время".(($uSup->detalized)?"\nСотрудник":""),"Консультант","Статус","Автор","Дата открытия запроса".(($uSup->detalized)?"\nДата отметки":""),"Потраченное время","Дата закрытия/изменения запроса".(($uSup->detalized)?"\nКомментарий":""));
    $last_com_id=0;
    $com_title_row=array();
    $req_title_row=array();
    for($i=0;$time=$uSup->q_time[$i];$i++) {
        if($_GET['date_filter_time_logged']=='true') {
            $time_spent_field="SUM(time_spent)";
            $time->tic_time_spent=$time->$time_spent_field;
        }
        if($last_com_id!=$time->company_id) {
            $last_com_id=$time->company_id;
            $com_title_row[count($com_title_row)]=count($table);
            $table[count($table)]=array($uSup->com_id2title($time->company_id));
        }
        $hours=floor($time->tic_time_spent/60);
        $minutes=$time->tic_time_spent-$hours*60;
        if(strlen($minutes)<2) $minutes='0'.$minutes;
        $req_title_row[count($req_title_row)]=count($table);
        $author=$uSup->user_id2data($time->user_id);
        $table[count($table)]=array(
            '#'.$time->tic_id.' '.uString::sql2text($time->tic_subject),$uSup->user_id2data($time->cons_id),
            $uSup->status2label[$time->tic_status],
            $author,
            date('d.m.Y H:i:s',$time->tic_opened_timestamp+$_SESSION['SESSION']['timezone_difference_isset_always']),
            $hours.':'.$minutes,
            date('d.m.Y H:i:s',$time->tic_changed_timestamp+$_SESSION['SESSION']['timezone_difference_isset_always'])
        );
        $query=$uSup->tic_id2time($time->tic_id);
        if($uSup->detalized){
            /** @noinspection PhpUndefinedMethodInspection */
            while($time_log=$query->fetch(PDO::FETCH_OBJ)) {
                $hours_spent=floor($time_log->time_spent/60);
                $minutes_spent=$time_log->time_spent-$hours_spent*60;
                if(strlen($minutes_spent)<2) $minutes_spent='0'.$minutes_spent;
                $table[count($table)]=array(
                    $uSup->user_id2data($time_log->user_id),
                    '',
                    '',
                    '',
                    date('d.m.Y H:i:s',$time_log->timestamp+$_SESSION['SESSION']['timezone_difference_isset_always']),
                    $hours_spent.':'.$minutes_spent,
                    uString::sql2text($time_log->comment)
                );
            }
        }
    }
    $tableHeaderStyleArray = array(
        'font'  => array(
            'bold'  => true,
            'color' => array('rgb' => '000000'),
            'size'  => 10,
            'name'  => 'Verdana',
        ),
        'fill' => array(
            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                'rgb' => 'DBDBDB'
            )
        )
    );
    $comTitleStyleArray = array(
        'font'  => array(
            'bold'  => true,
            'color' => array('rgb' => '000000'),
            'size'  => 15,
            'name'  => 'Verdana',
        )
    );
    $reqTitleStyleArray = array(
        'font'  => array(
            'bold'  => false,
            'color' => array('rgb' => '000000'),
            'size'  => 10,
            'name'  => 'Verdana',
        ),
        'fill' => array(
            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                'rgb' => 'efefef'
            )
        )
    );

    $doc->setActiveSheetIndex(0);

    $newsheet=$doc->getActiveSheet();
    $newsheet->setTitle("Отчет");
    $newsheet->fromArray($table, null, 'A1');
    $newsheet->getStyle('A1:G1')->applyFromArray($tableHeaderStyleArray );
    $newsheet->getStyle('A1:G1')->getAlignment()->setWrapText(true);
    for($i=0;$i<count($com_title_row);$i++) {
        $newsheet->getStyle('A'.($com_title_row[$i]+1))->applyFromArray($comTitleStyleArray );
    }
    for($i=0;$i<count($req_title_row);$i++) {
        $newsheet->getStyle('A'.($req_title_row[$i]+1))->applyFromArray($reqTitleStyleArray);
        $newsheet->getStyle('B'.($req_title_row[$i]+1))->applyFromArray($reqTitleStyleArray);
        $newsheet->getStyle('C'.($req_title_row[$i]+1))->applyFromArray($reqTitleStyleArray);
        $newsheet->getStyle('D'.($req_title_row[$i]+1))->applyFromArray($reqTitleStyleArray);
        $newsheet->getStyle('E'.($req_title_row[$i]+1))->applyFromArray($reqTitleStyleArray);
        $newsheet->getStyle('F'.($req_title_row[$i]+1))->applyFromArray($reqTitleStyleArray);
        $newsheet->getStyle('G'.($req_title_row[$i]+1))->applyFromArray($reqTitleStyleArray);
    }
    $newsheet->getColumnDimension('A')->setAutoSize(true);
    $newsheet->getColumnDimension('B')->setAutoSize(true);
    $newsheet->getColumnDimension('C')->setAutoSize(true);
    $newsheet->getColumnDimension('D')->setAutoSize(true);
    $newsheet->getColumnDimension('E')->setAutoSize(true);
    $newsheet->getColumnDimension('F')->setAutoSize(true);
    $newsheet->getColumnDimension('G')->setAutoSize(true);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$uSup->report_filename.'.xls"');
    header('Cache-Control: max-age=0');

    // Do your stuff here
    $writer = \PHPExcel_IOFactory::createWriter($doc, 'Excel5');

    $writer->save('php://output');
    exit;
}
elseif($uSup->format=='html') /** @noinspection PhpUndefinedVariableInspection */
    echo $html;
?>
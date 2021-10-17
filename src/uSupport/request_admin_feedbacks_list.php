<?
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uSup_request_admin_feedbacks_list {
    public $uFunc;
    public $uSes;
    private $uCore;
    private $catId2Title_ar;
    public $status2Icon_ar,$qReqListOpened, $qReqListClosed, $qCompList, $qUserList, $ticId, $req_status,$statusHeader, $req_type, $reqTypeHeader, $com_filterHeader,$date_filterHeader, $req_assignment;

    private function getReqList() {
        //Достаем список тикетов
        if(!$this->qReqListOpened=$this->uCore->query('uSup',"SELECT
        `tic_id`,
        `company_id`,
        `tic_subject`,
        `tic_changed_timestamp`,
        `tic_cat`,
        `tic_feedback_info`
        FROM
        `u235_requests`
        WHERE
        (`tic_status`='case_closed' OR `tic_status`='req_closed') AND
        (`tic_feedback_info`='positive' OR `tic_feedback_info`='neutral' OR `tic_feedback_info`='negative') AND
        `site_id`='".site_id."'
        ORDER BY
        `tic_changed_timestamp` DESC
        ")) $this->uCore->error(1);
    }

    public function userId2names($user_id) {
        if(!$query=$this->uCore->query('uAuth',"SELECT DISTINCT
        `u235_users`.`firstname`,
        `u235_users`.`lastname`
        FROM
        `u235_users`,
        `u235_usersinfo`
        WHERE
        `u235_users`.`user_id`='".$user_id."' AND
        `u235_users`.`user_id`=`u235_usersinfo`.`user_id` AND
        `u235_usersinfo`.`site_id`='".site_id."'
        ")) $this->uCore->error(2);
        return $query->fetch_object();
    }
    public function com_id2title($com_id) {
        if(!$query=$this->uCore->query('uSup',"SELECT
        `com_title`
        FROM
        `u235_comps`
        WHERE
        `site_id`='".site_id."' AND
        `com_id`='".$com_id."'
        ")) $this->uCore->error(3);
        $qr=$query->fetch_object();
        return uString::sql2text($qr->com_title);
    }
    public function catId2Title($cat_id) {
        if(!isset($this->catId2Title_ar[$cat_id])) {
            if(!$query=$this->uCore->query("uSup","SELECT
            `cat_title`
            FROM
            `u235_requests_cats`
            WHERE
            `cat_id`='".$cat_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(4);
            if(mysqli_num_rows($query)>0) {
                $data=$query->fetch_object();
                $cat_title=$data->cat_title;
                $this->catId2Title_ar[$cat_id]=$cat_title;
                return $cat_title;
            }
            return '';
        }
        return $this->catId2Title_ar[$cat_id];
    }
    public function getLastMsgUser($tic_id) {
        if(!$query=$this->uCore->query("uSup","SELECT
        `msg_sender`
        FROM
        `u235_msgs`
        WHERE
        `tic_id`='".$tic_id."' AND
        `site_id`='".site_id."'
        ORDER BY
        `msg_timestamp` DESC
        LIMIT 1
        ")) $this->error(5);
        if(mysqli_num_rows($query)) {
            $qr=$query->fetch_object();
            return $qr->msg_sender;
        }
        else return false;
    }
    public function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);


        $this->getReqList();
    }
}

$uSupport=new uSup_request_admin_feedbacks_list($this);

//ВЫВОД
$this->uFunc->incCss(u_sroot.'templates/u235/css/u235/u235_common.css');
ob_start();

?>
<div class="uSup_requests uSup_requests_admin u235_admin">
    <p><a href="<? echo u_sroot.$this->mod;?>/requests" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-arrow-left"></span> Вернуться к списку запросов</a></p>
<h1><?=$this->page['page_title']?></h1>
<? if(mysqli_num_rows($uSupport->qReqListOpened)>0) { ?>
<div><table class="table table-striped table-condensed">
    <? while($req=$uSupport->qReqListOpened->fetch_object()) {
        //Достаем имя пользователя, последнего сменившего запрос
        $req->tic_subject=uString::sql2text($req->tic_subject); //Деконвертируем заголовки тикетов
        $req->msg_changed_user_name='';
        $req->msg_changed_user_id=0;
        $msg_sender=$uSupport->getLastMsgUser($req->tic_id);
        if($msg_sender) {
            $names=$uSupport->userId2names($msg_sender);
            $req->msg_changed_user_name=$names->firstname.' '.$names->lastname;
            $req->msg_changed_user_id=$msg_sender;
        }
        ?>
    <tr id="tr_tic_<?=$req->tic_id?>">
        <td>
            <?=$req->tic_id?>
        </td>
        <td>
            <a href="<?=u_sroot.$this->mod?>/request_show/<?=$req->tic_id?>"><?=$req->tic_subject?></a><br>

            <?if($req->tic_feedback_info=='positive'||$req->tic_feedback_info=='negative'||$req->tic_feedback_info=='neutral'){?>
                    <a href="<?=u_sroot.$this->mod?>/request_admin_feedback_show/<?=$req->tic_id?>" class="btn btn-default btn-xs <?=($req->tic_feedback_info=='negative')?'btn-danger':''?> <?=($req->tic_feedback_info=='positive')?'btn-success':''?> uTooltip pull-left" title="Клиент оставил отзыв на этот запрос"><span class="glyphicon glyphicon-thumbs-<?=($req->tic_feedback_info=='negative')?'down':'up'?>"></span></a>&nbsp;
                <?}?>

            <small class="text-muted">
                <?$subject=$uSupport->catId2Title($req->tic_cat);
                if(!empty($subject)) echo '<b>'.$uSupport->catId2Title($req->tic_cat).'</b>, ';
                if($req->msg_changed_user_id!=$uSupport->uSes->get_val("user_id")) {?>
                <a target="_blank" href="<?=u_sroot?>uAuth/profile/<?=$req->msg_changed_user_id?>"><?=$req->msg_changed_user_name?></a>,
                <a target="_blank" href="<?=u_sroot.$this->mod?>/admin_com_info/<?=$req->company_id?>"><?=$uSupport->com_id2title($req->company_id)?></a>
            <?}?>
            <?=date ('d.m.Y H:i' ,$req->tic_changed_timestamp)?>
            </small>

        </td>
    </tr>
    <? } ?>
</table>
</div>
<?}
else {?>
    <p>Нет отзывов</p>
<?}?>
</div>

<?
$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/u235/template.php";

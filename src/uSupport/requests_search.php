<?
namespace uSupport;
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

use PDO;
use PDOException;
use processors\uFunc;
use uString;

class requests_search {
    private $uCore,$catId2Title_ar,$req_per_page,
        $qu_comps;
    public $qRequests,$q_search,$q_com,$req_found,$search_string,$pageNumber,$status2Icon_ar,$curPage,
$companies_count,$def_com_id,$is_com_client,$is_com_admin,$is_consultant,$is_operator,$has_access,
        $q_cons_list;

    private function setLabels() {
        $this->status2Icon_ar['req_open']=' class="btn btn-xs btn-info uTooltip" title="Запрос открыт">
        <span class="glyphicon glyphicon-envelope"></span>';
        $this->status2Icon_ar['req_answered']=' class="btn btn-xs btn-success uTooltip" title="Есть ответ на запрос">
        <span class="glyphicon glyphicon-share-alt"></span>';
        $this->status2Icon_ar['req_processing']=' class="btn btn-xs btn-warning uTooltip" title="Запрос рассматривается">
        <span class="glyphicon glyphicon-time"></span>';
        $this->status2Icon_ar['req_closed']=' class="btn btn-xs btn-default uTooltip" title="Запрос закрыт">
        <span class="glyphicon glyphicon-lock"></span>';

        $this->status2Icon_ar['case_open']=' class="btn btn-xs btn-info uTooltip" title="Кейс открыт">
        <span class="glyphicon glyphicon-envelope text-warning"></span>';
        $this->status2Icon_ar['case_answered']=' class="btn btn-xs btn-success uTooltip" title="Кейс отвечен">
        <span class="glyphicon glyphicon-share-alt"></span>';
        $this->status2Icon_ar['case_processing']=' class="btn btn-xs btn-warning uTooltip" title="Кейс рассматривается">
        <span class="glyphicon glyphicon-time"></span>';
        $this->status2Icon_ar['case_closed']=' class="btn btn-xs btn-default uTooltip" title="Кейс закрыт">
        <span class="glyphicon glyphicon-lock"></span>';
        $this->status2Icon_ar['case_done']=' class="btn btn-xs btn-primary uTooltip" title="Кейс выполнен">
        <span class="glyphicon glyphicon-check"></span>';
    }
    private function def_search() {
        $this->q_search='(1=0) AND';
        if(isset($_GET['search'])) {
            $squery=$_GET['search'];
            $this->search_string=$search=preg_replace("#[^\w\dа-я]#iu", ' ', $squery);
            //$search=str_replace(' ','%',$search);
            $search_ar=explode(' ',$search);
            if(count($search_ar)) {
                $req_search=$msg_search="(";
                for($i=0;$i<count($search_ar);$i++) {
                    $q_search=uString::replace4sqlLike($search_ar[$i]);
                    $req_search.=" (tic_subject LIKE '%".$q_search."%' OR u235_requests.tic_id='".$search_ar[$i]."') AND";
                    $msg_search.=" msg_text LIKE '%".$q_search."%' AND";
                }
                $req_search.=")";
                $msg_search.=")";
                $req_search=str_replace('AND)',')',$req_search);
                $msg_search=str_replace('AND)',')',$msg_search);
                $this->q_search="(".$req_search." OR ".$msg_search."  OR u235_requests.tic_id='".uString::text2sql(trim($_GET['search']))."') AND ";
            }
        }
    }
    private function def_com_filter() {
        if($this->uSes->access(8)||$this->uSes->access(9)) {//operator or consultant
            $this->q_com='';
            return true;
        }
        //get all user's companies
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            com_id
            FROM
            u235_com_users
            WHERE
            user_id=:user_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $user_id=$this->uSes->get_val("user_id");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            $this->q_com=$q_com='';
            /** @noinspection PhpUndefinedMethodInspection */
            for($i=0; $com[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) $q_com.=" company_id='".$com[$i]->com_id."' OR";
            if(!count($com)) $q_com=" 1=0 OR ";

            $this->q_com='('.$q_com.' u235_requests.user_id=:user_id ) AND ';
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
        return 1;
    }
    private function defCurPage() {
        $this->req_per_page=(int)$this->uCore->uFunc->getConf('requests_per_search_page','uSup');

        $this->curPage=0;
        if(isset($_GET['page'])) {
            $page=$_GET['page'];
            if(uString::isDigits($page)) {
                $this->curPage=$page;
            }
        }
    }
    private function search() {
        //define search request
        $this->def_search();
        $this->def_com_filter();
        //get requests
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT DISTINCT
            u235_requests.tic_id,
            tic_subject,
            tic_opened_timestamp,
            tic_changed_timestamp,
            tic_feedback_info,
            tic_cat,
            company_id,
            user_id,
            tic_status,
            uknowbase_solution_isset
            FROM
            u235_requests
            JOIN 
            u235_msgs
            ON
            u235_requests.tic_id=u235_msgs.tic_id AND
            u235_requests.site_id=u235_msgs.site_id
            WHERE ".
                $this->q_search.$this->q_com.
                "u235_requests.tic_status!='new' AND
            u235_requests.site_id=:site_id
            ORDER BY
            FIELD (tic_status,
            'req_open',
            'case_open',
            'req_processing',
            'case_processing',
            'req_answered',
            'case_answered',
            'case_done',
            'case_closed',
            'req_closed'
            ),
            tic_changed_timestamp DESC,
            u235_requests.tic_subject ASC
            LIMIT ".($this->req_per_page*$this->curPage).", ".$this->req_per_page."
            ");
            $site_id=site_id;
            $user_id=$this->uSes->get_val("user_id");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            if(!$this->uSes->access(8)&&!$this->uSes->access(9)) /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $this->qRequests[$i]=$stm->fetch(PDO::FETCH_ASSOC); $i++) {};
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

        //get requests count
        try {
            $q_status=" (u235_requests.tic_status='case_closed' OR u235_requests.tic_status='req_closed') ";
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            COUNT(DISTINCT u235_requests.tic_id)
            FROM
            u235_requests
            JOIN
            u235_msgs
            ON
            u235_requests.tic_id=u235_msgs.tic_id AND 
            u235_requests.site_id=u235_msgs.site_id
            WHERE  ".
                $this->q_search.$this->q_com.$q_status.
                "  AND
            u235_requests.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            if(!$this->uSes->access(8)&&!$this->uSes->access(9)) /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$stm->fetch(PDO::FETCH_ASSOC);
            $this->req_found=$qr["COUNT(DISTINCT u235_requests.tic_id)"];
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    public function catId2Title($cat_id) {
        if(!isset($this->catId2Title_ar[$cat_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
                cat_title
                FROM
                u235_requests_cats
                WHERE
                cat_id=:cat_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                    $cat_title=uString::sql2text($qr->cat_title);
                    $this->catId2Title_ar[$cat_id]=$cat_title;
                    return $cat_title;
                }
                return '';
            }
            catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        }
        return $this->catId2Title_ar[$cat_id];
    }
    public function userId2names($user_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT DISTINCT
            firstname,
            secondname,
            u235_users.lastname
            FROM
            u235_users
            JOIN 
            u235_usersinfo
            ON
            u235_users.user_id=u235_usersinfo.user_id
            WHERE
            u235_users.user_id=:user_id AND
            
            u235_usersinfo.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
        return 0;
    }
    public function com_id2title($com_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            com_title
            FROM
            u235_comps
            WHERE
            site_id=:site_id AND
            com_id=:com_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return uString::sql2text($qr->com_title);
            else return "";
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
        return "";
    }
    public function insertPageNums() {
        $cnt='';
        //echo $this->pageNumber;
        if($this->pageNumber>1) {
            $cnt='<ul class="pagination">';
            $butNum=4;//number of buttons before and after
            $start=0;
            $end=$this->pageNumber;
            if($this->pageNumber>$butNum*2) {
                $start=($this->curPage-$butNum)<0?0:($this->curPage-$butNum);
                $end=($this->curPage+$butNum)>$this->pageNumber?$this->pageNumber:($this->curPage+$butNum);
                if(($start+$end)<$this->pageNumber) $end=($start+$butNum*2)<$this->pageNumber?$start+$butNum*2:$this->pageNumber;
            }
            if($start>0) {
                $cnt.='<li><a href="'.u_sroot.'uSupport/requests_search/?page='.($start-1).'&search='.$this->search_string.'">&laquo;</a></li>';
            }
            for($i=$start;$i<$end;$i++) {
                    $cnt.='<li '; if($this->curPage==$i) $cnt.='class="active"'; $cnt.='><a href="'.u_sroot.'uSupport/requests_search?page='.$i.'&search='.$this->search_string.'">'.($i+1).'</a></li>';
            }
            if($end<$this->pageNumber) {
                $cnt.='<li><a href="'.u_sroot.'uSupport/requests_search/?page='.($end).'&search='.$this->search_string.'">&raquo;</a></li>';
            }
            $cnt.='</ul>';
        }
        return $cnt;
    }
    private function check_access() {
        $this->companies_count=0;
        $this->is_com_client=$this->is_com_admin=$this->is_consultant=$this->is_operator=$this->has_access=false;
        //consultant or operator
        if($this->uSes->access(9)) {
            $this->is_operator=true;
            return true;
        }
        if($this->uSes->access(8)) {
            $this->is_consultant=true;
            return true;
        }
        //check if client of any company or admin
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            com_id,
            admin
            FROM
            u235_com_users
            WHERE
            user_id=:user_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $user_id=$this->uSes->get_val("user_id");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $com_ar[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};

            if($this->companies_count=count($com_ar)-1) {
                $this->qu_comps="(";
                for($i=0;$com=$com_ar[$i];$i++) {
                    if($this->companies_count==1) $this->def_com_id=$com->com_id;
                    if($com->admin=='1') $this->is_com_admin=true;
                    $this->qu_comps.="company_id='".$com->com_id."' OR ";
                }
                $this->qu_comps.="1=0)";
                if(!$this->is_com_admin) $this->is_com_client=true;
                return true;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

        //check if we can receive request from users not in companies
        if($this->uCore->uFunc->getConf("receive_only_from_comps_users","uSup")=='0') return true;
        return false;
    }
    private function get_cons_users() {
        //get consultants and operators
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT DISTINCT
            u235_users.user_id,
            firstname,
            secondname,
            lastname
            FROM
            u235_users
            JOIN 
            u235_usersinfo
            ON
            u235_usersinfo.user_id=u235_users.user_id AND 
            u235_usersinfo.status=u235_users.status
            JOIN 
            u235_usersinfo_groups
            ON
            u235_usersinfo_groups.user_id=u235_usersinfo.user_id AND
            u235_usersinfo_groups.site_id=u235_usersinfo.site_id
            WHERE
            u235_users.status='active' AND
            u235_usersinfo.site_id=:site_id AND
            (group_id=4 OR group_id=5)
            ORDER BY
            firstname ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $this->q_cons_list[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}
    }
    public function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);


        if($this->uSes->access(2)) {
            if($this->check_access()) {
                $this->has_access=true;

                $this->defCurPage();
                $this->search();
                $this->get_cons_users();
                $this->setLabels();
                $this->pageNumber=ceil($this->req_found/$this->req_per_page);
            }
        }
    }
}

$uSupport=new requests_search($this);

//ВЫВОД
ob_start();
if($uSupport->uSes->access(2)) {
    if($uSupport->has_access) {
        //datepicker
        $this->uFunc->incJs(u_sroot."js/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js");
        $this->uFunc->incJs(u_sroot."js/bootstrap-datepicker/dist/locales/bootstrap-datepicker.ru.min.js");
        $this->uFunc->incCss(u_sroot."js/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css");

    $this->uFunc->incJs(u_sroot.'uSupport/js/requests_search.min.js');
    $this->uFunc->incJs(u_sroot.'uSupport/js/request_show_common.min.js');
    if(isset($_GET['search'])) {?>
        <div style="float: right; display: table">
            <div class="btn-group">
                <form class="form-inline" style="display: table;">
                    <div class="input-group">
                        <input name="search" placeholder="Поиск по запросам" class="form-control input-sm" value="<?=(isset($_GET['search'])?$_GET['search']:'')?>">
                    <span class="input-group-btn">
                        <button class="btn btn-default btn-sm">
                            <span class="glyphicon glyphicon-search"></span>
                        </button>
                    </span>
                    </div>
                </form>
            </div>
        </div>

        <div>
            <a href="<?=u_sroot?>uSupport/requests" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-arrow-left"></span> Запросы</a>
        </div>

    <div class="uSup_requests">
    <h1>Поиск <?=htmlspecialchars($_GET['search'])?></h1>
        <h3>Запросов найдено <?=$uSupport->req_found?></h3>
        <?=$uSupport->insertPageNums()?>
    <? if(count($uSupport->qRequests)>1) { ?>
        <div><table class="table table-striped table-condensed">
                <? for($i=0;$data=$uSupport->qRequests[$i];$i++) {
                    //Достаем имя пользователя, последнего сменившего запрос
                    $data['tic_subject']=uString::sql2text($data['tic_subject']); //Деконвертируем заголовки тикетов
                    $data['msg_changed_user_name']='';
                    $data['msg_changed_user_id']=0;
                    $user_names=$uSupport->userId2names($data['user_id']);
                    ?>
                    <tr id="tr_tic_<?=$data['tic_id']?>">
                        <td><button onclick="uSup.show_request(<?=$data['tic_id']?>)"
                            <?=$uSupport->status2Icon_ar[$data['tic_status']]?><br>
                            <?=$data['tic_id']?>
                            </button>
                        </td>
                        <td>
                            <a href="<?=u_sroot?>uSupport/request_show/<?=$data['tic_id']?>" onclick="uSup.show_request(<?=$data['tic_id']?>); return false;"><?=$data['tic_subject']?></a><br>
                            <div class="pull-left">
                                <div class="btn-group">
                                    <?if($data['tic_status']=='req_closed'||$data['tic_status']=='case_closed') {
                                        if(
                                        ($uSupport->uSes->access(8)||$uSupport->uSes->access(9))&&
                                        ($data['tic_feedback_info']=='positive'||$data['tic_feedback_info']=='negative'||$data['tic_feedback_info']=='neutral')
                                        ){?>
                                            <a href="<?=u_sroot?>uSupport/request_admin_feedback_show/<?=$data['tic_id']?>" class="btn btn-default btn-xs <?=($data['tic_feedback_info']=='negative')?'btn-danger':''?> <?=($data['tic_feedback_info']=='positive')?'btn-success':''?> uTooltip pull-left" title="Клиент оставил отзыв на этот запрос" target="_blank"><span class="glyphicon glyphicon-thumbs-<?=($data['tic_feedback_info']=='negative')?'down':'up'?>"></span></a>
                                        <?}?>
                                        <?if($data['uknowbase_solution_isset']=='1') {?>
                                            <button type="button" id="uSup_uKnowbase_btn1" onclick="uSup.open_solution(<?=$data['tic_id']?>)" class="uknowbase_rec_btn btn btn-default btn-xs uTooltip" title="Есть решение в базе знаний."><span class="glyphicon glyphicon-book"></span></button>
                                        <?}?>
                                    <?}?>
                                </div>
                            </div>&nbsp;
                            <small class="text-muted">
                                <?$subject=$uSupport->catId2Title($data['tic_cat']);
                                if(!empty($subject)) echo '<b>'.$uSupport->catId2Title($data['tic_cat']).'</b>, ';?>
                                    <a target="_blank" href="<?=u_sroot?>uAuth/profile/<?=$data['user_id']?>"><?=$user_names['firstname'].' '.$user_names['secondname'].' '.$user_names['lastname']?></a>,
                                    <a target="_blank" href="<?=u_sroot?>uSupport/company_info/<?=$data['company_id']?>"><?=$uSupport->com_id2title($data['company_id'])?></a>
                                <?=date ('d.m.Y H:i' ,$data['tic_changed_timestamp'])?>
                            </small>
                        </td>
                    </tr>
                <? } ?>
            </table>
        </div>
        <?if(count($uSupport->qRequests)>6) echo $uSupport->insertPageNums()?>

        <!--modals-->
        <div class="modal fade" id="uSup_open_req_dg" tabindex="-1" role="dialog" aria-labelledby="uSup_open_req_dgLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="uSup_open_req_dgLabel">Назначить решение к запросу</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Нажмите на решение - оно откроется в новой вкладке</p>
                        <div id="uSup_open_req_solutions"></div>
                    </div>
                </div>
            </div>
        </div>
        <?include_once 'inc/requests_search_dialogs.php';?>
        <?include_once 'inc/request_show_dialogs.php';?>
        <script type="text/javascript">
            if(typeof uSup==="undefined") uSup={};
            uSup.search_query="<?=rawurlencode($_GET['search'])?>";
            uSup.cur_page=<?=$uSupport->curPage?>;
        </script>
    <?}
    else {?>
        <p>Ничего не найдено</p>
        <p>Я нахожу только то, что есть в тексте сообщения. Я не понимаю, что фраза "правильный запрос" и "правильные запросы" - одно и то же.</p>
        <p>Попробуйте использовать более простой запрос без лишних слов, например вместо "Как ввести правильный запрос в техподдержку" пишите "Правильный запрос".</p>
        <p>Используйте поисковые слова в ином числе или склонении, например вместо "Правильные слова запроса" пишите "Правильный запрос".</p>
        <p>Лучше всего вводите запрос без части слова со склонением или числом, то есть "правильн<b>ы</b> запрос" вместо "Правильный запрос" - уберите лишние буквы в конце. Я легко найду то, что вы ищите лишь по части слова.</p>
    <?}?>
    </div>
    <?}
    else {?>
        <div class="jumbotron">
            <h1>Поиск по запросам техподдержки</h1>
            <form>
            <div class="form-group">
                <div class="input-group">
                    <input placeholder="Что искать?" class="form-control" name="search">
                        <span class="input-group-btn">
                            <button id="uEditor_pages_admin_filter_btn" class="btn btn-default"><span class="glyphicon glyphicon-search" onclick="uEditor.filter()"></span></button>
                        </span>
                </div>
            </div>
            </form>
            <p class="help-block">Поиск найдет только то, что есть в тексте или заголовке запроса или сообщений внутри.<br>
                Фраза "правильный запрос" и "правильные запросы" - разные запросы.<br>
                Используйте простой запрос без лишних слов, например вместо "Как ввести правильный запрос в техподдержку" пишите "Правильный запрос".<br>
                <br>
                Можно вводить слова без части слова со склонением или числом, то есть "правильны запрос" вместо "Правильный запрос" - уберите лишние буквы в конце.<br>
                Поиск легко найдет то, что Вы ищите лишь по части слова.
            </p>
        </div>
    <?}
    }
    else {
        ?>
        <div class="jumbotron">
            <h1 class="page-header">Техническая поддержка.</h1>
            <p>Пожалуйста, авторизуйтесь</p>
            <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
        </div>
    <?}
}
else {
        ?>
        <div class="jumbotron">
            <h1 class="page-header">Техническая поддержка</h1>
            <p>Пожалуйста, авторизуйтесь</p>
            <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
        </div>
    <?}
$this->page_content=ob_get_contents();
ob_end_clean();

/** @noinspection PhpIncludeInspection */
include "templates/template.php";
?>

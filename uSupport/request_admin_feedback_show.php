<?php
class uSup_request_admin_feedback_show {
    private $uCore;
    public $tic_id,$tic,$html,$feedback,$author,$cons;

    private function error($text) {
        $this->html=$text;
        return false;
    }
    private function check_data() {
        if(!isset($this->uCore->url_prop[1])) {
            if(!isset($_POST['tic_id'])) return $this->error("Такой страницы не существует");
            else $this->tic_id=$_POST['tic_id'];
        }
        else $this->tic_id=$this->uCore->url_prop[1];
        if(!uString::isDigits($this->tic_id)) return $this->error("Такой страницы не существует");

        return true;
    }
    private function get_tic_data() {
        //get tic_subject, consultant and owner
        if(!$query=$this->uCore->query("uSup","SELECT
        `tic_subject`,
        `user_id`,
        `cons_id`
        FROM
        `u235_requests`
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(10);
        if(!mysqli_num_rows($query)) return $this->error("Такой страницы не существует");
        $this->tic=$query->fetch_object();
    }
    private function get_feedback() {
        //check if there are still no feedback registered for this tic_id
        if(!$query=$this->uCore->query("uSup","SELECT
        `ans1`,
        `ans2`,
        `ans3`,
        `ans4`,
        `ans5`,
        `ans6`,
        `ans7`,
        `ans8`,
        `ans9`,
        `ans10`,
        `ans11`,
        `ans1_comment`,
        `ans2_comment`,
        `ans3_comment`,
        `ans4_comment`,
        `ans5_comment`,
        `ans6_comment`,
        `ans7_comment`,
        `ans8_comment`,
        `ans9_comment`,
        `ans10_comment`,
        `ans11_comment`,
        `timestamp`
        FROM
        `u235_requests_feedbacks`
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(20);
        if(!mysqli_num_rows($query)) return $this->error("Такой страницы не существует");
        $this->feedback=$query->fetch_object();
    }
    private function get_author() {
        if(!$query=$this->uCore->query("uAuth","SELECT DISTINCT
        `firstname`,
        `secondname`,
        `lastname`
        FROM
        `u235_users`,
        `u235_usersinfo`
        WHERE
        `u235_users`.`user_id`='".$this->tic->user_id."' AND
        `u235_usersinfo`.`user_id`='".$this->tic->user_id."' AND
        `u235_users`.`status`='active' AND
        `u235_usersinfo`.`status`='active' AND
        `u235_usersinfo`.`site_id`='".site_id."'
        ")) $this->uCore->error(30);
        if(mysqli_num_rows($query)) {
            $user=$query->fetch_object();
            $this->author=$user->firstname.' '.$user->secondname.' '.$user->lastname;
        }
        else $this->author=false;
    }
    private function get_consultant() {
        if(!$query=$this->uCore->query("uAuth","SELECT DISTINCT
        `firstname`,
        `secondname`,
        `lastname`
        FROM
        `u235_users`,
        `u235_usersinfo`
        WHERE
        `u235_users`.`user_id`='".$this->tic->cons_id."' AND
        `u235_usersinfo`.`user_id`='".$this->tic->user_id."' AND
        `u235_users`.`status`='active' AND
        `u235_usersinfo`.`status`='active' AND
        `u235_usersinfo`.`site_id`='".site_id."'
        ")) $this->uCore->error(40);
        if(mysqli_num_rows($query)) {
            $user=$query->fetch_object();
            $this->cons=$user->firstname.' '.$user->secondname.' '.$user->lastname;
        }
        else $this->cons=false;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if($this->uCore->access(8)||$this->uCore->access(9)) {
            if(!$this->check_data()) return false;
            $this->get_tic_data();
            $this->get_feedback();
            $this->get_author();
            $this->get_consultant();
        }

        return true;
    }
}
$uSup=new uSup_request_admin_feedback_show($this);
if(!isset($_POST['tic_id'])) ob_start();
if($this->access(8)||$this->access(9)) {
    $questions_ar[1]='Насколько вы довольны услугами нашей компании в целом?';
    $ans_ar[1][5]='В высшей степени доволен';
    $ans_ar[1][4]='Очень доволен';
    $ans_ar[1][3]='Все устраивает';
    $ans_ar[1][2]='Скорее недоволен';
    $ans_ar[1][1]='Очень разочарован';

    $questions_ar[2]='Насколько удобно пользоваться технической поддержкой?';
    $ans_ar[2][5]='Очень удобно';
    $ans_ar[2][4]='Скорее удобно';
    $ans_ar[2][3]='Все устраивает';
    $ans_ar[2][2]='Неудобно';
    $ans_ar[2][1]='Очень неудобно';

    $questions_ar[3]='Насколько качественно специалист решил вашу проблему?';
    $ans_ar[3][5]='Я остался в высшей степени доволен';
    $ans_ar[3][4]='Я остался доволен';
    $ans_ar[3][3]='Меня все устроило';
    $ans_ar[3][2]='Некачественно';
    $ans_ar[3][1]='Я остался крайне недоволен';

    $questions_ar[4]='Насколько быстро специалист решил вашу проблему?';
    $ans_ar[4][5]='Я остался в высшей степени доволен';
    $ans_ar[4][4]='Очень быстро';
    $ans_ar[4][3]='Меня все устроило';
    $ans_ar[4][2]='Долго';
    $ans_ar[4][1]='Я остался крайне недоволен';

    $questions_ar[5]='Насколько быстро специалист откликался на ваши вопросы?';
    $ans_ar[5][5]='Крайне быстро';
    $ans_ar[5][4]='Очень быстро';
    $ans_ar[5][3]='Меня все устроило';
    $ans_ar[5][2]='Мне пришлось ждать';
    $ans_ar[5][1]='Я очень долго ждал';

    $questions_ar[6]='Были ли соблюдены оговоренные сроки решения проблемы?';
    $ans_ar[6][3]='Да <small>(или сроки не оговаривались)</small>';
    $ans_ar[6][1]='Нет';

    $questions_ar[7]='Насколько специалист был любезен с вами?';
    $ans_ar[7][5]='Я остался в высшей степени доволен';
    $ans_ar[7][4]='Достаточно любезен';
    $ans_ar[7][3]='Меня все устроило';
    $ans_ar[7][2]='Я остался не доволен';
    $ans_ar[7][1]='Я остался крайне недоволен';

    $questions_ar[8]='Как бы вы оценили компетентность специалиста?';
    $ans_ar[8][5]='В высшей степени компетентен';
    $ans_ar[8][4]='Достаточно компетентен';
    $ans_ar[8][3]='Меня все устроило';
    $ans_ar[8][2]='Плохо ориентируется в этой области';
    $ans_ar[8][1]='Совершенно не понимает эту область';

    $questions_ar[9]='Для решения этой проблемы вы обратились в техподдержку';
    $ans_ar[9][3]='Всего 1 раз';
    $ans_ar[9][2]='Со второго раза все решили';
    $ans_ar[9][1]='Мне пришлось обратиться более двух раз';

    $questions_ar[10]='Насколько вероятно, что вы обратитесь в нашу техподдержку еще раз?';
    $ans_ar[10][5]='Мне все понравилось. Буду обращаться';
    $ans_ar[10][3]='Меня все устроило. Обращусь, если будет нужно';
    $ans_ar[10][1]='Больше никогда не буду обращаться';

    $questions_ar[11]='Насколько вероятно, что вы посоветуете нашу техподдержку коллегам?';
    $ans_ar[11][5]='Обязательно посоветую';
    $ans_ar[11][3]='Скорее всего посоветую';
    $ans_ar[11][2]='Скорее нет';
    $ans_ar[11][1]='Никому не посоветую';

    $ans_val2context_color[1]='class="text-danger"';
    $ans_val2context_color[2]='class="text-warning"';
    $ans_val2context_color[3]='class="text-muted"';
    $ans_val2context_color[4]='class="text-primary"';
    $ans_val2context_color[5]='class="text-success"';
?>
    <div class="uSup_request_feedback">
        <?if(!isset($_POST['tic_id'])){?>
        <p><a href="<? echo u_sroot.$this->mod;?>/requests" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-arrow-left"></span> Запросы</a>
            <a href="<? echo u_sroot.$this->mod;?>/request_admin_feedbacks_list" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-arrow-left"></span> Отзывы</a></p>
        <?}?>
    <?if(is_null($uSup->html)) {
        if(!isset($_POST['tic_id'])){?>
        <h1 class="page-header"><?=$this->page['page_title']?><br><small><a href="<?=u_sroot.$this->mod?>/request_show/<?=$uSup->tic_id?>" target="_blank"><?=$uSup->tic->tic_subject?></a></small></h1>
        <?}?>

        <dl class="dl-horizontal">
            <dt>Автор запроса</dt>
            <dd><?if($uSup->author){?>
                    <a href="<?=u_sroot?>uAuth/profile/<?=$uSup->tic->user_id?>" target="_blank"><?=$uSup->author?></a>
                <?}
                else echo 'Пользователь не найден';
            ?></dd>
            <dt>Консультант</dt>
            <dd><?if($uSup->cons) {?>
                <a href="<?=u_sroot?>uAuth/profile/<?=$uSup->tic->cons_id?>" target="_blank"><?=$uSup->cons?></a>
                <?} else echo 'Консультант не назначен';
            ?></dd>
        </dl>


        <?for($i=1;$i<12;$i++) {
            $ans='ans'.$i;
            $ans_comment='ans'.$i.'_comment';
            if(isset($ans_ar[$i][$uSup->feedback->$ans])||!empty($uSup->feedback->$ans_comment)) {?>
                <h4><?=$questions_ar[$i]?></h4>
                <blockquote>
                    <?if(isset($ans_ar[$i][$uSup->feedback->$ans])) {?>
                        <p <?=$ans_val2context_color[$uSup->feedback->$ans]?>><?=$ans_ar[$i][$uSup->feedback->$ans]?></p>
                    <?}
                if(!empty($uSup->feedback->$ans_comment)) echo '<p>'.nl2br(uString::sql2text($uSup->feedback->$ans_comment)).'</p>'?>
                </blockquote>
            <?}?>
        <?}?>


    <?} else {?>
        <h1 class="page-header"><?=$this->page['page_title']?></h1>
    <p><?=$uSup->html?></p>
    <?}?>

    </div>
<?
} else {?>
    <div class="jumbotron">
        <h1 class="page-header">Техническая поддержка</h1>
        <?if($this->access(2)) {?>
            <p class="bg-danger">В доступе отказано</p>
            <p>У вас нет прав для просмотра этой страницы. Обратитесь к администратору.</p>
        <?} else {?>
            <p>Пожалуйста, авторизуйтесь</p>
            <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
        <?}?>
    </div>
<?}
if(!isset($_POST['tic_id'])) {
    $this->page_content=ob_get_contents();
    ob_end_clean();

    include "templates/u235/template.php";
}

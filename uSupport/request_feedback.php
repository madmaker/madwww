<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uSup_requests_feedback {
    public $uFunc;
    public $uSes;
    private $uCore;
    public $allow,$handler,$tic_subject,$html;

    private function error($text) {
        if(!isset($_POST['in_dialog'])) {
            $this->html=$text;
        }
        else die($text);
        return false;
    }
    public function check_data() {
        $feedback_time_limit=604800;//7days

        if(!isset($this->uCore->url_prop[1])) {
            if(!isset($_POST['tic_id'])) return $this->error("Такой страницы не существует");
            else $this->handler=$_POST['tic_id'];
        }
        else $this->handler=$this->uCore->url_prop[1];
        if(uString::isDigits($this->handler)&&$this->uCore->access(2)) {//must be tic_id
            if(!$query=$this->uCore->query("uSup","SELECT
            `tic_id`,
            `tic_subject`
            FROM
            `u235_requests`
            WHERE
            `tic_id`='".$this->handler."' AND
            `site_id`='".site_id."' AND
            `user_id`='".$this->uSes->get_val("user_id")."'
            ")) $this->uCore->error(1);
            if(!mysqli_num_rows($query)) return $this->error("Такой страницы не существует");
            $tic=$query->fetch_object();
        }
        else if(uString::isHash($this->handler)) {//must be hash link
            if(!$query=$this->uCore->query("uSup","SELECT
            `tic_id`,
            `tic_subject`,
            `tic_changed_timestamp`
            FROM
            `u235_requests`
            WHERE
            `tic_feedback_info`='".$this->handler."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(2);
            if(!mysqli_num_rows($query)) return $this->error("Такой страницы не существует");
            $tic=$query->fetch_object();
            if($tic->tic_changed_timestamp<(time()-$feedback_time_limit)) return $this->error("Такой страницы не существует");
        }
        else return $this->error("Такой страницы не существует");

        $this->tic_subject=uString::sql2text($tic->tic_subject);

        //check if there are still no feedback registered for this tic_id
        if(!$query=$this->uCore->query("uSup","SELECT
        `tic_id`
        FROM
        `u235_requests_feedbacks`
        WHERE
        `tic_id`='".$tic->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
        if(mysqli_num_rows($query)) return $this->error("Мы уже приняли ваш отзыв на этот запрос");

        return true;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        //if($this->uCore->access(2)) {
            $this->allow=$this->check_data();
        //}
    }
}
$uSup=new uSup_requests_feedback($this);

if(!isset($_POST['in_dialog'])) ob_start();

/*if($this->access(2)) {*/?>
    <div class="uSup_request_feedback">
    <?if(!isset($_POST['in_dialog'])){?><h1 class="page-header"><?=$this->page['page_title']?><br><small><?=$uSup->tic_subject?></small></h1><?}
    else {?><input type="hidden" id="uSup_request_feedback_tic_id" value="<?=$uSup->handler?>"><?}?>

    <?if($uSup->allow) {?>
        <form id="uSup_request_feedback_form" method="post" action="<?=u_sroot.$this->mod?>/<?=$this->page_name?>_2/<?=$uSup->handler?>">
        <h3>Насколько вы довольны услугами нашей компании в целом?</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans1" id="ans1_5" value="5">
                        В высшей степени доволен
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans1" id="ans1_4" value="4">
                        Очень доволен
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans1" id="ans1_3" value="3">
                        Все устраивает
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans1" id="ans1_2" value="2">
                        Скорее недоволен
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans1" id="ans1_1" value="1">
                        Очень разочарован
                    </label></div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Комментарий</label>
                    <textarea name="ans1_comment" id="ans1_comment" class="form-control" placeholder="Вы можете написать, почему поставили такую оценку"></textarea>
                    <span class="help-block">Не более 255 символов.</span>
                </div>
            </div>
        </div>

        <h3>Насколько удобно пользоваться технической поддержкой?</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans2" id="ans2_5" value="5">
                        Очень удобно
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans2" id="ans2_4" value="4">
                        Скорее удобно
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans2" id="ans2_3" value="3">
                        Все устраивает
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans2" id="ans2_2" value="2">
                        Неудобно
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans2" id="ans2_1" value="1">
                        Очень неудобно
                    </label></div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Комментарий</label>
                    <textarea name="ans2_comment" id="ans2_comment" class="form-control" placeholder="Вы можете написать, почему поставили такую оценку"></textarea>
                    <span class="help-block">Не более 255 символов.</span>
                </div>
            </div>
        </div>

        <h3>Насколько качественно специалист решил вашу проблему?</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans3" id="ans3_5" value="5">
                        Я остался в высшей степени доволен
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans3" id="ans3_4" value="4">
                        Я остался доволен
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans3" id="ans3_3" value="3">
                        Меня все устроило
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans3" id="ans3_2" value="2">
                        Некачественно
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans3" id="ans3_1" value="1">
                        Я остался крайне недоволен
                    </label></div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Комментарий</label>
                    <textarea name="ans3_comment" id="ans3_comment" class="form-control" placeholder="Вы можете написать, почему поставили такую оценку"></textarea>
                    <span class="help-block">Не более 255 символов.</span>
                </div>
            </div>
        </div>

        <h3>Насколько быстро специалист решил вашу проблему?</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans4" id="ans4_5" value="5">
                        Я остался в высшей степени доволен
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans4" id="ans4_4" value="4">
                        Очень быстро
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans4" id="ans4_3" value="3">
                        Меня все устроило
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans4" id="ans4_2" value="2">
                        Долго
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans4" id="ans4_1" value="1">
                        Я остался крайне недоволен
                    </label></div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Комментарий</label>
                    <textarea name="ans4_comment" id="ans4_comment" class="form-control" placeholder="Вы можете написать, почему поставили такую оценку"></textarea>
                    <span class="help-block">Не более 255 символов.</span>
                </div>
            </div>
        </div>

        <h3>Насколько быстро специалист откликался на ваши вопросы?</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans5" id="ans5_5" value="5">
                        Крайне быстро
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans5" id="ans5_4" value="4">
                        Очень быстро
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans5" id="ans5_3" value="3">
                        Меня все устроило
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans5" id="ans5_2" value="2">
                        Мне пришлось ждать
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans5" id="ans5_1" value="1">
                        Я очень долго ждал
                    </label></div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Комментарий</label>
                    <textarea name="ans5_comment" id="ans5_comment" class="form-control" placeholder="Вы можете написать, почему поставили такую оценку"></textarea>
                    <span class="help-block">Не более 255 символов.</span>
                </div>
            </div>
        </div>

        <h3>Были ли соблюдены оговоренные сроки решения проблемы?</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans6" id="ans6_2" value="3">
                        Да <small>(или сроки не оговаривались)</small>
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans6" id="ans6_1" value="1">
                        Нет
                    </label></div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Комментарий</label>
                    <textarea name="ans6_comment" id="ans6_comment" class="form-control" placeholder="Вы можете написать, почему поставили такую оценку"></textarea>
                    <span class="help-block">Не более 255 символов.</span>
                </div>
            </div>
        </div>

        <h3>Насколько специалист был любезен с вами?</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans7" id="ans7_5" value="5">
                        Я остался в высшей степени доволен
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans7" id="ans7_4" value="4">
                        Достаточно любезен
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans7" id="ans7_3" value="3">
                        Меня все устроило
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans7" id="ans7_2" value="2">
                        Я остался не доволен
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans7" id="ans7_1" value="1">
                        Я остался крайне недоволен
                    </label></div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Комментарий</label>
                    <textarea name="ans7_comment" id="ans7_comment" class="form-control" placeholder="Вы можете написать, почему поставили такую оценку"></textarea>
                    <span class="help-block">Не более 255 символов.</span>
                </div>
            </div>
        </div>

        <h3>Как бы вы оценили компетентность специалиста?</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans8" id="ans8_5" value="5">
                        В высшей степени компетентен
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans8" id="ans8_4" value="4">
                        Достаточно компетентен
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans8" id="ans8_3" value="3">
                        Меня все устроило
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans8" id="ans8_2" value="2">
                        Плохо ориентируется в этой области
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans8" id="ans8_1" value="1">
                        Совершенно не понимает эту область
                    </label></div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Комментарий</label>
                    <textarea name="ans8_comment" id="ans8_comment" class="form-control" placeholder="Вы можете написать, почему поставили такую оценку"></textarea>
                    <span class="help-block">Не более 255 символов.</span>
                </div>
            </div>
        </div>

        <h3>Для решения этой проблемы вы обратились в техподдержку</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans9" id="ans9_3" value="3">
                        Всего 1 раз
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans9" id="ans9_2" value="2">
                        Со второго раза все решили
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans9" id="ans9_1" value="1">
                        Мне пришлось обратиться более двух раз
                    </label></div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Комментарий</label>
                    <textarea name="ans9_comment" id="ans9_comment" class="form-control" placeholder="Вы можете написать, почему поставили такую оценку"></textarea>
                    <span class="help-block">Не более 255 символов.</span>
                </div>
            </div>
        </div>

        <h3>Насколько вероятно, что вы обратитесь в нашу техподдержку еще раз?</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans10" id="ans10_5" value="5">
                        Мне все понравилось. Буду обращаться
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans10" id="ans10_3" value="3">
                        Меня все устроило. Обращусь, если будет нужно
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans10" id="ans10_1" value="1">
                        Больше никогда не буду обращаться
                    </label></div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Комментарий</label>
                    <textarea name="ans10_comment" id="ans10_comment" class="form-control" placeholder="Вы можете написать, почему поставили такую оценку"></textarea>
                    <span class="help-block">Не более 255 символов.</span>
                </div>
            </div>
        </div>

        <h3>Насколько вероятно, что вы посоветуете нашу техподдержку коллегам?</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans11" id="ans11_5" value="5">
                        Обязательно посоветую
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans11" id="ans11_3" value="3">
                        Скорее всего посоветую
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans11" id="ans11_2" value="2">
                        Скорее нет
                    </label></div>
                <div class="radio"><label class="radio-custom" data-initialize="radio" ><input  class="sr-only"  type="radio" name="ans11" id="ans11_1" value="1">
                        Никому не посоветую
                    </label></div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Комментарий</label>
                    <textarea name="ans11_comment" id="ans11_comment" class="form-control" placeholder="Вы можете написать, почему поставили такую оценку"></textarea>
                    <span class="help-block">Не более 255 символов.</span>
                </div>
            </div>
        </div>

        <?if(!isset($_POST['in_dialog'])){?><input type="submit" class="btn btn-primary" value="Отправить отзыв"><?}?>
        </form>
    <?} else {?>
    <p><?=$uSup->html?></p>
    <?}?>

    </div>
<?/*}
else {?>
    <div class="jumbotron">
        <h1 class="page-header">Техническая поддержка</h1>
        <p>Пожалуйста, авторизуйтесь</p>
        <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
    </div>
<?}*/
if(!isset($_POST['in_dialog'])) {
    $this->page_content=ob_get_contents();
    ob_end_clean();

    include "templates/template.php";
}

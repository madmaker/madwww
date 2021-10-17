<?php
try {
    /** @noinspection PhpUndefinedMethodInspection */
    $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                        header,
                        submit_btn_text,
                        show_name_field
                        FROM
                        el_config_uSubscr_news_form
                        WHERE 
                        cols_els_id=:cols_els_id AND 
                        site_id=:site_id
                    ");
    $site_id=site_id;
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

    /** @noinspection PhpUndefinedMethodInspection */
    $res=$stm->fetch(PDO::FETCH_OBJ);?>
<?}
catch(PDOException $e) {$this->uFunc->error('uPage_elements_uSubscr_news_form_cpe 10'/*.$e->getMessage()*/);}?>
<div class="container-fluid">
    <p><?=$res->header?></p>
    <?if((int)$res->show_name_field){?><input id="uPage_el_name_<?=$cols_els_id?>" class="form-control" type="text" placeholder="<?=$this->text("Name"/*Имя*/)?>" /><?}?>
    <input id="uPage_el_email_<?=$cols_els_id?>" class="form-control" type="text" placeholder="Email" />
    <button class="btn btn-sm btn-primary col-md-12" onclick="uPage_uSubscr_form_submit_<?=$cols_els_id?>()"><?=$res->submit_btn_text?></button>
</div>

<script type="text/javascript">
    var save_notice='';
    function uPage_uSubscr_form_submit_<?=$cols_els_id?>() {
        var show_name_field=<?=(int)$res->show_name_field?>;

        var user_name;
        if(show_name_field) user_name=$("#uPage_el_name_<?=$cols_els_id?>").val();
        else user_name="";

        var user_email=$("#uPage_el_email_<?=$cols_els_id?>").val();

        if(!uString.isEmail(user_email)) {
            pnotify_fc.show_stack_bar_top("<?=$this->text("Error"/*Ошибка*/)?>","<?=$this->text("Enter valid email please"/*Введите, пожалуйста, свой email*/)?>","error");
            return 0;
        }

        try{save_notice.remove()}catch(e){}
        save_notice=pnotify_fc.show_stack_bar_top("<?=$this->text("Your subscription is being updated"/*Подписка оформляется*/)?>","<?=$this->text("Wait please"/*Пожалуйста, подождите*/)?>","info",false,true);

        $.ajax({
            type: "POST",
            url: u_sroot+"uSubscr/subscribe_bg",
            data: 'user_name='+encodeURIComponent(user_name)+
            '&user_email='+encodeURIComponent(user_email)+
            '&cols_els_id='+<?=$cols_els_id?>,
            timeout:20000,
            success: function(answer){
                try{save_notice.remove()}catch(e){}
                try {
                    eval('('+answer+')');
                }
                catch (e) {
                    if (e instanceof SyntaxError) {
                        alert(answer);
                        return false;
                    }
                }
                var ans=eval('('+answer+')');
                if (ans['status'] == 'done') {
                    pnotify_fc.show_stack_bar_top("<?=$this->text("You've been subscribed"/*Подписка оформлена*/)?>","<?=$this->text("We've sent a verification message. Check your e-mail"/*Мы отправили сообщение с подтверждением. Проверьте ваш e-mail*/)?>","success");
                }
                else if(ans['status']=='error') {
                    if(ans['msg']=='user_email') {
                        pnotify_fc.show_stack_bar_top("<?=$this->text("Error")?>","<?=$this->text("Enter valid email please")?>","error");
                    }
                    else alert(ans['msg']);
                }
                else alert(answer);
            },
            error: function(){
                try{save_notice.remove()} catch(e){}
                uTemplate_common.ajax_timeout();
            }
        });
    }
</script>
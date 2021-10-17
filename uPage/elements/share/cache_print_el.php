<?php
try {
    /** @noinspection PhpUndefinedMethodInspection */
    $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                    show_fb, 
                    show_lj, 
                    show_mail, 
                    show_ok, 
                    show_twitter, 
                    show_vk, 
                    show_in, 
                    orientation, 
                    hide, 
                    share_btn_txt, 
                    shape, 
                    size
                    FROM
                    el_config_share
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
    <div>
        <?if((int)$res->hide){?><button class="btn btn-link uPage_share_btn_placeholder" onclick="uPage_share_btn_onClick()"><?=$res->share_btn_txt?></button><?}?>
        <div class="uPage_share
                <?if((int)$res->orientation){?> vertical <?} else {?> horizontal <?}?>
                <?if((int)$res->hide){?> hidden <?}?>
                <?if((int)$res->shape===0){?>  circle <?}?>
                <?if((int)$res->shape===1){?>  square <?}?>
                <?if((int)$res->shape===2){?>  transparent <?}?>
                <?if((int)$res->shape===3){?>  white <?}?>
                <?if((int)$res->size==0){?> small <?}?>
                <?if((int)$res->size==1){?> normal <?}?>
                <?if((int)$res->size==2){?> big <?}?>
                ">
            <?if((int)$res->show_fb){?><a href="" title="<?=$this->text("Share to facebook"/*Поделиться в Facebook*/)?> target="_blank" class="social_btn sb_facebook"></a><?}?>
            <?if((int)$res->show_lj){?><a href="" title="<?=$this->text("Publish in LJ"/*Опубликовать в LiveJournal*/)?>" target="_blank" class="social_btn sb_livejournal"></a><?}?>
            <?if((int)$res->show_mail){?><a href="" title="<?=$this->text("Share to mail.ru"/*Поделиться в Моем Мире@Mail.Ru*/)?>" target="_blank" class="social_btn sb_mailru"></a><?}?>
            <?if((int)$res->show_ok){?><a href="" title="<?=$this->text("Add to OK"/*Добавить в Одноклассники*/)?>" target="_blank" class="social_btn sb_odnoklassniki"></a><?}?>
            <?if((int)$res->show_twitter){?><a href="" title="<?=$this->text("Tweet"/*Добавить в Twitter*/)?>" target="_blank" class="social_btn sb_twitter"></a><?}?>
            <?if((int)$res->show_vk){?><a href="" title="<?=$this->text("Share to VK"/*Поделиться В Контакте*/)?>" target="_blank" class="social_btn sb_vkontakte"></a><?}?>
            <?if((int)$res->show_in){?><a href="" title="<?=$this->text("Share to Linked In"/*Поделиться В Linked In*/)?>" target="_blank" class="social_btn sb_in"></a><?}?>

            <script type="text/javascript">
                function uPage_share_init_btns() {
                    var sb_title = $('title').text();
                    var sb_title_array = sb_title.split('/');
                    if (sb_title_array.length >= 2) {
                        sb_title = sb_title_array[sb_title_array.length - 1];
                    }

                    var sb_url = document.location.href;
                    var sb_facebook = encodeURI("http://www.facebook.com/sharer.php?u="+sb_url+"&t="+sb_title);
                    var sb_livejournal = encodeURI("http://www.livejournal.com/update.bml?event="+sb_url+"&subject="+sb_title);
                    var sb_mailru = encodeURI("http://connect.mail.ru/share?url="+sb_url+"&title="+sb_title);
                    var sb_odnoklassniki = encodeURI("http://www.odnoklassniki.ru/dk?st.cmd=addShare&st._surl="+sb_url+"&title="+sb_title);
                    var sb_twitter = encodeURI("http://twitter.com/share?text="+sb_title);
                    var sb_vkontakte = encodeURI("http://vkontakte.ru/share.php?url="+sb_url+"&title="+sb_title);
                    var sb_in= encodeURI("http://www.linkedin.com/shareArticle?mini=true&url="+sb_url);
                    $('.sb_facebook').attr('href', sb_facebook);
                    $('.sb_livejournal').attr('href', sb_livejournal);
                    $('.sb_mailru').attr('href', sb_mailru);
                    $('.sb_odnoklassniki').attr('href', sb_odnoklassniki);
                    $('.sb_twitter').attr('href', sb_twitter);
                    $('.sb_vkontakte').attr('href', sb_vkontakte);
                    $('.sb_in').attr('href', sb_in);
                }
                <?if((int)$res->hide){?>
                function uPage_share_btn_onClick () {
                    $(".uPage_share_btn_placeholder").addClass("hidden");
                    $(".uPage_share").removeClass('hidden');
                }
                <?}?>

                $(document).ready(function() {
                    uPage_share_init_btns();
                });

            </script>
        </div>
    </div>
<?}
catch(PDOException $e) {$this->uFunc->error('uPage_elements_share_cache_print_el 10'/*.$e->getMessage()*/);}
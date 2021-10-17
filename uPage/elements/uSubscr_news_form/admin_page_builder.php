<?php
try {
    /** @noinspection PhpUndefinedMethodInspection */
    $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                                header,
                                submit_btn_text,
                                show_name_field,
                                channels_used
                                FROM
                                el_config_uSubscr_news_form
                                WHERE 
                                cols_els_id=:cols_els_id AND 
                                site_id=:site_id
                                ");
    $site_id=site_id;
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $element->cols_els_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

    /** @noinspection PhpUndefinedMethodInspection */
    $res=$stm->fetch(PDO::FETCH_OBJ);?>
    if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
    if (typeof uPage_setup_uPage.uSubscr_news_form2data=== "undefined") {uPage_setup_uPage.uSubscr_news_form2data=[];}

    uPage_setup_uPage.uSubscr_news_form2data[<?=$element->cols_els_id?>]=[];
    uPage_setup_uPage.uSubscr_news_form2data[<?=$element->cols_els_id?>]['header']=decodeURIComponent("<?=rawurlencode($res->header)?>");
    uPage_setup_uPage.uSubscr_news_form2data[<?=$element->cols_els_id?>]['submit_btn_text']=decodeURIComponent("<?=rawurlencode($res->submit_btn_text)?>");
    uPage_setup_uPage.uSubscr_news_form2data[<?=$element->cols_els_id?>]['show_name_field']=<?=$res->show_name_field?>;
    uPage_setup_uPage.uSubscr_news_form2data[<?=$element->cols_els_id?>]['channels_used']="<?=$res->channels_used?>";
<?}
catch(PDOException $e) {$this->uFunc->error('uPage_elements_uSubscr_news_form_apb 10'/*.$e->getMessage()*/);}
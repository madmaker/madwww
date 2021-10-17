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
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $element->cols_els_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

    /** @noinspection PhpUndefinedMethodInspection */
    $res=$stm->fetch(PDO::FETCH_OBJ);?>
    if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
    if (typeof uPage_setup_uPage.share2data=== "undefined") {uPage_setup_uPage.share2data=[]}

    uPage_setup_uPage.share2data[<?=$element->cols_els_id?>]=[];
    uPage_setup_uPage.share2data[<?=$element->cols_els_id?>]['show_fb']=<?=$res->show_fb?>;
    uPage_setup_uPage.share2data[<?=$element->cols_els_id?>]['show_lj']=<?=$res->show_lj?>;
    uPage_setup_uPage.share2data[<?=$element->cols_els_id?>]['show_mail']=<?=$res->show_mail?>;
    uPage_setup_uPage.share2data[<?=$element->cols_els_id?>]['show_ok']=<?=$res->show_ok?>;
    uPage_setup_uPage.share2data[<?=$element->cols_els_id?>]['show_twitter']=<?=$res->show_twitter?>;
    uPage_setup_uPage.share2data[<?=$element->cols_els_id?>]['show_vk']=<?=$res->show_vk?>;
    uPage_setup_uPage.share2data[<?=$element->cols_els_id?>]['show_in']=<?=$res->show_in?>;
    uPage_setup_uPage.share2data[<?=$element->cols_els_id?>]['orientation']=<?=$res->orientation?>;
    uPage_setup_uPage.share2data[<?=$element->cols_els_id?>]['hide']=<?=$res->hide?>;
    uPage_setup_uPage.share2data[<?=$element->cols_els_id?>]['share_btn_txt']=decodeURIComponent("<?=rawurlencode($res->share_btn_txt)?>");
    uPage_setup_uPage.share2data[<?=$element->cols_els_id?>]['shape']=<?=$res->shape?>;
    uPage_setup_uPage.share2data[<?=$element->cols_els_id?>]['size']=<?=$res->size?>;
<?}
catch(PDOException $e) {$this->uFunc->error('uPage_elements_share_admin_page_builder 10'/*.$e->getMessage()*/);}
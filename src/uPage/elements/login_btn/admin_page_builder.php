<?php
try {
    /** @noinspection PhpUndefinedMethodInspection */
    $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                                btn_primary,
                                btn_info,
                                btn_success,
                                btn_danger,
                                btn_warning,
                                btn_sm,
                                btn_xs,
                                btn_lg,
                                btn_text,
                                replace_with_logout,
                                logout_text
                                FROM
                                el_config_login_btn
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

    if (typeof uPage_setup_uPage === "undefined") {uPage_setup_uPage={};}
    if (typeof uPage_setup_uPage.login_btn2data === "undefined") {uPage_setup_uPage.login_btn2data=[];}

    uPage_setup_uPage.login_btn2data[<?=$element->cols_els_id?>]=[];
    uPage_setup_uPage.login_btn2data[<?=$element->cols_els_id?>]['btn_primary']=<?=$res->btn_primary?>;
    uPage_setup_uPage.login_btn2data[<?=$element->cols_els_id?>]['btn_info']=<?=$res->btn_info?>;
    uPage_setup_uPage.login_btn2data[<?=$element->cols_els_id?>]['btn_success']=<?=$res->btn_success?>;
    uPage_setup_uPage.login_btn2data[<?=$element->cols_els_id?>]['btn_danger']=<?=$res->btn_danger?>;
    uPage_setup_uPage.login_btn2data[<?=$element->cols_els_id?>]['btn_warning']=<?=$res->btn_warning?>;
    uPage_setup_uPage.login_btn2data[<?=$element->cols_els_id?>]['btn_sm']=<?=$res->btn_sm?>;
    uPage_setup_uPage.login_btn2data[<?=$element->cols_els_id?>]['btn_xs']=<?=$res->btn_xs?>;
    uPage_setup_uPage.login_btn2data[<?=$element->cols_els_id?>]['btn_lg']=<?=$res->btn_lg?>;
    uPage_setup_uPage.login_btn2data[<?=$element->cols_els_id?>]['replace_with_logout']=<?=$res->replace_with_logout?>;
    uPage_setup_uPage.login_btn2data[<?=$element->cols_els_id?>]['btn_text']=decodeURIComponent("<?=rawurlencode($res->btn_text)?>");
    uPage_setup_uPage.login_btn2data[<?=$element->cols_els_id?>]['logout_text']=decodeURIComponent("<?=rawurlencode($res->logout_text)?>");
<?}
catch(PDOException $e) {$this->uFunc->error('uPage_elements_login_btn_admin_page_builder 10'/*.$e->getMessage()*/);}
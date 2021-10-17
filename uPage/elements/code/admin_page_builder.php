<?php
    try {
        /** @noinspection PhpUndefinedMethodInspection */
        $stm = $this->uFunc->pdo("uPage")->prepare("SELECT
        code,
        do_not_run_in_editor
        FROM
        el_config_code
        WHERE 
        cols_els_id=:cols_els_id AND 
        site_id=:site_id
        ");
        $site_id = site_id;
        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $element->cols_els_id, PDO::PARAM_INT);
        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
        /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

        /** @noinspection PhpUndefinedMethodInspection */
        $res = $stm->fetch(PDO::FETCH_OBJ);
        if(!$res) {
            $res=new stdClass();
            $res->do_not_run_in_editor=1;
            $res->code="";
        }?>

        if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
        if (typeof uPage_setup_uPage.code2data=== "undefined") {uPage_setup_uPage.code2data=[]}

        uPage_setup_uPage.code2data[<?= $element->cols_els_id ?>]=[];
        uPage_setup_uPage.code2data[<?= $element->cols_els_id ?>]['do_not_run_in_editor']=<?=$res->do_not_run_in_editor?>;
        uPage_setup_uPage.code2data[<?= $element->cols_els_id ?>]['code']=decodeURIComponent("<?=rawurlencode($res->code)?>");
    <?
    } catch (PDOException $e) {
        $this->uFunc->error('uPage_elements_code_admin_page_builder 10'/*.$e->getMessage()*/);
    }
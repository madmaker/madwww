<?php
    try {
        /** @noinspection PhpUndefinedMethodInspection */
        $stm = $this->uFunc->pdo("uPage")->prepare("SELECT
        background_stretch,
        background_repeat_x,
        background_repeat_y,
        background_color,
        background_img,
        background_position,
        min_height,
        font_color,
        font_size,
        text
        FROM
        el_config_banner
        WHERE 
        cols_els_id=:cols_els_id AND 
        site_id=:site_id
        ");
        $site_id = site_id;
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        $stm->bindParam(':cols_els_id', $element->cols_els_id, PDO::PARAM_INT);
        /** @noinspection PhpUndefinedMethodInspection */
        $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
        /** @noinspection PhpUndefinedMethodInspection */
        $stm->execute();

        /** @noinspection PhpUndefinedMethodInspection */
        $res = $stm->fetch(PDO::FETCH_OBJ); ?>

        if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
        if (typeof uPage_setup_uPage.banner2data=== "undefined") {uPage_setup_uPage.banner2data=[]}

        uPage_setup_uPage.banner2data[<?= $element->cols_els_id ?>]=[];
        uPage_setup_uPage.banner2data[<?= $element->cols_els_id ?>]['background_stretch']=<?=$res->background_stretch?>;
        uPage_setup_uPage.banner2data[<?= $element->cols_els_id ?>]['background_repeat_x']=<?=$res->background_repeat_x?>;
        uPage_setup_uPage.banner2data[<?= $element->cols_els_id ?>]['background_repeat_y']=<?=$res->background_repeat_y?>;
        uPage_setup_uPage.banner2data[<?= $element->cols_els_id ?>]['background_color']="<?=$res->background_color?>";
        uPage_setup_uPage.banner2data[<?= $element->cols_els_id ?>]['background_img']=decodeURIComponent("<?=rawurlencode($res->background_img)?>");
        uPage_setup_uPage.banner2data[<?= $element->cols_els_id ?>]['background_position']=<?=$res->background_position?>;
        uPage_setup_uPage.banner2data[<?= $element->cols_els_id ?>]['min_height']=<?=$res->min_height?>;
        uPage_setup_uPage.banner2data[<?= $element->cols_els_id ?>]['font_color']="<?=$res->font_color?>";
        uPage_setup_uPage.banner2data[<?= $element->cols_els_id ?>]['font_size']=<?=$res->font_size?>;
        uPage_setup_uPage.banner2data[<?= $element->cols_els_id ?>]['text']=decodeURIComponent("<?=rawurlencode($res->text)?>");
    <?
    } catch (PDOException $e) {
        $this->uFunc->error('/uPage/elements/banner/admin_page_builder/10'/*.$e->getMessage()*/);
    }
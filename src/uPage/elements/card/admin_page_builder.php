<?php
    try {
        /** @noinspection PhpUndefinedMethodInspection */
        $stm = $this->uFunc->pdo("uPage")->prepare("SELECT
        card_background_color,
        card_img_url,
        card_img_size,
        card_img_position,
        card_text,
        card_min_height_lg,
        card_min_height_md,
        card_min_height_sm,
        card_min_height_xs,
        card_font_size,
        card_font_color
        FROM
        el_config_card
        WHERE 
        cols_els_id=:cols_els_id AND 
        site_id=:site_id
        ");
        $site_id = site_id;
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */$stm->bindParam(':cols_els_id', $element->cols_els_id, PDO::PARAM_INT);
        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
        /** @noinspection PhpUndefinedMethodInspection */
        $stm->execute();

        /** @noinspection PhpUndefinedMethodInspection */
        $res = $stm->fetch(PDO::FETCH_OBJ); ?>

        if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
        if (typeof uPage_setup_uPage.card2data=== "undefined") {uPage_setup_uPage.card2data=[]}


        uPage_setup_uPage.card2data[<?= $element->cols_els_id ?>]=[];
        uPage_setup_uPage.card2data[<?= $element->cols_els_id ?>]['card_background_color']="<?=$res->card_background_color?>";
        uPage_setup_uPage.card2data[<?= $element->cols_els_id ?>]['card_img_url']=decodeURIComponent("<?=rawurlencode($res->card_img_url)?>");
        uPage_setup_uPage.card2data[<?= $element->cols_els_id ?>]['card_img_size']=<?=$res->card_img_size?>;
        uPage_setup_uPage.card2data[<?= $element->cols_els_id ?>]['card_img_position']=<?=$res->card_img_position?>;
        uPage_setup_uPage.card2data[<?= $element->cols_els_id ?>]['card_text']=decodeURIComponent("<?=rawurlencode($res->card_text)?>");
        uPage_setup_uPage.card2data[<?= $element->cols_els_id ?>]['card_min_height_lg']=<?=$res->card_min_height_lg?>;
        uPage_setup_uPage.card2data[<?= $element->cols_els_id ?>]['card_min_height_md']=<?=$res->card_min_height_md?>;
        uPage_setup_uPage.card2data[<?= $element->cols_els_id ?>]['card_min_height_sm']=<?=$res->card_min_height_sm?>;
        uPage_setup_uPage.card2data[<?= $element->cols_els_id ?>]['card_min_height_xs']=<?=$res->card_min_height_xs?>;
        uPage_setup_uPage.card2data[<?= $element->cols_els_id ?>]['card_font_size']=<?=$res->card_font_size?>;
        uPage_setup_uPage.card2data[<?= $element->cols_els_id ?>]['card_font_color']="<?=$res->card_font_color?>";
    <?
    } catch (PDOException $e) {
        $this->uFunc->error('/uPage/elements/card/admin_page_builder/10'/*.$e->getMessage()*/);
    }
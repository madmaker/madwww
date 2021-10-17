<?php
    try {
        /** @noinspection PhpUndefinedMethodInspection */
        $stm = $this->uFunc->pdo("uPage")->prepare("SELECT
        zoom,
        map_center_lat,
        map_center_long,
        marker_lat,
        marker_long,
        marker_img_url,
        height,
        style_geometry_color,
        style_labels_text_fill_color,
        style_road_geometry_fill_color,
        style_road_geometry_stroke_color,
        style_landscape_geometry_color,
        style_landscape_man_made_geometry_stroke_color,
        style_poi_labels_text_fill_color
        FROM
        el_config_gmap
        WHERE 
        cols_els_id=:cols_els_id AND 
        site_id=:site_id
        ");
        $site_id = site_id;
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */$stm->bindParam(':cols_els_id', $element->cols_els_id, PDO::PARAM_INT);
        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
        /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

        /** @noinspection PhpUndefinedMethodInspection */$res = $stm->fetch(PDO::FETCH_OBJ);
        if($res) {?>

        if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
        if (typeof uPage_setup_uPage.gmap2data=== "undefined") {uPage_setup_uPage.gmap2data=[]}

        uPage_setup_uPage.gmap2data[<?= $element->cols_els_id ?>]=[];
        uPage_setup_uPage.gmap2data[<?= $element->cols_els_id ?>]['zoom']=<?=$res->zoom?>;
        uPage_setup_uPage.gmap2data[<?= $element->cols_els_id ?>]['map_center_lat']="<?=$res->map_center_lat?>";
        uPage_setup_uPage.gmap2data[<?= $element->cols_els_id ?>]['map_center_long']="<?=$res->map_center_long?>";
        uPage_setup_uPage.gmap2data[<?= $element->cols_els_id ?>]['marker_lat']="<?=$res->marker_lat?>";
        uPage_setup_uPage.gmap2data[<?= $element->cols_els_id ?>]['marker_long']="<?=$res->marker_long?>";
        uPage_setup_uPage.gmap2data[<?= $element->cols_els_id ?>]['marker_img_url']="<?=$res->marker_img_url?>";
        uPage_setup_uPage.gmap2data[<?= $element->cols_els_id ?>]['height']=<?=$res->height?>;
        uPage_setup_uPage.gmap2data[<?= $element->cols_els_id ?>]['style_geometry_color']="<?=$res->style_geometry_color?>";
        uPage_setup_uPage.gmap2data[<?= $element->cols_els_id ?>]['style_labels_text_fill_color']="<?=$res->style_labels_text_fill_color?>";
        uPage_setup_uPage.gmap2data[<?= $element->cols_els_id ?>]['style_road_geometry_fill_color']="<?=$res->style_road_geometry_fill_color?>";
        uPage_setup_uPage.gmap2data[<?= $element->cols_els_id ?>]['style_road_geometry_stroke_color']="<?=$res->style_road_geometry_stroke_color?>";
        uPage_setup_uPage.gmap2data[<?= $element->cols_els_id ?>]['style_landscape_geometry_color']="<?=$res->style_landscape_geometry_color?>";
        uPage_setup_uPage.gmap2data[<?= $element->cols_els_id ?>]['style_landscape_man_made_geometry_stroke_color']="<?=$res->style_landscape_man_made_geometry_stroke_color?>";
        uPage_setup_uPage.gmap2data[<?= $element->cols_els_id ?>]['style_poi_labels_text_fill_color']="<?=$res->style_poi_labels_text_fill_color?>";
    <?}
    } catch (PDOException $e) {
        $this->uFunc->error('uPage_elements_gmap_admin_page_builder 10'/*.$e->getMessage()*/);
    }
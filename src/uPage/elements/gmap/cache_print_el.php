<?php
try {
    /** @noinspection PhpUndefinedMethodInspection */
    $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
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
    $site_id=site_id;
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

    /** @noinspection PhpUndefinedMethodInspection */
    if($res=$stm->fetch(PDO::FETCH_OBJ)) {

    ?>
    <div class="uPage_gmap" id="uPage_gmap_<?=$cols_els_id?>">
        <style>#uPage_el_gmap_<?=$cols_els_id?> {height: <?=$res->height?>px;}</style>
        <div id="uPage_el_gmap_<?=$cols_els_id?>"></div>
        <script>
            function uPage_el_gmap_initMap_init() {
            var uPage_el_gmap_<?=$cols_els_id?> = new google.maps.Map(document.getElementById("uPage_el_gmap_<?=$cols_els_id?>"), {
                center: {lat: <?=$res->map_center_lat?>, lng: <?=$res->map_center_long?>},
                zoom: <?=$res->zoom?>,
                mapTypeId: 'roadmap',
                styles: [
                    {elementType: 'geometry', stylers: [{color: '#<?=$res->style_geometry_color?>'}]},
                    {elementType: 'labels.text.stroke', stylers: [{"visibility": "off"}]},
                    {elementType: 'labels.text.fill', stylers: [{color: '#<?=$res->style_labels_text_fill_color?>'}]},
                    {
                        featureType: 'road',
                        elementType: 'geometry.fill',
                        stylers: [{color: '#<?=$res->style_road_geometry_fill_color?>'}]
                    },
                    {
                        featureType: 'road',
                        elementType: 'geometry.stroke',
                        stylers: [{color: '#<?=$res->style_road_geometry_stroke_color?>'}]
                    },
                    {
                        featureType: "landscape",
                        elementType: "geometry",
                        stylers: [
                            {
                                "color": "#<?=$res->style_landscape_geometry_color?>"
                            },
                            {
                                "lightness": 20
                            }
                        ]
                    },
                    {
                        featureType: 'landscape.man_made',
                        elementType: 'geometry.stroke',
                        stylers: [{
                            color: '#<?=$res->style_landscape_man_made_geometry_stroke_color?>'
                        }]
                    },
                    {
                        featureType: 'poi',
                        elementType: 'all',
                        stylers: [
                            {visibility:"on"}
                        ]
                    },
                    {
                        featureType: 'poi',
                        elementType: 'labels.text.fill',
                        stylers: [
                            {visibility:"on"},
                            {color: '#<?=$res->style_poi_labels_text_fill_color?>'}
                        ]
                    }
                ]
            });
            var marker = new google.maps.Marker({
                position: {lat: <?=$res->marker_lat?>, lng: <?=$res->marker_long?>},
                map: uPage_el_gmap_<?=$cols_els_id?>,
                icon: "<?=$res->marker_img_url?>"
            });
            }
            </script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBj7vxm5QY_7MPomU0anUcaBMpV9zqqhjw&callback=uPage_el_gmap_initMap_init" async defer></script>
        </div>
<?}
}
catch(PDOException $e) {$this->uFunc->error('uPage_elements_gmap_cache_print_el  10'/*.$e->getMessage()*/);}
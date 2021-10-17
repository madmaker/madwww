<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uPage\common;
use uString;

class gmap{
    private $uPage;
    private $uFunc;
    private $uCore;

    private function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            * 
            FROM 
            el_config_gmap 
            WHERE
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_gmap_common 10'/*.$e->getMessage()*/);}

        return 0;
    }

    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!$el_settings=$this->get_el_settings($el->cols_els_id,$source_site_id)) return 0;

        //attach art to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'gmap',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO el_config_gmap (
            cols_els_id, 
            site_id, 
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
            ) VALUES (
            :cols_els_id, 
            :site_id, 
            :zoom, 
            :map_center_lat, 
            :map_center_long, 
            :marker_lat, 
            :marker_long, 
            :marker_img_url, 
            :height, 
            :style_geometry_color, 
            :style_labels_text_fill_color, 
            :style_road_geometry_fill_color, 
            :style_road_geometry_stroke_color, 
            :style_landscape_geometry_color, 
            :style_landscape_man_made_geometry_stroke_color, 
            :style_poi_labels_text_fill_color          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':zoom', $el_settings->zoom,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':map_center_lat', $el_settings->map_center_lat,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':map_center_long', $el_settings->map_center_long,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':marker_lat', $el_settings->marker_lat,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':marker_long', $el_settings->marker_long,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':marker_img_url', $el_settings->marker_img_url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':height', $el_settings->height,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_geometry_color', $el_settings->style_geometry_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_labels_text_fill_color', $el_settings->style_labels_text_fill_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_road_geometry_fill_color', $el_settings->style_road_geometry_fill_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_road_geometry_stroke_color', $el_settings->style_road_geometry_stroke_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_landscape_geometry_color', $el_settings->style_landscape_geometry_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_landscape_man_made_geometry_stroke_color', $el_settings->style_landscape_man_made_geometry_stroke_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_poi_labels_text_fill_color', $el_settings->style_poi_labels_text_fill_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_gmap_common 20'/*.$e->getMessage()*/);}

        return $cols_els_id;
    }

    public function attach_el2col($col_id,$el_id) {
        $el_pos=$this->uPage->define_new_el_pos($col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach element to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'gmap',$col_id,$el_id);

        $height=50;
        if(isset($_POST['height'])) {
            if((int)$_POST['height']>50) $height=$_POST['height'];
        }
        $res[0]='"height":"'.$height.'",'.$res[0];

        $map_center_lat=59.9479723;
        if(isset($_POST['map_center_lat'])) {
            str_replace(",",".",$_POST['map_center_lat']);
            $map_center_lat=(float)$_POST['map_center_lat'];
        }
        $res[0]='"map_center_lat":"'.$map_center_lat.'",'.$res[0];

        $map_center_long=30.3617107;
        if(isset($_POST['map_center_long'])) {
            str_replace(",",".",$_POST['map_center_long']);
            $map_center_long=(float)$_POST['map_center_long'];
        }
        $res[0]='"map_center_long":"'.$map_center_long.'",'.$res[0];

        $marker_lat=59.9479723;
        if(isset($_POST['marker_lat'])) {
            str_replace(",",".",$_POST['marker_lat']);
            $marker_lat=(float)$_POST['marker_lat'];
        }
        $res[0]='"marker_lat":"'.$marker_lat.'",'.$res[0];

        $marker_long=30.3617107;
        if(isset($_POST['marker_long'])) {
            str_replace(",",".",$_POST['marker_long']);
            $marker_long=(float)$_POST['marker_long'];
        }
        $res[0]='"marker_long":"'.$marker_long.'",'.$res[0];

        $zoom=17;
        if(isset($_POST['zoom'])) {
            if((int)$_POST['zoom']>0) $zoom=$_POST['zoom'];
        }
        $res[0]='"zoom":"'.$zoom.'",'.$res[0];


        $style_geometry_color="C8C8C8";
        if(isset($_POST['style_geometry_color'])) {
            $_POST['style_geometry_color']=str_replace("#","",trim($_POST['style_geometry_color']));
            if(uString::isHexColor($style_geometry_color)) $style_geometry_color=$_POST['style_geometry_color'];
        }
        $res[0]='"style_geometry_color":"'.$style_geometry_color.'",'.$res[0];


        $style_labels_text_fill_color="787c7f";
        if(isset($_POST['style_labels_text_fill_color'])) {
            $_POST['style_labels_text_fill_color']=str_replace("#","",trim($_POST['style_labels_text_fill_color']));
            if(uString::isHexColor($style_labels_text_fill_color)) $style_labels_text_fill_color=$_POST['style_labels_text_fill_color'];
        }
        $res[0]='"style_labels_text_fill_color":"'.$style_labels_text_fill_color.'",'.$res[0];


        $style_road_geometry_fill_color="feffff";
        if(isset($_POST['style_road_geometry_fill_color'])) {
            $_POST['style_road_geometry_fill_color']=str_replace("#","",trim($_POST['style_road_geometry_fill_color']));
            if(uString::isHexColor($style_road_geometry_fill_color)) $style_road_geometry_fill_color=$_POST['style_road_geometry_fill_color'];
        }
        $res[0]='"style_road_geometry_fill_color":"'.$style_road_geometry_fill_color.'",'.$res[0];


        $style_road_geometry_stroke_color="dde1e4";
        if(isset($_POST['style_road_geometry_stroke_color'])) {
            $_POST['style_road_geometry_stroke_color']=str_replace("#","",trim($_POST['style_road_geometry_stroke_color']));
            if(uString::isHexColor($style_road_geometry_stroke_color)) $style_road_geometry_stroke_color=$_POST['style_road_geometry_stroke_color'];
        }
        $res[0]='"style_road_geometry_stroke_color":"'.$style_road_geometry_stroke_color.'",'.$res[0];


        $style_landscape_geometry_color="C8C8C8";
        if(isset($_POST['style_landscape_geometry_color'])) {
            $_POST['style_landscape_geometry_color']=str_replace("#","",trim($_POST['style_landscape_geometry_color']));
            if(uString::isHexColor($style_landscape_geometry_color)) $style_landscape_geometry_color=$_POST['style_landscape_geometry_color'];
        }
        $res[0]='"style_landscape_geometry_color":"'.$style_landscape_geometry_color.'",'.$res[0];


        $style_landscape_man_made_geometry_stroke_color="787c7f";
        if(isset($_POST['style_landscape_man_made_geometry_stroke_color'])) {
            $_POST['style_landscape_man_made_geometry_stroke_color']=str_replace("#","",trim($_POST['style_landscape_man_made_geometry_stroke_color']));
            if(uString::isHexColor($style_landscape_man_made_geometry_stroke_color)) $style_landscape_man_made_geometry_stroke_color=$_POST['style_landscape_man_made_geometry_stroke_color'];
        }
        $res[0]='"style_landscape_man_made_geometry_stroke_color":"'.$style_landscape_man_made_geometry_stroke_color.'",'.$res[0];


        $style_poi_labels_text_fill_color="787c7f";
        if(isset($_POST['style_poi_labels_text_fill_color'])) {
            $_POST['style_poi_labels_text_fill_color']=str_replace("#","",trim($_POST['style_poi_labels_text_fill_color']));
            if(uString::isHexColor($style_poi_labels_text_fill_color)) $style_poi_labels_text_fill_color=$_POST['style_poi_labels_text_fill_color'];
        }
        $res[0]='"style_poi_labels_text_fill_color":"'.$style_poi_labels_text_fill_color.'",'.$res[0];


        $marker_img_url="";
        if(isset($_POST['marker_img_url'])) {
            $marker_img_url=str_replace("#","",trim($_POST['marker_img_url']));
//            require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';

            $config = \HTMLPurifier_Config::createDefault();
            if(!isset($this->purifier)) $this->purifier = new \HTMLPurifier($config);

            $marker_img_url=$this->purifier->purify(htmlspecialchars(trim($marker_img_url)));
        }
        $res[0]='"marker_img_url":"'.$marker_img_url.'",'.$res[0];


        //save element config
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO
            el_config_gmap (
                cols_els_id,
                site_id,
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
            ) VALUES (
                :cols_els_id,
                :site_id,
                :zoom,
                :map_center_lat,
                :map_center_long,
                :marker_lat,
                :marker_long,
                :marker_img_url,
                :height,
                :style_geometry_color,
                :style_labels_text_fill_color,
                :style_road_geometry_fill_color,
                :style_road_geometry_stroke_color,
                :style_landscape_geometry_color,
                :style_landscape_man_made_geometry_stroke_color,
                :style_poi_labels_text_fill_color
            )
            ");
            $cols_els_id=$res[1];
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':zoom', $zoom,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':map_center_lat', $map_center_lat,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':map_center_long', $map_center_long,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':marker_lat', $marker_lat,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':marker_long', $marker_long,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':marker_img_url', $marker_img_url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':height', $height,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_geometry_color', $style_geometry_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_labels_text_fill_color', $style_labels_text_fill_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_road_geometry_fill_color', $style_road_geometry_fill_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_road_geometry_stroke_color', $style_road_geometry_stroke_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_landscape_geometry_color', $style_landscape_geometry_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_landscape_man_made_geometry_stroke_color', $style_landscape_man_made_geometry_stroke_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_poi_labels_text_fill_color', $style_poi_labels_text_fill_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_gmap_common 30'.$e->getMessage());}

        echo '{
        '.$res[0].'}';
        exit;
    }

    public function save_el_conf($cols_els_id) {
        $height=50;
        if(isset($_POST['height'])) {
            if((int)$_POST['height']>50) $height=$_POST['height'];
        }

        $map_center_lat=59.9479723;
        if(isset($_POST['map_center_lat'])) {
            str_replace(",",".",$_POST['map_center_lat']);
            $map_center_lat=(float)$_POST['map_center_lat'];
        }

        $map_center_long=30.3617107;
        if(isset($_POST['map_center_long'])) {
            str_replace(",",".",$_POST['map_center_long']);
            $map_center_long=(float)$_POST['map_center_long'];
        }

        $marker_lat=59.9479723;
        if(isset($_POST['marker_lat'])) {
            str_replace(",",".",$_POST['marker_lat']);
            $marker_lat=(float)$_POST['marker_lat'];
        }

        $marker_long=30.3617107;
        if(isset($_POST['marker_long'])) {
            str_replace(",",".",$_POST['marker_long']);
            $marker_long=(float)$_POST['marker_long'];
        }

        $zoom=17;
        if(isset($_POST['zoom'])) {
            if((int)$_POST['zoom']>0) $zoom=$_POST['zoom'];
        }


        $style_geometry_color="C8C8C8";
        if(isset($_POST['style_geometry_color'])) {
            $_POST['style_geometry_color']=str_replace("#","",trim($_POST['style_geometry_color']));
            if(uString::isHexColor($style_geometry_color)) $style_geometry_color=$_POST['style_geometry_color'];
        }


        $style_labels_text_fill_color="787c7f";
        if(isset($_POST['style_labels_text_fill_color'])) {
            $_POST['style_labels_text_fill_color']=str_replace("#","",trim($_POST['style_labels_text_fill_color']));
            if(uString::isHexColor($style_labels_text_fill_color)) $style_labels_text_fill_color=$_POST['style_labels_text_fill_color'];
        }


        $style_road_geometry_fill_color="feffff";
        if(isset($_POST['style_road_geometry_fill_color'])) {
            $_POST['style_road_geometry_fill_color']=str_replace("#","",trim($_POST['style_road_geometry_fill_color']));
            if(uString::isHexColor($style_road_geometry_fill_color)) $style_road_geometry_fill_color=$_POST['style_road_geometry_fill_color'];
        }


        $style_road_geometry_stroke_color="dde1e4";
        if(isset($_POST['style_road_geometry_stroke_color'])) {
            $_POST['style_road_geometry_stroke_color']=str_replace("#","",trim($_POST['style_road_geometry_stroke_color']));
            if(uString::isHexColor($style_road_geometry_stroke_color)) $style_road_geometry_stroke_color=$_POST['style_road_geometry_stroke_color'];
        }


        $style_landscape_geometry_color="C8C8C8";
        if(isset($_POST['style_landscape_geometry_color'])) {
            $_POST['style_landscape_geometry_color']=str_replace("#","",trim($_POST['style_landscape_geometry_color']));
            if(uString::isHexColor($style_landscape_geometry_color)) $style_landscape_geometry_color=$_POST['style_landscape_geometry_color'];
        }


        $style_landscape_man_made_geometry_stroke_color="787c7f";
        if(isset($_POST['style_landscape_man_made_geometry_stroke_color'])) {
            $_POST['style_landscape_man_made_geometry_stroke_color']=str_replace("#","",trim($_POST['style_landscape_man_made_geometry_stroke_color']));
            if(uString::isHexColor($style_landscape_man_made_geometry_stroke_color)) $style_landscape_man_made_geometry_stroke_color=$_POST['style_landscape_man_made_geometry_stroke_color'];
        }


        $style_poi_labels_text_fill_color="787c7f";
        if(isset($_POST['style_poi_labels_text_fill_color'])) {
            $_POST['style_poi_labels_text_fill_color']=str_replace("#","",trim($_POST['style_poi_labels_text_fill_color']));
            if(uString::isHexColor($style_poi_labels_text_fill_color)) $style_poi_labels_text_fill_color=$_POST['style_poi_labels_text_fill_color'];
        }


        $marker_img_url="";
        if(isset($_POST['marker_img_url'])) {
            $marker_img_url=str_replace("#","",trim($_POST['marker_img_url']));
//            require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';

            $config = \HTMLPurifier_Config::createDefault();
            if(!isset($this->purifier)) $this->purifier = new \HTMLPurifier($config);

            $marker_img_url=$this->purifier->purify(htmlspecialchars(trim($marker_img_url)));
        }


        //save element config
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_gmap
                SET 
                zoom=:zoom,
                map_center_lat=:map_center_lat,
                map_center_long=:map_center_long,
                marker_lat=:marker_lat,
                marker_long=:marker_long,
                marker_img_url=:marker_img_url,
                height=:height,
                style_geometry_color=:style_geometry_color,
                style_labels_text_fill_color=:style_labels_text_fill_color,
                style_road_geometry_fill_color=:style_road_geometry_fill_color,
                style_road_geometry_stroke_color=:style_road_geometry_stroke_color,
                style_landscape_geometry_color=:style_landscape_geometry_color,
                style_landscape_man_made_geometry_stroke_color=:style_landscape_man_made_geometry_stroke_color,
                style_poi_labels_text_fill_color=:style_poi_labels_text_fill_color
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':zoom', $zoom,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':map_center_lat', $map_center_lat,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':map_center_long', $map_center_long,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':marker_lat', $marker_lat,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':marker_long', $marker_long,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':marker_img_url', $marker_img_url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':height', $height,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_geometry_color', $style_geometry_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_labels_text_fill_color', $style_labels_text_fill_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_road_geometry_fill_color', $style_road_geometry_fill_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_road_geometry_stroke_color', $style_road_geometry_stroke_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_landscape_geometry_color', $style_landscape_geometry_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_landscape_man_made_geometry_stroke_color', $style_landscape_man_made_geometry_stroke_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':style_poi_labels_text_fill_color', $style_poi_labels_text_fill_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_gmap_common 50'.$e->getMessage());}

        $page_id=$this->uPage->get_page_id('el',$cols_els_id);
        $this->uPage->clear_cache($page_id);

        exit('{
            "cols_els_id":"'.$cols_els_id.'",
            "status":"done"
            }');
    }

    public function load_el_content($cols_els_id,$site_id=site_id){
        $conf = $this->get_el_settings($cols_els_id, $site_id);


        echo('{
        "status":"done",
        "cols_els_id":"' . $cols_els_id . '",
        
        "height":"'.$conf->height.'
        "map_center_lat":"'.$conf->map_center_lat.'
        "map_center_long":"'.$conf->map_center_long.'
        "marker_lat":"'.$conf->marker_lat.'
        "marker_long":"'.$conf->marker_long.'
        "zoom":"'.$conf->zoom.'
        "style_geometry_color":"'.$conf->style_geometry_color.'
        "style_labels_text_fill_color":"'.$conf->style_labels_text_fill_color.'
        "style_road_geometry_fill_color":"'.$conf->style_road_geometry_fill_color.'
        "style_road_geometry_stroke_color":"'.$conf->style_road_geometry_stroke_color.'
        "style_landscape_geometry_color":"'.$conf->style_landscape_geometry_color.'
        "style_landscape_man_made_geometry_stroke_color":"'.$conf->style_landscape_man_made_geometry_stroke_color.'
        "style_poi_labels_text_fill_color":"'.$conf->style_poi_labels_text_fill_color.'
        "marker_img_url":"'.$conf->marker_img_url.'
        
        "code":"' . rawurlencode($conf->code) . '",
        "do_not_run_in_editor":"' . $conf->do_not_run_in_editor . '"
        }');
    }

        function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=&$this->uPage->uCore;
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;
    }
}

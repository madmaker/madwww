<?php
namespace uPage\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uPage\common;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once 'uPage/inc/common.php';

class uPage_admin_edit_row {
    public $uFunc;
    public $uPage;
    public $uSes;
    private $uCore,$page_id,$row_id,$field;
    private function check_data() {
        if(!isset($_POST['page_id'],$_POST['row_id'],$_POST['field'])) $this->uFunc->error(10);
        $this->page_id=$_POST['page_id'];
        $this->row_id=$_POST['row_id'];
        $this->field=$_POST['field'];
        if(!uString::isDigits($this->page_id)) $this->uFunc->error(20);
        if(!uString::isDigits($this->row_id)) $this->uFunc->error(30);
    }
    private function move_row() {
        $row_pos=$this->uPage->define_new_row_pos((int)$_POST['row_pos'],$this->page_id);
        //save new row

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_rows
            SET
            row_pos=:row_pos
            WHERE
            row_id=:row_id AND
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_pos', $row_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $this->row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        //return result
        //Достаем все row с row_id и row_pos, чтобы передать браузеру информацию об изменениях
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            row_id,
            row_pos
            FROM
            u235_rows
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            $result='{';
            /** @noinspection PhpUndefinedMethodInspection */
            while($row=$stm->fetch(PDO::FETCH_OBJ)) {
                $result.='"row_'.$row->row_id.'":"'.$row->row_pos.'",';
            }
            $result.='"status":"done"
            }';

            $this->clear_cache();
            die($result);
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
    }
    private function save_row_css() {
        if(!isset(
            $_POST['row_css'],
            $_POST['row_class'],
            $_POST['row_content_centered'],
            $_POST['row_background_color'],
            $_POST['row_background_img'],
            $_POST['row_background_stretch'],
            $_POST['row_background_repeat_x'],
            $_POST['row_background_repeat_y'],
            $_POST['row_background_position'],
            $_POST['row_background_parallax'],

            $_POST['row_margin_top_xlg'],
            $_POST['row_margin_top_lg'],
            $_POST['row_margin_top_md'],
            $_POST['row_margin_top_sm'],
            $_POST['row_margin_top_xs'],

            $_POST['row_margin_bottom_xlg'],
            $_POST['row_margin_bottom_lg'],
            $_POST['row_margin_bottom_md'],
            $_POST['row_margin_bottom_sm'],
            $_POST['row_margin_bottom_xs'],

            $_POST['row_padding_top_xlg'],
            $_POST['row_padding_top_lg'],
            $_POST['row_padding_top_md'],
            $_POST['row_padding_top_sm'],
            $_POST['row_padding_top_xs'],

            $_POST['row_padding_bottom_xlg'],
            $_POST['row_padding_bottom_lg'],
            $_POST['row_padding_bottom_md'],
            $_POST['row_padding_bottom_sm'],
            $_POST['row_padding_bottom_xs'],

            $_POST['row_min_height_xlg'],
            $_POST['row_min_height_lg'],
            $_POST['row_min_height_md'],
            $_POST['row_min_height_sm'],
            $_POST['row_min_height_xs'],
            $_POST['row_font_color'],
            $_POST['row_link_color'],
            $_POST['row_hoverlink_color'],
            $_POST['row_font_size']
        )) $this->uFunc->error(90);

        $row_style_ar['row_css']=$_POST['row_css'];
        $row_style_ar['row_class']=$_POST['row_class'];
        $row_style_ar['row_content_centered']=$_POST['row_content_centered'];
        $row_style_ar['row_background_color']=$_POST['row_background_color'];
        $row_style_ar['row_background_img']=$_POST['row_background_img'];
        $row_style_ar['row_background_stretch']=$_POST['row_background_stretch'];
        $row_style_ar['row_background_repeat_x']=$_POST['row_background_repeat_x'];
        $row_style_ar['row_background_repeat_y']=$_POST['row_background_repeat_y'];
        $row_style_ar['row_background_position']=$_POST['row_background_position'];
        $row_style_ar['row_background_parallax']=$_POST['row_background_parallax'];

        $row_style_ar['row_margin_top_xlg']=$_POST['row_margin_top_xlg'];
        $row_style_ar['row_margin_top_lg']=$_POST['row_margin_top_lg'];
        $row_style_ar['row_margin_top_md']=$_POST['row_margin_top_md'];
        $row_style_ar['row_margin_top_sm']=$_POST['row_margin_top_sm'];
        $row_style_ar['row_margin_top_xs']=$_POST['row_margin_top_xs'];

        $row_style_ar['row_margin_bottom_xlg']=$_POST['row_margin_bottom_xlg'];
        $row_style_ar['row_margin_bottom_lg']=$_POST['row_margin_bottom_lg'];
        $row_style_ar['row_margin_bottom_md']=$_POST['row_margin_bottom_md'];
        $row_style_ar['row_margin_bottom_sm']=$_POST['row_margin_bottom_sm'];
        $row_style_ar['row_margin_bottom_xs']=$_POST['row_margin_bottom_xs'];

        $row_style_ar['row_padding_top_xlg']=$_POST['row_padding_top_xlg'];
        $row_style_ar['row_padding_top_lg']=$_POST['row_padding_top_lg'];
        $row_style_ar['row_padding_top_md']=$_POST['row_padding_top_md'];
        $row_style_ar['row_padding_top_sm']=$_POST['row_padding_top_sm'];
        $row_style_ar['row_padding_top_xs']=$_POST['row_padding_top_xs'];

        $row_style_ar['row_padding_bottom_xlg']=$_POST['row_padding_bottom_xlg'];
        $row_style_ar['row_padding_bottom_lg']=$_POST['row_padding_bottom_lg'];
        $row_style_ar['row_padding_bottom_md']=$_POST['row_padding_bottom_md'];
        $row_style_ar['row_padding_bottom_sm']=$_POST['row_padding_bottom_sm'];
        $row_style_ar['row_padding_bottom_xs']=$_POST['row_padding_bottom_xs'];

        $row_style_ar['row_min_height_xlg']=$_POST['row_min_height_xlg'];
        $row_style_ar['row_min_height_lg']=$_POST['row_min_height_lg'];
        $row_style_ar['row_min_height_md']=$_POST['row_min_height_md'];
        $row_style_ar['row_min_height_sm']=$_POST['row_min_height_sm'];
        $row_style_ar['row_min_height_xs']=$_POST['row_min_height_xs'];
        $row_style_ar['row_font_color']=$_POST['row_font_color'];
        $row_style_ar['row_link_color']=$_POST['row_link_color'];
        $row_style_ar['row_hoverlink_color']=$_POST['row_hoverlink_color'];
        $row_style_ar['row_font_size']=$_POST['row_font_size'];

        $row_style_ar=$this->uPage->save_row_css($this->row_id,$row_style_ar);

        die('{
        "status":"done",
        "row_id":"'.$this->row_id.'",
        "row_css":"'.rawurlencode($row_style_ar['row_css']).'",
        "row_class":"'.rawurlencode($row_style_ar['row_class']).'",
        "row_background_img":"'.rawurlencode($row_style_ar['row_background_img']).'",
        "row_background_color":"'.$row_style_ar['row_background_color'].'",
        "row_background_stretch":"'.$row_style_ar['row_background_stretch'].'",
        "row_background_repeat_x":"'.$row_style_ar['row_background_repeat_x'].'",
        "row_background_repeat_y":"'.$row_style_ar['row_background_repeat_y'].'",
        "row_background_position":"'.$row_style_ar['row_background_position'].'",
        "row_background_parallax":"'.$row_style_ar['row_background_parallax'].'",
        
        "row_margin_top_xlg":"'.$row_style_ar['row_margin_top_xlg'].'",
        "row_margin_top_lg":"'.$row_style_ar['row_margin_top_lg'].'",
        "row_margin_top_md":"'.$row_style_ar['row_margin_top_md'].'",
        "row_margin_top_sm":"'.$row_style_ar['row_margin_top_sm'].'",
        "row_margin_top_xs":"'.$row_style_ar['row_margin_top_xs'].'",
        
        "row_margin_bottom_xlg":"'.$row_style_ar['row_margin_bottom_xlg'].'",
        "row_margin_bottom_lg":"'.$row_style_ar['row_margin_bottom_lg'].'",
        "row_margin_bottom_md":"'.$row_style_ar['row_margin_bottom_md'].'",
        "row_margin_bottom_sm":"'.$row_style_ar['row_margin_bottom_sm'].'",
        "row_margin_bottom_xs":"'.$row_style_ar['row_margin_bottom_xs'].'",
        
        "row_padding_top_xlg":"'.$row_style_ar['row_padding_top_xlg'].'",
        "row_padding_top_lg":"'.$row_style_ar['row_padding_top_lg'].'",
        "row_padding_top_md":"'.$row_style_ar['row_padding_top_md'].'",
        "row_padding_top_sm":"'.$row_style_ar['row_padding_top_sm'].'",
        "row_padding_top_xs":"'.$row_style_ar['row_padding_top_xs'].'",
        
        "row_padding_bottom_xlg":"'.$row_style_ar['row_padding_bottom_xlg'].'",
        "row_padding_bottom_lg":"'.$row_style_ar['row_padding_bottom_lg'].'",
        "row_padding_bottom_md":"'.$row_style_ar['row_padding_bottom_md'].'",
        "row_padding_bottom_sm":"'.$row_style_ar['row_padding_bottom_sm'].'",
        "row_padding_bottom_xs":"'.$row_style_ar['row_padding_bottom_xs'].'",
        
        "row_min_height_xlg":"'.$row_style_ar['row_min_height_xlg'].'",
        "row_min_height_lg":"'.$row_style_ar['row_min_height_lg'].'",
        "row_min_height_md":"'.$row_style_ar['row_min_height_md'].'",
        "row_min_height_sm":"'.$row_style_ar['row_min_height_sm'].'",
        "row_min_height_xs":"'.$row_style_ar['row_min_height_xs'].'",
        "row_font_color":"'.$row_style_ar['row_font_color'].'",
        "row_link_color":"'.$row_style_ar['row_link_color'].'",
        "row_hoverlink_color":"'.$row_style_ar['row_hoverlink_color'].'",
        "row_font_size":"'.$row_style_ar['row_font_size'].'",
        "row_content_centered":"'.$row_style_ar['row_content_centered'].'"
        }');
    }
    private function clear_cache() {
        $page_id=$this->uPage->get_page_id('row',$this->row_id);

        $this->uPage->clear_cache($page_id);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new common($this->uCore);
        $this->uSes=new \uSes($this->uCore);

        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->check_data();

        if($this->field=='row_pos') $this->move_row();
        elseif($this->field=='row_css') $this->save_row_css();
        else $this->uFunc->error(110);
    }
}
new uPage_admin_edit_row($this);
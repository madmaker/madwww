<?php
namespace uEditor\admin;

use processors\uFunc;
use uEditor\common;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uEditor/classes/common.php";

class new_page {
    public $page_timestamp;
    public $folder_id;
    public $uFunc;
    public $uSes;
    public $uEditor;
    private $uCore,$page_title,$page_id,$page_name,$page_category;
    private function check_data() {
        if(!isset($_POST['page_title'],$_POST['cur_folder_id'])) $this->uFunc->error(10);
        $this->page_title=trim($_POST['page_title']);
        $this->folder_id=$_POST['cur_folder_id'];
        if(!strlen($this->page_title)) die("{'status' : 'error', 'msg':'title is empty'}");
    }
    private function create_page() {
        //get new id
        $this->page_id=$this->uEditor->get_new_page_id();
        $this->page_name=$this->uEditor->page_title2page_name_converter($this->page_title);

        $page_text='<div>&nbsp;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas augue ante, euismod sit amet varius nec, consectetur at lorem. In ornare sem vitae venenatis facilisis. Suspendisse consequat lorem in sem pulvinar ornare. Praesent pellentesque a leo vel egestas. Etiam non rutrum neque. Praesent in arcu in mauris tristique dapibus id in leo. Quisque placerat tortor at eros pretium, ac congue mauris convallis. Donec faucibus, elit et molestie fringilla, risus magna vulputate arcu, sed molestie nisl nunc eu libero. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Duis leo ipsum, finibus sit amet aliquet quis, luctus quis lectus. Nullam finibus cursus varius. Aliquam a ultricies turpis.<br /><br />Sed pretium bibendum nulla eget malesuada. Vivamus nec dapibus ipsum. Sed in elementum eros. Fusce tincidunt scelerisque molestie. Pellentesque porta aliquet nisl, quis porttitor diam semper a. Aliquam vel nisl pulvinar, cursus purus non, tempor mauris. Proin nec ornare urna, quis lobortis nibh. Donec ut felis eu lectus lacinia tempus. Phasellus ornare, neque at lacinia tempor, sem nibh suscipit arcu, eget ultricies nisl justo in mi. Morbi eget scelerisque felis.<br /><br />Praesent fringilla gravida lectus. Quisque eget nisi varius velit semper bibendum ac et odio. Sed ornare orci in sollicitudin accumsan. Suspendisse feugiat, ipsum nec tempus ornare, tortor quam faucibus erat, vel sollicitudin purus lectus ac purus. Nulla et urna id odio volutpat eleifend quis in nibh. Maecenas venenatis hendrerit porttitor. Vestibulum vel tincidunt nunc. Phasellus non felis sed elit aliquet lacinia.<br /><br />Fusce efficitur lacus ac enim malesuada bibendum. Quisque sollicitudin justo urna, eget viverra velit tincidunt ut. Morbi eu nisi turpis. Sed vehicula massa a malesuada eleifend. Nulla maximus arcu id odio varius, vel molestie dui finibus. Vivamus arcu enim, laoreet et varius a, molestie sit amet mauris. Cras mi lectus, condimentum nec ipsum in, porta molestie nisi. Quisque commodo lorem nec posuere tincidunt. Vestibulum ut lobortis leo, quis consectetur massa. Ut ultrices, nunc eu venenatis rutrum, elit felis pretium tellus, at auctor lorem lorem nec lectus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque porta neque a posuere tempor. Sed maximus feugiat quam, nec finibus nunc vehicula sed. In et nisl risus. Aenean convallis metus quis eros ultricies laoreet.<br /><br />Proin sit amet aliquam nisi, eget accumsan eros. Quisque bibendum magna sit amet ante ultrices blandit ut at mauris. Sed bibendum orci erat. Integer commodo tempus nunc nec vehicula. Aliquam dapibus egestas turpis, et fermentum mi tincidunt id. Suspendisse vel condimentum dui. Sed scelerisque blandit commodo. Mauris lectus est, porttitor sed arcu id, pharetra ullamcorper neque. Vivamus sagittis placerat quam, a imperdiet ligula pretium eget. Morbi orci mi, sagittis in leo et, placerat vehicula felis.<br /><br /><br /></div>';

        $this->page_timestamp=time();

        $this->page_category='';

        $this->uEditor->create_page($page_text,$this->page_timestamp,$this->page_name,$this->page_title,$this->page_id,$this->folder_id);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uEditor=new common($this->uCore);

        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->create_page();
        $this->uFunc->set_flag_update_sitemap(1, site_id);

        echo "{
        'status' : 'done',
        'page_id' : '".$this->page_id."',
        'folder_id' : '".$this->folder_id."',
        'page_title':'".rawurlencode($this->page_title)."',
        'page_name':'".rawurlencode($this->page_name)."',
        'page_alias':'',
        'page_category':'".$this->page_category."',
        'deleted':'0',
        'page_timestamp':'".$this->page_timestamp."'
        }";
    }
}
new new_page($this);
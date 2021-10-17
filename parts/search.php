<?php
namespace parts;
use processors\uFunc;
//use uSes;

require_once "processors/classes/uFunc.php";
//require_once "processors/uSes.php";

class search {
    private $uFunc;
    public $search;
    private $uCore;
    private function check_data() {
        if(isset($_GET["search"])) $this->search=$_GET["search"];
        else $this->search="";
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
//        $this->uSes=new uSes($this->uCore);
        //if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();

        $this->uFunc->incJs(staticcontent_url.'js/lib/u235/notificator.min.js');
        $this->uFunc->incJs("parts/js/search.min.js");
        $this->uFunc->incJs("uCat/js/uCat_cart.min.js");
    }
}
$search=new search($this);

ob_start();?>

<div class="parts search container">

    <h1 class="page-header">Поиск запасных частей</h1>


    <div class="input-group">
        <form onsubmit="parts.search.find(); return false;">
        <input id="parts_search_input" type="text" class="form-control" placeholder="Поиск запасных частей по коду. Например, 5DM134400000">
        </form>
        <span class="input-group-btn">
            <button class="btn btn-primary" type="button" onclick="parts.search.find()">Поиск</button>
        </span>
    </div>

    <div class="row">&nbsp;</div>

    <div id="parts_search_result"></div>

</div>

<script type="text/javascript">
    if(typeof parts==="undefined") parts={};
    if(typeof parts.search==="undefined") parts.search={};
    parts.search.default_search="<?=rawurlencode($search->search)?>";
</script>

<?$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/template.php';

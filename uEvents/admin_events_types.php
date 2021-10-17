<?php
class uEvents_events_types {
    private $uCore;
    public function text($str) {
        return $this->uCore->text(array('uEvents','admin_events_types'),$str);
    }
    public function get_events_types() {
        if(!$query=$this->uCore->query("uEvents","SELECT
        `type_id`,
        `type_title`,
        `type_url`
        FROM
        `u235_events_types`
        WHERE
        `site_id`='".site_id."'
        ")) $this->uCore->error(10);
        return $query;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        $this->uCore->page['page_title']=$this->text("Page name"/*Типы событий*/);
    }
}
$uEvents=new uEvents_events_types($this);

$this->uFunc->incJs("uEvents/js/admin_events_types.js");
ob_start();?>

<script type="text/javascript">
    if(typeof uEvents_admin_events_types==="undefined") {
        uEvents_admin_events_types={};
        uEvents_admin_events_types.events_types=[];
    }

<?$events_types=$uEvents->get_events_types();
while($type=$events_types->fetch_object()) {?>
var i=uEvents_admin_events_types.events_types.length;
uEvents_admin_events_types.events_types[i]=[];
uEvents_admin_events_types.events_types[i]['type_id']=<?=$type->type_id?>;
uEvents_admin_events_types.events_types[i]['type_title']="<?=rawurlencode(uString::sql2text($type->type_title))?>";
uEvents_admin_events_types.events_types[i]['type_url']="<?=rawurlencode(uString::sql2text($type->type_url))?>";
<?}?>
</script>

<div class="container-fluid" id="uEvents_events_types_list"></div>

<?$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/u235/template.php";

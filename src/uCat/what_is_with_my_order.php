<?php
namespace uCat\checkout;

use processors\uFunc;
require_once 'processors/classes/uFunc.php';

class what_is_with_my_order {
    private $uCore;
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);

        //react
        $this->uFunc->incJs(u_sroot."js/react/react.min.js");
        $this->uFunc->incJs(u_sroot."js/react/react-dom.min.js");
//        $this->uFunc->incJs(u_sroot."js/react/browser.min.js");
    }
}
$uCat=new what_is_with_my_order($this);
ob_start();?>
    <h1 class="page-header">
        Что с моим заказом?
    </h1>
    <p>После оформления заказа вам на электронную почту должно прийти письмо с информацией о заказе.</p>
    <p>На каждом этапе обработки заказа мы вас уведомляем по электронной почте об изменении статуса заказа.</p>

    <div class="jumbotron" id="uCat_what_is_with_my_order_form"></div>
<?$terms_link=$terms_link_closer="";
$terms_page_id=(int)$uCat->uFunc->getConf("privacy_terms_text_id","content",1);
if($terms_page_id) {
    $txt_obj=$uCat->uFunc->getStatic_data_by_id($terms_page_id,"page_name");
    if($txt_obj) {
        $terms_link = '<a target="_blank" href="' . u_sroot . 'page/' . $txt_obj->page_name . '">';
        $terms_link_closer = "</a>";
    }
}
?>
    <script type="text/javascript">
        let terms_link='<?=$terms_link?>';
        let terms_link_closer='<?=$terms_link_closer?>';
    </script>

    <?$this->uFunc->incJs(u_sroot.'uCat/js/what_is_with_my_order.min.js');?>

<?$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/template.php";
<?php
namespace uAuth;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
class logout {
    private $uCore;
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->uSes->userLogout();

        if($this->uFunc->mod_installed('uCat')) {
            //js-cookie
            echo $this->uFunc->printJs(u_sroot.'js/js-cookie/js.cookie.min.js');
            ?>
            <script type="text/javascript">
                Cookies.set('cart_total_count',0);
                Cookies.set('cart_total_price',0);
            </script>
        <?}
    }
}
/*$newClass=*/new logout($this);?>

<script type="text/javascript">
    document.location="<?=u_sroot?>";
</script>

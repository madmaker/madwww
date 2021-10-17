<?php
namespace imap;
use processors\uFunc;
use translator\translator;
use uCore;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "translator/translator.php";

class api_doc {
    /**
     * @var uSes
     */
    public $uSes;
    /**
     * @var int
     */
    public $print_data;
    /**
     * @var translator
     */
    public $translator;
    /**
     * @var uFunc
     */
    private $uFunc;
    private $uCore;
    private function check_data() {

    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new uCore();
        $this->uSes=new uSes($this->uCore);
        $this->translator=new translator(site_lang,"imap/api_doc.php");
        $this->uCore->page["page_title"]=$this->translator->txt("Page title");

        ob_start();
        if(!$this->uSes->access(2)) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">'; print $this->translator->txt("You are not authorized"); print '</h1>
                <p>'; print $this->translator->txt("Please sign in"); print '</p>
                <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">'; print $this->translator->txt("Sign in"); print '</a></p>
            </div>';
            return 0;
        }
        else $this->print_data=1;

        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();

        return 1;
    }
}
$imap=new api_doc($this);

if($imap->print_data) {?>
    <div>
        <h1 class="page-header"><?=$imap->translator->txt("page header")?> <small>imap</small></h1>
        <a href="/imap/dashboard" class="btn btn-default"><span class="icon-left"></span> <?=$imap->translator->txt("Back to Dashboard")?></a>
    </div>
<?}

$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/template.php';

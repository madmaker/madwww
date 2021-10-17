<?php
namespace sms;
use translator\translator;
use uSes;

require_once 'processors/uSes.php';
require_once 'translator/translator.php';

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

    public function __construct (&$uCore) {
        $this->uSes=new uSes($uCore);
        $this->translator=new translator(site_lang, 'sms/api_doc.php');
        $uCore->page['page_title']=$this->translator->txt('Page title');

        ob_start();
        if(!$this->uSes->access(2)) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">'; print $this->translator->txt('You are not authorized'); print '</h1>
                <p>'; print $this->translator->txt('Please sign in'); print '</p>
                <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">'; print $this->translator->txt('Sign in'); print '</a></p>
            </div>';
            return 0;
        }

        $this->print_data = 1;

        return 1;
    }
}
$sms=new api_doc($this);

if($sms->print_data) {?>
    <div>
        <h1 class="page-header"><?=$sms->translator->txt('page header')?> <small>sms</small></h1>
        <a href="/sms/dashboard" class="btn btn-default"><span class="icon-left"></span> <?=$sms->translator->txt('Back to Dashboard')?></a>
    </div>
<?}

$this->page_content=ob_get_clean();

include 'templates/template.php';

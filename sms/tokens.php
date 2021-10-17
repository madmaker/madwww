<?php
namespace sms;
use processors\uFunc;
use translator\translator;
use uSes;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'sms/classes/sms.php';
require_once 'translator/translator.php';

class tokens {
    /**
     * @var int
     */
    public $print_data;
    /**
     * @var translator
     */
    public $translator;
    /**
     * @var array
     */
    public $access_token;

    public function __construct (&$uCore) {
        $this->translator=new translator(site_lang, 'sms/tokens.php');
        $uCore->page['page_title']=$this->translator->txt('page title');

        ob_start();
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">'; print $this->translator->txt('You are not authorized'); print '</h1>
                <p>'; print $this->translator->txt('Please sign in'); print '</p>
                <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">'; print $this->translator->txt('Sign in'); print '</a></p>
            </div>';

                return 0;
        }

        $uFunc=new uFunc($uCore);

        $sms=new sms($uCore);
        if(!$this->access_token=$sms->registerToken()) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">'; print $this->translator->txt('Server is offline'); print '</h1>
                <p>'; print $this->translator->txt('Unable to get tokens list'); print '</p>
            </div>';

            return 0;
        }

        $this->print_data = 1;

        $uFunc->incCss('/sms/css/tokens.min.css');

        $uFunc->incJs(staticcontent_url.'/node_modules/validator/validator.min.js');
        $uFunc->incJs(staticcontent_url.'js/translator/translator.min.js');
        $uFunc->incJs(staticcontent_url.'js/lib/u235/notificator.min.js');
        $uFunc->incJs('/sms/js/tokens.min.js');

        return 1;
    }
}
$sms=new tokens($this);

if($sms->print_data) {?>
    <div>
        <h1 class="page-header"><?=$sms->translator->txt('page header')?> <small>sms</small></h1>
        <div>
            <a href="/sms/dashboard" class="btn btn-default"><span class="icon-left"></span> <?=$sms->translator->txt('Back to Dashboard')?></a>
            <button class="btn btn-primary" onclick="sms.tokens.newTokenDg()"><span class="icon-plus"></span> <?=$sms->translator->txt('Create new token')?></button>
        </div>
        <p>&nbsp;</p>

        <table class="table table-hover" id="sms_tokens_list">
            <tr class="headerRow">
                <th><?=$sms->translator->txt('IP')?></th>
                <th><?=$sms->translator->txt('device_token')?></th>
                <th><?=$sms->translator->txt('ID')?></th>
                <th><?=$sms->translator->txt('Token')?></th>
                <th><?=$sms->translator->txt('Status')?></th>
            </tr>
            <tr id="sms_tokens_list_empty_table_row">
                <td class="loading"><?=$sms->translator->txt('Tokens are being loaded')?></td>
            </tr>
        </table>
        <script type="text/javascript">
            if(typeof sms==="undefined") sms={};
            if(typeof sms.tokens==="undefined") sms.tokens={};

            sms.tokens.access_token=<?=json_encode($sms->access_token)?>;
            sms.tokens.host=<?=json_encode(array(
                    'host' =>madsms_host,
                    'protocol' =>madsms_protocol,
                    'port' =>madsms_port
            ))?>;
        </script>
    </div>
<?}

$this->page_content=ob_get_clean();

include 'templates/template.php';

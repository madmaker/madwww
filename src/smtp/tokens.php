<?php
namespace smtp;
use processors\uFunc;
use translator\translator;
use uCore;
use uSes;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'smtp/classes/smtp.php';
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
        if(!isset($uCore)) {
            $uCore = new uCore();
        }

        $uSes=new uSes($uCore);
        $this->translator=new translator(site_lang, 'smtp/tokens.php');
        $uCore->page['page_title']=$this->translator->txt('page title');

        ob_start();
        if(!$uSes->access(2)) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">'; print $this->translator->txt('You are not authorized'); print '</h1>
                <p>'; print $this->translator->txt('Please sign in'); print '</p>
                <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">'; print $this->translator->txt('Sign in'); print '</a></p>
            </div>';

            /** @noinspection MagicMethodsValidityInspection */
            return 0;
        }

        $uFunc=new uFunc($uCore);

        $smtp=new smtp($uCore);
        if(!$this->access_token=$smtp->registerToken()) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">'; print $this->translator->txt('Server is offline'); print '</h1>
                <p>'; print $this->translator->txt('Unable to get tokens list'); print '</p>
            </div>';

            /** @noinspection MagicMethodsValidityInspection */
            return 0;
        }

        $this->print_data = 1;

        $uFunc->incCss('/smtp/css/tokens.min.css');

        $uFunc->incJs(staticcontent_url.'/node_modules/validator/validator.min.js');
        $uFunc->incJs(staticcontent_url.'js/translator/translator.min.js');
        $uFunc->incJs(staticcontent_url.'js/lib/u235/notificator.min.js');
        $uFunc->incJs(staticcontent_url.'js/smtp/tokens.min.js');

        /** @noinspection MagicMethodsValidityInspection */
        return 1;
    }
}
$smtp=new tokens($this);

if($smtp->print_data) {?>
    <div>
        <h1 class="page-header"><?=$smtp->translator->txt('page header')?> <small>SMTP</small></h1>
        <div>
            <!--suppress HtmlUnknownTarget -->
            <a href="/smtp/dashboard" class="btn btn-default"><span class="icon-left"></span> <?=$smtp->translator->txt('Back to Dashboard')?></a>
            <button class="btn btn-primary" onclick="smtp.tokens.newTokenDg()"><span class="icon-plus"></span> <?=$smtp->translator->txt('Create new token') ?></button>
        </div>
        <p>&nbsp;</p>

        <table class="table table-hover" id="smtp_tokens_list">
            <tr class="headerRow">
                <th><?=$smtp->translator->txt('IP')?></th>
                <th><?=$smtp->translator->txt('SMTP Server')?></th>
                <th><?=$smtp->translator->txt('Login')?></th>
                <th><?=$smtp->translator->txt('ID')?></th>
                <th><?=$smtp->translator->txt('Token')?></th>
                <th><?=$smtp->translator->txt('Status')?></th>
            </tr>
            <tr id="smtp_tokens_list_empty_table_row">
                <td class="loading"><?=$smtp->translator->txt('Tokens are being loaded')?></td>
            </tr>
        </table>
        <script type="text/javascript">
            if(typeof smtp==="undefined") smtp={};
            if(typeof smtp.tokens==="undefined") smtp.tokens={};

            smtp.tokens.access_token=<?=json_encode($smtp->access_token)?>;
            smtp.tokens.host=<?=json_encode(array(
                    'host' =>madsmtp_host_frontend,
                    'protocol' =>madsmtp_protocol,
                    'port' =>madsmtp_port
            ))?>;
        </script>
    </div>
<?}

$this->page_content=ob_get_clean();

include 'templates/template.php';

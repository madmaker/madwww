<?php
namespace imap;
use processors\uFunc;
use translator\translator;
use uCore;
use uSes;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'imap/classes/imap.php';
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
        $this->translator=new translator(site_lang, 'imap/tokens.php');
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

        $imap=new imap($uCore);
        if(!$this->access_token=$imap->registerToken()) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">'; print $this->translator->txt('Server is offline'); print '</h1>
                <p>'; print $this->translator->txt('Unable to get tokens list'); print '</p>
            </div>';

            /** @noinspection MagicMethodsValidityInspection */
            return 0;
        }

        $this->print_data = 1;

        $uFunc->incCss('/imap/css/tokens.min.css');

        $uFunc->incJs(staticcontent_url.'node_modules/validator/validator.min.js');
        $uFunc->incJs(staticcontent_url.'node_modules/socket.io-client/dist/socket.io.js');
        $uFunc->incJs(staticcontent_url.'js/translator/translator.min.js');
        $uFunc->incJs(staticcontent_url.'js/lib/u235/notificator.min.js');
        $uFunc->incJs(staticcontent_url.'js/imap/tokens.min.js');

        /** @noinspection MagicMethodsValidityInspection */
        return 1;
    }
}
$imap=new tokens($this);

if($imap->print_data) {?>
    <div>
        <h1 class="page-header"><?=$imap->translator->txt('page header')?> <small>imap</small></h1>
        <div>
            <!--suppress HtmlUnknownTarget -->
            <a href="/imap/dashboard" class="btn btn-default"><span class="icon-left"></span> <?=$imap->translator->txt('Back to Dashboard')?></a>
            <button class="btn btn-primary" onclick="imap.tokens.newTokenDg()"><span class="icon-plus"></span> <?=$imap->translator->txt('Create new token') ?></button>
        </div>
        <p>&nbsp;</p>

        <table class="table table-hover" id="imap_tokens_list">
            <tr class="headerRow">
                <th><?=$imap->translator->txt('IP')?></th>
                <th><?=$imap->translator->txt('imap Server')?></th>
                <th><?=$imap->translator->txt('Login')?></th>
                <th><?=$imap->translator->txt('ID')?></th>
                <th><?=$imap->translator->txt('Token')?></th>
                <th><?=$imap->translator->txt('Status')?></th>
            </tr>
            <tr id="imap_tokens_list_empty_table_row">
                <td class="loading"><?=$imap->translator->txt('Tokens are being loaded')?></td>
            </tr>
        </table>
        <script type="text/javascript">
            if(typeof imap==="undefined") imap={};
            if(typeof imap.tokens==="undefined") imap.tokens={};

            imap.tokens.access_token=<?=json_encode($imap->access_token)?>;
            imap.tokens.host=<?=json_encode(array(
                    'host' =>madimap_host_frontend,
                    'protocol' =>madimap_protocol,
                    'port' =>madimap_port
            ))?>;
        </script>
    </div>
<?}

$this->page_content=ob_get_clean();

include 'templates/template.php';

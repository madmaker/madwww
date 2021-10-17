<?ob_start();?>
<div class="container">
<h1 class="page-header">Главный заголовок страницы</h1>


<div class="wizard" data-initialize="wizard" id="uCat_checkout_wizard">
    <div class="steps-container">
        <ul class="steps">
            <li data-step="1" data-name="campaign" class="active">
                <span class="badge">1</span>Авторизация
                <span class="chevron"></span>
            </li>
            <li data-step="2">
                <span class="badge">2</span>Подтверждение
                <span class="chevron"></span>
            </li>
            <li data-step="3" data-name="template">
                <span class="badge">3</span>Оплата
                <span class="chevron"></span>
            </li>
        </ul>
    </div>
</div>

<h1>Popover</h1>
<div class="popover" role="tooltip" style="display: block; position: static;"><div class="arrow"></div><h3 class="popover-title">Popover</h3><div class="popover-content">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse quis odio nec mi rutrum condimentum congue sodales erat. Phasellus in mauris tincidunt, ullamcorper tortor ut, varius dui. Nulla aliquet tortor sit amet mauris imperdiet, ac tincidunt tortor accumsan</div></div>
<h1>Navigation</h1>
    <ul class="pagination">
        <li class="active"><a href="#">1</a></li>
        <li><a href="#">2</a></li>
        <li><a href="#">3</a></li>
        <li><a href="#">4</a></li>
        <li><a href="#">5</a></li>
        <li><a href="#">6</a></li>
        <li><a href="#">7</a></li>
        <li><a href="#">8</a></li>
    </ul>

<nav class="navbar navbar-default">
    <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">Brand</a>
            </div>

            <ul class="nav navbar-nav navbar-left">
                <li class="navbar-form"><button class="btn btn-primary"><span class="glyphicon glyphicon-earphone"></span> Закажите обратный звонок</button></li>
            </ul>

        <ul class="nav navbar-nav navbar-right">
            <li><a href="#">Помощь</a></li>
            <li><a href="#">Регистрация</a></li>
            <li><a href="#"><span class="icon-login"></span> Выход</a></li>
            <li class="navbar-text">Signed in as Mark Otto</li>
        </ul>
    </div>
</nav>

<nav class="navbar navbar-default navbar-inverse">
    <div class="container-fluid">
        <ul class="nav navbar-nav navbar-left">
            <li class="navbar-form"><button class="btn btn-default">Обратная связь</button> </li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li><a href="#"><span class="icon-basket"></span> Корзина</a></li>
            <li class="navbar-text"><span class="label label-default" id="uCat_cart_items_number">0</span></li>
            <li class="navbar-text"><span class="label label-primary" id="uCat_cart_items_number" href="#">12 pr</span></li>
            <li class="navbar-text"><span class="label label-success" id="uCat_cart_items_number" href="#">sc</span></li>
            <li class="navbar-text"><span class="label label-info" id="uCat_cart_items_number" href="#">inf</span></li>
            <li class="navbar-text"><span class="label label-warning" id="uCat_cart_items_number" href="#">war</span></li>
            <li class="navbar-text"><span class="label label-danger" id="uCat_cart_items_number" href="#">dg</span></li>
            <li class="navbar-text" id="uCat_cart_total_price">12 000 р.</li>
            <li class="navbar-form"><button id="uCat_cart_order_btn" class="btn btn-primary">Оформить заказ</button> </li>
        </ul>
    </div>
</nav>

<hr>

<h1>Heading one</h1>
<h2>Header two</h2>
<h3>Header three</h3>
<h4>Header four</h4>
<h5>Header five</h5>
<h6>Header six</h6>
<p>This is a copy of one of the sample pages from the <a href="http://www.wordpress.org" onclick="_gaq.push(['_trackEvent', 'outbound-article', 'http://www.wordpress.org', 'WordPress']);" >WordPress</a> theme development test content. I found it handy to keep a copy of this for building sites that aren’t using WordPress. 99% of the credit goes to them, I’m just hosting it in a handy place in case it’s useful to anyone other than me.</p>

<h2>Blockquote Tests</h2>
<p>Blockquote:</p>
<blockquote><p>Here’s a one line quote.</p>
</blockquote>
<p>This part isn’t quoted.  Here’s a longer quote:</p>
<blockquote><p>I have learned, that if one advances confidently in the direction of his dreams, and endeavors to live the life he has imagined, he will meet with a success unexpected in common hours.</p>
    <p><cite>Henry David Thoreau </cite></p>
</blockquote>
<p>And some trailing text.</p>
<h2>Table Layout Test</h2>
<table class="statsDay">
    <tbody>
    <tr>
        <th>Title</th>
        <th class="views">Views</th>
        <th></th>
    </tr>
    <tr class="alternate">
        <td class="label"><a href="http:///example.com/" onclick="_gaq.push(['_trackEvent', 'outbound-article', 'http:///example.com/', 'About Test User']);" >About Test User</a></td>
        <td class="views">1</td>
        <td class="more">More</td>
    </tr>
    <tr>
        <td class="label"><a href="http://example.com/" onclick="_gaq.push(['_trackEvent', 'outbound-article', 'http://example.com/', '260']);" >260</a></td>
        <td class="views">1</td>
        <td class="more">More</td>
    </tr>
    <tr class="alternate">
        <td class="label"><a href="http://example.com" onclick="_gaq.push(['_trackEvent', 'outbound-article', 'http://example.com', 'Archives']);" >Archives</a></td>
        <td class="views">1</td>
        <td class="more">More</td>
    </tr>
    <tr>
        <td class="label"><a href="http://example.com" onclick="_gaq.push(['_trackEvent', 'outbound-article', 'http://example.com', '235']);" >235</a></td>
        <td class="views">1</td>
        <td class="more">More</td>
    </tr>
    </tbody>
</table>

<div data-example-id="contextual-table" class="bs-example">
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Column heading</th>
            <th>Column heading</th>
            <th>Column heading</th>
            <th class="info">Info heading</th>
        </tr>
        </thead>
        <tbody>
        <tr class="active">
            <th scope="row">1</th>
            <td>Column content</td>
            <td>Column content</td>
            <td>Column content</td>
            <td class="info">Column content</td>
        </tr>
        <tr>
            <th scope="row">2</th>
            <td>Column content</td>
            <td>Column content</td>
            <td>Column content</td>
            <td class="info">Column content</td>
        </tr>
        <tr class="success">
            <th scope="row">3</th>
            <td>Column content</td>
            <td>Column content</td>
            <td>Column content</td>
            <td class="info">Column content</td>
        </tr>
        <tr>
            <th scope="row">4</th>
            <td>Column content</td>
            <td>Column content</td>
            <td>Column content</td>
            <td class="info">Column content</td>
        </tr>
        <tr class="info">
            <th scope="row">5</th>
            <td>Column content</td>
            <td>Column content</td>
            <td>Column content</td>
            <td class="info">Column content</td>
        </tr>
        <tr>
            <th scope="row">6</th>
            <td>Column content</td>
            <td>Column content</td>
            <td>Column content</td>
            <td class="info">Column content</td>
        </tr>
        <tr class="warning">
            <th scope="row">7</th>
            <td>Column content</td>
            <td>Column content</td>
            <td>Column content</td>
            <td class="info">Column content</td>
        </tr>
        <tr>
            <th scope="row">8</th>
            <td>Column content</td>
            <td>Column content</td>
            <td>Column content</td>
            <td class="info">Column content</td>
        </tr>
        <tr class="danger">
            <th scope="row">9</th>
            <td>Column content</td>
            <td>Column content</td>
            <td>Column content</td>
            <td class="info">Column content</td>
        </tr>
        </tbody>
    </table>
</div>

<h2>List Type Tests</h2>
<h3>Definition List</h3>
<dl>
    <dt>Definition List Title</dt>
    <dd>This is a definition list division.</dd>
    <dt>Definition</dt>
    <dd>An exact statement or description of the nature, scope, or meaning of something: <em>our definition of what constitutes poetry.</em></dd>
    <dt>Gallery</dt>
    <dd>A feature introduced with WordPress 2.5, that is specifically an exposition of images attached to a post. In that same vein, an upload is “attached to a post” when you upload it while editing a post.</dd>
    <dt>Gravatar</dt>
    <dd>A globally recognized avatar (a graphic image or picture that represents a user). A gravatar is associated with an email address, and is maintained by the Gravatar.com service. Using this service, a blog owner can configure their blog so that a user’s gravatar is displayed along with their comments.</dd>
</dl>
<h3>Unordered List (Nested)</h3>
<ul>
    <li>List item one
        <ul>
            <li>List item one
                <ul>
                    <li>List item one</li>
                    <li>List item two</li>
                    <li>List item three</li>
                    <li>List item four</li>
                </ul>
            </li>
            <li>List item two</li>
            <li>List item three</li>
            <li>List item four</li>
        </ul>
    </li>
    <li>List item two</li>
    <li>List item three</li>
    <li>List item four</li>
</ul>
<h3>Ordered List</h3>
<ol>
    <li>List item one
        <ol>
            <li>List item one
                <ol>
                    <li>List item one</li>
                    <li>List item two</li>
                    <li>List item three</li>
                    <li>List item four</li>
                </ol>
            </li>
            <li>List item two</li>
            <li>List item three</li>
            <li>List item four</li>
        </ol>
    </li>
    <li>List item two</li>
    <li>List item three</li>
    <li>List item four</li>
</ol>
<h2>HTML Element Tag Tests</h2>
<p>All other HTML tags listed in the <a href="http://en.support.wordpress.com/code/" onclick="_gaq.push(['_trackEvent', 'outbound-article', 'http://en.support.wordpress.com/code/', 'FAQ']);" >FAQ</a>:</p>
<p>Here is the address for Automattic, using the <code><address></code> tag:</p>
<address>355 1st Street Suite 202<br />
    San Francisco, CA 94105<br />
    United States</address>


<h3>Context Forms</h3>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label">Noraml State</label>
                <input type="text" class="form-control">
            </div>
            <button class="btn btn-default">Button Default</button>
        </div>
        <div class="col-md-3">
            <div class="form-group has-success has-feedback">
                <label class="control-label">Has Success</label>
                <input type="text" class="form-control">
                <span class="glyphicon glyphicon-ok form-control-feedback" aria-hidden="true"></span>
            </div>
            <button class="btn btn-success">Button Success</button>
        </div>
        <div class="col-md-3">
            <div class="form-group has-warning has-feedback">
                <label class="control-label">Has Warning</label>
                <input type="text" class="form-control">
                <span class="glyphicon glyphicon-warning-sign form-control-feedback" aria-hidden="true"></span>
            </div>
            <button class="btn btn-warning">Button Warning</button>
        </div>
        <div class="col-md-3">
            <div class="form-group has-error has-feedback">
                <label class="control-label">Has Error</label>
                <input type="text" class="form-control">
                <span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>
            </div>
            <button class="btn btn-danger">Button Danger</button>
        </div>
    </div>


<div class="row">&nbsp;</div>
    <textarea class="form-control" rows="3"></textarea>

    <div class="checkbox">
        <label>
            <input type="checkbox" value="">
            <span>Option one is this and that&mdash;be sure to include why it's great</span>
        </label>
    </div>
    <div class="checkbox disabled">
        <label>
            <input type="checkbox" value="" disabled>
            <span>Option two is disabled</span>
        </label>
    </div>

    <div class="radio">
        <label class="radio-custom" data-initialize="radio" >
            <input class="sr-only" type="radio" name="optionsRadios" id="optionsRadios1" value="option1" checked>
            Option one is this and that&mdash;be sure to include why it's great
        </label>
    </div>
    <div class="radio" >
        <label class="radio-custom" data-initialize="radio" >
            <input class="sr-only" type="radio" name="optionsRadios" id="optionsRadios2" value="option2">
            Option two can be something else and selecting it will deselect option one
        </label>
    </div>
    <div class="radio disabled">
        <label class="radio-custom" data-initialize="radio" >
            <input class="sr-only" type="radio" name="optionsRadios" id="optionsRadios3" value="option3" disabled>
            Option three is disabled
        </label>
    </div>

    <label class="checkbox-inline">
        <input type="checkbox" id="inlineCheckbox1" value="option1">
        <span >1</span>
    </label>
    <label class="checkbox-inline">
        <input type="checkbox" id="inlineCheckbox2" value="option2"> <span >2</span>
    </label>
    <label class="checkbox-inline" data-initialize="checkbox">
        <input type="checkbox" id="inlineCheckbox3" value="option3"> <span >3</span>
    </label>

    <label class="radio-inline radio-custom" data-initialize="radio" >
        <input class="sr-only" type="radio" name="inlineRadioOptions" id="inlineRadio1" value="option1"> 1
    </label>
    <label class="radio-inline radio-custom" data-initialize="radio">
        <input class="sr-only" type="radio" name="inlineRadioOptions" id="inlineRadio2" value="option2"> 2
    </label>
    <label class="radio-inline radio-custom" data-initialize="radio">
        <input class="sr-only" type="radio" name="inlineRadioOptions" id="inlineRadio3" value="option3"> 3
    </label>

    <input class="form-control" id="disabledInput" type="text" placeholder="Disabled input here..." disabled>

        <div class="checkbox has-success">
            <label>
                <input type="checkbox" id="checkboxSuccess" value="option1">
                <span >Checkbox with success</span>
            </label>
        </div>
        <div class="checkbox has-warning">
            <label data-initialize="checkbox">
                <input type="checkbox" id="checkboxWarning" value="option1">
                <span >Checkbox with warning</span>
            </label>
        </div>
        <div class="checkbox has-error">
            <label data-initialize="checkbox">
                <input type="checkbox" id="checkboxError" value="option1">
                <span >Checkbox with error</span>
            </label>
        </div>

<div class="form-group">
    <label>selectbox</label>
    <select class="form-control csstest_selectbox">
        <option>seelect 1</option>
        <option>seelect 2</option>
    </select>
</div>
<!--<script type="text/javascript">
    jQuery('.csstest_selectbox').selectpicker({
        //liveSearch: true
    });
</script>-->

<h3>Button States</h3>
    <h4>Link</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-link">Btn Link</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-link">Link Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-link" disabled>Btn Link Disabled</button>
        </div>
    </div>
    <h4>Default</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-default">Btn Default</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-default">Default Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-default" disabled>Btn Default Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-default active">Btn Default Active</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-default btn-led">Btn LED</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-default btn-led active">Btn LED Active</button>
        </div>
    </div>
    <h4>Primary</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-primary">Btn Primary</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary">Primary Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary" disabled>Btn Primary Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary active">Btn Primary Active</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-led">Btn LED</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-led active">Btn LED Active</button>
        </div>
    </div>
    <h4>Info</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-info">Btn Info</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-info">Info Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-info" disabled>Btn Info Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-info active">Btn Info Active</button>
        </div>

        <div class="col-md-2">
            <button class="btn btn-info btn-led">Btn LED</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-info btn-led active">Btn LED Active</button>
        </div>
    </div>
    <h4>Danger</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-danger">Btn Danger</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-danger">Danger Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-danger" disabled>Btn Danger Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-danger active">Btn Danger Active</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-danger btn-led">Btn LED</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-danger btn-led active">Btn LED Active</button>
        </div>
    </div>
    <h4>Success</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-success">Btn Success</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success">Success Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success" disabled>Btn Success Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success active">Btn Success Active</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success btn-led">Btn LED</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success btn-led active">Btn LED Active</button>
        </div>
    </div>
    <h4>Warning</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-warning">Btn Warning</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-warning">Warning Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-warning" disabled>Btn Warning Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-warning active">Btn Warning Active</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-warning btn-led">Btn LED</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-warning btn-led active">Btn LED Active</button>
        </div>
    </div>

<div>
    <h3>Outline Buttons</h3>
    <h4>Default</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-default btn-outline">Btn Default</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-default btn-outline">Default Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-default btn-outline" disabled>Btn Default Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-default btn-outline active">Btn Default Active</button>
        </div>
    </div>
    <h4>Primary</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-primary btn-outline ">Btn Primary</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-outline ">Primary Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-outline " disabled>Btn Primary Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-outline active">Btn Primary Active</button>
        </div>
    </div>
    <h4>Info</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-info btn-outline ">Btn Info</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-info btn-outline">Info Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-info btn-outline" disabled>Btn Info Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-info btn-outline active">Btn Info Active</button>
        </div>
    </div>
    <h4>Danger</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-danger btn-outline">Btn Danger</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-danger btn-outline">Danger Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-danger btn-outline" disabled>Btn Danger Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-danger btn-outline active">Btn Danger Active</button>
        </div>
    </div>
    <h4>Success</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-success btn-outline">Btn Success</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success btn-outline">Success Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success btn-outline" disabled>Btn Success Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success btn-outline active">Btn Success Active</button>
        </div>
    </div>
    <h4>Warning</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-warning btn-outline">Btn Warning</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-warning btn-outline">Warning Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-warning btn-outline" disabled>Btn Warning Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-warning btn-outline active">Btn Warning Active</button>
        </div>
    </div>
</div>
<div style="background:url('/uPage/img/democon_img/600.jpeg'); padding-top: 20px; padding-bottom: 20px; margin-top: 20px;" class="container-fluid">
    <h3>Outline Buttons with bg</h3>
    <h4>Default</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-default btn-outline">Btn Default</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-default btn-outline">Default Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-default btn-outline" disabled>Btn Default Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-default btn-outline active">Btn Default Active</button>
        </div>
    </div>
    <h4>Primary</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-primary btn-outline ">Btn Primary</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-outline ">Primary Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-outline " disabled>Btn Primary Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-outline active">Btn Primary Active</button>
        </div>
    </div>
    <h4>Info</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-info btn-outline ">Btn Info</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-info btn-outline">Info Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-info btn-outline" disabled>Btn Info Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-info btn-outline active">Btn Info Active</button>
        </div>
    </div>
    <h4>Danger</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-danger btn-outline">Btn Danger</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-danger btn-outline">Danger Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-danger btn-outline" disabled>Btn Danger Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-danger btn-outline active">Btn Danger Active</button>
        </div>
    </div>
    <h4>Success</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-success btn-outline">Btn Success</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success btn-outline">Success Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success btn-outline" disabled>Btn Success Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success btn-outline active">Btn Success Active</button>
        </div>
    </div>
    <h4>Warning</h4>
    <div class="row">
        <div class="col-md-2">
            <button class="btn btn-warning btn-outline">Btn Warning</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-warning btn-outline">Warning Badge <span class="badge">4</span></button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-warning btn-outline" disabled>Btn Warning Disabled</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-warning btn-outline active">Btn Warning Active</button>
        </div>
    </div>
</div>

<h3>Button Groups</h3>
    <h4>Default</h4>
    <div class="btn-group">
        <button class="btn btn-default">Button</button>
        <button class="btn btn-default">Button</button>
        <button class="btn btn-default active">Active</button>
        <button class="btn btn-default">Button</button>
        <button class="btn btn-default">Button</button>
        <button class="btn btn-default" disabled>Disabled</button>
    </div>
    <h4>Info</h4>
    <div class="btn-group">
        <button class="btn btn-info">Button</button>
        <button class="btn btn-info">Button</button>
        <button class="btn btn-info active">Active</button>
        <button class="btn btn-info">Button</button>
        <button class="btn btn-info">Button</button>
        <button class="btn btn-info" disabled>Disabled</button>
    </div>
    <h4>Danger</h4>
    <div class="btn-group">
        <button class="btn btn-danger">Button</button>
        <button class="btn btn-danger">Button</button>
        <button class="btn btn-danger active">Active</button>
        <button class="btn btn-danger">Button</button>
        <button class="btn btn-danger">Button</button>
        <button class="btn btn-danger" disabled>Disabled</button>
    </div>
    <h4>Success</h4>
    <div class="btn-group">
        <button class="btn btn-success">Button</button>
        <button class="btn btn-success">Button</button>
        <button class="btn btn-success active">Active</button>
        <button class="btn btn-success">Button</button>
        <button class="btn btn-success">Button</button>
        <button class="btn btn-success" disabled>Disabled</button>
    </div>
    <h4>Warning</h4>
    <div class="btn-group">
        <button class="btn btn-warning">Button</button>
        <button class="btn btn-warning">Button</button>
        <button class="btn btn-warning active">Active</button>
        <button class="btn btn-warning">Button</button>
        <button class="btn btn-warning">Button</button>
        <button class="btn btn-warning" disabled>Disabled</button>
    </div>
    <h4>Primary</h4>
    <div class="btn-group">
        <button class="btn btn-primary">Button</button>
        <button class="btn btn-primary">Button</button>
        <button class="btn btn-primary active">Active</button>
        <button class="btn btn-primary">Button</button>
        <button class="btn btn-primary">Button</button>
        <button class="btn btn-primary" disabled>Disabled</button>
    </div>

<h3>Bs Callouts</h3>
    <div class="bs-callout bs-callout-default">
        <p>Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo. </p>
    </div>
    <div class="bs-callout bs-callout-success">
        <p>Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo. </p>
    </div>
    <div class="bs-callout bs-callout-warning">
        <p>Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo. </p>
    </div>
    <div class="bs-callout bs-callout-danger">
        <p>Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo. </p>
    </div>
    <div class="bs-callout bs-callout-primary">
        <p>Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo. </p>
    </div>
    <div class="bs-callout bs-callout-info">
        <p>Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo. </p>
    </div>

<h3>Links-styled</h3>
<div class="row">
    <div class="col-md-6">
        <p><a href="#">Normal Link</a> </p>
        <p><a href="#" class="text-primary">Text-Primary Link</a> </p>
        <p><a href="#" class="text-info">Text-Info Link</a> </p>
        <p><a href="#" class="text-warning">Text-Warning Link</a> </p>
        <p><a href="#" class="text-success">Text-Success Link</a> </p>
        <p><a href="#" class="text-danger">Text-Danger Link</a> </p>
    </div>
    <div class="col-md-6">
        <p><a href="#">Normal Link</a> </p>
        <p><a href="#" class="bg-primary">Bg-Primary Link</a> </p>
        <p><a href="#" class="bg-info">Bg-Info Link</a> </p>
        <p><a href="#" class="bg-warning">Bg-Warning Link</a> </p>
        <p><a href="#" class="bg-success">Bg-Success Link</a> </p>
        <p><a href="#" class="bg-danger">Bg-Danger Link</a> </p>
    </div>
</div>

<h3>Text text-xxxx</h3>

<p class="text-success">Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. <a href="#">Hyperlink here</a>. Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo. </p>
<p class="text-primary">Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.  <a href="#">Hyperlink here</a>. Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo. </p>
<p class="text-danger">Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.  <a href="#">Hyperlink here</a>. Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo. </p>
<p class="text-warning">Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.  <a href="#">Hyperlink here</a>. Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo. </p>
<p class="text-info">Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.  <a href="#">Hyperlink here</a>. Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo. </p>
<p class="text-muted">Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.  <a href="#">Hyperlink here</a>. Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo. </p>

<h3>Text bg-xxxx</h3>
    <div class="bg-primary">
        <p>Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.  <a href="#">Hyperlink here</a>. Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo. </p>
    </div>
<div class="bg-info">
    <p>Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.  <a href="#">Hyperlink here</a>. Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo.</p>
</div>
<div class="bg-danger">
    <p>Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.  <a href="#">Hyperlink here</a>.  Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo. </p>
</div>
<div class="bg-warning">
    <p>Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.  <a href="#">Hyperlink here</a>.  Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo. </p>
</div>
<div class="bg-success">
    <p>Nulla lobortis faucibus leo eget vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.  <a href="#">Hyperlink here</a>.   Suspendisse fringilla, tortor eu ultrices ornare, metus erat vulputate orci, at blandit ante elit nec nunc. Fusce sed ipsum euismod, cursus ligula nec, dignissim nulla. In semper mi in mi porttitor, id efficitur dolor tempor. Pellentesque imperdiet lacus ut purus euismod, a fringilla elit sodales. Praesent libero eros, accumsan vitae est eu, bibendum consequat nibh. Etiam eu metus non odio pulvinar ultrices. Fusce mattis leo arcu, non mollis nibh mattis eget. Donec faucibus laoreet ultrices. In hac habitasse platea dictumst. Morbi lobortis dolor ante, consequat fringilla felis placerat suscipit. Curabitur vitae rhoncus dolor. Suspendisse bibendum leo in magna lacinia commodo. </p>
</div>

</div>

<?
$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/template.php";
?>
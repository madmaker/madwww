<!-- Modals -->
<!--suppress HtmlUnknownAttribute -->
<div class="modal fade" id="uCat_request_price_form_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_request_price_form_dgLabel" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uCat_request_price_form_dgLabel">Заполните данные заинтересовавшего товара</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="uCat_request_price_form_name">Ваше имя</label>
                    <input type="text" id="uCat_request_price_form_name" class="form-control">
                </div>
                <div class="form-group">
                    <label for="uCat_request_price_form_email">Ваш email или телефон</label>
                    <input type="text" id="uCat_request_price_form_email" class="form-control">
                </div>
                <div class="form-group">
                    <label for="uCat_request_price_form_item_title">Заинтересовавший товар</label>
                    <input type="text" id="uCat_request_price_form_item_title" class="form-control">
                    <input type="hidden" id="uCat_request_price_form_item_id">
                    <input type="hidden" id="uCat_request_price_form_var_id">
                </div>
                <div class="form-group">
                    <label for="uCat_request_price_form_comment">Комментарий</label>
                    <textarea id="uCat_request_price_form_comment" class="form-control">Уточните, пожалуйста, точную стоимость этого товара</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uCat_request_price_form.send_form();">Запросить</button>
            </div>
        </div>
    </div>
</div>

<?//if(isset($this->uFunc)) $uFunc=&$this->uFunc;
//else $uFunc=&$this->uCore->uFunc;

/** @noinspection PhpUndefinedMethodInspection */
$this->uFunc->incJs(u_sroot."uCat/js/request_price_form.min.js");
?>
<div class="modal fade" id="configurator_new_product_dg" tabindex="-1" role="dialog" aria-labelledby="configurator_new_product_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="configurator_new_product_dgLabel">Новый продукт</h4>
            </div>
            <div class="modal-body">
                <div class="form-group" id="configurator_new_product_product_name_form_group">
                    <label for="configurator_new_product_product_name">Название продукта</label>
                    <input type="text" class="form-control" id="configurator_new_product_product_name" placeholder="Летающая машина">
                    <p class="help-block hidden"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="configurator.products_admin.new_product_save()">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="configurator_edit_pr_pos_dg" tabindex="-1" role="dialog" aria-labelledby="configurator_edit_pr_pos_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="configurator_edit_pr_pos_dgLabel">Изменить положение продукта</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="form-control" id="configurator_edit_pr_pos_pr_id">
                <div class="form-group" id="configurator_edit_pr_pos_product_name_form_group">
                    <label for="configurator_edit_pr_pos_input">Положение продукта</label>
                    <input type="number" class="form-control" id="configurator_edit_pr_pos_input">
                    <div class="bs-callout bs-callout-default">
                        Укажите число.<br>
                        Продукты будут отображаться согласно положению: 0, 1, 2, 3, 4.<br>
                        Чтобы продукт был выше других, его положение должно быть меньше, чтобы продукт был ниже - больше
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="configurator.products_admin.pr_pos_save()">Сохранить</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="configurator_company_bank_details_dg" tabindex="-1" role="dialog" aria-labelledby="configurator_company_bank_details_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Реквизиты компании</h4>
            </div>
            <div class="modal-body" id="configurator_company_bank_details_dg_body">Загрузка реквизитов</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="configurator.products_admin.save_company_bank_details()">Сохранить</button>
            </div>
        </div>
    </div>
</div>
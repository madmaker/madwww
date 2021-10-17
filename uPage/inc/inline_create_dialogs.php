<div class="modal fade" id="uPage_common_create_page_dg" tabindex="-1" role="dialog" aria-labelledby="uPage_common_create_page_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-90">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uPage_common_create_page_dgLabel"><?=$this->uInt->text(array('uPage','inline_create_dialogs'),"New page - dg title"/*Новая страница*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="uPage_common_create_page_title"><?=$this->uInt->text(array('uPage','inline_create_dialogs'),"New page title - input label"/*Заголовок:*/)?></label>
                    <input type="text" id="uPage_common_create_page_title" class="form-control">
                </div>
                <div class="form-group">
                    <label><?=$this->uInt->text(array('uPage','inline_create_dialogs'),"New page - select template"/*Выберите шаблон*/)?></label>

                    <div class="container-fluid" id="uPage_create_page_select_tmp_dg_body">
                        <div class="row">
                            <?for($i=0;$i<6;$i++) {?>
                                <div id="uPage_common_template_ex_page_<?=$i?>" class="uPage_common_template_ex img-rounded" onclick="uPage_common.choose_template('page',<?=$i?>)">
                                    <img class="img-responsive" src="uPage/img/templates/template_<?=$i?>.jpg">
                                </div>
                            <?}?>
                        </div>
                        <h4>Top Panel</h4>
                        <div class="row">
                            <?for($i=0;$i<1;$i++) {?>
                                <div id="uPage_common_template_ex_top_panel_<?=$i?>" class="uPage_common_template_ex img-rounded" onclick="uPage_common.choose_template('top_panel',<?=$i?>)">
                                    <img class="img-responsive" src="uPage/img/templates/top_panel_<?=$i?>.jpg">
                                </div>
                            <?}?>
                        </div>
                        <h4>Header</h4>
                        <div class="row">
                            <?for($i=0;$i<1;$i++) {?>
                                <div id="uPage_common_template_ex_header_<?=$i?>" class="uPage_common_template_ex img-rounded" onclick="uPage_common.choose_template('header',<?=$i?>)">
                                    <img class="img-responsive" src="uPage/img/templates/header_<?=$i?>.png">
                                </div>
                            <?}?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
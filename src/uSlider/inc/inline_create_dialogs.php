<div class="modal fade" id="uSlider_new_slider_dg" tabindex="-1" role="dialog" aria-labelledby="uSlider_new_slider_dgLabel" aria-hidden="true">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title" id="uSlider_new_slider_dgLabel"><?=$this->uInt->text(array('uSlider','inline_create_dialogs'),"New slider - dg title"/*Новый слайдер*/)?></h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label><?=$this->uInt->text(array('uSlider','inline_create_dialogs'),"Slider title - label"/*Название слайдера*/)?></label>
                <input type="hidden" id="uSlider_new_slider_type">
                <input id="uSlider_new_slider_title" type="text" class="form-control" placeholder="<?=$this->uInt->text(array('uSlider','inline_create_dialogs'),"Slider title - placeholder"/*Мой новый слайдер*/)?>" onkeypress="u235_common.create_func_press_enter(event,'input','uSlider_common.create_slider()')">
                <p class="help-block"><?=$this->uInt->text(array('uSlider','inline_create_dialogs'),"Slider title - hint"/*Введите название нового слайдера, например "Мой новый слайдер"*/)?></p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uInt->text(array('uSlider','inline_create_dialogs'),"Close - btn"/*Закрыть*/)?></button>
            <button id="uSlider_new_slider_dg_submit_btn" type="button" class="btn btn-primary" onclick="uSlider_common.create_slider();"><?=$this->uInt->text(array('uSlider','inline_create_dialogs'),"Save - btn"/*Создать*/)?></button>
        </div>
    </div>
</div>
</div>
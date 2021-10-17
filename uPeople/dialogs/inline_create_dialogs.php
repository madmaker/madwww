<div class="modal fade" id="uPeople_new_user_dg" tabindex="-1" role="dialog" aria-labelledby="uPeople_new_user_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uPeople_new_user_dgLabel">Добавить запись</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="uPeople_new_user_firstname">Имя*:</label>
                    <input type="text" id="uPeople_new_user_firstname" name="uPeople_new_user_firstname" class="form-control">
                </div>
                <div class="form-group">
                    <label for="uPeople_new_user_secondname">Отчество:</label>
                    <input type="text" id="uPeople_new_user_secondname" name="uPeople_new_user_secondname" class="form-control">
                </div>
                <div class="form-group">
                    <label for="uPeople_new_user_lastname">Фамилия:</label>
                    <input type="text" id="uPeople_new_user_lastname" name="uPeople_new_user_lastname" class="form-control">
                </div>
                <div class="form-group">
                    <p class="help-block">* - отмеченные поля обязательны.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="uPeople_inline_create.create_user_do()">Создать</button>
            </div>
        </div>
    </div>
</div>
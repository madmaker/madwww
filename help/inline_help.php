<div class="modal fade" id="u235_help_dg" tabindex="-1" role="dialog" aria-labelledby="u235_help_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="u235_help_dgLabel"><?= $this->uInt->text(
                    ['help', 'inline_help'],
                    "help - dg header"
                ) ?></h4>
            </div>
            <div class="modal-body">
                <div class="panel-group" id="uHelp_accordion" role="tablist" aria-multiselectable="true">
                    <?
                    if($this->uFunc->mod_installed('uCat')&&$this->access(25)){?>
                        <div class="panel panel-default">
                            <div class="panel-heading" role="tab" id="uHelp_collapsibles_uCat">
                                <h4 class="panel-title">
                                    <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#uHelp_collapsible_uCat" aria-expanded="false" aria-controls="collapseThree"><?= $this->uInt->text(
                                        ['help', 'inline_help'],
                                        "uCat - help section name"
                                    ) ?></a>
                                    </h4>
                                </div>
                            <div id="uHelp_collapsible_uCat" class="panel-collapse collapse <?= $this->mod ==
                            'uCat'
                                ? 'in'
                                : '' ?>" role="tabpanel" aria-labelledby="uHelp_collapsibles_uCat">
                                <div class="panel-body"><?include_once 'uCat/initial.php';?></div>
                                </div>
                            </div>
                    <?}
                    if($this->access(7)){?>
                        <div class="panel panel-default">
                            <div class="panel-heading" role="tab" id="uHelp_collapsibles_content">
                                <h4 class="panel-title">
                                    <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#uHelp_collapsible_content" aria-expanded="false" aria-controls="collapseThree"><?= $this->uInt->text(
                                        ['help', 'inline_help'],
                                        "uPage - help section name"
                                    ) ?></a>
                                    </h4>
                                </div>
                            <div id="uHelp_collapsible_content" class="panel-collapse collapse <?= $this->mod ==
                                'uPage' ||
                            $this->mod == 'uEditor' ||
                            $this->mod == 'page' ||
                            $this->mod == 'static'
                                ? 'in'
                                : '' ?>" role="tabpanel" aria-labelledby="uHelp_collapsibles_content">
                                <div class="panel-body"><?include_once 'uPage/initial.php';?></div>
                                </div>
                            </div>
                    <?}

                    if($this->uFunc->mod_installed('uViblog')&&$this->access(4)){?>
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="uHelp_collapsibles_uViblog">
                            <h4 class="panel-title">
                                <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#uHelp_collapsible_uViblog" aria-expanded="false" aria-controls="collapseThree"><?= $this->uInt->text(
                                    ['help', 'inline_help'],
                                    "uViblog - help section name"
                                ) ?></a>
                            </h4>
                        </div>
                        <div id="uHelp_collapsible_uViblog" class="panel-collapse collapse <?= $this->mod ==
                        'uViblog'
                            ? 'in'
                            : '' ?>" role="tabpanel" aria-labelledby="uHelp_collapsibles_uViblog">
                            <div class="panel-body"><?include_once 'uViblog/initial.php';?></div>
                        </div>
                    </div>
                    <?}?>

                    </div>
            </div>
            <div class="modal-footer">
                <p><a href="http://madwww.ru/uPage/support" target="_blank"><?= $this->uInt->text(
                    ['help', 'inline_help'],
                    "Manuals link"
                ) ?></a></p>
                <small class="text-muted"><?= $this->uInt->text(
                    ['help', 'inline_help'],
                    "Need assistance text"
                ) ?><br>
                    <?= $this->uInt->text(
                        ['help', 'inline_help'],
                        "Write us at - text"
                    ) ?><a href="mailto:support@madwww.org">info@madwww.org</a></small>
            </div>
        </div>
    </div>
</div>
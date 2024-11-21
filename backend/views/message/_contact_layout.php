<div class='box-body no-padding'>
    <div style="height: 480px; overflow-y: scroll">
        {items}
    </div>
</div>
<div class='box-footer '>
        <div class="col-md-8">
            {pager}
        </div>
        <div class="col-md-4  text-right">
            <button type="button" class="btn btn-flat btn-default"
                    data-dismiss="modal"><?= __('Close') ?></button>
            <button type="button" class="btn btn-flat btn-primary"
                    onclick="addSelected()"><?= __('Add Selected') ?></button>
        </div>
</div>
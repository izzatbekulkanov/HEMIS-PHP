<div class='box-body no-padding'>
    <div class="mailbox-controls">
        <!-- Check all button -->
        <!--<div class="pull-left">
            <button type="button" class="btn btn-default btn-sm checkbox-toggle" onclick="toggleMailItems()"><i
                        class="fa fa-square-o"></i>
            </button>
            <div class="btn-group">
                <button type="button" class="btn btn-default btn-sm" onclick="deleteMailItems()"><i
                            class="fa fa-trash-o"></i></button>
            </div>
        </div>-->
        <!-- /.btn-group -->
        <div class="text-right">
            {summary}
            {pager}
        </div>
    </div>
    <div class="table-responsive mailbox-messages">
        {items}
    </div>
    <div class="mailbox-controls">
        <div class="text-right">
            {summary}
            {pager}
        </div>
    </div>
</div>
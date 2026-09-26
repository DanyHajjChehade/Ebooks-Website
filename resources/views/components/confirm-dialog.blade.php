{{--
    Shared confirm dialog (DESIGN.md §4.18). Any submit button with data-confirm="Question?" opens it:
      data-confirm-body   consequence sentence
      data-confirm-ok     destructive label ("Delete book")      data-confirm-cancel  safe label ("Keep book")
      data-confirm-busy   loading label ("Deleting…")            data-confirm-variant danger (default) | primary
    Without JS the button simply submits, and the server handles it.
--}}
<dialog class="dialog" id="confirm-dialog" aria-labelledby="confirm-dialog-title" aria-describedby="confirm-dialog-body" data-confirm-dialog>
    <div class="dialog__body">
        <h2 class="h3" id="confirm-dialog-title" data-confirm-title>Are you sure?</h2>
        <p class="text-muted" id="confirm-dialog-body" data-confirm-text></p>
    </div>
    <div class="dialog__actions">
        <button class="btn btn-secondary" type="button" data-dialog-close data-confirm-cancel autofocus>Keep it</button>
        <button class="btn btn-danger" type="button" data-confirm-ok>Confirm</button>
    </div>
</dialog>

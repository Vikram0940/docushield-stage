<!-- Modal Empty -->
<!-- Vertically centered scrollable modal -->
<div class="modal fade" id="emptyModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="emptyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">        
        <div class="modal-content">
            <?php echo form_open("", "class='NewProject needs-validation' id='frmempty' name='frmempty' novalidate"); ?>
                <div class="modal-header">
                    <h3 class="modal-title fw-300 text-16" id="emptyModalLabel">Create a New Project</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary text-14" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnsubmit" class="btn btn-primary text-14">Submit</button>
                </div>
            <?=form_close()?>
        </div>        
    </div>
</div>
<!-- Modal Welcome User -->
<!-- Tutorial Intro Modal -->
<div class="modal fade" id="createFolderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <?php echo form_open("", "class='needs-validation' id='frmdata' name='frmdata' novalidate"); ?>
        <div class="modal-header">
          <h5 class="modal-title">Create Folder 🎉</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Folder name</label>
            <input type="text" name="name" class="form-control" placeholder="Enter folder name" required>
          </div>
        </div>
        <div class="modal-footer">
          <button id="skipTutorial" type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button id="btnsubmit" type="submit" class="btn btn-primary">Submit</button>
        </div>
      <?=form_close()?>
    </div>
  </div>
</div>
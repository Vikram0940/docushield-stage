<!-- Rename Modal -->
<div class="modal fade" id="renameModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <?php echo form_open("", "class='needs-validation' id='frmrename' name='frmrename' novalidate"); ?>
    <div class="modal-content">
      <div class="modal-header">
        <p class="modal-title">Rename File</p>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center mb-3">
          <div id="renameFileBadge"></div>
          <div class="ms-2">
            <div id="renameFileTitle" class="fw-bold">untitled.pdf</div>
            <small id="renameFileSize" class="text-muted">0 KB</small>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">File Name</label>
          <input type="text" id="renameFileName" name="file_name" class="form-control" required />
        </div>
        <?php
        $projects = (new \App\Models\Project)->findAll();
        ?>
        <div class="mb-3">
          <label class="form-label">File Location</label>
          <select id="renameFileLocation" name="new_location" class="form-select">
            <option value="-1" selected>Storage</option>
            <?php
            if (!empty($projects)) {
              foreach ($projects as $proj) {
                echo "<option value='".$proj->id."'>".$proj->name."</option>";
              }
            }
            ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="renameSaveBtn" class="btn btn-primary">Save</button>
      </div>
    </div>
    <?=form_close()?>
  </div>
</div>
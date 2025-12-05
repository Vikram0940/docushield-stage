<!-- Modal Archive Project -->
<!-- Vertically centered scrollable modal -->
<div class="modal fade" id="uploadModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fw-300 text-16" id="uploadModalLabel">Upload Documents</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- File List -->
                <ul id="fileList" class="list-group d-none mb-3"></ul>
                <div id="dropArea" class="drop-area">
                  Drag files here or <a href="javascript:void(0)" id="browseBtn" class="text-decoration-none">browser</a>
                  <input type="file" id="fileInput" multiple hidden>
                </div>
            </div>
            <!-- <div class="modal-footer">
                <button type="button" class="btn btn-secondary text-14" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary text-14">Archive Project</button>
            </div> -->
        </div>
    </div>
</div>
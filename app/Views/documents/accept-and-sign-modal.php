<!-- Accept & Sign Confirmation Modal -->
<div class="modal fade" id="acceptsignModal" tabindex="-1" aria-labelledby="acceptsignModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content shadow-sm">
      <div class="modal-header">
        <h6 class="modal-title fw-300 text-primary text-dark" id="acceptsignModalLabel">Accept & Sign</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h5 class="fw-400 mb-2">Accept and Sign Document?</h5>
        <p class="text-primary text-16 mb-0">This will create a PDF singable document and mark this document as complete.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary text-16 fw-300 text-primary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary px-4 fw-300" id="confirmAccept" data-url="<?=site_url("dpanel/document/accept-and-sign/".$documentId)?>">Yes, Accept & Sign</button>
      </div>
    </div>
  </div>
</div>
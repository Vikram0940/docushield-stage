<!-- Return to Draft Confirmation Modal -->
<div class="modal fade" id="returndraftModal" tabindex="-1" aria-labelledby="returndraftModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg">
	    <div class="modal-content shadow-sm">
		    <div class="modal-header">
		        <h6 class="modal-title fw-300 text-primary text-dark" id="returndraftModalLabel">Return to Draft</h6>
		        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		    </div>
		    <div class="modal-body">
		        <h5 class="fw-400 mb-2">Return this document to Draft?</h5>
		        <p class="text-primary text-16 mb-0">This will disable all non team members from accessing or leaving comments on this document..</p>
		    </div>
		    <div class="modal-footer">
		        <button type="button" class="btn btn-secondary text-16 fw-300 text-primary" data-bs-dismiss="modal">Cancel</button>
		        <button type="button" class="btn btn-primary px-4 fw-300" id="confirmDraft" data-url="<?=site_url("dpanel/document/return-to-draft/".$documentId)?>">Yes, Return to Draft</button>
		    </div>
	    </div>
	</div>
</div>
<!-- Modal Edit Project -->
<!-- Vertically centered scrollable modal -->
<div class="modal fade" id="editproject" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editProjectLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <?php echo form_open("", "class='NewProject needs-validation' id='frmproject' name='frmproject' novalidate"); ?>
                <div class="modal-header">
                    <h3 class="modal-title fw-300 text-16" id="editProjectLabel">Edit Project</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="NewProject" novalidate>
                        <!-- Project Name -->
                        <div class="mb-3">
                            <label for="projectname" class="form-label text-primary">Project Name <span class="text-secondary">*</span></label>
                            <input type="text" name="name" class="form-control pe-5" id="projectname" placeholder="Enter project name" required data-error="Enter a project name." />
                        </div>
                        <!-- Description -->
                        <div class="mb-3">
                            <label for="projectdesc" class="form-label text-primary">Description</label>
                            <textarea name="description" id="projectdesc" class="form-control pe-5" rows="4" placeholder="Enter project description"></textarea>
                        </div>
                        <!-- Add Collabs -->
                        <div class="mb-3">
                            <label for="collaborators" class="form-label text-primary">Add Collaborators</label>
                            <div class="ds-input-btn d-flex align-items-start gap-3 mb-2 collab-email-wrapper">
                                <div class="collabs-dd-wrapper flex-grow-1">
                                    <div class="input-group form-control overflow-visible position-relative px-3 py-2 collab-email">
                                        <input type="text" name="collaborator_email" class="d-inline-block form-control p-0 border-0" id="collaborator_email" placeholder="Enter email" />
                                        <select name="permissions" class="d-none permissions text-secondary">
                                            <option value="1">can view</option>
                                            <option value="2">can comment</option>
                                        </select>
                                    </div>
                                    <div class="d-none collabs-list with-scrollbar" id="myUsersList">
                                        <ul class="collaborators-list" tabindex="0">
                                        </ul>
                                    </div>
                                </div>
                                <div class="add-collab-btn">
                                    <button type="button" id="addCollabs" class="d-inline-block btn btn-primary text-nowrap text-14" data-url="<?=site_url("dpanel/user/get-info")?>">Add Collaborators</button>
                                </div>
                            </div>
                            
                            <div class="form-text text-12">Users will need to sign up before they can add comments to the document</div>

                        </div>

                        <!-- Collabs List with permissions -->
                        <div class="mb-3 d-none" id="collaborators_list">
                            <label for="collaborators" class="form-label text-primary">Collaborators</label>
                            <div class="ds-input-btn d-flex align-items-center gap-3 mb-2">
                                <div class="card collabs-list with-scrollbar existing-collabs">
                                    <ul class="collaborators-list">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary text-14" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnsubmit" class="btn btn-primary text-14">Update Project</button>
                </div>
            <?=form_close()?>
        </div>
    </div>
</div>
<!-- Modal Archive Project -->
<!-- Vertically centered scrollable modal -->
<div class="modal fade" id="archiveproject" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="archiveProjectLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fw-300 text-16" id="archiveProjectLabel">Archive Project</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="NewProject" novalidate>
                    <!-- Project Name -->
                    <div class="mb-3">
                        <label for="projectname" class="form-label text-primary">Project Name <span class="text-secondary">*</span></label>
                        <input type="text" name="projectname" class="form-control pe-5" id="projectname" placeholder="Enter project name" required data-error="Enter a project name." value="Lilo vs. Stitch" />
                    </div>
                    <!-- Description -->
                    <div class="mb-3">
                        <label for="projectdesc" class="form-label text-primary">Description</label>
                        <textarea name="project-description" id="projectdesc" class="form-control pe-5" rows="4" placeholder="Enter project description" value="Case #12345">Case #12345</textarea>
                    </div>
                    <!-- Add Collabs -->
                    <div class="mb-3">
                        <label for="collaborators" class="form-label text-primary">Add Collaborators</label>
                        <div class="ds-input-btn d-flex align-items-start gap-3 mb-2 collab-email-wrapper">
                            <div class="collabs-dd-wrapper flex-grow-1">
                                <div class="input-group form-control overflow-visible position-relative px-3 py-2 collab-email">
                                    <input type="text" name="add-collabs" class="d-inline-block form-control p-0 border-0" id="collaborators" placeholder="Enter email" />
                                    <select name="permissions" class="d-none permissions text-secondary">
                                        <option>can view</option>
                                        <option>can edit</option>
                                    </select>
                                </div>
                                <div class="d-none collabs-list with-scrollbar">
                                    <ul class="collaborators-list" tabindex="0">
                                        <li>
                                            <!-- Collab Dropdown -->
                                            <div class="avatar">
                                                <img src="https://i.pravatar.cc/100?img=2" alt="Bob Singh" class="rounded-circle">
                                            </div>
                                            <div class="collaborator">
                                                <div class="name">Tom Maniford</div>
                                                <div class="email">tom@mail.com</div>
                                            </div>
                                        </li>
                                        <li>
                                            <!-- Collab Dropdown -->
                                            <div class="avatar">
                                                <img src="https://i.pravatar.cc/100?img=2" alt="Bob Singh" class="rounded-circle">
                                            </div>
                                            <div class="collaborator">
                                                <div class="name">Tom Maniford</div>
                                                <div class="email">tom@mail.com</div>
                                            </div>
                                        </li>
                                        <li>
                                            <!-- Collab Dropdown -->
                                            <div class="avatar">
                                                <img src="https://i.pravatar.cc/100?img=2" alt="Bob Singh" class="rounded-circle">
                                            </div>
                                            <div class="collaborator">
                                                <div class="name">Tom Maniford</div>
                                                <div class="email">tom@mail.com</div>
                                            </div>
                                        </li>
                                        <li>
                                            <!-- Collab Dropdown -->
                                            <div class="avatar">
                                                <img src="https://i.pravatar.cc/100?img=2" alt="Bob Singh" class="rounded-circle">
                                            </div>
                                            <div class="collaborator">
                                                <div class="name">Tom Maniford</div>
                                                <div class="email">tom@mail.com</div>
                                            </div>
                                        </li>
                                        <li>
                                            <!-- Collab Dropdown -->
                                            <div class="avatar">
                                                <img src="https://i.pravatar.cc/100?img=2" alt="Bob Singh" class="rounded-circle">
                                            </div>
                                            <div class="collaborator">
                                                <div class="name">Tom Maniford</div>
                                                <div class="email">tom@mail.com</div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="add-collab-btn">
                                <button type="button" id="addCollabs" class="d-inline-block btn btn-primary text-nowrap text-14">Add Collaborators</button>
                            </div>
                        </div>
                        
                        <div class="form-text text-12">Users will need to sign up before they can add comments to the document</div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary text-14" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary text-14">Archive Project</button>
            </div>
        </div>
    </div>
</div>
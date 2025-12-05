<!-- Modal Share Project -->
<!-- Vertically centered scrollable modal -->
<div class="modal fade" id="shareproject" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="shareProjectLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fw-300 text-16 flex-grow-1" id="shareProjectLabel">Share Project</h3>
                <div class="link-close align-self-center">
                    <a href="#" class="copy-link no-underline btn-text-secondary">
                        <i class="ds ds-link"></i>
                        <span class="text-underlined">Copy Link</span>
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body">
                <form class="NewProject" novalidate>
                    <!-- Add Collabs -->
                    <div class="mb-3">
                        <label for="collaborators" class="form-label text-primary">Email</label>
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
                                <button type="button" id="addCollabs" class="d-inline-block btn btn-primary text-nowrap text-14">Add Collaborator</button>
                            </div>
                        </div>
                        
                        <div class="form-text text-12">Users will need to sign up before they can add comments to the document</div>

                    </div>

                    <!-- Collabs List with permissions -->
                    <div class="mb-3">
                        <label for="collaborators" class="form-label text-primary">Collaborators</label>
                        <div class="ds-input-btn d-flex align-items-center gap-3 mb-2">
                            <div class="card collabs-list with-scrollbar existing-collabs">
                                <ul class="collaborators-list">
                                    <li>
                                        <!-- Collab Existing -->
                                        <div class="avatar">
                                            <img src="https://i.pravatar.cc/100?img=2" alt="Bob Singh" class="rounded-circle">
                                        </div>
                                        <div class="collab-permission">
                                            <div class="collaborator flex-grow-1">
                                                <div class="name">Tom Maniford</div>
                                                <div class="email">tom@mail.com</div>
                                            </div>
                                            <div class="invite-permissions">
                                                <select class="permission">
                                                    <option value="view">Can view</option>
                                                    <option value="comment">Can comment</option>
                                                    <option value="owner">Owner</option>
                                                </select>
                                            </div>
                                            
                                        </div>
                                    </li>
                                    <li>
                                        <!-- Collab Existing -->
                                        <div class="avatar">
                                            <img src="https://i.pravatar.cc/100?img=2" alt="Bob Singh" class="rounded-circle">
                                        </div>
                                        <div class="collab-permission">
                                            <div class="collaborator flex-grow-1">
                                                <div class="name">Tom Maniford</div>
                                                <div class="email">tom@mail.com</div>
                                            </div>
                                            <div class="invite-permissions">
                                                <select class="permission">
                                                    <option value="view">Can view</option>
                                                    <option value="comment">Can comment</option>
                                                    <option value="owner">Owner</option>
                                                </select>
                                            </div>
                                            
                                        </div>
                                    </li>
                                    <li>
                                        <!-- Collab Existing -->
                                        <div class="avatar">
                                            <img src="https://i.pravatar.cc/100?img=2" alt="Bob Singh" class="rounded-circle">
                                        </div>
                                        <div class="collab-permission">
                                            <div class="collaborator flex-grow-1">
                                                <div class="name">Tom Maniford</div>
                                                <div class="email">tom@mail.com</div>
                                            </div>
                                            <div class="invite-permissions">
                                                <span class="badge bg-utility-warning invite-pending">Invite pending</span>
                                                <select class="permission">
                                                    <option value="comment">Can comment</option>
                                                    <option value="view">Can view</option>
                                                    <option value="owner">Owner</option>
                                                </select>
                                            </div>
                                            
                                        </div>
                                    </li>
                                    <li>
                                        <!-- Collab Existing -->
                                        <div class="avatar">
                                            <img src="https://i.pravatar.cc/100?img=2" alt="Bob Singh" class="rounded-circle">
                                        </div>
                                        <div class="collab-permission">
                                            <div class="collaborator flex-grow-1">
                                                <div class="name">Tom Maniford</div>
                                                <div class="email">tom@mail.com</div>
                                            </div>
                                            <div class="invite-permissions">
                                                <select class="permission">
                                                    <option value="view">Can view</option>
                                                    <option value="comment">Can comment</option>
                                                    <option value="owner">Owner</option>
                                                </select>
                                            </div>
                                            
                                        </div>
                                    </li>
                                    <li>
                                        <!-- Collab Existing -->
                                        <div class="avatar">
                                            <img src="https://i.pravatar.cc/100?img=2" alt="Bob Singh" class="rounded-circle">
                                        </div>
                                        <div class="collab-permission">
                                            <div class="collaborator flex-grow-1">
                                                <div class="name">Tom Maniford</div>
                                                <div class="email">tom@mail.com</div>
                                            </div>
                                            <div class="invite-permissions">
                                                <select class="permission">
                                                    <option value="view">Can view</option>
                                                    <option value="comment">Can comment</option>
                                                    <option value="owner">Owner</option>
                                                </select>
                                            </div>
                                            
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-start">
                <div class="project-permissions">
                    <div class="permission-dd">
                        <div class="dropdown access-dropdown">
                            <button class="btn d-flex align-items-center justify-content-between w-100 p-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="access-icon">
                                        <i class="ds ds-globe ds-xl icon-grey"></i>
                                    </span>
                                    <div class="d-flex flex-column text-start text-10">
                                        <span class="fw-300">Anyone with the link can</span>
                                        <span class="fw-500">View Only</span>
                                    </div>
                                </div>
                            </button>

                            <ul class="dropdown-menu w-100">
                                <li><a class="dropdown-item" href="#">View Only</a></li>
                                <li><a class="dropdown-item" href="#">Can Comment</a></li>
                                <li><a class="dropdown-item" href="#">Can Edit</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-buttons ms-auto">
                    <button type="button" class="btn btn-secondary text-14" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary text-14">Send</button>
                </div>
            </div>
        </div>
    </div>
</div>
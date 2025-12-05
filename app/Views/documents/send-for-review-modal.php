<!-- Send Sign Modal -->
<!-- Vertically centered scrollable modal -->
<div class="modal fade" id="SendAndSign" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="SendSignLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fw-300 text-16 flex-grow-1" id="shareProjectLabel">Send & Sign</h3>
                <div class="link-close align-self-center">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body">
                <form class="SendSign" novalidate>
                    <!-- Add Collabs -->
                    <div class="mb-3">
                        <div class="ds-input-btn d-flex align-items-start gap-3 mb-2 signee-email-wrapper">
                            <div class="signees-dd-wrapper flex-grow-1">
                                <div class="input-group form-control overflow-visible position-relative px-3 py-2 signee-email">
                                    <input type="text" name="add-signee" class="d-inline-block form-control p-0 border-0" id="collaborators" placeholder="Enter email" />
                                </div>
                                <div class="d-none signees-list with-scrollbar">
                                    <ul class="isignees-list" tabindex="0">
                                        <li>
                                            <!-- Signee Dropdown -->
                                            <div class="avatar">
                                                <img src="https://i.pravatar.cc/100?img=2" alt="Bob Singh" class="rounded-circle">
                                            </div>
                                            <div class="signee">
                                                <div class="name">Tom Maniford</div>
                                                <div class="email">tom@mail.com</div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="add-signee-btn">
                                <button type="button" id="addSignees" class="d-inline-block btn btn-primary text-nowrap text-14">Add Signee</button>
                            </div>
                        </div>
                        
                        <div class="form-text text-12">Users will need to sign up before they can add comments to the document</div>

                    </div>


                    <!-- Collabs List with permissions -->
                    <div class="mb-3">
                        <label for="signees" class="form-label text-primary">Signees</label>
                        <div class="no-signees">
                            <img src="<?=site_url("public/assets/images/sad-emoji.png")?>" alt="sad-emoji" class="responsive image" />
                            <p>No signature fields added to document</p>
                        </div>
                        <div class="ds-input-btn d-flex align-items-center gap-3 mb-2">
                            <div class="card signees-list with-scrollbar existing-signees">
                                <ul class="signees-list">
                                    <li>
                                        <!-- Signee Existing -->
                                        <div class="avatar">
                                            <img src="https://i.pravatar.cc/100?img=2" alt="Bob Singh" class="rounded-circle">
                                        </div>
                                        <div class="signee-permission">
                                            <div class="isignee flex-grow-1">
                                                <div class="signee-name-email">
                                                    <div class="name">Tom Maniford</div>
                                                    <div class="email">tom@mail.com</div>
                                                </div>
                                            </div>
                                            <div class="signee-assignments">
                                                <span class="initials">4 Initials</span>
                                                <span class="signatures">1 Signature</span>
                                            </div>
                                            <div class="signee-order">
                                                <span class="up">
                                                    <i class="ds ds-arrow-up"></i>
                                                </span>
                                                <span class="down">
                                                    <i class="ds ds-arrow-down"></i>
                                                </span>
                                            </div>
                                            
                                        </div>
                                    </li>
                                    <li>
                                        <!-- Signee Existing -->
                                        <div class="avatar">
                                            <img src="https://i.pravatar.cc/100?img=2" alt="Bob Singh" class="rounded-circle">
                                        </div>
                                        <div class="signee-permission">
                                            <div class="isignee flex-grow-1">
                                                <div class="signee-name-email">
                                                    <div class="name">Tom Maniford</div>
                                                    <div class="email">tom@mail.com</div>
                                                </div>
                                                <div class="signee-you">
                                                    <span class="yourself">
                                                        You
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="signee-assignments">
                                                <span class="signatures">1 Signature</span>
                                            </div>
                                            <div class="signee-order">
                                                <span class="up">
                                                    <i class="ds ds-arrow-up"></i>
                                                </span>
                                                <span class="down">
                                                    <i class="ds ds-arrow-down"></i>
                                                </span>
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
                <div class="signee-order-document">
                    <div class="form-check">
                        <input class="form-check-input" name="specific-order" type="checkbox" value="" id="specificOrder">
                        <label class="form-check-label" for="specificOrder">
                            Set specific order for signing document
                        </label>
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
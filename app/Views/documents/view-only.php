<!-- Main content area -->
<main class="ds-main flex-grow-1 pb-4">
    <div class="container-fluid">
        <!-- Toolbar -->
        <div class="toolbar d-flex align-items-center justify-content-between px-3 py-2 sticky-top bg-white shadow-sm border-bottom mb-4">
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-400 text-24 mb-0"><?=$document->title?></h5>
                <?php
                $documentStatus = document_status($document->status);
                ?>
                <span class="badge ds-<?=$documentStatus["color"]?> bg-<?=$documentStatus["color"]?> fw-300"><?=$documentStatus["name"]?></span>
                <button class="btn btn-sm btn-square btn-secondary btn-text-secondary btn-sm star">
                    <i class="ds ds-star icon-grey"></i>
                </button>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-secondary text-16 fw-300">Version History</button>
                <?php
                if ($document->user_id == session()->get("dc_userid")) {
                ?>
                    <button class="btn btn-primary ds-btn-primary text-16 fw-300">Accept Changes</button>
                <?php
                }
                ?>
            </div>
        </div>

        <!-- Document + Comments split -->
        <div class="row">
            <!-- Document viewer -->
            <div class="col-lg-8 with-scrollbar" style="height: calc(100vh - 180px);">
                <div class="document-viewer" style="padding-bottom: 5rem;">
                    <div class="doc-content">
                        <div class="page">
                            <?=$document->content?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments -->
            <div class="col-lg-4 with-scrollbar" style="height: calc(100vh - 180px);">
                <div class="comments-panel h-100" style="height: calc(100vh - 200px); overflow-y:auto;">
                <div class="previous-comment active">
                    <div class="comment-header">
                        <div class="avatar-name">
                            <div class="c-avatar">
                                <a href="#" class="avatar" data-bs-toggle="tooltip" aria-label="Alice Johnson" data-bs-original-title="Alice Johnson">
                                    <img src="https://i.pravatar.cc/100?img=1" alt="Alice Johnson" class="rounded-circle" />
                                </a>
                            </div>
                            <div class="c-title">Monica Smith</div>
                            <div class="c-time">1m</div>
                        </div>
                        <div class="comment-actions">
                            <div class="c-actions dropdown ms-auto">
                                <a href="#" class="text-secondary c-action" id="commentMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="commentMenu">
                                    <li><a class="dropdown-item" href="#" data-action="reply">Reply</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="edit">Edit</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="delete">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="comment-content">
                        This bit doesn’t need to be here, please remove and let me know about the changes.
                    </div>
                    <!-- Replies container -->
                    <div class="replies ms-2 mt-1"></div>
                </div>
                <div class="previous-comment">
                    <div class="comment-header">
                        <div class="avatar-name">
                            <div class="c-avatar">
                                <a href="#" class="avatar" data-bs-toggle="tooltip" aria-label="Alice Johnson" data-bs-original-title="Alice Johnson">
                                    <img src="https://i.pravatar.cc/100?img=1" alt="Alice Johnson" class="rounded-circle" />
                                </a>
                            </div>
                            <div class="c-title">Monica Smith</div>
                            <div class="c-time">1m</div>
                        </div>
                        <div class="comment-actions">
                            <div class="c-actions dropdown ms-auto">
                                <a href="#" class="text-secondary c-action" id="commentMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="commentMenu">
                                    <li><a class="dropdown-item" href="#" data-action="reply">Reply</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="edit">Edit</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="delete">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="comment-content">
                        This bit doesn’t need to be here, please remove and let me know about the changes.
                    </div>
                    <!-- Replies container -->
                    <div class="replies ms-2 mt-1"></div>
                </div>
                <div class="previous-comment">
                    <div class="comment-header">
                        <div class="avatar-name">
                            <div class="c-avatar">
                                <a href="#" class="avatar" data-bs-toggle="tooltip" aria-label="Alice Johnson" data-bs-original-title="Alice Johnson">
                                    <img src="https://i.pravatar.cc/100?img=1" alt="Alice Johnson" class="rounded-circle" />
                                </a>
                            </div>
                            <div class="c-title">Monica Smith</div>
                            <div class="c-time">1m</div>
                        </div>
                        <div class="comment-actions">
                            <div class="c-actions dropdown ms-auto">
                                <a href="#" class="text-secondary c-action" id="commentMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="commentMenu">
                                    <li><a class="dropdown-item" href="#" data-action="reply">Reply</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="edit">Edit</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="delete">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="comment-content">
                        This bit doesn’t need to be here, please remove and let me know about the changes.
                    </div>
                    <!-- Replies container -->
                    <div class="replies ms-2 mt-1"></div>
                </div>
                <div class="previous-comment">
                    <div class="comment-header">
                        <div class="avatar-name">
                            <div class="c-avatar">
                                <a href="#" class="avatar" data-bs-toggle="tooltip" aria-label="Alice Johnson" data-bs-original-title="Alice Johnson">
                                    <img src="https://i.pravatar.cc/100?img=1" alt="Alice Johnson" class="rounded-circle" />
                                </a>
                            </div>
                            <div class="c-title">Monica Smith</div>
                            <div class="c-time">1m</div>
                        </div>
                        <div class="comment-actions">
                            <div class="c-actions dropdown ms-auto">
                                <a href="#" class="text-secondary c-action" id="commentMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="commentMenu">
                                    <li><a class="dropdown-item" href="#" data-action="reply">Reply</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="edit">Edit</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="delete">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="comment-content">
                        This bit doesn’t need to be here, please remove and let me know about the changes.
                    </div>
                    <!-- Replies container -->
                    <div class="replies ms-2 mt-1"></div>
                </div>
                <div class="previous-comment">
                    <div class="comment-header">
                        <div class="avatar-name">
                            <div class="c-avatar">
                                <a href="#" class="avatar" data-bs-toggle="tooltip" aria-label="Alice Johnson" data-bs-original-title="Alice Johnson">
                                    <img src="https://i.pravatar.cc/100?img=1" alt="Alice Johnson" class="rounded-circle" />
                                </a>
                            </div>
                            <div class="c-title">Monica Smith</div>
                            <div class="c-time">1m</div>
                        </div>
                        <div class="comment-actions">
                            <div class="c-actions dropdown ms-auto">
                                <a href="#" class="text-secondary c-action" id="commentMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="commentMenu">
                                    <li><a class="dropdown-item" href="#" data-action="reply">Reply</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="edit">Edit</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="delete">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="comment-content">
                        This bit doesn’t need to be here, please remove and let me know about the changes.
                    </div>
                    <!-- Replies container -->
                    <div class="replies ms-2 mt-1"></div>
                </div>
                <div class="previous-comment">
                    <div class="comment-header">
                        <div class="avatar-name">
                            <div class="c-avatar">
                                <a href="#" class="avatar" data-bs-toggle="tooltip" aria-label="Alice Johnson" data-bs-original-title="Alice Johnson">
                                    <img src="https://i.pravatar.cc/100?img=1" alt="Alice Johnson" class="rounded-circle" />
                                </a>
                            </div>
                            <div class="c-title">Monica Smith</div>
                            <div class="c-time">1m</div>
                        </div>
                        <div class="comment-actions">
                            <div class="c-actions dropdown ms-auto">
                                <a href="#" class="text-secondary c-action" id="commentMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="commentMenu">
                                    <li><a class="dropdown-item" href="#" data-action="reply">Reply</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="edit">Edit</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="delete">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="comment-content">
                        This bit doesn’t need to be here, please remove and let me know about the changes.
                    </div>
                    <!-- Replies container -->
                    <div class="replies ms-2 mt-1"></div>
                </div>
                <div class="previous-comment">
                    <div class="comment-header">
                        <div class="avatar-name">
                            <div class="c-avatar">
                                <a href="#" class="avatar" data-bs-toggle="tooltip" aria-label="Alice Johnson" data-bs-original-title="Alice Johnson">
                                    <img src="https://i.pravatar.cc/100?img=1" alt="Alice Johnson" class="rounded-circle" />
                                </a>
                            </div>
                            <div class="c-title">Monica Smith</div>
                            <div class="c-time">1m</div>
                        </div>
                        <div class="comment-actions">
                            <div class="c-actions dropdown ms-auto">
                                <a href="#" class="text-secondary c-action" id="commentMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="commentMenu">
                                    <li><a class="dropdown-item" href="#" data-action="reply">Reply</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="edit">Edit</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="delete">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="comment-content">
                        This bit doesn’t need to be here, please remove and let me know about the changes.
                    </div>
                    <!-- Replies container -->
                    <div class="replies ms-2 mt-1"></div>
                </div>
                <div class="previous-comment">
                    <div class="comment-header">
                        <div class="avatar-name">
                            <div class="c-avatar">
                                <a href="#" class="avatar" data-bs-toggle="tooltip" aria-label="Alice Johnson" data-bs-original-title="Alice Johnson">
                                    <img src="https://i.pravatar.cc/100?img=1" alt="Alice Johnson" class="rounded-circle" />
                                </a>
                            </div>
                            <div class="c-title">Monica Smith</div>
                            <div class="c-time">1m</div>
                        </div>
                        <div class="comment-actions">
                            <div class="c-actions dropdown ms-auto">
                                <a href="#" class="text-secondary c-action" id="commentMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="commentMenu">
                                    <li><a class="dropdown-item" href="#" data-action="reply">Reply</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="edit">Edit</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="delete">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="comment-content">
                        This bit doesn’t need to be here, please remove and let me know about the changes.
                    </div>
                    <!-- Replies container -->
                    <div class="replies ms-2 mt-1"></div>
                </div>
                <div class="previous-comment">
                    <div class="comment-header">
                        <div class="avatar-name">
                            <div class="c-avatar">
                                <a href="#" class="avatar" data-bs-toggle="tooltip" aria-label="Alice Johnson" data-bs-original-title="Alice Johnson">
                                    <img src="https://i.pravatar.cc/100?img=1" alt="Alice Johnson" class="rounded-circle" />
                                </a>
                            </div>
                            <div class="c-title">Monica Smith</div>
                            <div class="c-time">1m</div>
                        </div>
                        <div class="comment-actions">
                            <div class="c-actions dropdown ms-auto">
                                <a href="#" class="text-secondary c-action" id="commentMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="commentMenu">
                                    <li><a class="dropdown-item" href="#" data-action="reply">Reply</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="edit">Edit</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="delete">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="comment-content">
                        This bit doesn’t need to be here, please remove and let me know about the changes.
                    </div>
                    <!-- Replies container -->
                    <div class="replies ms-2 mt-1"></div>
                </div>    
            </div>

            <template id="comment-template">
                <div class="previous-comment">
                    <div class="comment-header">
                        <div class="avatar-name">
                            <div class="c-avatar">
                                <a href="#" class="avatar" data-bs-toggle="tooltip" aria-label="Alice Johnson" data-bs-original-title="Alice Johnson">
                                    <img src="https://i.pravatar.cc/100?img=1" alt="Alice Johnson" class="rounded-circle" />
                                </a>
                            </div>
                            <div class="c-title">Monica Smith</div>
                            <div class="c-time">1m</div>
                        </div>
                        <div class="comment-actions">
                            <div class="c-actions dropdown ms-auto">
                                <a href="#" class="text-secondary c-action" id="commentMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="commentMenu">
                                    <li><a class="dropdown-item" href="#" data-action="reply">Reply</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="edit">Edit</a></li>
                                    <li><a class="dropdown-item" href="#" data-action="delete">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="comment-content">
                        This bit doesn’t need to be here, please remove and let me know about the changes.
                    </div>
                    <!-- Replies container -->
                    <div class="replies ms-2 mt-1"></div>
                </div>
            </template>

            <template id="footer-template">
                <div class="comment-footer">
                    <div class="comment-reply d-flex align-items-start gap-2 my-3">
                        <!-- Avatar -->
                        <img src="https://i.pravatar.cc/50?img=12" alt="User Avatar" class="rounded-circle" width="40" height="40">

                        <!-- Input & Actions -->
                        <div class="flex-grow-1">
                            <textarea class="form-control reply-input" rows="1" placeholder="Add Reply"></textarea>
                            <!-- Buttons (hidden by default) -->
                            <div class="d-flex justify-content-end gap-2 mt-2 action-buttons d-none">
                                <button type="button" class="btn btn-sm btn-light" data-action="composer-cancel">Cancel</button>
                                <button type="button" class="btn btn-sm btn-primary" data-action="composer-post" disabled>Post</button>
                            </div>

                        </div>
                    </div>
                </div>
            </template>

        </div>
    </div>
</main>
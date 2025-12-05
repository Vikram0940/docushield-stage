<!-- Main Content Area -->
<main class="ds-main flex-grow-1 pb-4">
    <div class="container-fluid">
<input type="hidden" id="documentId" value="<?= $documentId ?? '' ?>">
        <div class="doc-shell">

            <?= view("templates/alert-template"); ?>

            <div class="row">

                <!-- Right Main Column -->
                <div class="col-lg-9 col-xl d-flex flex-column min-vh-100 pt-0">

                    <!-- Document Header -->
                    <div class="doc-topbar">

                        <div class="document-header">
                            <div class="container-fluid py-2 d-flex align-items-center justify-content-between ds-editor-top-header">
                                
                                <!-- Left Title Section -->
                                <div class="d-flex flex-column ds-editor-th-left">
                                    <div class="project-name text-10 text-secondary">Jefferson vs Monica</div>

                                    <div class="document-details d-flex align-items-center gap-3">
                                        <input id="doc-title" class="form-control-plaintext" value="New Document">

                                        <div class="doc-components d-flex gap-3 align-items-center">
                                            <span class="badge ds-primary bg-warning fw-300">Draft</span>

                                            <button class="btn btn-square btn-secondary btn-sm star doc-btn-sm">
                                                <i class="ds ds-star icon-grey"></i>
                                            </button>

                                            <button class="btn btn-square btn-secondary btn-sm doc-btn-sm">
                                                <i class="fa-regular fa-folder"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Action Buttons -->
                                <div class="d-flex align-items-center gap-2 ds-doc-right-buttons">
                                    <button id="save-draft" class="btn btn-secondary text-16 fw-400 text-primary">
                                        Save Draft
                                    </button>

                                    <button id="send-for-review" class="btn btn-primary ds-btn-primary text-16 fw-400" disabled>
                                        Send For Review
                                    </button>
                                </div>
                            </div>

                            <!-- Top Menu Bar -->
                            <div class="container-fluid d-flex justify-content-between align-items-center ds-menu-bar">
                                <ul class="menu-bar nav">

                                    <!-- FILE MENU -->
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle px-3" data-bs-toggle="dropdown">File</a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" data-cmd="new">New</a></li>
                                            <li><a class="dropdown-item" href="#" data-cmd="open">Open…</a></li>
                                            <li><a class="dropdown-item" href="#" data-cmd="save">Save Draft</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#" data-cmd="export-pdf">Export PDF</a></li>
                                        </ul>
                                    </li>

                                    <!-- EDIT MENU -->
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle px-3" data-bs-toggle="dropdown">Edit</a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" data-ck="undo">Undo</a></li>
                                            <li><a class="dropdown-item" href="#" data-ck="redo">Redo</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#" data-ck="find">Find</a></li>
                                        </ul>
                                    </li>

                                    <!-- VIEW MENU -->
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle px-3" data-bs-toggle="dropdown">View</a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" id="toggle-page-margins">Toggle Page Margins</a></li>
                                            <li><a class="dropdown-item" href="#" id="toggle-sidebar">Toggle Comments Sidebar</a></li>
                                        </ul>
                                    </li>

                                    <!-- HELP MENU -->
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle px-3" data-bs-toggle="dropdown">Help</a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" id="show-shortcuts">Keyboard Shortcuts</a></li>
                                        </ul>
                                    </li>
                                </ul>

                                <!-- Right Sidebar Buttons -->
                                <div class="doc-small-buttons d-flex gap-2">
                                    
                                    <button id="version-btn" class="ds-version-history btn btn-square btn-secondary btn-sm doc-btn-sm text-12 text-primary">
                                        Version History
                                    </button>

                                    <button id="comments-toggle" class="ds-doc-icomment btn btn-square btn-secondary btn-sm doc-btn-sm text-12 text-primary position-relative">
                                        <span class="position-absolute top-0 left-5 start-0 translate-middle badge rounded-50 bg-warning text-dark">5</span>
                                        <i class="ds ds-message icon-grey"></i> &nbsp; Comments
                                    </button>

                                    <button class="btn btn-square btn-secondary btn-sm doc-btn-sm share text-12 text-primary">
                                        <i class="ds ds-share icon-grey"></i> &nbsp; Share
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="editor-toolbar" class="mt-2 mb-2"></div>
                    </div>

                    <!-- Document Body -->
                    <div class="doc-body">

                        <!-- Editor -->
                        <div class="editor-wrapper with-scrollbar" id="editor-col">
                            <div class="editor-page page">
                                 <!-- <textarea id="docushield-editor" placeholder="Start typing here..." name="content" required></textarea> -->
                               <textarea id="docushield-editor" 
                                    placeholder="Start typing here..." 
                                    name="content" 
                                    required><?= $documentData["content"] ?? "" ?></textarea>

                            </div>
                        </div>

                        <!-- Comments Sidebar -->
                        <div class="comments-sidebar" id="comments-col">
                            <div id="ds-comments-sidebar" class="h-100 with-scrollbar"></div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- Hidden File Input -->
        <input type="file" id="open-file" class="d-none" accept=".html,.txt,.md">

        <?php echo form_close(); ?>

    </div>
</main>


<?php
echo view("documents/document-modal");
echo view("documents/send-for-review-modal");
?>


<!-- Main content area -->
<main class="ds-main flex-grow-1 pb-4">
    <div class="container-fluid">
        <?php echo form_open(site_url("dpanel/document/submit/".$document->id), "class='NewProject needs-validation' id='frmdocument' name='frmdocument' novalidate"); ?>
        <div class="doc-shell">
            <?php
            echo view("templates/alert-template");
            ?>
            <!-- Title row + actions -->
            <div class="doc-topbar">
                <div class="document-header">
                    <div class="container-fluid py-2 d-flex align-items-center justify-content-between ds-editor-top-header">
                        <div class="d-flex flex-column ds-editor-th-left">                            
                            <div class="project-name text-10 text-secondary"><?=$document->project_name?></div>
                            <div class="document-details d-flex align-items-center gap-3">
                                <input id="doc-title" class="form-control-plaintext" value="<?=$document->title?>" name="name" required>
                                <div class="doc-components d-flex gap-3 align-items-center">
                                    <?php
                                    $documentStatus = document_status($document->status);
                                    ?>
                                    <span class="badge ds-<?=$documentStatus["color"]?> bg-<?=$documentStatus["color"]?> fw-300"><?=$documentStatus["name"]?></span>
                                    <button class="btn btn-square btn-secondary btn-sm star doc-btn-sm">
                                        <i class="ds ds-star icon-grey"></i>
                                    </button>
                                    <button class="btn btn-square btn-secondary btn-sm doc-btn-sm">
                                        <i class="fa-regular fa-folder"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php
                        if ($document->user_id == session()->get("dc_userid")) {
                        ?>
                            <div class="d-flex align-items-center gap-2 ds-doc-right-buttons">
                                <?php
                                if ($document->status == 4) {
                                    if ($document->user_id == session()->get("dc_userid")) {
                                ?>
                                        <button type="submit" id="btnsubmit" class="btn btn-secondary text-16 fw-400 text-primary">Save Draft</button>
                                        <button type="button" id="send-for-review" class="btn btn-primary ds-btn-primary text-16 fw-300" data-geturl="<?=site_url("dpanel/users/get-li-list")?>" data-url="<?=site_url("dpanel/document/send-for-review/".$document->id)?>" data-event="review">Send For Review</button>
                                <?php
                                    }
                                    else {
                                    ?>
                                        <button type="button" class="btn btn-secondary text-16 fw-300" onclick="window.location.href='<?=site_url("dpanel/document/version-history/".$document->id)?>'">Version History</button>
                                        <button class="btn btn-primary ds-btn-primary text-16 fw-300">Accept Changes</button>
                                    <?php
                                    }
                                }                            
                                else if ($document->status == 5) {
                                ?>
                                    <button type="button" id="return-to-draft" class="btn btn-secondary text-16 fw-400 text-primary ds-return-to-draft">Return To Draft</button>
                                    <button type="button" id="accept-sign" class="btn btn-primary ds-btn-primary text-16 fw-300 ds-accept-sign">Accept & Sign</button>
                                    <!-- <button class="btn btn-primary ds-btn-primary text-16 fw-300">Accept Changes</button> -->
                                <?php
                                }
                                ?>
                            </div>
                        <?php
                        }
                        ?>
                    </div>

                    <!-- Menubar -->
                    <div class="container-fluid d-flex justify-content-between align-items-center ds-menu-bar">
                        <ul class="menu-bar nav">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3" data-bs-toggle="dropdown" href="#">File</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" data-cmd="new">New</a></li>
                                    <li><a class="dropdown-item" href="#" data-cmd="open">Open…</a></li>
                                    <li><a class="dropdown-item" href="#" data-cmd="save">Save Draft</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" data-cmd="export-pdf">Export PDF</a></li>
                                </ul>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3" data-bs-toggle="dropdown" href="#">Edit</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" data-ck="undo">Undo</a></li>
                                    <li><a class="dropdown-item" href="#" data-ck="redo">Redo</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" data-ck="find">Find</a></li>
                                </ul>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3" data-bs-toggle="dropdown" href="#">View</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" id="toggle-page-margins">Toggle Page Margins</a></li>
                                    <li><a class="dropdown-item" href="#" id="toggle-sidebar">Toggle Comments Sidebar</a></li>
                                </ul>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3" data-bs-toggle="dropdown" href="#">Help</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" id="show-shortcuts">Keyboard Shortcuts</a></li>
                                </ul>
                            </li>
                        </ul>

                        <div class="doc-small-buttons d-flex gap-2">
                            <?php
                            if ($document->status == 4 && $document->user_id == session()->get("dc_userid")) {
                            ?>
                                <button id="version-btn1" class="btn btn-square btn-secondary btn-sm doc-btn-sm text-12 text-primary" type="button" onclick="window.location.href='<?=site_url("dpanel/document/version-history/".$document->id)?>'">
                                    Version History 
                                </button>
                            <?php
                            }
                            if ($document->status != 4) {
                            ?>
                                <button id="comments-toggle" type="button" class="ds-doc-icomment btn btn-square btn-secondary btn-sm doc-btn-sm text-12 text-primary position-relative">
                                    <span class="position-absolute top-0 left-5 start-0 translate-middle badge rounded rounded-50 bg-warning text-dark">5</span>
                                    <i class="ds ds-message icon-grey"></i> &nbsp; Comments
                                </button>
                                <?php
                                if ($document->user_id == session()->get("dc_userid")) {
                                ?>
                                    <button class="btn btn-square btn-secondary btn-sm doc-btn-sm share text-12 text-primary" type="button" id="btnshare" data-geturl="<?=site_url("dpanel/document/get-json-detail/".$document->id)?>" data-url="<?=site_url("dpanel/document/share/".$document->id)?>" data-event="share">
                                        <i class="ds ds-share icon-grey"></i> &nbsp; Share
                                    </button>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div id="editor-toolbar" class="mt-2 mb-2"></div>
            </div>

            <!-- Body -->
            <div class="doc-body">
                <!-- Editor -->
                <div class="editor-wrapper with-scrollbar" id="editor-col">
                    <div class="editor-page page">
                        <textarea id="docushield-editor" placeholder="Start typing here..." name="content" required><?=$document->content?></textarea>
                    </div>
                </div>
                <!-- Comments Sidebar (target for the comments plugin) -->
                <div class="comments-sidebar" id="comments-col">
                    <div id="ds-comments-sidebar" class="h-100 with-scrollbar"></div>
                </div>
            </div>
        </div>

        <!-- Hidden file input for File → Open -->
        <input type="file" id="open-file" class="d-none" accept=".html,.txt,.md">
        <?php echo form_close(); ?>
    </div>
</main>
<?php
echo view("documents/document-modal", ["documentId" => $document->id]);
echo view("documents/return-to-draft-confirmation", ["documentId" => $document->id]);
echo view("documents/accept-and-sign-modal", ["documentId" => $document->id]);
echo view("documents/send-for-review-modal");
?>
<!-- Main content area -->
<main class="ds-main flex-grow-1 pb-4">
    <div class="container-fluid">
         <!-- Main content -->
        <div class="main">
            <!-- Topbar -->
            <div class="topbar">
                <div class="container-fluid py-3 d-flex justify-content-between align-items-center">
                    <div class="back-wrap">
                        <a id="backLink" class="btn btn-sm btn-ghost border" href="<?=site_url("dpanel/document/detail/".$document->id)?>">← Back to Document</a>
                        <div class="title h5 mb-0">
                            <input id="docTitle" class="form-control-plaintext text-light-emphasis" value="—" />
                            <span class="badge rounded-pill text-bg-secondary ms-1" id="revCount">0</span>
                        </div>
                    </div>

                    <div class="controls">
                        <div class="d-flex align-items-center gap-2">
                            <label class="text-secondary small">Left:</label>
                            <?php
                            $revisions = (new \App\Models\Revisions)->where("document_id", $document->id)->orderBy("version")->findAll();
                            $revCount = 1;
                            $content = "";
                            ?>
                            <select class="form-select form-select-sm" id="leftSelect">
                                <option value="">Choose version</option>
                                <?php
                                if (!empty($revisions)) {
                                    foreach ($revisions as $rev) {
                                        $selected = "";
                                        if ($revCount == sizeof($revisions)) {
                                            $selected = "selected='selected'";
                                            $content = $rev->content;
                                        }
                                    ?>
                                        <option value="<?=$rev->id?>" <?=$selected?>><?=date("m/d/Y h:i A", strtotime($rev->created_date)). " - V".$rev->version?></option>
                                    <?php
                                        $revCount += 1;
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="text-secondary small">Right:</label>
                            <select class="form-select form-select-sm" id="rightSelect"></select>
                        </div>
                        <button class="btn btn-sm btn-outline-success" id="restoreBtn" disabled>Restore Right → Document</button>
                    </div>
                </div>
            </div>

            <!-- Workspace: two panels -->
            <div class="workspace">
                <section class="panel">
                    <div class="panel-head d-flex justify-content-between align-items-center">
                    <strong id="leftLabel">Left</strong>
                    <span class="text-muted small" id="leftMeta">—</span>
                    </div>
                    <div class="panel-body" id="diffContainer">
                        <?=$content?>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel-head d-flex justify-content-between align-items-center">
                    <strong id="rightLabel">Right (Current)</strong>
                    <span class="text-muted small" id="rightMeta">—</span>
                    </div>
                    <div class="panel-body" id="currentContent">
                        <?=$document->content?>                            
                    </div>
                </section>
                <div id="diff-container"></div>
            </div>
        </div>
    </div>
</main>
<aside id="dsSidebar" class="col-lg-3 col-xl-3 col-xxl-2 d-lg-block offcanvas-lg offcanvas-start ds-sidebar py-0 border-end" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="dsSidebarLabel">
    <!-- Offcanvas header for mobile -->
    <div class="offcanvas-header d-lg-none">
        <h5 class="offcanvas-title fw-semibold" id="dsSidebarLabel">DocuShield</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#dsSidebar" aria-label="Close"></button>
    </div>

    <!-- Sidebar body -->
    
    <div class="offcanvas-body d-flex flex-column flex-grow-1 overflow-y-auto py-lg-4 pb-lg-3">
        <!-- Brand (desktop only) -->
        <div class="d-none d-lg-block text-center mb-4">
            <img src="<?=site_url("public/assets/ds-logo-vertical-1.svg")?>" alt="DocuShield"/>
        </div>

        <!-- Primary actions -->
        <!-- Primary actions -->
        <?php
        if (session()->get("dc_roles") == "ROLE_ADMIN") {
            $uri = service('uri');
            $segments = $uri->getSegments();
            $addURL = site_url("dpanel/document/add-new");
            if (in_array("files", $segments) || in_array("storage", $segments)) {
                $param = [];
                if (isset($_GET["parent"])) {
                    array_push($param, "parent=".$_GET["parent"]);
                }
                if (isset($_GET["parent_type"])) {
                    array_push($param, "parent_type=".$_GET["parent_type"]);
                }
                $param = empty($param) ? "" : "?".implode("&", $param);
                $addURL = site_url("dpanel/document/add-new".$param);
            }
            else if (in_array("folder", $segments) && in_array("project", $segments)) {
                $lastSegment = $uri->getSegment($uri->getTotalSegments());
                if (is_numeric($lastSegment)) {
                    $addURL = site_url("dpanel/document/add-new?parent=".$lastSegment."&parent_type=2");
                }
            }
            else if (in_array("detail", $segments) && in_array("project", $segments)) {
                $lastSegment = $uri->getSegment($uri->getTotalSegments());
                if (is_numeric($lastSegment)) {
                    $projectInfo = (new \App\Models\Project)->where("case_id", $lastSegment)->first();
                    $addURL = site_url("dpanel/document/add-new?parent=".$projectInfo->id."&parent_type=1");
                }
            }
        ?>
            <div class="d-grid gap-3 mt-4 ds-new-upload">
                <button class="btn btn-primary ds-btn-primary text-16 fw-300" onclick="window.location.href='<?=$addURL?>'">
                    <i class="ds ds-new-document"></i> New Document
                </button>
                <button class="btn btn-secondary btn-text-secondary text-16 fw-300" id="btnupload">
                    <i class="ds ds-upload"></i> Upload File
                </button>
            </div>

            <div class="align-items-center mt-4">
                <hr class="flex-grow-1 text-muted">
            </div>
        <?php
        }
        ?>

        <!-- Navigation -->
        <nav class="mt-4 ds-nav" aria-label="Sidebar navigation">
            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link <?php echo in_array("dashboard", $segments) ? "active" : ""; ?>" href="<?=site_url("dpanel/dashboard")?>"><i class="fa-regular fa-house"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (in_array("projects", $segments) || (in_array("project", $segments) && in_array("detail", $segments))) ? "active" : ""; ?>" href="<?=site_url("dpanel/projects")?>"><i class="fa-regular fa-folder"></i> Projects</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo in_array("files", $segments) ? "active" : ""; ?>" href="<?=site_url("dpanel/files")?>"><i class="fa-regular fa-file"></i> All Files</a>
                </li>
                <li class="nav-item">
                <a class="nav-link <?php echo in_array("favorites", $segments) ? "active" : ""; ?>" href="<?=site_url("dpanel/favorites")?>"><i class="fa-regular fa-star"></i> Favorites</a>
                </li>
            </ul>

            <div class="align-items-center mt-4">
                <hr class="flex-grow-1 text-muted">
            </div>

            <div class="mt-4">
                <div class="small text-uppercase text-muted mb-2">
                    <a class="nav-link" href="<?=site_url("dpanel/storage")?>"><i class="fa-regular fa-cloud"></i> Storage</a>
                </div>
                <div class="progress ds-progress mb-1" style="height: 4px;">
                    <div class="progress-bar bg-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Documents" role="progressbar" style="width: 25%;" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                    <div class="progress-bar bg-utility-positive" data-bs-toggle="tooltip" data-bs-placement="top" title="Images" role="progressbar" style="width: 15%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                    <div class="progress-bar bg-utility-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Other" role="progressbar" style="width: 11%;" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                
                <div class="small text-secondary fw-300">
                    <?php
                    $files = (new \App\Models\Storage)->selectSum("file_size", "size")->where(["user_id" => session()->get("dc_userid")])->whereIn("storage_type", [2,3])->first();
                    $fileSize = empty($files) ? 0 : formatBytes($files->size);
                    ?>
                    <?=$fileSize?> of 512mb used
                </div>
            </div>
        </nav>

        <div class="mt-auto pt-0 d-flex align-items-center gap-2 ds-footer-nav ds-nav">
            <ul class="nav flex-column gap-1 mt-4 w-100">
                <li class="nav-item">
                    <a class="nav-link" href="<?=site_url("dpanel/user/team-members")?>">
                        <svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M6 1.66671C4.89543 1.66671 4 2.56214 4 3.66671C4 4.77128 4.89543 5.66671 6 5.66671C7.10457 5.66671 8 4.77128 8 3.66671C8 2.56214 7.10457 1.66671 6 1.66671ZM2.66667 3.66671C2.66667 1.82576 4.15905 0.333374 6 0.333374C7.84095 0.333374 9.33333 1.82576 9.33333 3.66671C9.33333 5.50766 7.84095 7.00004 6 7.00004C4.15905 7.00004 2.66667 5.50766 2.66667 3.66671ZM10.0208 0.921348C10.1122 0.564664 10.4753 0.349549 10.832 0.440874C11.549 0.624458 12.1846 1.04146 12.6384 1.62613C13.0922 2.21081 13.3386 2.9299 13.3386 3.67004C13.3386 4.41018 13.0922 5.12927 12.6384 5.71395C12.1846 6.29862 11.549 6.71562 10.832 6.89921C10.4753 6.99053 10.1122 6.77542 10.0208 6.41873C9.92951 6.06205 10.1446 5.69887 10.5013 5.60754C10.9315 5.49739 11.3128 5.24719 11.5851 4.89639C11.8574 4.54558 12.0052 4.11413 12.0052 3.67004C12.0052 3.22596 11.8574 2.7945 11.5851 2.4437C11.3128 2.09289 10.9315 1.84269 10.5013 1.73254C10.1446 1.64122 9.92951 1.27803 10.0208 0.921348ZM0.976311 9.30969C1.60143 8.68456 2.44928 8.33337 3.33333 8.33337H8.66667C9.55072 8.33337 10.3986 8.68456 11.0237 9.30969C11.6488 9.93481 12 10.7827 12 11.6667V13C12 13.3682 11.7015 13.6667 11.3333 13.6667C10.9651 13.6667 10.6667 13.3682 10.6667 13V11.6667C10.6667 11.1363 10.456 10.6276 10.0809 10.2525C9.70581 9.87742 9.1971 9.66671 8.66667 9.66671H3.33333C2.8029 9.66671 2.29419 9.87742 1.91912 10.2525C1.54405 10.6276 1.33333 11.1363 1.33333 11.6667V13C1.33333 13.3682 1.03486 13.6667 0.666667 13.6667C0.298477 13.6667 0 13.3682 0 13V11.6667C0 10.7827 0.35119 9.93481 0.976311 9.30969ZM12.6878 8.92004C12.7799 8.56354 13.1435 8.34916 13.5 8.44121C14.2151 8.62585 14.8486 9.04276 15.3011 9.62647C15.7536 10.2102 15.9994 10.9277 16 11.6662V13C16 13.3682 15.7015 13.6667 15.3333 13.6667C14.9651 13.6667 14.6667 13.3682 14.6667 13V11.6672C14.6663 11.2241 14.5188 10.7936 14.2474 10.4434C13.9759 10.0931 13.5957 9.84299 13.1667 9.7322C12.8102 9.64016 12.5958 9.27654 12.6878 8.92004Z" fill="#050507"/>
                        </svg>
                        Team Members
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="bi bi-gear me-2" aria-hidden="true"></i>Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?=site_url("dpanel/logout")?>"><i class="fa fa-right-from-bracket"></i>Logout</a>
                </li>
            </ul>
        </div>

        <div class="align-items-center mt-0">
            <hr class="flex-grow-1 text-muted">
        </div>
        <!-- Account footer -->
        <div class="d-flex align-items-center gap-1 border border-primary rounded-1 p-2">
            <img src="<?=user_avatar(session()->get("dc_avatar"))?>" alt="UA" class="img-thumbnail ds-avatar p-0"/>
            <div class="ms-2">
                <div class="text-primary small"><?=session()->get("dc_name")?></div>
                <div class="text-secondary small">Partner</div>
            </div>
        </div>
    </div>
</aside>
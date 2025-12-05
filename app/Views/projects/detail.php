<style>
    td, .item-info .label a {
        white-space: normal;   /* allow wrapping */
        word-break: break-all; /* break long words (good for hashes) */
        overflow-wrap: anywhere; /* modern way to wrap anywhere */
        max-width: 300px;      /* set a width limit */
        line-height: 1.4;      /* add spacing between lines */
    }
</style>
<!-- Main content area -->
<main class="ds-main flex-grow-1 pb-4">
    <div class="container-fluid">
        <div class="inner-subheader">
            <ul class="breadcrumbs">
                <li>Projects</li>
                <li class="active"><?=$project_info->name?></li>
            </ul>
            <div class="content-right">
                <?php
                $favoriteInfo = (new \App\Models\Favorites)->where([
                    "source_id"   => $project_info->id,
                    "source_type" => 1,
                ])->first();
                ?>
                <input type="hidden" name="project_id" id="parent_id" value="<?=$project_info->id?>">
                <input type="hidden" name="project_source" id="parent_type" value="1">
                <button class="btn btn-sm btn-square btn-secondary btn-text-secondary star markfavorite <?php echo !empty($favoriteInfo) ? "starred" : ""; ?>" data-id="<?=$project_info->id?>" data-type="1">
                    <i class="ds ds-star"></i>
                </button>
                <button class="btn btn-sm btn-square btn-secondary btn-text-secondary">
                    <i class="ds ds-dot-vertical"></i>
                </button>
            </div>
        </div>
        <?php
        if (session()->get("dc_roles") === "ROLE_ADMIN" && $project_info->user_id == session()->get("dc_userid")) {
        ?>
            <h4 class="fw-400 py-3 text-secondary">Folders</h4>
            <div class="row g-3">
                <div class="recent-folders ds-auto-scroll">
                    <div class="card ds-card p-3 col-10 col-md-5 col-lg-3 folder new">
                        <a href="javascript:;" style="text-decoration: none;" data-url="<?=site_url("dpanel/project/create-folder/".$project_info->id)?>" id="btncreatefolder">
                            <div class="d-flex justify-content-between mb-5">
                                <div>
                                    <div class="ds-card-title text-brand-blue"><i class="ds ds-plus-folder"></i> Create New Folder</div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php
                    echo view("folders/folders-list", ["project_id" => $project_info->id]);
                    ?>
                </div>
            </div>
        <?php
        }
        ?>
        <div class="d-flex align-items-center my-2">
            <h4 class="fw-400 py-3">Files</h4>
        </div>

        <!-- Mobile Filters -->
        <!-- Custom Filter Panel -->
        <?php
        $my_users = (new \App\Models\Users)->where(["created_by" => session()->get("dc_userid"), "status" => 1])->orderBy("name")->findAll();
        if ($agent->isMobile()) {
        ?>
            <div id="filterPanel" class="filter-panel shadow rounded-end">
                <div class="p-3 position-relative">
                    <!-- Close Button -->
                    <button type="button" id="filterClose" class="btn-close position-absolute top-0 end-0 m-2" aria-label="Close"></button>
                    
                    <h6 class="fw-bold text-primary mb-3 mt-1">
                        <i class="bi bi-sliders me-2"></i>Filters
                    </h6>
                
                    <div class="mb-3 field-wrap">
                        <label class="form-label fw-semibold small text-muted">Status</label>
                        <select class="form-select form-select-sm shadow-sm filterselect" id="cmbstatus">
                            <option value="" selected>All</option>
                            <option value="1">Public</option>
                            <option value="2">Private</option>
                            <option value="0">Draft</option>
                        </select>
                    </div>

                    <div class="mb-3 field-wrap">
                        <label class="form-label fw-semibold small text-muted">Type</label>
                        <select class="form-select form-select-sm shadow-sm filterselect" id="cmbtype">
                            <option value="" selected>All</option>
                            <option value="1">Document</option>
                            <option value="2">File</option>
                            <option value="3">Image</option>
                        </select>
                    </div>
                    <div class="mb-3 field-wrap">
                        <label class="form-label fw-semibold small text-muted">Last Modified</label>
                        <select class="form-select form-select-sm shadow-sm filterselect" id="cmblastmodified">
                            <option value="" selected>All</option>
                            <option value="1">Today</option>
                            <option value="2">Yesterday</option>
                            <option value="3">Within a week</option>
                        </select>
                    </div>

                    <div class="mb-3 field-wrap">
                        <label class="form-label fw-semibold small text-muted">People</label>
                        <select class="form-select form-select-sm shadow-sm filterselect" id="cmbpeople">
                            <option value="" selected>All</option>
                            <?php
                            if (!empty($my_users)) {
                                foreach ($my_users as $user) {
                            ?>
                                    <option value="<?=$user->id?>"><?=$user->name?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3 field-wrap">
                        <label class="form-label fw-semibold small text-muted">Sort By</label>
                        <select class="form-select form-select-sm shadow-sm" id="cmbpopularity">
                            <option value="desc" selected>Latest</option>
                            <option value="1">Popularity</option>
                            <option value="asc">Older</option>
                            <option value="desc">Newer</option>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100 rounded-pill shadow-sm">Apply Filters</button>
                </div>
            </div>
        <?php
        }
        ?>
        <!-- Custom Filter Panel -->

        <!-- Overlay -->
        <div id="filterOverlay" class="filter-overlay"></div>
        <div class="card">
            <div class="card-body">                            
                <div class="d-flex justify-content-between mb-4 filters-wrapper">
                    <div class="mobile-filters d-block d-lg-none">
                        <button id="filterToggle" class="btn btn-primary z-3 rounded-end shadow" style="padding: 10px 12px;">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                    <?php
                    if (!$agent->isMobile()) {
                    ?>
                        <div class="ds-left-filters justify-content-start d-none d-lg-flex gap-2 no-label">
                            <div class="mb-3 field-wrap">
                                <label class="form-label fw-semibold small text-muted">Status</label>
                                <select class="form-select form-select-sm shadow-sm filterselect" id="cmbstatus">
                                    <option selected>All</option>
                                    <option value="1">Public</option>
                                    <option value="2">Private</option>
                                    <option value="0">Draft</option>
                                </select>
                            </div>

                            <div class="mb-3 field-wrap">
                                <label class="form-label fw-semibold small text-muted">Type</label>
                                <select class="form-select form-select-sm shadow-sm filterselect" id="cmbtype">
                                    <option value="" selected>All</option>
                                    <option value="1">Document</option>
                                    <option value="2">File</option>
                                    <option value="3">Image</option>
                                </select>
                            </div>
                            <div class="mb-3 field-wrap">
                                <label class="form-label fw-semibold small text-muted">Last Modified</label>
                                <select class="form-select form-select-sm shadow-sm filterselect" id="cmblastmodified">
                                    <option value="" selected>All</option>
                                    <option value="1">Today</option>
                                    <option value="2">Yesterday</option>
                                    <option value="3">Within a week</option>
                                </select>
                            </div>

                            <div class="mb-3 field-wrap">
                                <label class="form-label fw-semibold small text-muted">People</label>
                                <select class="form-select form-select-sm shadow-sm filterselect" id="cmbpeople">
                                    <option value="" selected>All</option>
                                    <?php
                                    if (!empty($my_users)) {
                                        foreach ($my_users as $user) {
                                    ?>
                                            <option value="<?=$user->id?>"><?=$user->name?></option>
                                    <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3 field-wrap">
                                <label class="form-label fw-semibold small text-muted">Sort By</label>
                                <select class="form-select form-select-sm shadow-sm" id="cmbpopularity">
                                    <option value="desc" selected>Latest</option>
                                    <option value="1">Popularity</option>
                                    <option value="asc">Older</option>
                                    <option value="desc">Newer</option>
                                </select>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                    <div class="ds-right-filters justify-content-end  d-flex gap-2">
                        <div class="view-_grid p-1">
                            <ul class="ds-icon-menu nav nav-tabs">
                                <li id="deleteRowButton" class="d-none">
                                    <a href="javascript:;" class="btn btn-danger btn-text-danger btn-top-delete me-2" data-url="files/bulk-delete" id="btnBulkDelete" data-message="Are you sure? you want to remove selected files" data-loading="Please wait..">Delete</a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="button active" data-bs-toggle="tab" data-bs-target="#list-view">
                                        <span class="list-view text-20 fw-400">
                                            <i class="ds ds-list-view icon-grey"></i>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="button" data-bs-toggle="tab" data-bs-target="#grid-view">
                                        <span class="grid-view text-20 fw-400">
                                            <i class="ds ds-grid-view icon-grey"></i>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mb-0">
                    <div class="tab-content bg-white">
                        <div id="list-view" class="tab-pane fade show active">
                            <div class="table-responsive table-scroll">
                                <table id="tbldata" class="table align-middle mb-0 table-striped ds-table table-borderless table-project-files w-100">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>
                                                <label class="docushield-checkbox">
                                                    <input type="checkbox" name="rememberme" class="chkall" />
                                                    <span class="checkmark"></span>
                                                </label>
                                            </th>
                                            <th></th>
                                            <th>
                                                <div class="title-with-sort">
                                                    <span>File Name</span>
                                                    <div class="dropdown d-inline">
                                                        <a class="text-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="ds ds-sort icon-grey"></i>
                                                        </a>
                                                        <ul class="dropdown-menu ds-dropdown">
                                                            <li>
                                                                <a class="dropdown-item" href="#" data-col="1" data-order="asc">
                                                                    <i class="ds ds-sort-az-asc icon-grey"></i> 
                                                                    Sort A-Z
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="#" data-col="1" data-order="desc">
                                                                    <i class="ds ds-sort-az-dsc icon-grey"></i> 
                                                                    Sort A-Z
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="title-with-sort">
                                                    <span>File Type</span>
                                                    <div class="dropdown d-inline">
                                                        <a class="text-secondary" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                            <i class="ds ds-sort icon-grey"></i>
                                                        </a>
                                                        <ul class="dropdown-menu sort-dropdown ds-dropdown">
                                                            <li>
                                                                <label class="docushield-checkbox">
                                                                    <input type="checkbox" name="documents" />
                                                                    Documents
                                                                </label>
                                                            </li>
                                                            <li>
                                                                <label class="docushield-checkbox">
                                                                    <input type="checkbox" name="files" />
                                                                    Files
                                                                </label>
                                                            </li>
                                                            <li>
                                                                <label class="docushield-checkbox">
                                                                    <input type="checkbox" name="images" />
                                                                    Images
                                                                </label>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="title-with-sort">
                                                    <span>Size</span>
                                                    <div class="dropdown d-inline">
                                                        <a class="text-secondary" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                            <i class="ds ds-sort icon-grey"></i>
                                                        </a>
                                                        <ul class="dropdown-menu ds-dropdown">
                                                            <li>
                                                                <a class="dropdown-item" href="#" data-col="1" data-order="asc">
                                                                    <i class="ds ds-sort-az-asc icon-grey"></i> 
                                                                    Sort A-Z
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="#" data-col="1" data-order="desc">
                                                                    <i class="ds ds-sort-az-dsc icon-grey"></i> 
                                                                    Sort A-Z
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>Activity/File Size</th>
                                            <th>
                                                <div class="title-with-sort">
                                                    <span>Last Updated</span>
                                                    <div class="dropdown d-inline">
                                                        <a class="text-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="ds ds-sort icon-grey"></i>
                                                        </a>
                                                        <ul class="dropdown-menu ds-dropdown">
                                                            <li>
                                                                <a class="dropdown-item" href="#" data-col="1" data-order="desc">
                                                                    <i class="ds ds-arrow-up icon-grey"></i> 
                                                                    Oldest - Newest
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="#" data-col="1" data-order="desc">
                                                                    <i class="ds ds-arrow-down icon-grey"></i> 
                                                                    Newest - Oldest
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="title-with-sort">
                                                    <span>Date Added</span>
                                                    <div class="dropdown d-inline">
                                                        <a class="text-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="ds ds-sort icon-grey"></i>
                                                        </a>
                                                        <ul class="dropdown-menu ds-dropdown">
                                                            <li>
                                                                <a class="dropdown-item" href="#" data-col="1" data-order="desc">
                                                                    <i class="ds ds-arrow-up icon-grey"></i> 
                                                                    Oldest - Newest
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="#" data-col="1" data-order="desc">
                                                                    <i class="ds ds-arrow-down icon-grey"></i> 
                                                                    Newest - Oldest
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>Status</th>
                                            <th>
                                                Teams
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="listBody"></tbody>
                                </table>
                            </div>
                            <div class="d-flex align-items-center justify-content-between p-3 px-0 dash-table-footer">
                                <div class="small text-primary">Rows per page:
                                    <select class="form-select form-select-sm d-inline-block w-auto border-0">
                                        <option>10</option>
                                        <option>25</option>
                                        <option>50</option>
                                    </select>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="text-muted small">Page 1 of 10</div>
                                    <div class="ds-btn-group">
                                        <button class="ds-button btn border-primary btn-sm" aria-label="Prev"><i class="ds ds-arrow-left text-primary"></i></button>
                                        <button class="ds-button btn border-primary btn-sm" aria-label="Next"><i class="ds ds-arrow-right text-primary"></i></button>
                                    </div>
                                </div>
                            </div>
                            <template id="filesRowTemplate">
                                <tr>
                                    <td>
                                        <a href="javascript:;" class="star"><i class="ds ds-star icon-grey"></i></a>
                                    </td>
                                    <td><a href="project-details.html" class="no-underline text-primary item-name">Lilo vs. Stitch</a></td>
                                    <td class="text-muted small item-type">PDF</td>
                                    <td class="text-muted small item-size">3MB</td>
                                    <td class="item-activity"></td>
                                    <td class="text-muted small item-updated">1 week ago</td>
                                    <td class="text-muted small item-added">January 15, 2025</td>
                                    <td>
                                        <div class="avatar-actions">
                                            <div class="item-teams"></div>
                                            <!-- Dropdown Menu -->
                                            <div class="dropdown">
                                                <button class="btn btn-link text-muted p-0 ms-1" aria-label="More" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
                                                <ul class="dropdown-menu ds-dropdown">
                                                    <li>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editproject">
                                                            <i class="ds ds-edit ds-lg icon-grey"></i> 
                                                            Edit project
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#shareproject">
                                                            <i class="ds ds-share ds-lg icon-grey"></i> 
                                                            Share project
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#archiveproject">
                                                            <i class="ds ds-archive ds-lg icon-grey"></i> 
                                                            Archive project
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </div>
                        <div id="grid-view" class="tab-pane fade pt-3">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php
echo view("folders/create-folder-modal");
?>
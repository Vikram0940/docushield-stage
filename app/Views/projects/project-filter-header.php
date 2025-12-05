<?php
$my_users = (new \App\Models\Users)->where(["created_by" => session()->get("dc_userid"), "status" => 1])->orderBy("name")->findAll();
if ($agent->isMobile()) {
?>
    <!-- Mobile Filters -->
    <!-- Custom Filter Panel -->
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
                    <option value="1">Private</option>
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
                    <option value="" selected>Latest</option>
                    <option value="1">Popularity</option>
                    <option value="2">Older</option>
                    <option value="3">Newer</option>
                </select>
            </div>
            <button class="btn btn-primary w-100 rounded-pill shadow-sm">Apply Filters</button>
        </div>
    </div>
<?php
}
?>

<!-- Overlay -->
<div id="filterOverlay" class="filter-overlay"></div>
<?php
if (session()->get("dc_roles") == "ROLE_ADMIN") {
    $sWhere = "user_id=".session()->get("dc_userid");
}
else {
    $sWhere = "(tblprojects.user_id = ".session()->get("dc_userid")." OR tblprojects.id IN (SELECT tblinvitees.source_id FROM tblinvitees WHERE tblinvitees.user_id = ".session()->get("dc_userid")." AND tblinvitees.source_type=1))";
}
$total = (new \App\Models\Project)->where($sWhere)->countAllResults();
?>
<div class="card <?php echo ($total==0) ? "d-none" : ""; ?>" id="projects_exist">
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
                            <option value="" selected>All</option>
                            <option value="2">Public</option>
                            <option value="3">Private</option>
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
                            <a href="javascript:;" class="btn btn-danger btn-text-danger btn-top-delete" data-url="projects/bulk-delete" id="btnBulkDelete" data-message="Are you sure? you want to remove selected projects" data-loading="Please wait..">Delete</a>
                        </li>
                        <li>
                            <a href="javascript:;" class="button" data-bs-toggle="tab" data-bs-target="#grid-view">
                                <span class="grid-view text-20 fw-400">
                                    <i class="ds ds-grid-view icon-grey"></i>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:;" class="button active" data-bs-toggle="tab" data-bs-target="#list-view">
                                <span class="list-view text-20 fw-400">
                                    <i class="ds ds-list-view icon-grey"></i>
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <ul class="nav nav-tabs border-0 dash-project-tabs">
                <li class="nav-item">
                    <button class="nav-link ds-tab active" data-bs-toggle="tab" data-bs-target="#tab-active" data-type="active">Active Projects</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link ds-tab" data-bs-toggle="tab" data-bs-target="#tab-archived" data-type="archived">Archived Projects</button>
                </li>
            </ul>

            <div class="tab-content bg-white">
                <div class="tab-pane fade show active" id="tab-active">
                </div>

                <div class="tab-pane fade" id="tab-archived">
                </div>

                <div id="list-view" class="tab-pane fade show active">
                    <div class="table-responsive table-scroll">
                        <table id="tblprojects" class="table align-middle mb-0 table-striped ds-table table-borderless table-dash-projects w-100">
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
                                            <span>Project Name</span>
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
                                                            Sort Z-A
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </th>
                                    <th>
                                        <span>Description</span>
                                    </th>
                                    <th>
                                        <div class="title-with-sort">
                                            <span>Contents</span>
                                            <div class="dropdown d-inline">
                                                <a class="text-secondary" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                    <i class="ds ds-sort icon-grey"></i>
                                                </a>
                                                <ul class="dropdown-menu sort-dropdown ds-dropdown">
                                                    <li>
                                                        <label class="docushield-checkbox">
                                                            <input type="checkbox" name="documents" id="chkdocuments" class="checkbox-item" data-col="8" data-order="asc" />
                                                            Documents
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="docushield-checkbox">
                                                            <input type="checkbox" name="files" id="chkfiles" class="checkbox-item" data-col="9" data-order="asc" />
                                                            Files
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="docushield-checkbox">
                                                            <input type="checkbox" name="images" id="chkimages" class="checkbox-item" data-col="10" data-order="asc" />
                                                            Images
                                                        </label>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="title-with-sort">
                                            <span>Activity</span>
                                            <div class="dropdown d-inline">
                                                <a class="text-secondary" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                    <i class="ds ds-sort icon-grey"></i>
                                                </a>
                                                <ul class="dropdown-menu ds-dropdown">
                                                    <li>
                                                        <label class="docushield-checkbox">
                                                            <input type="checkbox" name="new-comments" />
                                                            New Comments
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="docushield-checkbox">
                                                            <input type="checkbox" name="new-signatures" />
                                                            New Signatures
                                                        </label>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="title-with-sort">
                                            <span>Last Updated</span>
                                            <div class="dropdown d-inline">
                                                <a class="text-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ds ds-sort icon-grey"></i>
                                                </a>
                                                <ul class="dropdown-menu ds-dropdown">
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-col="5" data-order="desc">
                                                            <i class="ds ds-arrow-up icon-grey"></i> 
                                                            Oldest - Newest
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-col="5" data-order="asc">
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
                                                        <a class="dropdown-item" href="#" data-col="6" data-order="desc">
                                                            <i class="ds ds-arrow-up icon-grey"></i> 
                                                            Oldest - Newest
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-col="6" data-order="asc">
                                                            <i class="ds ds-arrow-down icon-grey"></i> 
                                                            Newest - Oldest
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </th>
                                    <th>
                                        Teams
                                    </th>
                                    <th></th> <!-- document sort -->
                                    <th></th> <!-- file sort -->
                                    <th></th> <!-- image sort -->
                                    <th></th> <!-- status sort -->
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-3 px-0 dash-table-footer">
                        <div class="small text-primary">Rows per page:
                            <select id="rowsPerPage" class="form-select form-select-sm d-inline-block w-auto border-0">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="text-muted small" id="pageInfo">Page 1 of 10</div>
                            <div class="ds-btn-group">
                                <button class="ds-button btn border-primary btn-sm" aria-label="Prev" id="prevPage"><i class="ds ds-arrow-left text-primary"></i></button>
                                <button class="ds-button btn border-primary btn-sm" aria-label="Next" id="nextPage"><i class="ds ds-arrow-right text-primary"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="grid-view" class="tab-pane fade pt-3">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card <?php echo ($total==0) ? "" : "d-none"; ?>" id="projects_not_exist">
    <div class="card-body">
        <div class="no-projects-yet">
            <div class="wrapper">
                <img src="<?=site_url("public/assets/images/no-projects-yet.svg")?>" alt="No Projects Yet!" class="rounded mx-auto d-block" />
                <h3 class="title text-primary fw-300">You haven't created a project yet</h3>
                <p class="info text-primary fw-300">You'll find all of your projects here once you've created some.</p>
                <?php
                if (session()->get("dc_roles") == "ROLE_ADMIN") {
                ?>
                    <button class="btn btn-primary ds-btn-primary" id="btnaddproject" data-geturl="<?=site_url("dpanel/users/get-li-list")?>" data-url="<?=site_url("dpanel/project/submit")?>"><i class="fa-regular fa-square-plus"></i> Create a new project</button>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>
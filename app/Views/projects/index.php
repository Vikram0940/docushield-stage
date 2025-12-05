<!-- Main content area -->
<main class="ds-main flex-grow-1 pb-4">
    <div class="container-fluid">        
        <div class="d-flex align-items-center my-2">
            <h4 class="fw-400 py-3">All Projects</h4>
            <?php
            if (session()->get("dc_roles") == "ROLE_ADMIN") {
            ?>
                <button class="btn btn-secondary btn-text-secondary fw-300 text-16 ms-auto" id="btnaddproject" data-geturl="<?=site_url("dpanel/users/get-li-list")?>" data-url="<?=site_url("dpanel/project/submit")?>">
                    <i class="fa-regular fa-square-plus"></i>
                    New Project
                </button>
            <?php
            }
            ?>
        </div>

        <?php
        echo view("projects/project-filter-header");
        ?>
    </div>
</main>
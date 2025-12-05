<div id="gridView" class="row g-3 projects-grid">
    <?php
    $favoritesModel = new \App\Models\Favorites;
    $projectModel = new \App\Models\Project;
    foreach ($projects as $project) {
        /* Check favorite is or not */
        $favoriteInfo = $favoritesModel->where([
            "source_id"   => $project->id,
            "source_type" => 1
        ])->first();
        $starred = empty($favoriteInfo) ? "" : "starred";
        /* Check favorite is or not */
    ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
            <div class="card ds-card p-3 project-item">
                <div class="section-start mb-auto">
                    <div class="star-actions mb-auto">
                        <a href="javascript:;" class="star markfavorite <?=$starred?>" data-id="<?=$project->id?>" data-type="1">
                            <i class="ds ds-star"></i>
                        </a>
                        <!-- Dropdown Menu -->
                        <?php
                        if (session()->get("dc_roles") == "ROLE_ADMIN") {
                        ?>
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0 ms-1" aria-label="More" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
                                <ul class="dropdown-menu ds-dropdown">
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item editproject" data-geturl="<?=site_url("dpanel/project/get-json-detail/".$project->id)?>" data-url="<?=site_url("dpanel/project/submit/".$project->id)?>" data-event="edit">
                                            <i class="ds ds-edit ds-lg icon-grey"></i> 
                                            Edit project
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item editproject" data-geturl="<?=site_url("dpanel/project/get-json-detail/".$project->id)?>" data-url="<?=site_url("dpanel/project/share/".$project->id)?>" data-event="share">
                                            <i class="ds ds-share ds-lg icon-grey"></i> 
                                            Share project
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item editproject" data-geturl="<?=site_url("dpanel/project/get-json-detail/".$project->id)?>" data-url="<?=site_url("dpanel/project/archive/".$project->id)?>" data-event="archive">
                                            <i class="ds ds-archive ds-lg icon-grey"></i> 
                                            Archive project
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item delete" data-url="<?=site_url("dpanel/project/".$project->id)?>" data-event="archive" data-method="DELETE" data-message="Are you sure? you want to delete this project.">
                                            <i class="fa fa-trash-alt ds-lg icon-grey"></i> 
                                            Delete project
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        <?php
                        }
                        ?>
                    </div>

                    <div class="item-title">
                        <h2><a href="<?=site_url("dpanel/project/detail/".$project->case_id)?>" class="item-name"><?=$project->name?></a></h2>
                    </div>

                    <div class="item-description">
                        Case #<?=$project->case_id?>
                    </div>
                </div>
                <div class="section-end mt-auto">
                    <div class="item-includes">
                        <span class="chip docs me-1">Documents <span class="count"><?=$projectModel->getCount($project->id)?></span></span>
                        <span class="chip files me-1">Files <span class="count"><?=$projectModel->getCount($project->id, 2)?></span></span>
                        <span class="chip images">Images <span class="count"><?=$projectModel->getCount($project->id, 1)?></span></span>
                    </div>
                    
                    <div class="item-activity">
                        <span class="activity text-primary">
                            <i class="ds ds-message text-primary"></i> New comments
                        </span>
                        <span class="activity text-primary">
                            <i class="ds ds-feather text-primary"></i> New signatures
                        </span>
                    </div>
                    
                    <div class="item-footer mt-auto">
                        <div class="item-teams">
                            <?php
                            echo view("projects/project-avatars", ["project_id" => $project->id, "showCount" => true]);
                            ?>
                        </div>
                        <div class="ds-small ms-1 item-updated"><?php echo time_ago($project->created_at);?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
    ?>
</div>
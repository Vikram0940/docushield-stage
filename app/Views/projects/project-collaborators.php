<div class="d-flex align-items-center gap-1 float-end">
    <?php
    echo view("templates/invitee-avatars", ["source_id" => $project_id, "source_type" => 1, "showCount" => $showCount, "title" => $project->name]);
    ?>
    <!-- Dropdown Menu -->
    <div class="dropdown">
        <button class="btn btn-link text-muted p-0 ms-1" aria-label="More" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
        <ul class="dropdown-menu ds-dropdown">
            <li>
                <a href="javascript:void(0)" class="dropdown-item editproject" data-geturl="<?=site_url("dpanel/project/get-json-detail/".$project_id)?>" data-url="<?=site_url("dpanel/project/submit/".$project_id)?>" data-event="edit">
                    <i class="ds ds-edit ds-lg icon-grey"></i> 
                    Edit project
                </a>
            </li>
            <li>
                <a href="javascript:void(0)" class="dropdown-item editproject" data-geturl="<?=site_url("dpanel/project/get-json-detail/".$project_id)?>" data-url="<?=site_url("dpanel/project/share/".$project_id)?>" data-event="share">
                    <i class="ds ds-share ds-lg icon-grey"></i> 
                    Share project
                </a>
            </li>
            <li>
                <a href="javascript:void(0)" class="dropdown-item editproject" data-geturl="<?=site_url("dpanel/project/get-json-detail/".$project_id)?>" data-url="<?=site_url("dpanel/project/archive/".$project_id)?>" data-event="archive">
                    <i class="ds ds-archive ds-lg icon-grey"></i> 
                    Archive project
                </a>
            </li>
            <li>
                <a href="javascript:void(0)" class="dropdown-item delete" data-url="<?=site_url("dpanel/project/".$project_id)?>" data-event="archive" data-method="DELETE" data-message="Are you sure? you want to delete this project.">
                    <i class="fa fa-trash-alt ds-lg icon-grey"></i> 
                    Delete project
                </a>
            </li>
        </ul>
    </div>
</div>
<div class="d-flex align-items-center gap-1 float-end">
    <?php
    if ($value->user_id != session()->get("dc_userid")) {
        $source_type = ($value->storage_type==1) ? 2 : 3;
        $inviteeInfo = (new \App\Models\Invitee)->where(["source_id" => $value->id, "source_type" => $source_type])->first();
    }

    if ((isset($storage_type) && $storage_type == 1) || (isset($value->storage_type) && $value->storage_type == 1)) {
        echo view("templates/invitee-avatars", ["source_id" => $file_id, "source_type" => 2, "showCount" => $showCount, "title" => $value->title]);
    }
    ?>
    <!-- Dropdown Menu -->
    <div class="dropdown">
        <button class="btn btn-link text-muted p-0 ms-1" aria-label="More" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
        <ul class="dropdown-menu ds-dropdown">
            <li>
                <a href="<?=site_url("dpanel/document/detail/".$file_id)?>" class="dropdown-item">
                    <i class="ds ds-edit ds-lg icon-grey"></i> 
                    <?php echo ($value->user_id == session()->get("dc_userid")) ? "Edit document" : "View document"; ?>
                </a>
            </li>
            <?php
            if ($value->user_id == session()->get("dc_userid")) {
            ?>
                <li>
                    <a href="<?=site_url("dpanel/document/detail/".$file_id)?>" class="dropdown-item">
                        <i class="ds ds-share ds-lg icon-grey"></i> 
                        Share document
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="dropdown-item delete" data-url="<?=site_url("dpanel/document/archive/".$file_id)?>" data-message="Are you sure? you want to archive select file.">
                        <i class="ds ds-archive ds-lg icon-grey"></i> 
                        Archive document
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" class="dropdown-item delete" data-url="<?=site_url("dpanel/file/".$file_id)?>" data-message="Are you sure? you want to delete selected file.">
                        <i class="fa fa-trash-alt ds-lg icon-grey"></i> 
                        Delete document
                    </a>
                </li>
            <?php
            }
            ?>
        </ul>
    </div>
</div>
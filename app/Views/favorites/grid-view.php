<?php
if (!empty($files)) {
?>
    <div class="row g-3 files-gridview grid-view-items">
        <?php
        $favoritesModel = new \App\Models\Favorites;
        foreach ($files as $file) {
            if ($file->parent_type == 1) {
                $file_size = "";
                $file_type = "document";
            }
            else {
                $file_type = empty($file->storage_path) ? "document" : strtolower(getFileType($file->storage_path));
                $file_size = empty($file->storage_path) ? "" : getFileSize("uploads/files/".$file->storage_path);
            }

            /* Check favorite is or not */
            $favoriteInfo = $favoritesModel->where([
                "source_id"   => $file->id,
                "source_type" => ($file->storage_type+1)
            ])->first();
            $starred = empty($favoriteInfo) ? "" : "starred";
            /* Check favorite is or not */
        ?>
            <div class="card col-lg-4 ds-card p-3 folder item <?=$file_type?>">
                <div class="star-actions mb-auto">
                    <a href="javascript:;" class="star starred markfavorite" data-id='<?=$file->parent_id?>' data-type='<?=$file->parent_type?>'>
                        <i class="ds ds-star"></i>
                    </a>
                    <!-- Dropdown Menu -->
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-0 ms-1" aria-label="More" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu ds-dropdown">
                            <li>
                                <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editproject">
                                    <i class="ds ds-share ds-lg icon-grey"></i> 
                                    Open
                                </a>
                            </li>
                            <li>
                                <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#shareproject">
                                    <i class="ds ds-edit ds-lg icon-grey"></i> 
                                    Rename
                                </a>
                            </li>
                            <li>
                                <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#archiveproject">
                                    <i class="ds ds-archive ds-lg icon-grey"></i> 
                                    Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <?php
                if (strpos($file->file_mime_type, 'image/') === 0) {
                ?>
                    <div class="mid-icon p-0">
                        <div class='gallery'>
                            <a href="<?=site_url("dpanel/files/view/".$file->storage_path)?>" class="no-underline text-primary item-name"><img src="<?=site_url("dpanel/files/view/".$file->storage_path)?>" class="img-responsive w-100" style="max-height: 120px;"></a>
                        </div>
                    </div>
                <?php
                }
                else {
                ?>
                    <div class="mid-icon" <?php echo ($file->file_icon_type==4) ? "style='--dsibg: url('/assets/images/test-item-image.png');'" : ""; ?>>
                        <?php
                        if ($file->file_icon_type == "pdf") {
                        ?>
                            <i class="ds ds-pdf"></i>
                        <?php
                        }
                        else if ($file->file_icon_type == "document") {
                        ?>
                            <i class="ds ds-doc"></i>
                        <?php
                        }
                        else if ($file->file_icon_type == "image") {
                        ?>
                            <a href="<?=site_url("uploads/".$file->storage_path)?>" class="stretched-link file-image-link">&nbsp;</a>
                        <?php
                        }
                        ?>
                    </div>               

                    <div class="item-activity notification mb-1">
                        <span class="activity">New Comments</span>
                    </div>
                <?php
                }
                ?> 
                
                <div class="d-flex justify-content-between item-footer">
                    <div class="item-info">
                        <div class="label">
                            <?php
                            if (strpos($file->file_mime_type, 'image/') === 0) {
                            ?>
                                <div class='gallery'>
                                    <a href="<?=site_url("dpanel/files/view/".$file->storage_path)?>" class="no-underline text-primary item-name"><?=$file->title?></a>
                                </div>
                            <?php
                            }
                            else {
                                if ($file->storage_type == 1) {
                                    echo "<a href=\"".site_url("dpanel/document/detail/".$file->id)."\" class=\"item-name no-underline text-primary\">".$file->title."</a>";
                                }
                                else {
                                    if ($file->source_type == 1) {
                                        echo $name = "<a href=\"".site_url("dpanel/project/detail/".$file->case_id)."\" class=\"item-name no-underline text-primary\">".$file->title."</a>";
                                    }
                                    else {
                                        $name = "<a href=\"".site_url("dpanel/files/view/".$file->storage_path)."\" class=\"item-name no-underline text-primary\">".$file->title."</a>";
                                    }
                                }
                            }
                            ?>
                        </div>
                        <div class="item-status <?php echo empty($file->storage_path) ? "" : "d-none"; ?>">
                            <span class="activity"><?php echo file_status($file->status)[0]["name"]; ?></span>
                        </div>
                        <div class="item-size">
                            <div class="ds-small"><?=$file_size?></div>
                        </div>
                    </div>
                    <div class="avatars-action">
                        <div class="d-flex align-items-center justify-content-end avatars">
                            <div class="item-teams">
                                <?php
                                echo view("templates/invitee-avatars", ["source_id" => $file->id, "source_type" => $file->storage_type, "showCount" => true, "title" => $file->title]);
                                ?>
                            </div>
                        </div>
                        <div class="ds-small ms-1 item-updated"><?php echo empty($file->updated_at) ? time_ago($file->created_at) : time_ago($file->updated_at)?></div>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
    <?php
}
?>
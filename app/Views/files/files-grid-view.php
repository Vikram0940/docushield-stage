<?php
if (!empty($files)) {
?>
    <div class="row g-3 files-gridview grid-view-items">
        <?php
        $favoritesModel = new \App\Models\Favorites;
        foreach ($files as $file) {
            $ext = "doc";

            $file_size = !empty($file->file_size) ? formatBytes($file->file_size) : "";

            if (!empty($file->storage_path)) {
                $ext = pathinfo($file->storage_path, PATHINFO_EXTENSION);
            }

            /* Check favorite is or not */
            $favoriteInfo = $favoritesModel->where([
                "source_id"   => $file->id,
                "source_type" => ($file->storage_type+1)
            ])->first();
            $starred = empty($favoriteInfo) ? "" : "starred";
            $favorite = "<a href=\"javascript:;\" class=\"star markfavorite ".$starred."\" data-id='".$file->id."' data-type='".($file->storage_type+1)."'><i class=\"ds ds-star icon-grey\"></i></a>";            
            /* Check favorite is or not */
        ?>
            <div class="card col-lg-4 ds-card p-3 folder item <?=$ext?>">
                <div class="star-actions mb-auto">
                    <a href="javascript:;" class="star markfavorite <?=$starred?>" data-id="<?=$file->id?>" data-type="<?=($file->storage_type+1)?>">
                        <i class="ds ds-star"></i>
                    </a>
                    <!-- Dropdown Menu -->
                    <div class="dropdown d-none">
                        <button class="btn btn-link text-muted p-0 ms-1" aria-label="More" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu ds-dropdown" style="">
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
                    <div class="mid-icon">
                        <i class="ds ds-pdf"></i>
                    </div>
                <?php
                }
                ?>

                <!-- <div class="item-activity notification mb-1">
                    <span class="activity">2 New Comments</span>
                </div> -->
                
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
                            ?>
                                <a href="<?=site_url("dpanel/document/detail/".$file->id)?>" class="item-name no-underline text-primary"><?=$file->title?></a>
                            <?php
                            }
                            ?>
                        </div>
                        <div class="item-status">
                            <?php
                            $fileStatus = document_status($file->status);
                            ?>
                            <span class="badge ds-<?php echo empty($fileStatus) ? "primary" : $fileStatus["color"]?> bg-<?php echo empty($fileStatus) ? "primary" : $fileStatus["color"]?> fw-300"><?php echo !empty($fileStatus) ? $fileStatus["name"] : ""; ?></span>
                        </div>
                        <div class="item-size">
                            <div class="ds-small"><?php echo $file_size; ?></div>
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
else {
?>
    <div class="alert alert-danger">
        Sorry! seems there is no records available at the moment.
    </div>
<?php
}
?>
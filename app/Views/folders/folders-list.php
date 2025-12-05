<?php
$folders = (new \App\Models\Folders)->where(["user_id" => session()->get("dc_userid"), "parent_id" => $project_id])->orderBy("created_date", "desc")->findAll();
if (!empty($folders)) {
    foreach ($folders as $folder) {
        $files = (new \App\Models\Storage)->where(["user_id" => session()->get("dc_userid"), "parent_id" => $folder->id])->whereIn("storage_type", [1, 2,3])->findAll();
        
        // Initialize summary array
        $summary = [];        
        if (!empty($files)) {
            // Loop and group by storage_type
            foreach ($files as $file) {
                $type = $file->storage_type;
                if (!isset($summary[$type])) {
                    $summary[$type] = [
                        'count'      => 0,
                        'total_size' => 0
                    ];
                }
                $summary[$type]['count']++;
                $summary[$type]['total_size'] += (int)$file->file_size;
            }
        }
        //print_r($summary);
        /*echo $totalCount = $summary[2]["count"];
        echo $totalSize = array_sum(array_column($files, 'file_size'));*/
        //print_r($files);
        //$fileSize = empty($files) ? 0 : formatBytes($files->size);

        /* Get image count & sizes */
        //$imageCount = isset($summary[2]) ? $summary
        $fileSize = formatBytes(0);
    ?>
        <div class="card ds-card p-3 col-md-5 col-10 col-lg-3 folder project-folder">
            <div class="d-flex justify-content-between mb-2">
                <div>
                    <div class="ds-card-title"><a href="<?=site_url("dpanel/project/folder/".$folder->id)?>" style="text-decoration: none;color: unset;"><?=$folder->name?></a></div>
                    <div class="ds-small"><?=$fileSize?></div>
                </div>
                <div>
                    <div class="d-flex align-items-center justify-content-end">
                        <!-- Dropdown Menu -->
                        <div class="dropdown">
                            <button class="btn btn-link text-muted p-0 ms-1" aria-label="More" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu ds-dropdown">
                                <li>
                                    <a href="<?=site_url("dpanel/project/folder/".$folder->id)?>" class="dropdown-item">
                                        <i class="ds ds-share ds-lg icon-grey"></i> 
                                        Open
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="dropdown-item editfolder" data-url="<?=site_url("dpanel/project/rename-folder/".$folder->id)?>" data-name="<?=$folder->name?>">
                                        <i class="ds ds-edit ds-lg icon-grey"></i> 
                                        Rename
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="dropdown-item delete" data-url="<?=site_url("dpanel/project/delete-folder/".$folder->id)?>" data-method="DELETE" data-message="Are you sure? you want to delete this folder. Please note that you will not be able to recover the data available under this folder.">
                                        <i class="ds ds-archive ds-lg icon-grey"></i> 
                                        Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="ds-small ms-1"><?=time_ago($folder->created_date)?></div>
                </div>
            </div>    
            <div class="d-flex flex-wrap gap-1 mt-auto">
                <a href="<?=site_url("dpanel/files?parent=".$folder->id."&type=1&parent_type=2")?>" class="chip docs">
                    <span class="count"><?php echo isset($summary[1]) ? $summary[1]["count"] : 0; ?></span>
                </span>
                <a href="<?=site_url("dpanel/storage?parent=".$folder->id."&type=2&parent_type=2")?>" class="chip files">
                    <span class="count"><?php echo isset($summary[2]) ? $summary[2]["count"] : 0; ?></span>
                </a>
                <a href="<?=site_url("dpanel/storage?parent=".$folder->id."&type=3&parent_type=2")?>" class="chip images">
                    <span class="count"><?php echo isset($summary[3]) ? $summary[3]["count"] : 0; ?></span>
                </a>
            </div>
        </div>
    <?php
    }
}
?>
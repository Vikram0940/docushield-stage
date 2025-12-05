<?php
$projects = (new \App\Models\Project)->select("*,(SELECT count(*) FROM tbldocuments WHERE tbldocuments.project_id=tblprojects.id AND tbldocuments.deleted_date) as total_documents")->where(["user_id" => session()->get("dc_userid"), "status" => 1])->orderBy("created_at desc")->findAll(10);
$projectModel = new \App\Models\Project;
$inviteeModel = new \App\Models\Invitee;
$userModel = new \App\Models\Users;
if (!empty($projects)) {
?>
    <h4 class="fw-400 py-3">Recent Projects</h4>
    <div class="recent-projects ds-auto-scroll">
        <?php
        foreach ($projects as $project) {
        ?>
            <div class="col-12 col-md-6 col-xl-4 card ds-card p-3 recent-project">
                <div class="d-flex justify-content-between mb-4">
                    <div>
                        <div class="ds-card-title"><a href="<?=site_url("dpanel/project/detail/".$project->case_id)?>" class="no-underline text-primary"><?=$project->name?></a></div>
                        <div class="ds-small">Case #<?=$project->case_id?></div>
                    </div>
                    <div>
                        <?php echo view("projects/project-collaborators", ["project_id" => $project->id, "showCount" => false, "project" => $project]); ?>
                        <div class="ds-small ms-1">
                            <?php echo time_ago($project->created_at);?>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex flex-wrap gap-2 mt-auto">
                    <a href="<?=site_url("dpanel/files?parent=".$project->id."&parent_type=1&type=1")?>">
                        <span class="chip docs">Documents<span class="count"><?=$projectModel->getCount($project->id, 1)?></span></span>
                    </a>
                    <a href="<?=site_url("dpanel/files?parent=".$project->id."&parent_type=1&type=2")?>">
                        <span class="chip files">Files <span class="count"><?=$projectModel->getCount($project->id, 2)?></span></span>
                    </a>
                    <a href="<?=site_url("dpanel/files?parent=".$project->id."&parent_type=1&type=3")?>">
                        <span class="chip images">Images <span class="count"><?=$projectModel->getCount($project->id, 3)?></span></span>
                    </a>
                </div>
            </div>    
        <?php
        }
        ?>
    </div>
<?php
}
?>
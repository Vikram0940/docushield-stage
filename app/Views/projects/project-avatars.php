<?php
$inviteeModel = new \App\Models\Invitee;
$userModel = new \App\Models\Users;
$invitees = $inviteeModel->where([
    "source_id"   => $project_id,
    "source_type" => 1
])->findAll(2);
if (!empty($invitees)) {
?>
    <!-- Avatars with 5 more -->
    <div class="avatar-group" role="group" aria-label="Shared with">
        <?php
        $inviteeIds = [];
        foreach ($invitees as $invitee) {
            $avatar = ""; $name = "";
            if ($invitee->user_id != 0) {
                $user_info = $userModel->find($invitee->user_id);
                $avatar = !empty($user_info) ? $user_info->avatar : "";
                $name = !empty($user_info) ? $user_info->name : "";
            }
            array_push($inviteeIds, $invitee->id);
        ?>
            <a href="javascript:void(0);" class="avatar" data-bs-toggle="tooltip" title="<?=$name?>">
                <img src="<?=user_avatar($avatar)?>" alt="<?=$name?>" class="rounded-circle">
            </a>
        <?php
        }

        if ($showCount == true) {
            $total_invitees = $inviteeModel->where([
                "source_id"   => $project_id,
                "source_type" => 1
            ])->whereNotIn("id", $inviteeIds)->countAllResults();
            if ($total_invitees > 0) {
            ?>
                <span class="avatar avatar-more" data-bs-toggle="tooltip" title="<?=$total_invitees?> more">+<?=$total_invitees?></span>
            <?php
            }
        }
        ?>
    </div>
<?php
}
?>
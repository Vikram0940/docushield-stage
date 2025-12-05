<?php
$inviteeModel = new \App\Models\Invitee;
$userModel = new \App\Models\Users;
$invitees = $inviteeModel->where([
    "source_id"   => $source_id,
    "source_type" => $source_type
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
            <a href="javascript:void(0);" class="avatar <?php echo ($invitee->user_id!=0) ? "avatar_info" : ""; ?>" data-bs-toggle="tooltip" title="<?=$name?>" data-geturl="<?php echo ($invitee->user_id!=0) ? "/user/get-invited-lists/".$invitee->user_id : ""; ?>" data-url="<?php echo ($invitee->user_id!=0) ? "/user/update-roles/".$invitee->user_id : ""; ?>" data-name="<?=$name?>">
                <img src="<?=user_avatar($avatar)?>" alt="<?=$name?>" class="rounded-circle">
            </a>
        <?php
        }

        if ($showCount == true) {
            $total_invitees = $inviteeModel->where([
                "source_id"   => $source_id,
                "source_type" => $source_type
            ])->whereNotIn("id", $inviteeIds)->countAllResults();
            if ($total_invitees > 0) {
            ?>
                <a href="javascript:void(0);" class="avatar avatar-more" data-bs-toggle="tooltip" title="<?=$total_invitees?> more" style="text-decoration: none;" data-geturl="<?php echo "/get-more-avatars/".$source_type."/".$source_id; ?>" data-name="<?php echo isset($title) ? $title : ""; ?>">+<?=$total_invitees?></a>
            <?php
            }
        }
        ?>
    </div>
<?php
}
?>
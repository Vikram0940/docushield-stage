<?php
$userModel = new \App\Models\Users;
foreach ($invitees as $invitee) {
    $user_info = $userModel->where(["email" => $invitee->email, "status" => 1])->first();
    if (!empty($user_info)) {
        $name = $user_info->name;
        $email = $user_info->email;
        $avatar = user_avatar($user_info->avatar);
        $user_id = $user_info->id;
    }
    else {
        $name = "";
        $email = $invitee->email;
        $avatar = site_url('public/assets/images/no-avatar.png');
        $user_id = 0;
    }
    $view = ($invitee->role==1) ? "selected='selected'" : "";
    $comment = ($invitee->role==2) ? "selected='selected'" : "";
?>
    <li>
        <div class="avatar">
            <img src="<?=$avatar?>">
        </div>
        <div class="collab-permission">
            <div class="collaborator flex-grow-1">
                <div class="name"><?=$name?></div>
                <div class="email"><?=$email?></div>
            </div>
            <div class="invite-permissions">
                <span class="badge bg-utility-warning invite-pending <?php echo ($invitee->user_id!=0) ? "d-none" : ""; ?>">Invite pending</span>
                <select class="permission" name='invitee[<?=$invitee->id?>][permission]'>
                    <option value="1" <?=$view?>>Can view</option>
                    <option value="2" <?=$comment?>>Can comment</option>
                </select>
            </div>
            <input type='hidden' name='invitee[<?=$invitee->id?>][email]' value='<?=$email?>'>
            <input type='hidden' name='invitee[<?=$invitee->id?>][userid]' value='<?=$user_id?>'>
        </div>
    </li>
<?php
}
?>
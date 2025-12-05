<?php
function user_avatar($userAvatar)
{
    $default = site_url('public/assets/images/no-avatar.png');

    if (empty($userAvatar)) {
        return $default;
    }

    // Check if it's a full URL (Google image or external)
    if (filter_var($userAvatar, FILTER_VALIDATE_URL)) {
        return $userAvatar;
    }

    // Otherwise, assume it's uploaded file
    //$path = 'public/uploads/avatars/' . $userAvatar;
    $path = WRITEPATH . "uploads/avatars/". $userAvatar;
    if (file_exists($path)) {
        return site_url('dpanel/user/avatar/' . $userAvatar);
    }

    // Fallback
    return $default;
}
?>
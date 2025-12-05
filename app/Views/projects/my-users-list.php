<?php
foreach ($users as $user) {
    $user_avatar = user_avatar($user->avatar);
?>
    <li class="my_user" data-email="<?=$user->email?>">
        <!-- Collab Dropdown -->
        <div class="avatar">
            <img src="<?=$user_avatar?>" alt="<?=$user->name?>" class="rounded-circle">
        </div>
        <div class="collaborator">
            <div class="name"><?=$user->name?></div>
            <div class="email"><?=$user->email?></div>
        </div>
    </li>
<?php
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sharing <?=$entity_type?></title>
</head>
<body>
    <h2>Hi <?=esc($name)?>,</h2>
    <p><?=esc($sender_name)?> just shared <?=esc($entity_name)?> with you.</p>
    <p>View <?=$entity_type?> → <a href="<?=$entity_link?>">Click to View</a></p>
</body>
</html>

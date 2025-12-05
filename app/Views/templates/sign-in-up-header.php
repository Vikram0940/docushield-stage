<!-- Login Header -->
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
	<link rel="shortcut icon" href="<?=site_url("public/favicon.ico")?>">
	<title><?=$title?></title>
	<link rel="stylesheet" href="<?=site_url("public/assets/css/style.css?v=".uniqid())?>">	
	<link rel="stylesheet" href="<?=site_url("public/assets/css/custom.css?v=".uniqid())?>" />
	<!-- Sweet Alert-->
    <link href="<?=site_url("public/assets/libs/sweetalert2/sweetalert2.min.css")?>" rel="stylesheet" type="text/css" />
	<link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<?php
    if (!empty($morecss)) {
        foreach ($morecss as $value) {
    ?>
            <link href="<?php echo $value; ?>" rel="stylesheet" type="text/css" />
    <?php
        }
    }
    ?>
	<script type="text/javascript">
		const $app_url = "<?=site_url()?>";
        const $base_url = "<?=site_url()?>";
        const $please_wait = "Please wait..";
        const $error = "Sorry! something went wrong";
        var $csrf_hash = "<?=csrf_hash()?>";
        const $googleClientId = "<?=getenv('google.clientId')?>";
        const $googleCallbackURL = "<?=site_url("auth/google/callback")?>";
	</script>
</head>
<body class="h-100">
	<section class="container-fluid min-vh-100 px-0">
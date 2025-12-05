<!-- Main Header -->
<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="csrf-name" content="<?= csrf_token() ?>">
            <meta name="csrf-token" content="<?= csrf_hash() ?>">
            
            <link rel="shortcut icon" href="<?=site_url("public/favicon.ico")?>">
            <title><?php echo isset($page_title) ? $page_title : "Dashboard : ".env("COMPANY_NAME"); ?></title>
            <!-- Header Global Styles -->
            <link rel="stylesheet" href="<?=site_url("public/assets/css/style.css?v=".uniqid())?>">
            <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
            <!-- Sweet Alert-->
            <link href="<?=site_url("public/assets/libs/sweetalert2/sweetalert2.min.css")?>" rel="stylesheet" type="text/css" />
            <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
            <!-- toastr CSS -->
            <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
            <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <link rel="stylesheet" href="<?=site_url("public/assets/css/app.css?v=".uniqid())?>">
            <link rel="stylesheet" href="<?=site_url("public/assets/css/custom.css?v=".uniqid())?>">
            <link rel="stylesheet" href="<?=site_url("public/assets/css/upload-file.css?v=".uniqid())?>">
            <!-- Include Custom Style Here if Required, Just after the Global Styles -->
            <!-- Head tag closes here </head> and <body> tag starts -->
            <?php
            if (!empty($morecss)) {
                foreach ($morecss as $value) {
            ?>
                    <link href="<?php echo $value; ?>" rel="stylesheet" type="text/css" />
            <?php
                }
            }

            $uri = service('uri');
            $segments = $uri->getSegments();
            if (in_array("folder", $segments) && in_array("project", $segments)) {
                $lastSegment = $uri->getSegment($uri->getTotalSegments());
                if (is_numeric($lastSegment)) {
                    $objectId = $lastSegment;
                    $objectType = 2;
                }
            }
            else if (in_array("detail", $segments) && in_array("project", $segments)) {
                $lastSegment = $uri->getSegment($uri->getTotalSegments());
                if (is_numeric($lastSegment)) {
                    $projectInfo = (new \App\Models\Project)->where("case_id", $lastSegment)->first();
                    $objectId = $projectInfo->id;
                    $objectType = 1;
                }
            }
            ?>
            <script type="text/javascript">
                const $app_error = "Sorry! something went wrong";
                const $loading = "Please wait..";
                const $app_url = "<?=site_url("dpanel")?>";
                const $base_url = "<?=site_url()?>";
                var $csrf_hash = "<?=csrf_hash()?>";
                const $role = "<?=session()->get("dc_roles")?>";
                const $objectId = "<?php echo isset($objectId) ? $objectId : 0; ?>";
                const $objectType = "<?php echo isset($objectType) ? $objectType : 0; ?>";
            </script>
        </head>
            <body class="ds-body">
            <!-- In case we need any class on body tag for page specific, we can ignore including header-close-body-start.html, instead, we can write both those tags manually here with the classes on body tag we want -->
            <!-- ===== Global Header (reused on every page) ===== -->
            <div class="container-fluid">
                <div class="row">
                    <!-- Main Sidebar -->
                    <!--<aside id="dsSidebar" class="col-lg-3 col-xl-2 d-lg-block collapse ds-sidebar py-0 border-end" tabindex="-1" aria-labelledby="dsSidebarLabel">-->
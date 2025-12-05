<?php
if (session()->getFlashdata("message")) {
    if (session()->getFlashdata("class")) {
        $alert_class = session()->getFlashdata("class");
    }
    else {
        $alert_class = "success";
    }
?>
<!-- <div class="row">
    <div class="col-lg-12"> -->
        <div class="alert alert-dismissible fade show alert-<?=$alert_class?>" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <?php
            if (session()->getFlashdata("heading")) {
                echo "<h4 class=\"alert-heading\">".session()->getFlashdata("heading")."</h4>";
            }
            ?>
            <p class="font-weight-bold mb-0"><?=session()->getFlashdata("message")?></p>
        </div>    
    <!-- </div>
</div> -->
<?php
}
?>
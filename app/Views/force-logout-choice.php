<main class="col-12 d-flex flex-column justify-content-between min-vh-100 bg-white px-3 px-md-5">
    <div class="DocuShield-Logo py-3 text-center pt-5">
        <img src="<?=site_url("public/assets/images/ds-logo.webp")?>" alt="DocuShield Logo" style="height: 60px;">
    </div>
    <div class="d-flex flex-grow-1 align-items-center justify-content-center">
        <div class="row flex-grow-1">
            <div class="col-12 m-auto mb-sm-0 py-0 px-3 max-width-600">
                <!-- Form Step 1 -->
                <div class="card card-bg-primary card-border-primary text-primary mb-4 border-1 rounded-2 py-2 d-flex align-items-center justify-content-center login-page">
                    <div class="card-body w-100 text-center">
                        <h3>You are already logged in on another device.</h3>
                        <p>Would you like to log out from that device and continue here?</p>

                        <?php echo form_open(site_url("force-logout"), ""); ?>
                            <button type="submit" class="btn btn-secondary w-100 py-2 mb-3">Logout Other Device & Continue</button>
                        <?=form_close()?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer text-center text-14 text-primary py-3 pb-lg-5">
        2025 © Docushield Admin Panel
    </div>
</main>
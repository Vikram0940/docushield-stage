<main class="col-12 d-flex flex-column justify-content-between min-vh-100 bg-white px-3 px-md-5">
    <div class="DocuShield-Logo py-3 text-center pt-5">
        <img src="<?=site_url("public/assets/images/ds-logo.webp")?>" alt="DocuShield Logo" style="height: 60px;">
    </div>
    <div class="d-flex flex-grow-1 align-items-center justify-content-center">
        <div class="row flex-grow-1">
            <div class="col-12 m-auto mb-sm-0 py-0 px-3 max-width-600">
                <!-- Form Step 1 -->                
                <?php if(!empty(session()->getFlashdata('message'))) : ?>
                <div class="alert alert-success"><?= session()->getFlashdata('message'); ?></div>
                <?php endif ?>
                <div class="card card-bg-primary card-border-primary text-primary mb-4 border-1 rounded-2 py-2 d-flex align-items-center justify-content-center login-page">
                    <div class="card-body w-100">
                        <h2 class="card-title text-center fw-400 mb-3 text-20 text-lg-24 text-xl-30 ">Password recovery!</h2>
                        <p class="mt-3 text-muted text-center text-12 text-lg-14">
                            Please enter your registered email address to receive <br>a password reset link in your inbox.
                        </p>
                        <!-- Login Form -->                        
                        <?php echo form_open(site_url("forgot-password/send-link"), "class='login-form needs-validation' id='frmlogin' name='frmlogin' novalidate"); ?>
                            <div class="mb-3 ds-input-wrapper">
                                <label class="form-label fw-300" for="email">Email</label>
                                <input type="email" class="form-control form-control-lg" placeholder="Email Address" id="email" name="email" required />
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 mb-3" id="btnlogin">Submit</button>

                            <div class="form-group row mb-3 fw-300">
                                <div class="col ds-input-wrapper text-center">
                                    <a href="<?=site_url()?>" class="text-14">Back to Sign in</a>
                                </div>
                            </div>
                        <?=form_close()?>
                        <!-- // Login Form -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer text-center text-14 text-primary py-3 pb-lg-5">
        2025 © Docushield Admin Panel
    </div>
</main>
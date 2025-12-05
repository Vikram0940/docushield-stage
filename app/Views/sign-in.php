<main class="col-12 d-flex flex-column justify-content-between min-vh-100 bg-white px-3 px-md-5">
    <div class="DocuShield-Logo py-3 text-center pt-5">
        <img src="<?=site_url("public/assets/images/ds-logo.webp")?>" alt="DocuShield Logo" style="height: 60px;">
    </div>
    <div class="d-flex flex-grow-1 align-items-center justify-content-center">
        <div class="row flex-grow-1">
            <div class="col-12 m-auto mb-sm-0 py-0 px-3 max-width-600">
                <!-- Form Step 1 -->
                <div class="card card-bg-primary card-border-primary text-primary mb-4 border-1 rounded-2 py-2 d-flex align-items-center justify-content-center login-page">
                    <div class="card-body w-100">
                        <h2 class="card-title text-center fw-400 mb-3 text-20 text-lg-24 text-xl-30 ">👋 &nbsp; Welcome back!</h2>
                        <p class="mt-3 text-muted text-center text-12 text-lg-14">
                            Don't have an account yet? <a href="<?php echo isset($token) ? site_url("sign-up?token=".$token) : site_url("sign-up"); ?>" class="no-underline">Sign up now</a>
                        </p>
                        <?php if(!empty(session()->getFlashdata('fail'))) : ?>
                        <div class="alert alert-danger"><?= session()->getFlashdata('fail'); ?></div>
                        <?php endif ?>
                        <?php if(!empty(session()->getFlashdata('success'))) : ?>
                        <div class="alert alert-success"><?= session()->getFlashdata('success'); ?></div>
                        <?php endif ?>
                        <!-- Login Form -->                        
                        <?php echo form_open(site_url("auth/validate"), "class='login-form needs-validation' id='frmlogin' name='frmlogin' novalidate"); ?>
                            <div class="mb-3 ds-input-wrapper">
                                <label class="form-label fw-300" for="email">Email</label>
                                <input type="email" class="form-control form-control-lg" placeholder="Email Address" id="email" name="email" required />
                            </div>
                            <div class="mb-3">
                                <div class="password-wrapper position-relative">
                                    <label for="password" class="form-label fw-300">Password</label>
                                    <div class="ds-input-wrapper">
                                        <div class="ds-icon eye position-relative">
                                            <input type="password" id="password" name="password" class="form-control pe-5" placeholder="Enter password" autocomplete="new-password" required />
                                            <button type="button" class="toggle-password-btn" aria-label="Toggle password visibility">
                                                <img src="<?=site_url("public/assets/icons/eye.svg")?>" id="eye-icon" alt="Toggle visibility" width="20" height="20" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col d-block d-md-none text-end">
                                <a href="<?=site_url("forgot-password")?>" class="text-14 no-underline">Forgot your password?</a>
                            </div>

                            <div class="form-group row mb-3 fw-300">
                                <div class="col ds-input-wrapper">
                                    <label class="docushield-checkbox">
                                        <input type="checkbox" name="rememberme" />
                                        <span class="checkmark"></span>
                                        Remember me
                                    </label>
                                </div>
                                <div class="col d-none d-md-block text-end">
                                    <a href="<?=site_url("forgot-password")?>" class="text-14">Forgot your password?</a>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 mb-3" id="btnlogin">Login</button>
                            <div class="d-flex align-items-center mb-3">
                                <hr class="flex-grow-1 text-muted" />
                                <span class="mx-2 text-primary fw-300">OR</span>
                                <hr class="flex-grow-1 text-muted" />
                            </div>
                            <button type="button" class="btn btn-secondary w-100 py-2 mb-3" id="btngooglelogin" data-url="<?php echo isset($token) ? site_url("auth/google-sign-in?token=".$token) : site_url("auth/google-sign-in"); ?>">
                                <img src="<?=site_url("public/assets/icons/google.svg")?>" class="signup-icon" alt="Google" width="20" height="20" />
                                Login with Google
                            </button>
                            <!-- <a class="btn btn-secondary w-100 py-2 mb-3" href="javascript:void(0)" data-url="<?=site_url("google-sign-in")?>">
                                <img src="<?=site_url("public/assets/icons/google.svg")?>" class="signup-icon" alt="Google" width="20" height="20" />
                                Login with Google
                            </a> -->
                            <!-- <button type="button" class="btn btn-secondary w-100 py-2 mb-3">
                                <img src="<?=site_url("public/assets/icons/microsoft.svg")?>" class="signup-icon" alt="Microsoft" width="20" height="20" />
                                Login with Outlook
                            </button> -->
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
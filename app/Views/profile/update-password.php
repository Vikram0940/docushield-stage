<!-- Main content area -->
<main class="ds-main flex-grow-1 pb-4">
    <div class="container-fluid">
        <div class="inner-subheader mt-2 mb-3">
            <ul class="breadcrumbs">
                <li><a href="<?=site_url("dpanel")?>" class='text-dark' style="text-decoration: none;">Dashboard</a></li>
                <li class="active">Password</li>
            </ul>
            <div class="content-right"></div>
        </div>

        <div class="row">
            <div class="col-lg-6 mx-auto">
                <?php
                echo view("templates/alert-template");
                ?>
                <div class="card" data-active-step="1">
                    <div class="card-body">
                        <h2 class="card-title fw-400 mb-1 h4">Reset your login password!</h2>
                        <p class="mt-0 text-muted text-12 text-lg-14" style="line-height: 22px;">
                            Please enter a new password to update your account.<br>Make sure your password is strong and easy for you to remember.
                        </p>
                        <!-- Login Form -->                        
                        <?php echo form_open(site_url("dpanel/user/change-password"), "class='signup-form needs-validation' id='frmlogin' name='frmlogin' novalidate"); ?>
                            <div class="mb-3 ds-input-wrapper">
                                <label class="form-label" for="confirm">Current password</label>
                                <input type="password" class="form-control form-control-lg" placeholder="Enter current password" id="current" name="current" required />
                            </div>
                            <div class="mb-3">
                                <div class="password-wrapper position-relative">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="ds-input-wrapper">
                                        <div class="ds-icon eye position-relative">
                                            <input type="password" id="password" name="password" class="form-control pe-5" placeholder="Enter password" autocomplete="new-password" required />
                                            <button type="button" class="toggle-password-btn" aria-label="Toggle password visibility">
                                                <img src="<?=site_url("public/assets/icons/eye.svg")?>" id="eye-icon" alt="Toggle visibility" width="20" height="20" />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Password Strength Rules -->
                                    <ul class="password-rules mt-2">
                                        <li class="col-lg-6 col-12" data-rule="special">At least 1 special character</li>
                                        <li class="col-lg-6 col-12" data-rule="number">At least 1 number</li>
                                        <li class="col-lg-6 col-12" data-rule="length">8 characters or more</li>
                                        <li class="col-lg-6 col-12" data-rule="uppercase">1 uppercase letter</li>
                                        <li class="col-lg-6 col-12" data-rule="lowercase">1 lowercase letter</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mb-3 ds-input-wrapper">
                                <label class="form-label" for="confirm">Confirm password</label>
                                <input type="password" class="form-control form-control-lg" placeholder="Confirm your password" id="confirm" name="confirm" required />
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 mb-3" id="btnsubmit">Update</button>
                        <?=form_close()?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
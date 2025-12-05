<div class="steps-timeline row g-0 h-100" data-timeline-step="1">
	<!-- Left Panel -->
    <aside class="min-vh-100 col-4 d-none d-md-flex flex-column justify-content-start text-white p-3 p-lg-5 pt-lg-2 bg-primary-light sidebar-blue-bg">
	    <div class="w-100 signup-sidebar position-relative" style="max-width: 460px; margin-left: auto;">
	        <h2 class="heading text-black mt-6 mt-lg-2 mt-xl-4 text-24 text-lg-26 text-lg-32">Join the DocuShield<br />community today</h2>
        	<p class="text-black">Unlock a world of secure document management at your fingertips.</p>
	        
	        <div class="features mt-5 mt-lg-2 mt-xl-5">
            	<div class="feature ms-auto">
	                <img src="<?=site_url("public/assets/icons/folder.svg")?>" class="img-fluid mb-2" />
	                <div class="text-primary">
	                    <h5 class="text-18 mb-1 fw-400">Organize projects</h5>
	                    <p class="text-16 fw-300">Streamline your files for easy access</p>
	                </div>
	            </div>
	        </div>

	        <div class="cards-wrapper">
	            <!-- Card 1 (floating) -->
	            <div class="car-claim doc-card position-lg-static floating-card">
	                <img src="<?=site_url("public/assets/images/car-claim.webp")?>" class="img-fluid" />
	            </div>

	            <!-- Card 2 (background card) -->
	            <div class="case-intro doc-card position-lg-static bg-card">
	                <img src="<?=site_url("public/assets/images/case-intro.webp")?>" alt="Case Intro" class="img-fluid" />
	            </div>
	        </div>

	        <div class="features mt-5 mt-lg-0">
            	<div class="feature me-auto">
	                <img src="<?=site_url("public/assets/icons/shield.svg")?>" class="img-fluid mb-2" />
	                <div class="text-primary">
	                    <h5 class="text-18 mb-1 fw-400">Always encrypted</h5>
	                    <p class="text-16 fw-300">Manage your documents securely</p>
	                </div>
	            </div>
	        </div>
	    </div>
	</aside>

<!-- Right Panel -->
<!-- col-md-8 d-flex align-items-center justify-content-center bg-white px-3 px-md-5 -->
<!-- Subheader -->
<main class="col-md-8 d-flex flex-column justify-content-between min-vh-100 bg-white px-3 px-md-5">
  	<div class="DocuShield-Logo py-3 text-center pt-5 pt-sm-3 pt-lg-4">
    	<img src="<?=site_url("public/assets/images/ds-logo.webp")?>" alt="DocuShield Logo" style="height: 60px;">
  	</div>
    <div class="d-flex flex-grow-1 align-items-center justify-content-center">
        <div class="row flex-grow-1">
          	<div class="col-12 col-lg-10 m-auto mb-sm-0 py-0 py-sm-0 py-lg-0 px-3 max-width-600">
            	<!-- Form Step 1 -->
            	<div class="card card-bg-primary card-border-primary text-primary mb-4 border-1 rounded-2 py-2 pb-lg-0 d-flex align-items-center justify-content-center" data-active-step="1">
				    <div class="card-body w-100 py-lg-0">
				        <h2 class="card-title text-center fw-300 mb-4 mb-lg-0 text-20 text-lg-24">👋 &nbsp; Welcome to DocuShield </h2>
				        <?php if(!empty(session()->getFlashdata('fail'))) : ?>
                        <div class="alert alert-danger mt-2"><?= session()->getFlashdata('fail'); ?></div>
                        <?php endif ?>
				        <!-- Signup Forms -->
				        <?php echo form_open(site_url("sign-up/create-account"), "class='signup-form needs-validation' id='frmsignup' name='frmsignup' novalidate"); ?>
				            <div class="mb-3 ds-input-wrapper">
				                <label class="form-label" for="email">Email</label>
				                <input type="email" class="form-control form-control-lg" placeholder="Email Address" id="email" name="email" required />
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

				            <div class="mb-4 mb-lg-3 ds-input-wrapper">
				                <label class="docushield-checkbox">
				                    <input type="checkbox" name="tos" required />
				                    <span class="checkmark"></span>
				                    I accept the <a href="#">Terms & Conditions</a>
				                </label>
				            </div>
				            <?php
				            if (isset($_GET['token'])) {
				            ?>
				            	<input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
				            <?php
				            }
				            ?>				            
				            <button type="submit" class="btn btn-primary w-100 py-2 mb-3" id="btnsubmit">Create with email</button>
				            <div class="d-flex align-items-center mb-3">
				                <hr class="flex-grow-1 text-muted" />
				                <span class="mx-2 text-primary">OR</span>
				                <hr class="flex-grow-1 text-muted" />
				            </div>
				            <button type="button" class="btn btn-secondary w-100 py-2 mb-3" id="btngooglelogin" data-url="<?php echo isset($token) ? site_url("auth/google-sign-in?token=".$token) : site_url("auth/google-sign-in"); ?>">
				                <img src="<?=site_url("public/assets/icons/google.svg")?>" class="signup-icon" alt="Google" width="20" height="20" />
				                Sign up with Google
				            </button>
				            <!-- <button type="button" class="btn btn-secondary w-100 py-2">
				                <img src="<?=site_url("public/assets/icons/microsoft.svg")?>" class="signup-icon" alt="Microsoft" width="20" height="20" />
				                Sign up with Outlook
				            </button> -->
				        <?=form_close()?>
				        <!-- // Signup Forms -->
				        <p class="mt-4 mt-lg-3 text-muted text-center text-lg-14">
				            Already have an account? <a href="<?=site_url()?>" class="no-underline">Log in</a>
				        </p>
				    </div>
				</div>
			</div>
		</div>
	</div>
	<div class="footer text-center text-14 text-primary py-3 pb-lg-5">
	    2025 © <?=env("COMPANY_NAME")?> Admin Panel
	</div>
</main>
<div class="steps-timeline row g-0 h-100" data-timeline-step="2">
	<!-- Left Panel -->
    <aside class="min-vh-100 col-md-4 col-xl-3 d-none d-md-flex align-items-center flex-column justify-content-start py-md-5 text-white px-3 px-xl-4 px-xxl-4 bg-primary-light sidebar-blue-bg">
	    <div class="w-100 sticky-top" style="max-width: 460px; margin-left: auto;">
	        <div class="d-md-block d-none">
	            <div class="py-md-4 py-0 text-center text-md-start">
	    			<h2 class="fw-bold text-black text-20 text-lg-22 mb-2">Join us in just a few clicks</h2>
	    			<p class="mb-0 text-14 text-primary">Securely store and access your documents with a fast, simple sign-up - no extra steps required.</p>
				    <div class="signup-timeline-wrapper pt-sm-4 pt-md-5 pt-xxl-4 px-3 px-md-2 px-lg-3 px-xl-4 px-xxl-3">
				        <ul class="signup-timeline">
				            <li class="completed" data-step="1" data-bs-toggle="tooltip" title="Account creation">
				                <span class="step-label"><a class="text-black no-underline" href="javascript:void(0)">Account creation</a></span>
				            </li>
				            <li class="active" data-step="2" data-bs-toggle="tooltip" title="Business details">
				                <span class="step-label"><a class="text-black no-underline" href="javascript:void(0)">Business details</a></span>
				            </li>
				            <li class="<?php echo ($user_info->role == "ROLE_ADMIN") ? "" : "last"; ?>" data-step="3" data-bs-toggle="tooltip" title="Basic information & verification">
				                <span class="step-label"><a class="text-black no-underline" href="javascript:void(0)">Basic information & verification</a></span>
				            </li>
				            <?php
				            if ($user_info->role == "ROLE_ADMIN") {
				            ?>
					            <li class="last" data-step="4" data-bs-toggle="tooltip"  title="Pricing plan & payment">
					                <span class="step-label"><a class="text-black no-underline" href="javascript:void(0)">Pricing plan & payment</a></span>
					            </li>
				            <?php
				        	}
				            ?>
				        </ul>
				        <p class="d-block d-md-none mb-5 mb-md-0 text-center">
				            <span class="badge py-2 px-3 rounded-pill text-primary bg-success step-name">Basic information & verification</span>
				        </p>
				    </div>
				</div>
	        </div>
	    </div>
	</aside>

	<!-- Right Panel -->
    <!-- Subheader -->
    <main class="col-md-8 col-xl-9 d-flex flex-column justify-content-between min-vh-100 bg-white px-3 px-md-5">
    	<div class="DocuShield-Logo py-3 text-center pt-5 pt-lg-5">
            <img src="<?=site_url("public/assets/images/ds-logo.webp")?>" alt="DocuShield Logo" style="height: 60px;">
        </div>
        <div class="d-flex flex-grow-1 align-items-center justify-content-center">
            <div class="row flex-grow-1">
            	<div class="col-12 col-lg-10 m-auto mb-sm-0 pt-0 pt-xl-5 pb-5 px-3 max-width-600">
            		<!-- Form Step 2 -->
				    <div class="d-block d-md-none timeline-wrapper horizontal" data-timeline-step="2">
				    	<div class="py-md-4 py-0 text-center text-md-start">
				    		<h2 class="fw-bold text-black text-20 text-lg-22 mb-2 pt-1 pt-lg-5">Join us in just a few clicks</h2>
				    		<p class="mb-0 mb-md-5 text-14 text-primary">Securely store and access your documents with a fast, simple sign-up - no extra steps required.</p>
						    <div class="signup-timeline-wrapper pt-sm-4 pt-md-5 pt-xxl-4 px-3 px-md-2 px-lg-3 px-xl-4 px-xxl-3">
						        <ul class="signup-timeline">
						            <li class="completed" data-step="1" data-bs-toggle="tooltip" title="Account creation">
						                <span class="step-label"><a class="text-black no-underline" href="javascript:void(0)">Account creation</a></span>
						            </li>
						            <li class="active" data-step="2" data-bs-toggle="tooltip" title="Business details">
						                <span class="step-label"><a class="text-black no-underline" href="javascript:void(0)">Business details</a></span>
						            </li>
						            <li data-step="3" data-bs-toggle="tooltip" title="Basic information & verification">
						                <span class="step-label"><a class="text-black no-underline" href="javascript:void(0)">Basic information & verification</a></span>
						            </li>
						            <li class="last" data-step="4" data-bs-toggle="tooltip"  title="Pricing plan & payment">
						                <span class="step-label"><a class="text-black no-underline" href="javascript:void(0)">Pricing plan & payment</a></span>
						            </li>
						        </ul>
						        <p class="d-block d-md-none mb-5 mb-md-0 text-center">
						            <span class="badge py-2 px-3 rounded-pill text-primary bg-success step-name">Basic information & verification</span>
						        </p>
						    </div>
					    </div>
					</div>

					<div class="card p-4 shadow-sm card-bg-primary card-border-primary text-primary mb-4 border-1 rounded-2 py-2 d-flex align-items-center justify-content-center" data-active-step="2">
					    <div class="card-body w-100">					        
					        <!-- Signup Forms -->
					        <?php echo form_open(site_url("sign-up/submit-account-detail"), "class='signup-form step-2 needs-validation' id='frmsignup' name='frmsignup' novalidate"); ?>
					        	<div class="step-container" id="steps">
							        <div id="step-1" class="step-panel is-current">
							        	<h2 class="card-title fw-400 mb-4 text-20 text-lg-26">What type of business are you?</h2>
							            <!-- Step 1 content -->
							            <div id="business-type-group">
							                <label class="radio-card w-100 mb-3">
							                    <input type="radio" name="business_type" value="Law Office">
							                    <div class="text-primary">Law Office</div>
							                </label>

							                <label class="radio-card w-100 mb-3">
							                    <input type="radio" name="business_type" value="IT Professional">
							                    <div class="text-primary">IT Professional</div>
							                </label>

							                <label class="radio-card w-100 mb-3">
							                    <input type="radio" name="business_type" value="Other" id="radio-other">
							                    <div class="text-primary">Something else</div>
							                </label>

							                <div class="text-danger small mt-1 d-none" id="business-error">Please select a business type.</div>
							            </div>

							            <div id="other-input-wrapper" class="smooth-toggle mb-4">
							                <label for="otherBusinessType" class="form-label">Business type</label>
							                <input type="text" class="form-control" id="otherBusinessType" name="otherBusinessType" placeholder="Enter your business type">
							                <div class="text-danger small mt-1 d-none" id="other-error">Please enter your business type.</div>
							            </div>

							            <!-- Button always visible -->
    									<div class="step-actions">
							            	<button type="button" class="btn btn-primary w-100 py-2 mb-3 next-button" id="btnfirst">Next</button>
							            </div>
							        </div>
							        <?php
							        if (!empty($user_info->name)) {
							        	$name = explode(" ", $user_info->name);
							        }
							        ?>
							        <div id="step-2" class="step-panel">
							        	<h2 class="card-title fw-400 mb-4 text-20 text-lg-26">Account Information</h2>
							            <!-- First Name & Last Name -->
							            <div class="row mb-3">
							                <div class="col-12 col-md-6">
							                    <label for="firstName" class="form-label">First Name <span class="text-secondary">*</span></label>
							                    <input type="text" name="first_name" class="form-control" id="firstName" value="<?php echo isset($name[0]) ? trim($name[0]) : ""; ?>" required placeholder="Jane" data-error="First name is required"/>
							                </div>
							                <div class="col-12 col-md-6">
							                    <label for="lastName" class="form-label">Last Name <span class="text-secondary">*</span></label>
							                    <input type="text" name="last_name" class="form-control" id="lastName" value="<?php echo isset($name[1]) ? trim($name[1]) : ""; ?>" required placeholder="Doe" data-error="Last name is required" />
							                </div>
							            </div>

							            <!-- Company Name -->
							            <div class="mb-3">
							                <label for="company" class="form-label">Company Name</label>
							                <input type="text" name="company_name" class="form-control" id="company" placeholder="Company Name">
							            </div>

							            <!-- Phone Number -->
							            <div class="mb-3">
							                <label for="phone" class="form-label">Phone Number <span class="text-secondary">*</span></label>
							                <div class="input-igroup position-relative">
							                    <input type="tel" name="phone" class="form-control pe-5" id="phone" placeholder="Enter phone number" required data-error="Enter a valid 10-digit phone number." />
							                    <span class="di-info-icon" data-bs-toggle="tooltip" title="Include your area code">
							                    </span>
							                </div>
							            </div>

							            <!-- Identification Document Upload -->
							            <div class="mb-4">
							                <label for="idDocument" class="form-label">Identification Document <span class="text-secondary">*</span></label>
							                <div class="input-group docushield-file-input-group">
							                    <input type="text" class="form-control" placeholder="Select a file to upload" readonly id="ds-file-name-display">

							                    <span class="position-relative right-10px">
							                        <span class="di-info-icon" data-bs-toggle="tooltip" title="Only PDF, JPG, JPEG, PNG"></span>
							                    </span>

							                    <input type="file" class="d-none" id="idDocument" accept=".pdf,.jpg,.jpeg,.png" required data-error="Please upload your Identification document" name="file" />
							                    <label class="btn border-left-0 rounded-start-0 btn btn-primary py-2 id-document" for="idDocument">Choose file</label>
							                </div>
							            </div>

							            <!-- Submit Button -->
							            <div class="step-actions">
								            <?php
								            if (isset($_GET['token'])) {
								            ?>
								            	<input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
								            <?php
								            }
								            ?>
								            <button type="submit" class="btn btn-primary w-100 py-2 mb-3 next-button" id="btnsecond">Next</button>
								        </div>
							        </div>
							    </div>
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
</div>
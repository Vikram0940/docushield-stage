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
				            <li class="completed" data-step="2" data-bs-toggle="tooltip" title="Business details">
				                <span class="step-label"><a class="text-black no-underline" href="javascript:void(0)">Business details</a></span>
				            </li>
				            <li class="completed" data-step="3" data-bs-toggle="tooltip" title="Basic information & verification">
				                <span class="step-label"><a class="text-black no-underline" href="javascript:void(0)">Basic information & verification</a></span>
				            </li>
				            <?php
				            if ($user_info->role == "ROLE_ADMIN") {
				            ?>
					            <li class="active last" data-step="4" data-bs-toggle="tooltip"  title="Pricing plan & payment">
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
            	<div class="ol-12 col-lg-12 m-auto mb-sm-0 pt-0 pt-xl-3 pb-5 pb-lg-3 px-0">
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
						            <li class="completed" data-step="2" data-bs-toggle="tooltip" title="Business details">
						                <span class="step-label"><a class="text-black no-underline" href="javascript:void(0)">Business details</a></span>
						            </li>
						            <li class="completed" data-step="3" data-bs-toggle="tooltip" title="Basic information & verification">
						                <span class="step-label"><a class="text-black no-underline" href="javascript:void(0)">Basic information & verification</a></span>
						            </li>
						            <li class="active last" data-step="4" data-bs-toggle="tooltip"  title="Pricing plan & payment">
						                <span class="step-label"><a class="text-black no-underline" href="javascript:void(0)">Pricing plan & payment</a></span>
						            </li>
						        </ul>
						        <p class="d-block d-md-none mb-5 mb-md-0 text-center">
						            <span class="badge py-2 px-3 rounded-pill text-primary bg-success step-name">Basic information & verification</span>
						        </p>
						    </div>
					    </div>
					</div>
				</div>

				<section class="pricing-step px-3 px-md-0 px-xxl-1 pt-md-0 pt-0 py-4">
				    <div class="row g-4 justify-content-center justify-content-lg-between align-items-center pricing-step-header mb-4">
				        <div class="pricing-header-content col-12 col-lg-7 col-xl-9 text-center text-lg-start">
				            <h2 class="fw-bold text-20 text-xl-24 ">Pick a plan that works for you</h2>
				            <p class="text-muted text-16 text-lg-14">Experience secure document management with our tailored pricing plans.</p>
				        </div>
				        <div class="pricing-plan-toggle col-12 col-lg-5 col-xl-3 text-center text-lg-end mt-0 mt-lg-3">
				            <div class="form-check form-switch d-inline-flex align-items-center gap-2">
				                <input class="form-check-input always-blue-toggle" type="checkbox" role="switch" id="annualPlanSwitch" />
				                <label class="form-check-label" for="annualPlanSwitch">Annual plans</label>
				            </div>
				        </div>
				    </div>

				    <div class="row g-2 gy-4 g-lg-3 g-xl-4 justify-content-center">
				        <!-- Free Plan -->
				        <div class="col-12 col-lg-6 col-xl-4">
				            <div class="card h-100 border rounded-3 shadow-sm pricing-card">
				                <div class="card-body">
				                    <h5 class="text-24 text-lg-26 fw-300">Free</h5>
				                    <div class="ds-monthly-yearly" data-monthly="0" data-yearly="0">
				                        <span class="display-6 fw-bold ds-price">$0</span>
				                        <sub class="fs-6 fw-normal ds-term">/mo</sub>
				                    </div>
				                    <p class="text-black my-4 fw-300">Try our basic features for free</p>
				                    <ul class="list-unstyled mt-0 mb-4 pricing-features">
				                        <li>Protects files with end-to-end encryption</li>
				                        <li>Set who can view, edit, or share files</li>
				                        <li>Edit and comment with your team live</li>
				                    </ul>
				                    <div class="select-btn-wrapper">
				                        <button class="btn btn-primary text-14 fw-400 text-inverse w-100 next-button select-plan-btn" data-plan="Free" data-monthly="0" data-yearly="0" data-price="0">Select plan</button>
				                    </div>
				                </div>
				            </div>
				        </div>

				        <!-- Small Team Plan -->
				        <div class="col-12 col-lg-6 col-xl-4">
				            <div class="card h-100 border rounded-3 shadow-sm pricing-card position-relative">
				                <div class="badge bg-orange text-primary position-absolute fw-300 end-0 m-3 rounded-pill px-2 py-1 small">Popular</div>
				                <div class="card-body">
				                    <h5 class="text-24 text-lg-26 fw-300">Small Team</h5>
				                    <div class="ds-monthly-yearly" data-monthly="20" data-yearly="200">
				                        <span class="display-6 fw-bold ds-price">$20</span>
				                        <sub class="fs-6 fw-normal ds-term">/mo</sub>
				                    </div>
				                    <p class="text-black my-4 fw-300">Great for small businesses</p>
				                    <ul class="list-unstyled mt-0 mb-4 pricing-features">
				                        <li>Protects files with end-to-end encryption</li>
				                        <li>Set who can view, edit, or share files</li>
				                        <li>Edit and comment with your team live</li>
				                        <li>Keep files updated across all devices</li>
				                    </ul>
				                    <div class="select-btn-wrapper">
				                        <button class="btn btn-primary text-14 fw-400 text-inverse w-100 next-button select-plan-btn" data-plan="Small Team" data-monthly="2000" data-yearly="20000" data-price="2000">Select plan</button>
				                    </div>
				                </div>
				            </div>
				        </div>

				        <!-- Enterprise Plan -->
				        <div class="col-12 col-lg-12 col-xl-4">
				            <div class="card h-100 border rounded-3 shadow-sm pricing-card">
				                <div class="card-body">
				                    <h5 class="text-24 text-lg-26 fw-300">Enterprise</h5>
				                    <div class="ds-monthly-yearly" data-monthly="40" data-yearly="400">
				                        <span class="display-6 fw-bold ds-price">$40</span>
				                        <sub class="fs-6 fw-normal ds-term">/mo</sub>
				                    </div>
				                    <p class="text-black my-4 fw-300">Best for large organizations</p>
				                    <ul class="list-unstyled mt-0 mb-4 pricing-features">
				                        <li>Protects files with end-to-end encryption</li>
				                        <li>Set who can view, edit, or share files</li>
				                        <li>Edit and comment with your team live</li>
				                        <li>Keep files updated across all devices</li>
				                        <li>Find files fast with search and tags</li>
				                        <li>60GB cloud storage</li>
				                    </ul>
				                    <div class="select-btn-wrapper">
				                        <button class="btn btn-primary text-14 fw-400 text-inverse w-100 next-button select-plan-btn" data-plan="Enterprise" data-monthly="4000" data-yearly="40000" data-price="4000">Select plan</button>
				                    </div>
				                </div>
				            </div>
				        </div>
				    </div>
				</section>
			</div>
		</div>
		<div class="footer text-center text-14 text-primary py-3 pb-lg-5">
		    2025 © Docushield Admin Panel
		</div>
    </main>
</div>

<!-- Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content p-4">
      <h5>Complete Your Subscription</h5>
      <div id="dropin-container"></div>
      <button id="submit-button" class="btn btn-primary mt-3">Confirm Payment</button>
    </div>
  </div>
</div>
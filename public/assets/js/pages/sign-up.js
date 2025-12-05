$.ajaxSetup( {
    headers: {
        'X-CSRF-Token': $csrf_hash
    }
});
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
  if (bootstrap?.Tooltip) new bootstrap.Tooltip(el);
});

document.addEventListener("DOMContentLoaded", function () {
  const stepOne = document.querySelector('[data-active-step="1"]');
  if (!stepOne) return; // Exit if not on step 1

  // Toggle Password Visibility
  const toggleBtn = stepOne.querySelector(".toggle-password-btn");
  const passwordInput = stepOne.querySelector("#password");
  const eyeIcon = stepOne.querySelector("#eye-icon");
  const passwordRulesEl = document.getElementById("password-rules");

  if (toggleBtn && passwordInput && eyeIcon) {
    toggleBtn.addEventListener("click", function () {
      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.src = $app_url + "public/assets/icons/eye-slash.svg";
      } else {
        passwordInput.type = "password";
        eyeIcon.src = $app_url + "public/assets/icons/eye.svg";
      }
    });
  }

  // Password Rules Preview
  const rules = {
    length: stepOne.querySelector("[data-rule='length']"),
    uppercase: stepOne.querySelector("[data-rule='uppercase']"),
    lowercase: stepOne.querySelector("[data-rule='lowercase']"),
    number: stepOne.querySelector("[data-rule='number']"),
    special: stepOne.querySelector("[data-rule='special']")
  };

  if (passwordInput) {
    passwordInput.addEventListener('input', () => {
      const val = passwordInput.value;
      rules.length?.classList.toggle('valid', val.length >= 8);
      rules.uppercase?.classList.toggle('valid', /[A-Z]/.test(val));
      rules.lowercase?.classList.toggle('valid', /[a-z]/.test(val));
      rules.number?.classList.toggle('valid', /\d/.test(val));
      rules.special?.classList.toggle('valid', /[^A-Za-z0-9]/.test(val));
    });
  }

  // Signup Form Submission Validation
  const form = stepOne.querySelector(".signup-form");
  const emailInput = stepOne.querySelector("#email");
  const requiredFields = form.querySelectorAll("[required]");

  if (form) {
    form.addEventListener("submit", function (e) {
      let valid = true;

      const email = emailInput?.value.trim() || "";
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (!emailRegex.test(email)) {
        showError(emailInput, "Please enter a valid email address.");
        valid = false;
      } else {
        clearError(emailInput);
      }

      const password = passwordInput?.value || "";
      const requirements = [
        { rule: /.{8,}/, label: "8 characters or more" },
        { rule: /[A-Z]/, label: "1 uppercase letter" },
        { rule: /[a-z]/, label: "1 lowercase letter" },
        { rule: /\d/, label: "At least 1 number" },
        { rule: /[^A-Za-z0-9]/, label: "At least 1 special character" },
      ];

      const unmet = requirements.filter(r => !r.rule.test(password));
      if (unmet.length > 0) {
        showError(passwordInput, "The password doesn't meet all the criteria below.");
        valid = false;
      } else {
        clearError(passwordInput);
      }

      passwordInput.addEventListener("input", function(){
        const password = passwordInput?.value || "";
        let unmet = 0;

        requirements.forEach((req, index) => {
          const isMet = req.rule.test(password);
          if (!isMet) unmet++;
        });

        if (unmet === 0) {
          clearError(passwordInput);
        } else {
          showError(passwordInput, "The password doesn't meet all the criteria below.");
        }
      });

      const tosInput = document.querySelector('input[name="tos"]');
      if (!tosInput.checked) {
        showError(tosInput, "You must agree to the Terms of Service.");
        valid = false;
      } else {
        clearError(tosInput);
      }

      tosInput.addEventListener("change", () => {
        if (tosInput.checked) {
          clearError(tosInput);
        }
      });

      if (!valid) e.preventDefault();
      if (valid) {
        var me = $("#btnsubmit");
        var html = me.html();
        me
            .html($please_wait)
            .attr('disabled','disabled')
            .addClass('ld-ext-left running disabled')
            .append('<span class="ld ld-ring ld-cycle"></span>');

        var formURL = $(this).attr("action");
        var postData = $(this).serializeArray();
        $.ajax({
            url : formURL,
            type: "POST",
            data : postData,
            dataType:'json',
            cache: false,
            success:function(data)
            {
                me.removeAttr("disabled");
                me
                    .removeClass("ld-ext-left running disabled")
                    .removeAttr("disabled")
                    .find("span").remove();
                me.html(html);
                
                if (data.msg) {
                  alert(data.msg);
                }

                if (data.title) {
                    show_sweet_alert(data.title, data.text, data.class);
                }

                if (data.token) {
                    updateCsrfToken(data.token);
                    $csrf_hash = data.token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = $csrf_hash;
                }

                if (data.url) {
                  window.location.href = data.url;
                }
                if (data.response == 3) {
                  $('#suspended_div').removeClass("d-none");
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                show_sweet_alert('Error', jqXHR.status + textStatus + errorThrown, "error");
                me.removeAttr("disabled");
                me
                    .removeClass("ld-ext-left running disabled")
                    .removeAttr("disabled")
                    .find("span").remove();
                me.html(html);
                getToken().then(function(token) {
                    console.log('Got token:', token);
                    $csrf_hash = token;
                    $("#frmsignup input[name='csrf_token_name']").val(token);
                    $.ajaxSettings.headers["X-CSRF-Token"] = token;
                });
            }
        });
        e.preventDefault();
        e.unbind();
      }
    });

    emailInput?.addEventListener("blur", function () {
      const email = emailInput.value.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (email && !emailRegex.test(email)) {
        showError(emailInput, "Please enter a valid email address.");
      } else {
        clearError(emailInput);
      }
    });

    // Live validation
    requiredFields.forEach(field => {
      const eventType = field.type === "file" ? "change" : "input";
      field.addEventListener(eventType, () => {
        if (
          (field.type === "file" && field.files.length) ||
          (field.type !== "file" && field.value.trim() !== "")
        ) {
          clearError(field);
        }
      });
    });
  }

  // Helpers
  function showError(input, message) {
    if (!input) return;
    clearError(input);

    const wrapper = input.closest(".ds-input-wrapper");
    
    const errorEl = document.createElement("div");
    errorEl.className = "text-danger mt-1 small validation-error";
    errorEl.textContent = message;

    wrapper?.appendChild(errorEl);
    
    input.classList.add("is-invalid");
  }

  function clearError(input) {
    input?.classList.remove("is-invalid");
    const wrapper = input.closest(".ds-input-wrapper");
    const existingError = wrapper?.querySelector(".validation-error");
    existingError?.remove();
  }
});


// Step 2
document.addEventListener("DOMContentLoaded", function () {
  // const stepTwo = document.querySelector('[data-active-step="2"]') || document.querySelector('.step-2');
  const stepTwo = document.querySelector('[data-active-step="2"]');
  if (!stepTwo) return; // Exit if not on step 2

  const radios = stepTwo.querySelectorAll('input[name="businessType"]');
  const businessError = stepTwo.querySelector('#business-error');
  const otherRadio = stepTwo.querySelector('#radio-other');
  const otherInput = stepTwo.querySelector('#otherBusinessType');
  const otherInputWrapper = stepTwo.querySelector('#other-input-wrapper');
  const otherError = stepTwo.querySelector('#other-error');
  const form = stepTwo.querySelector('.signup-form');

  if (!form) return;

  // Real-time radio change handler
  radios.forEach((radio) => {
    radio.addEventListener('change', function () {
      if (radio.checked) {
        businessError?.classList.add('d-none');
      }

      if (radio.value === 'Other') {
        otherInputWrapper?.classList.add('show');
        setTimeout(() => otherInput?.focus(), 200);
      } else {
        otherInputWrapper?.classList.remove('show');
        otherError?.classList.add('d-none');
      }
    });
  });

  // Real-time input for "Other"
  otherInput?.addEventListener('input', function () {
    if (otherInput.value.trim() !== '') {
      otherError?.classList.add('d-none');
    }
  });

  // Final form submission validation
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const selectedRadio = stepTwo.querySelector('input[name="businessType"]:checked');
    let isValid = true;

    // Reset errors
    businessError?.classList.add('d-none');
    otherError?.classList.add('d-none');

    // Validate radio
    if (!selectedRadio) {
      businessError?.classList.remove('d-none');
      isValid = false;
    }

    // Validate "Other" if selected
    if (selectedRadio && selectedRadio.value === 'Other') {
      otherInputWrapper?.classList.add('show');
      if (otherInput.value.trim() === '') {
        otherError?.classList.remove('d-none');
        isValid = false;
      }
    }

    if (isValid) {
      console.log("Step 2 is valid. Proceeding...");
      window.location.href = "/signup-step-3.html";
      // Proceed to next step or submit
    }
  });
});


document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector(".signup-form.step-3");
  if (!form) return;

  const requiredFields = form.querySelectorAll("[required]");

  // Show errors on submit
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    let isValid = true;

    // Clear all previous errors
    clearAllErrors();

    requiredFields.forEach(field => {
      const name = field.name;
      const value = field.value.trim();

      if (field.type === "file") {
        if (!field.files || field.files.length === 0) {
          showError(field, field.dataset.error || "Please upload a file.");
          isValid = false;
        }
      } else if (name === "phone") {
        const phoneRegex = /^\d{10}$/;
        if (!phoneRegex.test(value)) {
          showError(field, field.dataset.error || "Enter a valid 10-digit phone number.");
          isValid = false;
        }
      } else {
        if (!value) {
          showError(field, field.dataset.error || "This field is required.");
          isValid = false;
        }
      }
    });

    if (isValid) {
      console.log("Step 3 validated.");
      window.location.href = "/signup-step-4.html";
      // form.submit(); or next step trigger
    }
  });

  // Live validation
  requiredFields.forEach(field => {
    const eventType = field.type === "file" ? "change" : "input";
    field.addEventListener(eventType, () => {
      if (
        (field.type === "file" && field.files.length) ||
        (field.type !== "file" && field.value.trim() !== "")
      ) {
        clearError(field);
      }
    });
  });

  function showError(field, message) {
    field.classList.add("is-invalid");

    const errorEl = document.createElement("div");
    errorEl.className = "text-danger mt-1 small validation-error";
    errorEl.textContent = message;

    // Check if input is inside input-group
    const groupWrapper = field.closest(".input-group");
    const insertAfter = groupWrapper || field;

    // Prevent duplicate errors
    const next = insertAfter.nextElementSibling;
    if (!next || !next.classList.contains("validation-error")) {
      insertAfter.parentNode.insertBefore(errorEl, insertAfter.nextSibling);
    }
  }

  function clearError(field) {
    field.classList.remove("is-invalid");

    const nextEl = field.nextElementSibling;
    if (nextEl && nextEl.classList.contains("validation-error")) {
      nextEl.remove();
    }
  }

  function clearAllErrors() {
    form.querySelectorAll(".is-invalid").forEach(el => el.classList.remove("is-invalid"));
    form.querySelectorAll(".validation-error").forEach(el => el.remove());
  }

  const fileInput = document.getElementById("idDocument");
  const fileNameDisplay = document.getElementById("ds-file-name-display");

  fileInput.addEventListener("change", function () {
    const fileName = this.files.length > 0 ? this.files[0].name : "";
    fileNameDisplay.value = fileName;
  });

});

document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.getElementById('annualPlanSwitch');
  if(!toggle){
    return;
  }

  const pricingElements = document.querySelectorAll('.ds-monthly-yearly');

  function updatePrices(isYearly) {
    pricingElements.forEach(el => {
      const monthly = parseInt(el.dataset.monthly, 10);
      const yearly = parseInt(el.dataset.yearly, 10);
      const priceEl = el.querySelector('.ds-price');
      const termEl = el.querySelector('.ds-term');

      if (isYearly) {
        priceEl.textContent = `$${yearly}`;
        termEl.textContent = '/yr';
      } else {
        priceEl.textContent = `$${monthly}`;
        termEl.textContent = '/mo';
      }
    });
  }

  // Initial state
  updatePrices(toggle.checked);
  // Toggle handler
  toggle.addEventListener('change', function () {
    updatePrices(this.checked);
  });
});

$(document).ready(function () {
    $("#btngooglelogin").click(function () {
        var url = $(this).data("url");
        $.getJSON(url, function (data) {
            window.location.href = data.url;
        });
    });
});

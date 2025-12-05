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
  const confirmPasswordInput = stepOne.querySelector("#confirm");
  const eyeIcon = stepOne.querySelector("#eye-icon");
  const passwordRulesEl = document.getElementById("password-rules");

  if (toggleBtn && passwordInput && eyeIcon) {
    toggleBtn.addEventListener("click", function () {
      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.src = $base_url + "/public/assets/icons/eye-slash.svg";
      } else {
        passwordInput.type = "password";
        eyeIcon.src = $base_url + "/public/assets/icons/eye.svg";
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
  const requiredFields = form.querySelectorAll("[required]");
  if (form) {
    form.addEventListener("submit", function (e) {
      let valid = true;

      const password = passwordInput?.value || "";
      const confirmPassword = confirmPasswordInput?.value || "";
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

      if (confirmPassword == "") {
        showError(confirmPasswordInput, "Please confirm your password");
        valid = false;
      } else {
        clearError(confirmPasswordInput);
      }

      confirmPasswordInput.addEventListener("input", function(){
        const confirmPassword = confirmPasswordInput?.value || "";
        if (confirmPassword !== "") {
          clearError(confirmPasswordInput);
        } else {
          showError(confirmPasswordInput, "Please confirm your password1");
        }
      });

      if (!valid) e.preventDefault();
      if (valid) {
        var me = $("#btnsubmit");
        var html = me.html();
        me
            .html("Please wait..")
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
        //e.unbind();
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
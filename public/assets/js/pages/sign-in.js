$.ajaxSetup( {
    headers: {
        'X-CSRF-Token': $csrf_hash
    }
});

document.addEventListener("DOMContentLoaded", function () {
  const LoginPage = document.querySelector(".login-page");
  if (!LoginPage) return;

  // Toggle Password Visibility
  const toggleBtn = LoginPage.querySelector(".toggle-password-btn");
  const passwordInput = LoginPage.querySelector("#password");
  const eyeIcon = LoginPage.querySelector("#eye-icon");

  if (toggleBtn && passwordInput && eyeIcon) {
    toggleBtn.addEventListener("click", function () {
      const isPw = passwordInput.type === "password";
      passwordInput.type = isPw ? "text" : "password";
      eyeIcon.src = isPw ? $app_url + "public/assets/icons/eye-slash.svg" : $app_url + "public/assets/icons/eye.svg";
    });
  }

  // Login form + validation
  const LoginForm = LoginPage.querySelector(".login-form");
  const emailInput = LoginPage.querySelector("#email");
  if (!LoginForm) return;

  // Live: password required
  if (passwordInput) {
    const validatePassword = () => {
      const v = passwordInput.value.trim();
      if (!v) {
        showError(passwordInput, "Please enter your password.");
        return false;
      }
      clearError(passwordInput);
      return true;
    };
    passwordInput.addEventListener("input", validatePassword);
    passwordInput.addEventListener("blur", validatePassword);
  }

  // Live: email format on blur + while typing clears error
  if (emailInput) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    emailInput.addEventListener("input", () => {
      // clear as soon as it *might* be valid
      if (emailInput.value.trim()) clearError(emailInput);
    });
    emailInput.addEventListener("blur", () => {
      const email = emailInput.value.trim();
      if (email && !emailRegex.test(email)) {
        showError(emailInput, "Please enter a valid email address.");
      } else {
        clearError(emailInput);
      }
    });
  }

  // Live: clear error when any required field gets value
  const requiredFields = LoginForm.querySelectorAll("[required]");
  requiredFields.forEach(field => {
    field.addEventListener(field.type === "file" ? "change" : "input", () => {
      if ((field.type === "file" && field.files.length) || (field.value.trim() !== "")) {
        clearError(field);
      }
    });
  });

  // Submit gate
  LoginForm.addEventListener("submit", function (e) {
    let valid = true;
    const email = emailInput?.value.trim() || "";
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailRegex.test(email)) {
      showError(emailInput, "Please enter a valid email address.");
      valid = false;
    } else clearError(emailInput);

    if (!passwordInput?.value.trim()) {
      showError(passwordInput, "Please enter your password.");
      valid = false;
    } else clearError(passwordInput);

    if (!valid) e.preventDefault();

    var me = $("#btnlogin");
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
                $("#frmlogin input[name='csrf_token_name']").val(data.token);
                $csrf_hash = data.token;
                $.ajaxSettings.headers["X-CSRF-Token"] = $csrf_hash;
            }

            if (data.response == 1)
                window.location.href = data.url;
            else if (data.response == 3) {
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
                $("#frmlogin input[name='csrf_token_name']").val(token);
                $.ajaxSettings.headers["X-CSRF-Token"] = token;
            });
        }
    });
    e.preventDefault();
    e.unbind();
  });

  // Helpers
  function showError(input, message) {
    if (!input) return;
    clearError(input);
    const wrapper = input.closest(".ds-input-wrapper") || input.parentElement;
    const el = document.createElement("div");
    el.className = "text-danger mt-1 small validation-error";
    el.textContent = message;
    el.setAttribute("aria-live", "polite");
    wrapper.appendChild(el);
    input.classList.add("is-invalid");
    input.setAttribute("aria-invalid", "true");
  }

  function clearError(input) {
    if (!input) return;
    input.classList.remove("is-invalid");
    input.removeAttribute("aria-invalid");
    const wrapper = input.closest(".ds-input-wrapper") || input.parentElement;
    wrapper?.querySelector(".validation-error")?.remove();
  }
});

let googleClient;

$(document).ready(function () {
    $("#btngooglelogin").click(function () {
        var url = $(this).data("url");
        $.getJSON(url, function (data) {
            window.location.href = data.url;
        });
    });
});

function handleCredentialResponse(response) {
    // Send ID token (JWT) to CodeIgniter backend
    $.ajax({
        url: $googleCallbackURL,
        type: "POST",
        dataType: 'json',
        data: { credential: response.credential },
        success: function (data) {
            window.location.href = data.url;
        },
        error: function () {
            alert("Google login failed!");
        }
    });
}

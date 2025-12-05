$(document).ready(function() {
    $.ajaxSetup( {
        headers: {
            'X-CSRF-Token': $csrf_hash
        }
    });

    var $steps = $(".step-container");
    var $panels = $(".step-panel");
    var currentIndex = 0;

    const $stepTwo = $('[data-active-step="2"]');
    const $radios = $stepTwo.find('input[name="business_type"]');
    const $businessError = $stepTwo.find('#business-error');
    const $otherRadio = $stepTwo.find('#radio-other');
    const $otherInput = $stepTwo.find('#otherBusinessType');
    const $otherInputWrapper = $stepTwo.find('#other-input-wrapper');
    const $otherError = $stepTwo.find('#other-error');
    const form = document.querySelector(".signup-form.step-3");

    // Select all required fields dynamically
    const $requiredFields = $("input[required], select[required], textarea[required]");

    // ✅ Resize active step height
    function adjustStepHeight() {
        let $active = $panels.eq(currentIndex);
        $steps.height($active.outerHeight());
        console.log($steps);
    }

    // ✅ Show given step
    function showStep(index) {
        let $current = $panels.eq(currentIndex);
        let $next = $panels.eq(index);

        $current.removeClass("is-current").addClass("is-prev");
        $next.removeClass("is-prev").addClass("is-current");

        currentIndex = index;

        setTimeout(adjustStepHeight, 50);
    }

    // Initialize height on page load
    $steps.height($panels.eq(0).outerHeight());

    // Show/hide "Other" input live
    $(document).on("change", "input[name='business_type']", function () {
        if ($(this).val() === "Other") {
            $otherInputWrapper.addClass("show");   // show input
        } else {
            $otherInputWrapper.removeClass("show"); // hide input
            $otherError.addClass("d-none");
        }

        // Recalc container height after DOM update
        setTimeout(() => {
            $steps.height($panels.eq(currentIndex).outerHeight());
        }, 10);
    });

    // Real-time radio change handler
    /*$radios.on('change', function () {
        if (this.checked) {
            $businessError.addClass('d-none');
        }

        if (this.value === 'Other') {
            $otherInputWrapper.addClass('show');
            setTimeout(() => $otherInput.focus(), 200);
        } else {
            $otherInputWrapper.removeClass('show');
            $otherError.addClass('d-none');
        }
    });*/

    // Real-time input for "Other"
    $otherInput.on('input', function () {
        if ($.trim($(this).val()) !== '') {
            $otherError.addClass('d-none');
        }
    });

    $(document).on('click', "#btnfirst", function (e) {
        e.preventDefault();

        var $selectedRadio = $("input[name='business_type']:checked");
        var isValid = true;

        // Reset errors
        $businessError.addClass('d-none');
        $otherError.addClass('d-none');

        // Validate radio
        if ($selectedRadio.length === 0) {
            $businessError.removeClass('d-none');
            isValid = false;
        }

        // Validate "Other" if selected
        if ($selectedRadio.length > 0 && $selectedRadio.val() === 'Other') {
            if ($.trim($otherInput.val()) === '') {
                $otherError.removeClass('d-none');
                isValid = false;
            }
        }

        // 🔑 Recalculate height after errors toggle
        setTimeout(() => {
            $steps.height($panels.eq(currentIndex).outerHeight());
        }, 10);

        if (isValid) {
            var nextIndex = currentIndex + 1;
            if (nextIndex < $panels.length) {
                showStep(nextIndex);
                goToNextStep();
            }
        }
    });

    $(document).on("click", "#btnsecond", function(e) {
        e.preventDefault();
        let isValid = true;

        // Clear all previous errors
        clearAllErrors($("#step-2"));

        $requiredFields.each(function () {
            const field = this;              // raw DOM element
            const $field = $(this);          // jQuery object
            const name = field.name;
            const value = $.trim($field.val());

            if (field.type === "file") {
                if (!field.files || field.files.length === 0) {
                    showError($field, $field.data('error') || "Please upload a file.");
                    isValid = false;
                }
            } else if (name === "phone") {
                const phoneRegex = /^\d{10}$/;
                if (!phoneRegex.test(value)) {
                    showError($field, $field.data('error') || "Enter a valid 10-digit phone number.");
                    isValid = false;
                }
            } else {
                if (!value) {
                    showError($field, $field.data('error') || "This field is required.");
                    isValid = false;
                }
            }
        });

        if (isValid) {
            submitForm(e);
        }
    });

    function getCurrentStep() {
        let current = $(".signup-timeline li.active").data("step");
        return current ? parseInt(current) : 1; // fallback to step 1
    }

    function updateTimeline(stepNumber) {
        $(".signup-timeline li").each(function () {
            let itemStep = parseInt($(this).data("step"));

            // reset
            $(this).removeClass("active completed");

            // completed
            if (itemStep < stepNumber) {
                $(this).addClass("completed");
            }

            // current
            if (itemStep === stepNumber) {
                $(this).addClass("active");
            }
        });
        adjustStepHeight();
    }

    // ✅ Example: go to next step
    function goToNextStep() {
        let currentStep = getCurrentStep();
        updateTimeline(currentStep + 1);
    }

    // ✅ Example: go to previous step
    function goToPrevStep() {
        let currentStep = getCurrentStep();
        updateTimeline(currentStep - 1);
    }

    // Helpers: accept DOM or jQuery
    function to$ (el) { return el && el.jquery ? el : $(el); }

    // Live validation
    $requiredFields.each(function () {
        const $field = $(this);
        const isFile = $field.is('[type="file"]');
        const evt = isFile ? 'change' : 'input';

        $field.on(evt, function () {
            const hasValue = isFile ? this.files.length > 0 : $.trim($field.val()) !== '';
            if (hasValue) clearError($field);
        });
    });

    function showError(field, message) {
        const $field = to$(field);
        $field.addClass('is-invalid');

        const $errorEl = $('<div class="text-danger mt-1 small validation-error"></div>').text(message);
        const $groupWrapper = $field.closest('.input-group');
        const $insertAfter = $groupWrapper.length ? $groupWrapper : $field;

        if (!$insertAfter.next().hasClass('validation-error')) {
            $insertAfter.after($errorEl);
        }
        adjustStepHeight();
    }

    function clearError(field) {
        const $field = to$(field);
        $field.removeClass('is-invalid');
        const $next = $field.next('.validation-error');
        if ($next.length) $next.remove();
        adjustStepHeight();
    }

    function clearAllErrors($form) {
        $form = to$($form);
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.validation-error').remove();
        adjustStepHeight();
    }

    // File input display
    $("#idDocument").on("change", function () {
        const fileName = this.files.length > 0 ? this.files[0].name : '';
        $('#ds-file-name-display').val(fileName);
    });

    function submitForm(e) {
        var me = $("#btnsecond");
        var html = me.html();
        me
            .html($please_wait)
            .attr('disabled','disabled')
            .addClass('ld-ext-left running disabled')
            .append('<span class="ld ld-ring ld-cycle"></span>');

        var formURL = $("#frmsignup").attr("action");
        var postData = new FormData($('#frmsignup')[0]);
        $.ajax({
            url : formURL,
            type: "POST",
            data : postData,
            dataType:'json',
            processData: false,
            contentType: false,
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
                  $("#frmsignup input[name='csrf_token_name']").val(data.token);
                  $csrf_hash = data.token;
                  $.ajaxSettings.headers["X-CSRF-Token"] = $csrf_hash;
                }

                if (data.url) {
                  window.location.href = data.url;
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
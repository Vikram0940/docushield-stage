$(document).ready(function() {
    $.ajaxSetup( {
        headers: {
            'X-CSRF-Token': $csrf_hash
        }
    });

    // Update both visible price + button data-price
    function updatePlanPrices() {
        $(".ds-monthly-yearly").each(function () {
            const $plan = $(this);
            const monthly = $plan.data("monthly");
            const yearly  = $plan.data("yearly");

            const $btn = $plan.closest(".card-body").find(".select-plan-btn");
            const $priceEl = $plan.find(".ds-price");
            const $termEl  = $plan.find(".ds-term");

            if ($("#annualPlanSwitch").is(":checked")) {
                // Annual
                $btn.data("price", yearly * 100); // Stripe needs cents
                $priceEl.text("$" + yearly);
                $termEl.text("/yr");
            } else {
                // Monthly
                $btn.data("price", monthly * 100);
                $priceEl.text("$" + monthly);
                $termEl.text("/mo");
            }
        });
    }

    // Run once on load
    updatePlanPrices();

    let selectedPlan = null;
    let dropinInstance = null;
    let btClientToken = null;

    // Toggle handler
    $("#annualPlanSwitch").on("change", function () {
        updatePlanPrices();
    });

    // When user clicks on a plan button
    $(".select-plan-btn").on("click", function () {
        var me = $(this);
        var html = me.html();
        me
            .html($please_wait)
            .attr('disabled','disabled')
            .addClass('ld-ext-left running disabled')
            .append('<span class="ld ld-ring ld-cycle"></span>');

        selectedPlan = $(this).data("plan");
        // Fetch client token from backend
        $.get($app_url + "braintree/getClientToken", function (tokenResponse) {
            me.removeAttr("disabled");
            me
                .removeClass("ld-ext-left running disabled")
                .removeAttr("disabled")
                .find("span").remove();
            me.html(html);
            if (dropinInstance) {
                dropinInstance.teardown(() => {
                    initDropin(tokenResponse.clientToken);
                });
            } else {
                initDropin(tokenResponse.clientToken);
            }
            if (tokenResponse.token) {
                $("#frmsignup input[name='csrf_token_name']").val(tokenResponse.token);
                $csrf_hash = tokenResponse.token;
                $.ajaxSettings.headers["X-CSRF-Token"] = $csrf_hash;
            }
            $("#paymentModal").modal("show");
        });
    });

    // Initialize drop-in
    function initDropin(clientToken) {
        btClientToken = clientToken;
        braintree.dropin.create({
            authorization: clientToken,
            container: '#dropin-container'
        }, function (err, instance) {
            if (err) return console.error(err);
            dropinInstance = instance;
        });
    }

    // On confirm payment
    $("#submit-button").on("click", function () {
        if (!dropinInstance) return;
        var me = $(this);
        var html = me.html();
        dropinInstance.requestPaymentMethod(function (err, payload) {
            if (err) return console.error(err);
            me
                .html($please_wait)
                .attr('disabled','disabled')
                .addClass('ld-ext-left running disabled')
                .append('<span class="ld ld-ring ld-cycle"></span>');
            $.post($app_url + "braintree/subscribe", {
                paymentMethodNonce: payload.nonce,
                planId: selectedPlan
            }, function (response) {
                me.removeAttr("disabled");
                me
                    .removeClass("ld-ext-left running disabled")
                    .removeAttr("disabled")
                    .find("span").remove();
                me.html(html);

                if (response.msg) {
                    alert(response.msg);
                }
                if (response.title) {
                    show_sweet_alert(response.title, response.text, response.class).then(function(val) {
                        if (val.value) {
                            if (response.success) {
                                $("#paymentModal").modal("hide");
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                }
                            }
                            else {
                                if (dropinInstance) {
                                    dropinInstance.teardown(() => {
                                        initDropin(btClientToken);
                                    });
                                } else {
                                    initDropin(btClientToken);
                                }
                            }
                        }
                    });
                }

                if (response.token) {
                    $("#frmsignup input[name='csrf_token_name']").val(response.token);
                    $csrf_hash = response.token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = $csrf_hash;
                }
            }, "json").fail(function(res) {
                me.removeAttr("disabled");
                me
                    .removeClass("ld-ext-left running disabled")
                    .removeAttr("disabled")
                    .find("span").remove();
                me.html(html);
                if (dropinInstance) {
                    dropinInstance.teardown(() => {
                        initDropin(btClientToken);
                    });
                } else {
                    initDropin(btClientToken);
                }
                //console.log(res.message);
                show_sweet_alert("Failed!", "Sorry! unable to process your payment.", "error");
                getToken().then(function(token) {
                    console.log('Got token:', token);
                    $csrf_hash = token;
                    $("#frmsignup input[name='csrf_token_name']").val(token);
                    $.ajaxSettings.headers["X-CSRF-Token"] = token;
                });
            });
        });
    });
});
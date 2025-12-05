$(document).ready(function () {
	$(document).on("submit", "#frmdocument", function(e) {
        var me = $('#frmdocument #btnsubmit');
        var formURL = $(this).attr('action');
        var postData = $(this).serializeArray();
        var html = me.html();

        me
            .html($loading)
            .attr('disabled','disabled')
            .addClass('ld-ext-left running disabled')
            .append('<span class="ld ld-ring ld-cycle"></span>');

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
                    .removeClass("ld-over running disabled")
                    .removeAttr("disabled")
                    .find("span").remove();
                me.html(html);

                if (data.msg) {
                    alert(data.msg);
                }

                if (data.title) {
                    show_sweet_alert(data.title, data.text, data.class).then(function(val) {
                        if (val.value) {
                            if (data.response == 1) {
                                if (cTable) {
                                    cTable.ajax.url(gURL).load(null, false);
                                }
                                if (data.recent_projects && $("#recent_projects").length > 0) {
                                    $("#recent_projects").html(data.recent_projects);
                                }
                                $("#projectModal").modal("hide");
                            }                         
                        }
                    });
                }

                if (data.token) {
                    $csrf_hash = data.token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = $csrf_hash;
                    updateCsrfToken($csrf_hash);
                }

                if (data.url) {
                    window.location.href = data.url;
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                getToken().then(function(token) {
                    $csrf_hash = token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = token;
                });
                show_sweet_alert("Failed!", $app_error, "error");
                me.removeAttr("disabled");
                me
                    .removeClass("ld-over running disabled")
                    .removeAttr("disabled")
                    .find("span").remove();
                me.html(html);
            }
        });
        e.preventDefault();
        e.unbind();
    });

    $(document).on("click", "#btnshare", function(e) {
        var geturl = $(this).data("geturl");
        var url = $(this).data("url");
        var event = $(this).data("event");
        document.dispatchEvent(new CustomEvent("eventReview", {
            detail: { geturl, url, event }
        }));
    });

    // Trigger after form submission
    document.addEventListener("eventReview", function (e) {
        $("#documentModal").modal("show");
        $("#projectModal #sharePermission").addClass("d-none");
        $("#projectModal #copyProjectLink").addClass("d-none");

        var event = e.detail.event;
        var getURL = e.detail.geturl;
        var formURL = e.detail.url;
        console.log(event);

        if (event == "share") {
            $("#documentModal #btnsubmit").html("Send");
            $("#documentModal #newProjectLabel").html("Share Document");
            $("#documentModal #sharePermission").addClass("d-none");
            $("#documentModal #copyProjectLink").addClass("d-none");
        }
        else if (event == "review") {
            $("#documentModal #btnsubmit").html("Send");
            $("#documentModal #newProjectLabel").html("Send For Review");
            $("#documentModal #sharePermission").removeClass("d-none");
            $("#documentModal #copyProjectLink").removeClass("d-none");
        }
        
        $("#documentModal").LoadingOverlay("show", {
            background  : "rgba(255, 255, 255, 0.8)"
        });
        $.getJSON(getURL, function(data) {
            $("#myUsersList .collaborators-list").html(data.users);
            if (data.invitees != "") {
                $("#documentModal #collaborators_list").removeClass("d-none");
            }
            $("#documentModal #collaborators_list .collaborators-list").html(data.invitees);
            $("#frmreview").attr("action", formURL);
            $("#documentModal").LoadingOverlay("hide", true);
        }).fail(function(res) {
            console.log(res);
            $("#documentModal").LoadingOverlay("hide", true);
            show_sweet_alert("Failed!", "Sorry! something went wrong", "error").then(function(val) {
                $("#documentModal").modal("hide");
            });
        });
    });

    $('#documentModal').on('hide.bs.modal', function (e) {
        $('#frmproject').trigger("reset");
        $('#frmproject').attr('action', "");
        $("#frmproject").removeClass("was-validated");
        $("#collaborators_list").addClass("d-none");
        $("#collaborators_list .collaborators-list").html("");
        $("#projectModal #btnsubmit").html("Create Project");
        $("#projectModal #newProjectLabel").html("Create a New Project");
    });

    $("#collaborator_email").on("keyup", function () {
        let value = $(this).val().toLowerCase();

        $(".collabs-dd-wrapper .collaborators-list li").filter(function () {
            let name = $(this).find(".name").text().toLowerCase();
            let email = $(this).find(".email").text().toLowerCase();

            // Show item if matches name or email
            $(this).toggle(name.indexOf(value) > -1 || email.indexOf(value) > -1);
        });

        // hide dropdown if nothing matches
        /*if ($(".collaborators-list li:visible").length === 0) {
            $(".collaborators-list").hide();
        } else {
            $(".collaborators-list").show();
        }*/
    });

    $(document).on("click", ".my_user", function() {
        let email = $(this).find(".email").text().trim();
        let name = $(this).find(".name").text().trim();

        // Append selected user to another section
        /*$(".existing-collabs .collaborators-list").append(`
            <li>
                <div class="avatar">
                    <img src="https://i.pravatar.cc/100?u=${email}" class="rounded-circle">
                </div>
                <div class="collaborator">
                    <div class="name">${name}</div>
                    <div class="email">${email}</div>
                </div>
            </li>
        `);*/

        // Hide dropdown list
        $(".collabs-dd-wrapper .collabs-list").removeClass("d-block").addClass("d-none");

        // Clear input
        $("#collaborator_email").val(email);

        // Hide dropdown list
        $("#addCollabs").focus();
        //$(".collaborators-list").hide();

        // Remove selected from dropdown (so it won’t appear again)
        $(this).remove();
    });

    $(document).on("click", "#addCollabs", function() {
        var me = $(this);
        var formURL = $(this).data('url');

        // Get CSRF token from meta
        var csrfName = $('meta[name="csrf-name"]').attr('content');
        var csrfHash = $('meta[name="csrf-hash"]').attr('content');

        var postData = {
            email: $("#collaborator_email").val(),
            permission: $("#permissions").val(),
            csrfName: csrfHash
        }
        var html = me.html();
        me
            .html($loading)
            .attr('disabled','disabled')
            .addClass('ld-ext-left running disabled')
            .append('<span class="ld ld-ring ld-cycle"></span>');

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
                    .removeClass("ld-over running disabled")
                    .removeAttr("disabled")
                    .find("span").remove();
                me.html(html);

                if (data.msg) {
                    alert(data.msg);
                }

                if (data.token) {
                    $csrf_hash = data.token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = $csrf_hash;
                    updateCsrfToken($csrf_hash);
                }

                if (data.title) {
                    show_sweet_alert(data.title, data.text, data.class);
                }

                if (data.html) {
                    // Append selected user to another section
                    $("#collaborators_list").removeClass("d-none");
                    $("#collaborators_list .collaborators-list").append(data.html);

                    let email = $("#collaborator_email").val();
                    $("#collaborator_email").val('');
                    // Find and remove the matching user from the list
                    $(".collabs-dd-wrapper .collabs-list li").each(function() {
                        let liEmail = $(this).data("email").toLowerCase();
                        if (liEmail === email.toLowerCase()) {
                            $(this).remove();  // remove user from list
                        }
                    });                    
                }

                if (data.url) {
                    window.location.href = data.url;
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                getToken().then(function(token) {
                    $csrf_hash = token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = token;
                });
                show_sweet_alert("Failed!", $app_error, "error");
                me.removeAttr("disabled");
                me
                    .removeClass("ld-over running disabled")
                    .removeAttr("disabled")
                    .find("span").remove();
                me.html(html);
            }
        });
        e.preventDefault();
        e.unbind();        
    });

    $(document).on("submit", "#frmreview", function(e){

        var me = $('#frmreview #btnsubmit');
        var formURL = $(this).attr('action');
        //var postData = $("#frmdocument, #frmreview").serializeArray();

        // ✅ Ensure CKEditor content is synced
        if (typeof CKEDITOR !== 'undefined') {
            for (const instance in CKEDITOR.instances) {
                CKEDITOR.instances[instance].updateElement();
            }
        }

        // Serialize both forms
        var form1Data = $('#frmdocument').serializeArray();
        var form2Data = $('#frmreview').serializeArray();
        // Combine them
        var postData = form1Data.concat(form2Data);

        var html = me.html();

        me
            .html($loading)
            .attr('disabled','disabled')
            .addClass('ld-ext-left running disabled')
            .append('<span class="ld ld-ring ld-cycle"></span>');

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
                    .removeClass("ld-over running disabled")
                    .removeAttr("disabled")
                    .find("span").remove();
                me.html(html);

                if (data.msg) {
                    alert(data.msg);
                }

                if (data.title) {
                    show_sweet_alert(data.title, data.text, data.class).then(function(val) {
                        if (val.value) {
                            if (data.response == 1) {
                                if (cTable) {
                                    cTable.ajax.url(gURL).load(null, false);
                                }
                                if (data.recent_projects && $("#recent_projects").length > 0) {
                                    $("#recent_projects").html(data.recent_projects);
                                }
                                $("#documentModal").modal("hide");
                            }                         
                        }
                    });
                }

                if (data.token) {
                    $csrf_hash = data.token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = $csrf_hash;
                    updateCsrfToken($csrf_hash);
                }

                if (data.url) {
                    window.location.href = data.url;
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                getToken().then(function(token) {
                    $csrf_hash = token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = token;
                });
                show_sweet_alert("Failed!", $app_error, "error");
                me.removeAttr("disabled");
                me
                    .removeClass("ld-over running disabled")
                    .removeAttr("disabled")
                    .find("span").remove();
                me.html(html);
            }
        });
        e.preventDefault();
        e.unbind();
    });

    // Trigger after return to draft
    document.addEventListener("returnToDraft", function (e) {
        var formURL = e.detail.url;
        var me = $(e.detail.button);
        
        var html = me.html();
        me
            .html($loading)
            .attr('disabled','disabled')
            .addClass('ld-ext-left running disabled')
            .append('<span class="ld ld-ring ld-cycle"></span>');

        $.ajax({
            url : formURL,
            type: "PUT",
            //data : postData,
            dataType:'json',
            cache: false,       
            success:function(data)
            {
                me.removeAttr("disabled");
                me
                    .removeClass("ld-over running disabled")
                    .removeAttr("disabled")
                    .find("span").remove();
                me.html(html);

                if (data.msg) {
                    alert(data.msg);
                }

                if (data.title) {
                    show_sweet_alert(data.title, data.text, data.class).then(function(val) {
                        if (val.value) {
                            if (data.response == 1) {
                                $("#returndraftModal").modal("hide");
                            }                         
                        }
                    });
                }

                if (data.token) {
                    $csrf_hash = data.token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = $csrf_hash;
                    updateCsrfToken($csrf_hash);
                }

                if (data.url) {
                    window.location.href = data.url;
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                getToken().then(function(token) {
                    $csrf_hash = token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = token;
                });
                show_sweet_alert("Failed!", $app_error, "error");
                me.removeAttr("disabled");
                me
                    .removeClass("ld-over running disabled")
                    .removeAttr("disabled")
                    .find("span").remove();
                me.html(html);
            }
        });
        e.preventDefault();
    });

    // Trigger after accept and sign
    document.addEventListener("acceptAndSign", function (e) {
        var formURL = e.detail.url;
        var me = $(e.detail.button);
        
        var html = me.html();
        me
            .html($loading)
            .attr('disabled','disabled')
            .addClass('ld-ext-left running disabled')
            .append('<span class="ld ld-ring ld-cycle"></span>');

        $.ajax({
            url : formURL,
            type: "PUT",
            //data : postData,
            dataType:'json',
            cache: false,       
            success:function(data)
            {
                me.removeAttr("disabled");
                me
                    .removeClass("ld-over running disabled")
                    .removeAttr("disabled")
                    .find("span").remove();
                me.html(html);

                if (data.msg) {
                    alert(data.msg);
                }

                if (data.title) {
                    show_sweet_alert(data.title, data.text, data.class).then(function(val) {
                        if (val.value) {
                            if (data.response == 1) {
                                $("#acceptsignModal").modal("hide");
                            }                         
                        }
                    });
                }

                if (data.token) {
                    $csrf_hash = data.token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = $csrf_hash;
                    updateCsrfToken($csrf_hash);
                }

                if (data.url) {
                    window.location.href = data.url;
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                getToken().then(function(token) {
                    $csrf_hash = token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = token;
                });
                show_sweet_alert("Failed!", $app_error, "error");
                me.removeAttr("disabled");
                me
                    .removeClass("ld-over running disabled")
                    .removeAttr("disabled")
                    .find("span").remove();
                me.html(html);
            }
        });
        e.preventDefault();
    });

    // Trigger after accept and sign
    document.addEventListener("duplicateDocument", function (e) {
        var formURL = e.detail.url;
        var me = $(e.detail.button);
        
        var html = me.html();
        me
            .html($loading)
            .attr('disabled','disabled')
            .addClass('ld-ext-left running disabled')
            .append('<span class="ld ld-ring ld-cycle"></span>');

        $.ajax({
            url : formURL,
            type: "PUT",
            //data : postData,
            dataType:'json',
            cache: false,       
            success:function(data)
            {
                me.removeAttr("disabled");
                me
                    .removeClass("ld-over running disabled")
                    .removeAttr("disabled")
                    .find("span").remove();
                me.html(html);

                if (data.msg) {
                    alert(data.msg);
                }

                if (data.title) {
                    show_sweet_alert(data.title, data.text, data.class).then(function(val) {
                        if (val.value) {
                            if (data.response == 1) {
                                $("#acceptsignModal").modal("hide");
                            }                         
                        }
                    });
                }

                if (data.token) {
                    $csrf_hash = data.token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = $csrf_hash;
                    updateCsrfToken($csrf_hash);
                }

                if (data.url) {
                    window.location.href = data.url;
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                getToken().then(function(token) {
                    $csrf_hash = token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = token;
                });
                show_sweet_alert("Failed!", $app_error, "error");
                me.removeAttr("disabled");
                me
                    .removeClass("ld-over running disabled")
                    .removeAttr("disabled")
                    .find("span").remove();
                me.html(html);
            }
        });
        e.preventDefault();
    });
});
$('.needs-validation').on('submit', function(e) {
    if (!this.checkValidity()) {
        var errorElements = document.querySelectorAll(".form-control:invalid");
        console.log(errorElements);
        //$('html,body').animate({scrollTop: $('.was-validated .form-control:invalid').first().offset().top - 120},'slow');
        $('html,body').animate({scrollTop: errorElements[0].offsetTop}, 'slow');
        e.preventDefault();
        e.stopPropagation();
    }
      /*if (!this.checkValidity()) {
        $('html,body').animate({scrollTop: $('.was-validated .form-control:invalid').first().offset().top - 120},'slow');
        e.preventDefault();
        e.stopPropagation();
      }*/

    $(this).addClass('was-validated');
});

setTimeout(function() {
    $('.alert-dismissible').fadeOut(500);
}, 10000);

$(document).on("click", ".markfavorite", function(e) {
    var me = $(this);
    var formURL = $app_url + "/user/favorites/add-remove";

    // Get CSRF token from meta
    var csrfName = $('meta[name="csrf-name"]').attr('content');
    var csrfHash = $('meta[name="csrf-hash"]').attr('content');

    var postData = {
        source_id  : me.data("id"),
        source_type: me.data("type"),
        csrfName: csrfHash
    }
    var html = me.html();
    me
        .html("")
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
                show_sweet_alert(data.title, data.text, data.class).then(function(val) {
                    if (!val.value) return;

                    const isAddEvent = data.event === "add";
                    const shouldStar = (data.response == 1) === isAddEvent;

                    me.toggleClass("starred", shouldStar);

                    // 🔥 trigger global event instead of direct cTable call
                    $(document).trigger("tableReload", [me, data]);
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
            if (me.data("event") == "add") {
                me.removeClass("starred");
            }
            else {
                me.addClass("starred");
            }
        }
    });
    e.preventDefault();
    e.unbind();      
});

$(document).on('click', '.delete', function(e) {
    e.preventDefault(); // stop default click action

    var me = $(this);
    var formURL = me.data("url");
    var method  = me.data("method") || 'DELETE'; // default fallback
    var html    = me.html();
    var confirmation = me.data("message") || "Are you sure? you want to proceed";

    confirmBox("Confirmation", confirmation, function(confirm) {
        if (!confirm) return;

        me
            .html(".")
            .attr('disabled','disabled')
            .addClass('ld-ext-left running disabled')
            .append('<span class="ld ld-ring ld-cycle"></span>');

        $.ajax({
            url: formURL,
            type: method,
            dataType: 'json',
            cache: false,
            success: function(data) {
                resetButton(me, html);

                if (data.msg) {
                    alert(data.msg);
                }

                if (data.title) {
                    show_sweet_alert(data.title, data.text, data.class).then(function(val) {
                        if (val.value && data.response == 1) {
                            // 🔥 trigger global event instead of direct cTable call
                            $(document).trigger("itemDeleted", [me, data]);
                            $(document).trigger("tableReload", [me, data]);
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
            error: function() {
                getToken().then(function(token) {
                    $csrf_hash = token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = token;
                });

                show_sweet_alert("Failed!", $app_error, "error");
                resetButton(me, html);
            }
        });
    });
});

$(document).on('click', '#btnBulkDelete', function(e) {
    e.preventDefault(); // stop default click action

    var rowIds = []; var checkCount = 0;
    $( ".chkrow" ).each(function( index ) {
        if ($( this ).prop('checked')) {
            checkCount += 1;
            rowIds.push($(this).val());
        }
    });
    if (checkCount == 0) {
        show_sweet_alert("Failed!", "Please select at least one row to proceed", "error");
        return false;
    }
    var postData = {rows: rowIds.join(',')};

    var me = $(this);
    var formURL = $app_url + "/" +  me.data("url");
    var html    = me.html();
    var confirmation = me.data("message") || "Are you sure? you want to proceed";
    var loadingMsg = me.data("loading") || "Please wait..";

    confirmBox("Confirmation", confirmation, function(confirm) {
        if (!confirm) return;

        me
            .html(loadingMsg)
            .attr('disabled','disabled')
            .addClass('ld-ext-left running disabled')
            .append('<span class="ld ld-ring ld-cycle"></span>');

        $.ajax({
            url: formURL,
            type: "POST",
            data: postData,
            dataType: 'json',
            cache: false,
            success: function(data) {
                resetButton(me, html);

                if (data.msg) {
                    alert(data.msg);
                }

                if (data.title) {
                    show_sweet_alert(data.title, data.text, data.class).then(function(val) {
                        if (val.value && data.response == 1) {
                            // 🔥 trigger global event instead of direct cTable call
                            $(document).trigger("itemDeleted", [me, data]);
                            $(document).trigger("tableReload", [me, data]);
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
            error: function() {
                getToken().then(function(token) {
                    $csrf_hash = token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = token;
                });

                show_sweet_alert("Failed!", $app_error, "error");
                resetButton(me, html);
            }
        });
    });
});

$(document).on('change', '.chkall', function(e) {
    $(".chkrow").prop("checked", $(this).prop("checked"));
    if ($("#deleteRowButton").length > 0) {
        if ($(this).prop("checked")) {
            $("#deleteRowButton").removeClass("d-none");
        }
        else {
            $("#deleteRowButton").addClass("d-none");
        }
    }
});

$(document).on('change', '.chkrow', function(e) {
    if ($("#deleteRowButton").length > 0) {
        if ($(this).prop("checked")) {
            $("#deleteRowButton").removeClass("d-none");
        }
        else {
            $("#deleteRowButton").addClass("d-none");
        }
    }
});

$(document).on('click', '.avatar-more', function(e) {
    var name = $(this).data("name");
    var getURL = $app_url + $(this).data("geturl");
    var formURL = $(this).data("url");
    $.getJSON(getURL, function(data) {
        $("#emptyModal #emptyModalLabel").html(name);
        $("#emptyModal .modal-body").html(data.html);
        $("#emptyModal .modal-footer").addClass("d-none");
        //$("#emptyModal #frmempty").attr("action", $app_url + formURL);
        $("#emptyModal").modal("show");
    });    
});

$(document).on('click', '.avatar_info', function(e) {
    var name = $(this).data("name");
    var getURL = $app_url + $(this).data("geturl");
    var formURL = $(this).data("url");
    $.getJSON(getURL, function(data) {
        $("#emptyModal #emptyModalLabel").html(name);
        $("#emptyModal .modal-body").html(data.html);
        $("#emptyModal #frmempty").attr("action", $app_url + formURL);
        $("#emptyModal").modal("show");
    });    
});

$('#emptyModal').on('hide.bs.modal', function (e) {
    $("#emptyModal #frmempty").removeAttr("action");
    $("#emptyModal .modal-body").html('');
    $("#emptyModal #emptyModalLabel").html();
});

/**
 * Helper function to reset button state
 */
function resetButton(btn, originalHtml) {
    btn.removeAttr("disabled")
       .removeClass("ld-over running disabled")
       .find("span").remove();
    btn.html(originalHtml);
}

function confirmBox(title, text, callback)
{
    Swal.fire({
        title: title,
        text: text,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        buttonsStyling: false,
        padding: '20px',
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-secondary ms-2',
        confirmButtonText: "Yes",
        cancelButtonText: "No"
    }).then((confirmed) => {
        if (confirmed) {
            callback(confirmed && confirmed.value == true);
        }
    });
}

$(document).on("submit", "#frmdata", function(e){

    var me = $('#frmdata #btnsubmit');
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
            resetButton(me, html);

            if (data.msg) {
                alert(data.msg);
            }

            if (data.title) {
                show_sweet_alert(data.title, data.text, data.class).then(function(val) {
                    if (val.value) {
                        if (data.response == 1) {
                            // 🔥 trigger global event instead of direct cTable call
                            $(document).trigger("frmUpdated", [me, data]);
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
            resetButton(me, html);
        }
    });
    e.preventDefault();
    e.unbind();
});

$(document).on("submit", "#frmempty", function(e){

    var me = $('#frmempty #btnsubmit');
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
            resetButton(me, html);

            if (data.msg) {
                alert(data.msg);
            }

            if (data.title) {
                show_sweet_alert(data.title, data.text, data.class).then(function(val) {
                    if (val.value) {
                        if (data.response == 1) {
                            // 🔥 trigger global event instead of direct cTable call
                            $(document).trigger("frmUpdated", [me, data]);
                            $("#emptyModal").modal("hide");
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
            resetButton(me, html);
        }
    });
    e.preventDefault();
    e.unbind();
});
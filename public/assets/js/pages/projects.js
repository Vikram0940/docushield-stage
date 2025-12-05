$(document).ready(function () {
    var cTable;
    let currentTab = "active"; // default
    var filter_Modified = 1;
    var gURL = "projects/get-json-data?type=" + currentTab;
    if ($("#tblprojects").length > 0) {
        $.fn.dataTable.ext.errMode = function ( settings, helpPage, message ) { 
            show_sweet_alert("Sorry!", message, "error");
            $("#tblprojects").LoadingOverlay("hide", true);
            getToken().then(function(token) {
                console.log('Got token:', token);
                $csrf_hash = token;
                $.ajaxSettings.headers["X-CSRF-Token"] = token;
            });
        };
        
        $.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings ) {
            return {
                "iStart": oSettings._iDisplayStart,
                "iEnd": oSettings.fnDisplayEnd(),
                "iLength": oSettings._iDisplayLength,
                "iTotal": oSettings.fnRecordsTotal(),
                "iFilteredTotal": oSettings.fnRecordsDisplay(),
                "iPage": Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
                "iTotalPages": Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
            };
        }

        cTable = $('#tblprojects').DataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sServerMethod": "POST",
            'sPaginationType': 'full_numbers',
            "bFilter": false,
            "bPaginate" : true,
            "aLengthMenu": [ [5, 10, 20, 50, 100, 200, 500, 1000], [5, 10, 20, 50, 100, 200, 500, 1000] ],
            "iDisplayLength": 20,
            "sAjaxSource": gURL,
            //"aaData": data,
            "bSort": true,
            "aaSorting": [], // no default sorting
            info: false, // hide default "Showing 1 to X of Y entries"
            "bStateSave": false,
            "bDeferRender": true,
            "dom": '<"top"f>rt<"bottom"ip><"clear">', // removes lengthMenu from top
            'columnDefs': [
                { "targets": "_all", "orderable": false }, // <-- no column is clickable for sort
                {
                    "targets": 0, // your case first column
                    "width": "3%",
                    "className": "text-center",
                    "visible": ($role=="ROLE_ADMIN") ? true : false
                },
                {
                    "targets": 1, // your case first column
                    "width": "3%",
                    "className": "text-center"
                },
                {
                    "targets": 2, // your case first column
                    "width": "18%"
                },
                {
                    "targets": 3, // option
                    "width": "18%",
                    "visible": false
                },
                {
                    "targets": 4, // option
                    "width": "40%"
                },
                {
                    "targets": 5, // option
                    "className": "text-center"
                },
                {
                    "targets": 6, // option
                    "width": "12%"
                },
                {
                    "targets": 7, // option
                    "width": "10%"
                },
                {
                    "targets": 8, // option
                    "width": "18%",
                    "className": "text-center",
                    "visible": ($role=="ROLE_ADMIN") ? true : false
                },
                {
                    "targets": 9, // option
                    "width": "22%",
                    "className": "text-center",
                    "visible": false
                },
                {
                    "targets": 10, // option
                    "width": "22%",
                    "className": "text-center",
                    "visible": false
                },
                {
                    "targets": 11, // option
                    "width": "22%",
                    "className": "text-center",
                    "visible": false
                },
                {
                    "targets": 12, // option
                    "width": "22%",
                    "className": "text-center",
                    "visible": false
                }
            ],
            'preDrawCallback': function(settings) {
                $("#tblprojects").LoadingOverlay("show", {
                    background  : "rgba(255, 255, 255, 0.8)",
                    zIndex: 15000
                });
                //$.LoadingOverlay("show");
            },
            "drawCallback": function( settings ) {
                $("#tblprojects").LoadingOverlay("hide", true);
                if (settings.json.token) {
                    $csrf_hash = settings.json.token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = $csrf_hash;
                    updateCsrfToken($csrf_hash);
                }
                if (settings.json.gridProjects) {
                    $("#grid-view").html(settings.json.gridProjects);
                }
                $('#tblprojects [data-bs-toggle="tooltip"]').tooltip();
                if (settings.json.iTotalRecords > 0) {
                    $("#projects_not_exist").addClass("d-none");
                    $("#projects_exist").removeClass("d-none");
                }
                /*else {
                    $("#projects_not_exist").removeClass("d-none");
                    $("#projects_exist").addClass("d-none");
                }*/
            }
        });

        // Tab change listener
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            /*currentTab = $(e.target).data('type'); // active | archived | etc.
            var gURL = "projects/get-json-data?type=" + currentTab;
            cTable.ajax.url(gURL).load(null, false);*/
            filter($(e.target).data('type'));
        });

        $('#rowsPerPage').on('change', function() {
            cTable.page.len($(this).val()).draw();
        });

        // Custom pagination buttons
        $('#prevPage').on('click', function () {
            if (cTable.page.info().page > 0) {
                cTable.page('previous').draw('page');
            }
        });

        $('#nextPage').on('click', function () {
            if (cTable.page.info().page < cTable.page.info().pages - 1) {
                cTable.page('next').draw('page');
            }
        });

        // Update page info
        function updatePageInfo() {
            var info = cTable.page.info();
            $('#pageInfo').text('Page ' + (info.page + 1) + ' of ' + info.pages);
        }

        // Run on load + every redraw
        updatePageInfo();
        cTable.on('draw', updatePageInfo);

        // Attach click handler to dropdown sort items
        $(document).on('click', '.dropdown-item[data-col]', function (e) {
            e.preventDefault();

            let colIndex = $(this).data('col');   // column index
            let orderDir = $(this).data('order'); // asc | desc
            console.log(colIndex + " " + orderDir);
            cTable.order([colIndex, orderDir]).draw();
        });

        // Attach click handler to checkbox sort items
        $(document).on('change', '.checkbox-item[data-col]', function (e) {
            e.preventDefault();
            if ($(this).prop("checked")) {
                let colIndex = $(this).data('col');   // column index
                let orderDir = $(this).data('order'); // asc | desc
                console.log(colIndex + " " + orderDir);
                cTable.order([colIndex, orderDir]).draw();
            }
            else {
                cTable.ajax.url(gURL).load();
            }
        });

        $(document).on('change', '.filterselect', function (e) {
            filter();
        });

        // Attach click handler to checkbox sort items
        $(document).on('change', '#cmbpopularity', function (e) {
            e.preventDefault();
            var orderDir = $(this).val();
            cTable.order([5, orderDir]).draw();
        });
    }

    function filter($type = "") {
        var paramArr = []; var sort = [];
        if ($("#chkdocuments").prop("checked")) {
            sort.push("total_documents");
        }
        if ($("#chkfiles").prop("checked")) {
            sort.push("total_files");
        }
        if ($("#chkimages").prop("checked")) {
            sort.push("total_images");
        }
        if ($("#cmblastmodified").val() != "") {
            paramArr.push("updated_at=" + $("#cmblastmodified").val());
        }
        if ($("#cmbpeople").val() != "") {
            paramArr.push("people=" + $("#cmbpeople").val());
        }
        if ($("#cmbstatus").val() != "") {
            paramArr.push("other_status=" + $("#cmbstatus").val());
        }
        if ($("#cmbtype").val() != "") {
            paramArr.push("storage_type=" + $("#cmbtype").val());
        }
        if (sort.length > 0) {
            paramArr.pus("sort=" + sort.join(","));
        }
        if ($type != "") {
            paramArr.push("type=" + $type);
        }
        var param = paramArr.join("&");
        gURL = "projects/get-json-data?type=" + currentTab + "&" + param;
        cTable.ajax.url(gURL).load();
    }

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

    $(document).on("submit", "#frmproject", function(e){

        var me = $('#frmproject #btnsubmit');
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

    $(document).on("click", "#btnaddproject", function(e) {
        $("#projectModal").modal("show");
        $("#projectModal .project-select").addClass("d-none");
        $("#projectModal #project").removeAttr("required");

        var getURL = $(this).data("geturl");
        var formURL = $(this).data("url");
        $("#projectModal").LoadingOverlay("show", {
            background  : "rgba(255, 255, 255, 0.8)"
        });
        $.getJSON(getURL, function(data) {
            $("#myUsersList .collaborators-list").html(data.users);
            $("#frmproject").attr("action", formURL);
            $("#projectModal").LoadingOverlay("hide", true);
        }).fail(function(res) {
            console.log(res);
            $("#projectModal").LoadingOverlay("hide", true);
            show_sweet_alert("Failed!", "Sorry! something went wrong", "error").then(function(val) {
                $("#projectModal").modal("hide");
            });
        });
    });

    $('#projectModal').on('hide.bs.modal', function (e) {
        $('#frmproject').trigger("reset");
        $('#frmproject').attr('action', "");
        $("#frmproject").removeClass("was-validated");
        $("#collaborators_list").addClass("d-none");
        $("#collaborators_list .collaborators-list").html("");
        $("#projectModal #btnsubmit").html("Create Project");
        $("#projectModal #projectname").attr("required", "required");
        $("#projectModal #projectname").removeClass("d-none");
        $("#projectModal #projectdesc").attr("required", "required");
        $("#projectModal #projectdesc").removeClass("d-none");
        $("#projectModal #newProjectLabel").html("Create a New Project");

        $("#projectModal .project-select").removeClass("d-none");
        $("#projectModal .project-input").removeClass("d-none");
        $("#projectModal #copyProjectLink").addClass("d-none");
        $("#projectModal #sharePermission").addClass("d-none");
    });

    $(document).on("click", ".editproject", function() {
        var getURL = $(this).data("geturl");
        var formURL = $(this).data("url");
        var event = $(this).data("event");

        $("#projectModal").modal("show");
        $("#projectModal #sharePermission").addClass("d-none");
        $("#projectModal #copyProjectLink").addClass("d-none");
        $("#projectModal .project-select").addClass("d-none");
        $("#projectModal .project-input").removeClass("d-none");
        $("#projectModal #project").removeAttr("required");
        $("#projectModal #projectname").removeAttr("required");

        if (event == "edit") {
            $("#projectModal #btnsubmit").html("Update Project");
            $("#projectModal #newProjectLabel").html("Edit Project");
            $("#projectModal #projectname").attr("required", "required");
        }
        else if (event == "archive") {
            $("#projectModal #btnsubmit").html("Archive Project");
            $("#projectModal #newProjectLabel").html("Archive Project");
        }
        else if (event == "share") {
            $("#projectModal #btnsubmit").html("Send");
            $("#projectModal #newProjectLabel").html("Share Project");
            $("#projectModal #sharePermission").removeClass("d-none");
            $("#projectModal #copyProjectLink").removeClass("d-none");
            $("#projectModal .project-input").addClass("d-none");
        }
        else if (event == "invite") {
            $("#projectModal #btnsubmit").html("Send");
            $("#projectModal #newProjectLabel").html("Invite members");
            $("#projectModal #sharePermission").addClass("d-none");
            $("#projectModal #copyProjectLink").addClass("d-none");
            $("#projectModal .project-select").removeClass("d-none");
            $("#projectModal .project-input").addClass("d-none");
        }
        
        $("#projectModal").LoadingOverlay("show", {
            background  : "rgba(255, 255, 255, 0.8)"
        });
        $.getJSON(getURL, function(data) {
            if (data.info != null) {
                $("#projectModal #projectname").val(data.info.name);
                $("#projectModal #projectdesc").val(data.info.description);
            }
            $("#myUsersList .collaborators-list").html(data.users);
            if (data.invitees != "") {
                $("#projectModal #collaborators_list").removeClass("d-none");
            }
            $("#projectModal #collaborators_list .collaborators-list").html(data.invitees);
            $("#frmproject").attr("action", formURL);
            $("#projectModal").LoadingOverlay("hide", true);
            if (event == "share") {
                $("#projectModal #copyProjectLink").attr("data-id", data.info.case_id);
            }
            if (event = "invite" && data.projects != "") {
                console.log(data.projects);
                $options = "";
                $.each(data.projects, function(key, value) {
                    console.log(value);
                    $options += "<option value='" + value.id + "'>" + value.name + "</option>";
                });
                $("#projectModal #project").append($options);
                $("#projectModal #project").attr("required", "required");
            }
        }).fail(function(res) {
            console.log(res);
            $("#projectModal").LoadingOverlay("hide", true);
            show_sweet_alert("Failed!", "Sorry! something went wrong", "error").then(function(val) {
                $("#projectModal").modal("hide");
            });
        });
    });

    // Example for users table
    $(document).on("tableReload", function(e, btn, data) {
        try {
            cTable.ajax.reload(null, false);
        }
        catch(ex){}
    });

    $(document).on("click", "#copyProjectLink", function(e) {
        e.preventDefault();
        let urlToCopy = $app_url + "/project/detail/" + $(this).data("id");
        navigator.clipboard.writeText(urlToCopy).then(function() {
            toastr.info("Link copied to clipboard!");
        }).catch(function(err) {
            toastr.error("Failed to copy link");
            console.error("Could not copy text: ", err);
        });
    });

    $(document).on("click", "#btncreatefolder", function(e) {
        var url = $(this).data("url");
        $("#createFolderModal #frmdata").attr("action", url);
        $("#createFolderModal").modal("show");
    });

    $(document).on("click", ".editfolder", function(e) {
        var url = $(this).data("url");
        $("#createFolderModal #frmdata input[name='name']").val($(this).data("name"));
        $("#createFolderModal .modal-title").html("Rename Folder");
        $("#createFolderModal #frmdata").attr("action", url);
        $("#createFolderModal").modal("show");
    });

    $('#createFolderModal').on('hide.bs.modal', function (e) {      
        $("#createFolderModal #frmdata").trigger("reset");
        $("#createFolderModal #frmdata").removeAttr("action");
        $("#createFolderModal #frmdata").removeClass("was-validated");
        $("#createFolderModal .modal-title").html("Create Folder");
    });

    // Trigger after form submission
    $(document).on("frmUpdated", function(e, btn, data) {
        $("#createFolderModal").modal("hide");
        if (data.list) {
            $(".project-folder").remove();
            $(".recent-folders").append(data.list);
        }
    });

    // Trigger after form submission
    $(document).on("itemDeleted", function(e, btn, data) {
        $("#createFolderModal").modal("hide");
        if (data.list) {
            $(".project-folder").remove();
            $(".recent-folders").append(data.list);
        }
    });
});
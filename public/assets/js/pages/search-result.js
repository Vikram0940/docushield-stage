$(document).ready(function () {
    var cTable;
    let currentTab = "active"; // default
    var filter_Modified = 1;
    var gURL = "search/get-json-data?q=" + $("#search_text").val();
    if ($("#tbldata").length > 0) {
        $.fn.dataTable.ext.errMode = function ( settings, helpPage, message ) { 
            show_sweet_alert("Sorry!", message, "error");
            $("#tbldata").LoadingOverlay("hide", true);
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

        cTable = $('#tbldata').DataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sServerMethod": "POST",
            'sPaginationType': 'full_numbers',
            "bFilter": false,
            "bPaginate" : true,
            "aLengthMenu": [ [5, 10, 20, 50, 100, 200, 500, 1000], [5, 10, 20, 50, 100, 200, 500, 1000] ],
            "iDisplayLength": $("#rowsPerPage").val(),
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
                    "className": "text-center"
                },
                {
                    "targets": 1, // option
                    "width": "18%"
                },
                {
                    "targets": 2, // option
                    "width": "11%",
                    "className": "text-center"
                },
                {
                    "targets": 3, // option
                    "width": "11%",
                    "className": "text-center"
                },
                {
                    "targets": 4, // option                    
                },
                {
                    "targets": 5, // option
                    "width": "13%",
                    "className": "text-center"
                },
                {
                    "targets": 6, // option
                    "width": "13%",
                    "className": "text-center"
                },
                {
                    "targets": 7, // option
                    "width": "22%",
                    "className": "text-center"
                }
            ],
            'preDrawCallback': function(settings) {
                $("#tbldata").LoadingOverlay("show", {
                    background  : "rgba(255, 255, 255, 0.8)"
                });
                //$.LoadingOverlay("show");
            },
            "drawCallback": function( settings ) {
                $("#tbldata").LoadingOverlay("hide", true);
                if (settings.json.token) {
                    $csrf_hash = settings.json.token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = $csrf_hash;
                    updateCsrfToken($csrf_hash);
                }
                if (settings.json.gridFiles) {
                    $("#grid-view").html(settings.json.gridFiles);
                }
                $('#tbldata [data-bs-toggle="tooltip"]').tooltip();
                if (settings.json.iTotalRecords > 0) {
                    $("#projects_not_exist").addClass("d-none");
                    $("#projects_exist").removeClass("d-none");
                }
                else {
                    $("#projects_not_exist").removeClass("d-none");
                    $("#projects_exist").addClass("d-none");
                }
                $("#total_records").html(settings.json.iTotalRecords);
            }
        });

        // Tab change listener
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            currentTab = $(e.target).data('type'); // active | archived | etc.
            var gURL = "projects/get-json-data?type=" + currentTab;
            cTable.ajax.url(gURL).load(null, false);
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

    function filter() {
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
        if ($("#cmbtype").val() != "") {
            paramArr.push("type=" + $("#cmbtype").val());
        }
        if ($("#cmbstatus").val() != "") {
            paramArr.push("status=" + $("#cmbstatus").val());
        }
        if (sort.length > 0) {
            paramArr.pus("sort=" + sort.join(","));
        }
        paramArr.push("q=" + $("#search_text").val());
        var param = paramArr.join("&");
        gURL = "search/get-json-data?" + param;
        cTable.ajax.url(gURL).load();
    }

    // Example for users table
    $(document).on("tableReload", function(e, btn, data) {
        cTable.ajax.reload(null, false);
    });
});
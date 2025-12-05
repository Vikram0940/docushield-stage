$(document).ready(function () {
    var cTable;
    let currentTab = "active"; // default
    var filter_Modified = 1;
    var gURL = "files/get-json-data?type=" + currentTab;
    if ($("#tblfiles").length > 0) {
        $.fn.dataTable.ext.errMode = function ( settings, helpPage, message ) { 
            show_sweet_alert("Sorry!", message, "error");
            $("#tblfiles").LoadingOverlay("hide", true);
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

        cTable = $('#tblfiles').DataTable({
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
                    "width": "18%"
                },
                {
                    "targets": 1, // option
                    "width": "18%"
                },
                {
                    "targets": 2, // option
                    "width": "30%"
                },
                {
                    "targets": 3, // option
                    "className": "text-center"
                },
                {
                    "targets": 4, // option
                    "width": "12%"
                },
                {
                    "targets": 5, // option
                    "width": "10%"
                },
                {
                    "targets": 6, // option
                    "width": "22%",
                    "className": "text-center"
                },
                {
                    "targets": 7, // option
                    "width": "22%",
                    "className": "text-center",
                    "visible": false
                },
                {
                    "targets": 8, // option
                    "width": "22%",
                    "className": "text-center",
                    "visible": false
                },
                {
                    "targets": 9, // option
                    "width": "22%",
                    "className": "text-center",
                    "visible": false
                }
            ],
            'preDrawCallback': function(settings) {
                $("#tblfiles").LoadingOverlay("show", {
                    background  : "rgba(255, 255, 255, 0.8)"
                });
                //$.LoadingOverlay("show");
            },
            "drawCallback": function( settings ) {
                $("#tblfiles").LoadingOverlay("hide", true);
                if (settings.json.token) {
                    $csrf_hash = settings.json.token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = $csrf_hash;
                    updateCsrfToken($csrf_hash);
                }
                if (settings.json.gridProjects) {
                    $("#grid-view").html(settings.json.gridProjects);
                }
                $('#tblfiles [data-bs-toggle="tooltip"]').tooltip();
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
        if (sort.length > 0) {
            paramArr.pus("sort=" + sort.join(","));
        }
        var param = paramArr.join("&");
        gURL = "projects/get-json-data?type=" + currentTab + "&" + param;
        cTable.ajax.url(gURL).load();
    }
});
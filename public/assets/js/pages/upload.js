$(document).ready(function() {
    // Trigger file input
    $("#dropArea").on("click", function(e){
        if(!$(e.target).is("#fileInput") && !$(e.target).is("#browseBtn")) {
            e.stopPropagation();
            $("#fileInput")[0].click();
        }
    });

    // Handle file selection
    $("#fileInput").on("change", function(e){
        uploadFiles(e.target.files);
    });

    // Drag & drop
    let dropArea = $("#dropArea");
    dropArea.on("dragover", function(e){
        e.preventDefault();
        $(this).addClass("dragover");
    });
    dropArea.on("dragleave", function(e){
        e.preventDefault();
        $(this).removeClass("dragover");
    });
    dropArea.on("drop", function(e){
        e.preventDefault();
        $(this).removeClass("dragover");
        uploadFiles(e.originalEvent.dataTransfer.files);
    });

    // Upload function
    function uploadFiles(files){
        $.each(files, function(i, file){
            let ext = file.name.split('.').pop().toLowerCase();
            let size = (file.size/1024).toFixed(1) + " KB";

            let li = $(`
                <li class="list-group-item">
                  <div class="row file-row1">
                    
                    <!-- Left: File Info -->
                    <div class="col-lg-4 p-0">
                        <div class='file-info'>
                          <div class="file-icon ${ext}">${ext.toUpperCase()}</div>
                          <div class="file-details">
                            <div><strong>${shortenFileName(file.name)}</strong></div>
                            <div class="text-muted">${size}</div>
                          </div>
                        </div>
                    </div>

                    <!-- Center: Upload Status -->
                    <div class="col-lg-8 status-area1">
                      <div class="progress mb-1">
                        <div class="progress-bar bg-success" style="width:0%"></div>
                      </div>
                      <div class="status text-muted"><small class='fs-12'>Uploading...</small></div>
                    </div>

                    <!-- Right: Dropdown -->
                    <div class="d-none col-lg-5 dropdown-area1 p-0">
                      <select class="form-select form-select-sm">
                        <option value="">Select Project</option>
                        <option>Label vs Title Books</option>
                        <option>Jeff Dave vs Typo / Testimonies</option>
                        <option>Primary Text</option>
                      </select>
                    </div>

                  </div>
                </li>
              `);

            $("#fileList").removeClass("d-none").append(li);
            sendFile(file, li);
        });
    }

    // AJAX upload with progress
    function sendFile(file, li) {
        let formData = new FormData();
        formData.append("file", file);
        $.ajax({
            xhr: function(){
                let xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(e){
                    if(e.lengthComputable){
                        let percent = Math.round((e.loaded / e.total) * 100);
                        li.find(".progress-bar").css("width", percent+"%");
                    }
                });
                return xhr;
            },
            url: $app_url + "/files/upload",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(data) {
                li.find(".status-area1").removeClass("col-lg-8").addClass("col-lg-3");
                li.find(".dropdown-area1").removeClass("d-none");
                li.find(".status").html("<small class='text-success fs-12'><i class='far fa-check-circle'></i> Completed</small>").removeClass("text-muted").addClass("text-success");
                li.find(".retry-btn").addClass("d-none");
                li.find(".progress").addClass("d-none");
                if (data.token) {
                    $csrf_hash = data.token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = $csrf_hash;
                    updateCsrfToken($csrf_hash);
                }
            },
            error: function() {
                li.find(".status").html("<small class='text-danger fs-12'><i class='far fa-info-circle'></i> Failed</small>").removeClass("text-muted").addClass("text-danger");
                li.find(".retry-btn").removeClass("d-none");
                getToken().then(function(token) {
                    console.log('Got token:', token);
                    $csrf_hash = token;
                    $.ajaxSettings.headers["X-CSRF-Token"] = token;
                });
            }
        });
    }

    // Retry
    $(document).on("click", ".retry-btn", function(){
        let li = $(this).closest("li");
        let fileName = li.find("strong").text();
        let input = document.getElementById("fileInput");
        $.each(input.files, function(i, file){
            if(file.name === fileName){
                li.find(".status").text("Retrying...").removeClass("text-danger").addClass("text-muted");
                li.find(".progress-bar").css("width","0%");
                sendFile(file, li);
            }
        });
    });

    // Rename (frontend only for now)
    $(document).on("click", ".rename-btn", function(){
        let li = $(this).closest("li");
        let newName = prompt("Enter new name:", li.find("strong").text());
        if(newName){
            li.find("strong").text(newName);
        }
    });

    $(document).on("click", "#btnupload", function(e) {
        $("#uploadModal").modal("show");
    });

    function shortenFileName(name, lastChars = 8) {
        if (name.length <= lastChars) return name;
        return "..." + name.slice(-lastChars);
    }

});
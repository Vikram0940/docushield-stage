// Configuration: Choose between real upload progress or simulated progress
const USE_SIMULATED_PROGRESS = true; // Set to true to use simulated progress, false for real upload progress

var csrfName = $('meta[name="csrf-name"]').attr('content');
var csrfHash = $('meta[name="csrf-hash"]').attr('content');

// File type colors
const typeColors = {
  pdf: "#dc2626",
  png: "#9333ea",
  jpg: "#9333ea",
  jpeg: "#9333ea",
  doc: "#2563eb",
  docx: "#2563eb",
  xls: "#16a34a",
  xlsx: "#16a34a",
  txt: "#6b7280"
};
var projects;
fetchProjects();
// Fetch projects from server
function fetchProjects() {
  $.getJSON($app_url + '/projects/populate', function(res) {
    projects = res.projects;
  });
}

function getFileBadge(ext) {
  const color = typeColors[ext.toLowerCase()] || "#6b7280";
  return `<span class="file-badge" style="background:${color}">${ext.toUpperCase()}</span>`;
}

// Truncate long filenames but keep the extension visible
function truncateFileName(filename, maxBaseLength = 26) {
  if (!filename || typeof filename !== 'string') return filename;
  const lastDot = filename.lastIndexOf('.');
  if (lastDot <= 0) {
    // no extension
    return filename.length > maxBaseLength
      ? filename.slice(0, maxBaseLength) + '…'
      : filename;
  }
  const base = filename.slice(0, lastDot);
  const ext = filename.slice(lastDot + 1);
  if (base.length <= maxBaseLength) return `${base}.${ext}`;
  return `${base.slice(0, maxBaseLength)}….${ext}`;
}

// Counters
let totalFiles = 0;
let successFiles = 0; //  only successful uploads

function updateCounter() {
  document.getElementById("uploadCounter").innerText =
    `(${successFiles}/${totalFiles} uploaded)`;
}

// Build server config conditionally so we don't double-upload
const serverConfig = {
  // keep revert available in both modes
  revert: (uniqueFileId, load, error) => {
    fetch($app_url + '/file/revert', {
      method: 'DELETE',
      body: uniqueFileId
    })
    .then(response => {
      if (response.ok) {
        load();
      } else {
        error('Revert failed');
      }
    })
    .catch(() => error('Revert failed'));
  }
};

// Only provide FilePond's process handler when we're using real progress
if (!USE_SIMULATED_PROGRESS) {
  serverConfig.process = (fieldName, file, metadata, load, error, progress, abort) => {
    console.log("🚀 Upload starting:", file.name);

    const formData = new FormData();
    formData.append('file', file, file.name);
    
    // Debug: Log what we're sending
    console.log('FilePond file object:', file);
    console.log('File name:', file.name);
    console.log('File size:', file.size);
    console.log('File type:', file.type);
    console.log('File constructor:', file.constructor.name);
    
    // Add CSRF token if available
    if (typeof $csrf_hash !== 'undefined' && $csrf_hash) {
      formData.append('csrf_test_name', $csrf_hash);
      console.log('CSRF token added:', $csrf_hash);
    }

    const request = new XMLHttpRequest();
    request.open('POST', $app_url + '/file/upload');

    request.upload.onloadstart = (e) => {
      // Don't use FilePond's progress, we'll use our custom one
      progress(0, 0, e.total);
    };

    request.upload.onprogress = (e) => {
      if (e.lengthComputable) {
        const percent = (e.loaded / e.total) * 100;
        // Update our custom progress bar
        const row = document.getElementById("row-" + file.id);
        if (row) {
          const progBar = row.querySelector(".progress-bar");
          const status = row.querySelector(".file-status");
          if (progBar) {
            progBar.style.width = percent + "%";
          }
          if (status) {
            status.innerHTML = `<i class="bi bi-upload text-info me-1"></i> Uploading ${Math.round(percent)}%`;
            status.className = "file-status text-info d-flex align-items-center";
          }
        }
        // Still call FilePond's progress for internal tracking
        progress(e.loaded / e.total, e.loaded, e.total);
      }
    };

    request.onload = function () {
      if (request.status >= 200 && request.status < 300) {
        try {
          const fileId = request.responseText;
          load(fileId); // FilePond expects the file ID as string
        } catch (e) {
          console.error('Invalid server response:', request.responseText);
          error('Invalid server response');
        }
      } else {
        console.error('Upload failed with status:', request.status, 'Response:', request.responseText);
        error(request.responseText || 'Upload failed');
      }
    };

    request.onerror = () => error('Upload error');
    request.send(formData);

    return {
      abort: () => {
        request.abort();
        abort();
      }
    };
  };
}

// FilePond init
const pond = FilePond.create(document.querySelector('.filepond'), {
  allowMultiple: true,
  instantUpload: true,
  credits: false,
  name: 'file',
  // Hide FilePond's default progress bar
  allowFileTypeValidation: false,
  allowFileSizeValidation: false,
  // Completely disable FilePond's UI
  allowRevert: false,
  allowRemove: false,
  allowReplace: false,
  allowBrowse: true,
  // Hide all status indicators
  showProgressIndicator: false,
  showFileStatus: false,
  showFileInfo: false,
  server: serverConfig,
  allowRevert: true
});

// Hide FilePond's default UI elements
document.querySelector('.filepond--list-scroller').style.display = 'none';

// Hide FilePond's progress bar and all UI elements
const style = document.createElement('style');
style.textContent = `
  /* Hide all FilePond progress and status elements */
  .filepond--file-status-main,
  .filepond--file-status-sub,
  .filepond--file-info-main,
  .filepond--file-info-sub,
  .filepond--progress-indicator,
  .filepond--file-action-button,
  .filepond--file-status,
  .filepond--file-info,
  .filepond--file-status-container,
  .filepond--file-info-container,
  .filepond--file-wrapper,
  .filepond--file-status-icon,
  .filepond--file-info-name,
  .filepond--file-info-size,
  .filepond--file-status-label,
  .filepond--progress-bar,
  .filepond--progress-bar-fill,
  .filepond--progress-bar-background {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    height: 0 !important;
    width: 0 !important;
    overflow: hidden !important;
  }
  
  /* Hide the entire file item content */
  .filepond--file {
    padding: 0 !important;
    margin: 0 !important;
    height: 0 !important;
    min-height: 0 !important;
    overflow: hidden !important;
  }
  
  /* Hide file item wrapper */
  .filepond--file-wrapper {
    display: none !important;
  }
  
  /* Hide the entire list */
  .filepond--list {
    display: none !important;
  }
  
  /* Hide list scroller */
  .filepond--list-scroller {
    display: none !important;
  }
  
  /* Hide the entire file list container */
  .filepond--list-container {
    display: none !important;
  }
  
  /* Keep only the drop area visible */
  .filepond--root {
    min-height: 120px !important;
  }
  
  /* Hide any remaining file items */
  .filepond--item {
    display: none !important;
  }
`;
document.head.appendChild(style);

// Additional approach: Hide FilePond elements after they're created
setTimeout(() => {
  const filepondElements = document.querySelectorAll('.filepond--file, .filepond--list, .filepond--list-scroller, .filepond--list-container');
  filepondElements.forEach(el => {
    if (el) {
      el.style.display = 'none';
      el.style.visibility = 'hidden';
      el.style.height = '0';
      el.style.overflow = 'hidden';
    }
  });
}, 100);

// Use MutationObserver to hide any FilePond elements that get created dynamically
const observer = new MutationObserver((mutations) => {
  mutations.forEach((mutation) => {
    mutation.addedNodes.forEach((node) => {
      if (node.nodeType === 1) { // Element node
        if (node.classList && (
          node.classList.contains('filepond--file') ||
          node.classList.contains('filepond--list') ||
          node.classList.contains('filepond--list-scroller') ||
          node.classList.contains('filepond--list-container') ||
          node.classList.contains('filepond--item')
        )) {
          node.style.display = 'none';
          node.style.visibility = 'hidden';
          node.style.height = '0';
          node.style.overflow = 'hidden';
        }
      }
    });
  });
});

// Start observing
observer.observe(document.body, {
  childList: true,
  subtree: true
});

// Make server request with custom progress bar
function makeServerRequest(file, row, status, editBtn, projectSelect, retryBtn) {
  const formData = new FormData();
  // FilePond's addfile event gives a FilePond File object; grab the native Blob/File
  const nativeFile = file && file.file ? file.file : file;
  const fileName = file && file.filename ? file.filename : (nativeFile && nativeFile.name ? nativeFile.name : 'upload');
  formData.append('file', nativeFile, fileName);
  
  // Add CSRF token if available
  if (typeof $csrf_hash !== 'undefined' && $csrf_hash) {
    formData.append('csrf_test_name', $csrf_hash);
  }

  const request = new XMLHttpRequest();
  request.open('POST', $app_url + '/file/upload');

  // Simulate progress bar animation
  let progress = 0;
  const progressInterval = setInterval(() => {
    progress += 10;
    const progBar = row.querySelector(".progress-bar");
    if (progBar) {
      progBar.style.width = progress + "%";
    }
    if (progress >= 90) {
      clearInterval(progressInterval);
    }
  }, 200);

  request.upload.onprogress = (e) => {
    if (e.lengthComputable) {
      const percent = (e.loaded / e.total) * 100;
      const progBar = row.querySelector(".progress-bar");
      if (progBar) {
        progBar.style.width = percent + "%";
      }
    }
  };

  request.onload = function (evt) {
    clearInterval(progressInterval);
    
    // Complete the progress bar
    const progBar = row.querySelector(".progress-bar");
    if (progBar) {
      progBar.style.width = "100%";
    }
    
    // Remove progress bar
    row.querySelector(".file-progress").innerHTML = "";
    
    if (request.status >= 200 && request.status < 300) {
      // ✅ Success
      let serverFileId = null;
      try {
        const resp = JSON.parse(request.responseText);
        serverFileId = resp.fileId;
      } catch (e) {
        console.error('Invalid JSON response:', request.responseText);
        return;
      }
      row.setAttribute('data-server-file-id', serverFileId);

      status.innerHTML = `<i class="bi bi-check-circle-fill text-success me-1"></i> Complete`;
      status.className = "file-status text-success d-flex align-items-center";
      row.classList.add("success");
      row.classList.remove("failed");

      editBtn.classList.remove("d-none");
      editBtn.setAttribute("data-server-id", serverFileId);
      projectSelect.classList.remove("d-none");
      $(projectSelect).select2({
        dropdownParent: $(row),
        width: '200px',
        placeholder: 'Search Projects'
      });

      if ($(projectSelect).val() != "") {
        attachFileToProject($(projectSelect).val(), serverFileId);
      }

      // Attach the change event right after initializing Select2
      $(projectSelect).on('change', function() {
        const selectedProjectId = $(this).val();
        const serverFileId = row.getAttribute('data-server-file-id');
        attachFileToProject(selectedProjectId, serverFileId);
        // ...your update code...
      });

      successFiles++;
      updateCounter();

      // Trigger
      document.dispatchEvent(new CustomEvent("tableReload", {
          detail: { me: null, data: null }
      }));
    } else {
      // ❌ Failed
      status.innerHTML = `<i class="bi bi-exclamation-circle-fill text-danger me-1"></i> Failed`;
      status.className = "file-status text-danger d-flex align-items-center";
      row.classList.add("failed");
      row.classList.remove("success");

      // Hide edit button and project select on failure
      editBtn.classList.add("d-none");
      projectSelect.classList.add("d-none");
      
      // Show retry button
      retryBtn.classList.remove("d-none");
      
      console.error('Upload failed with status:', request.status, 'Response:', request.responseText);
    }
  };

  request.onerror = () => {
    clearInterval(progressInterval);
    
    // Remove progress bar
    row.querySelector(".file-progress").innerHTML = "";
    
    // ❌ Failed
    status.innerHTML = `<i class="bi bi-exclamation-circle-fill text-danger me-1"></i> Failed`;
    status.className = "file-status text-danger d-flex align-items-center";
    row.classList.add("failed");
    row.classList.remove("success");

    // Hide edit button and project select on failure
    editBtn.classList.add("d-none");
    projectSelect.classList.add("d-none");
    
    // Show retry button
    retryBtn.classList.remove("d-none");
    
    console.error('Upload error');
  };

  request.send(formData);
}

// Simulate upload function for custom progress bar (kept for reference)
function simulateUpload(row, status, editBtn, projectSelect, retryBtn) {
  let uploaded = 0;
  const progBar = row.querySelector(".progress-bar");

  const interval = setInterval(() => {
    uploaded += 10;
    progBar.style.width = uploaded + "%";

    if (uploaded >= 100) {
      clearInterval(interval);
      row.querySelector(".file-progress").innerHTML = ""; // remove bar

      if (Math.random() > 0.3) {
        // ✅ success
        status.innerHTML = `<i class="bi bi-check-circle-fill text-success me-1"></i> Complete`;
        status.className = "file-status text-success d-flex align-items-center";
        row.classList.add("success");
        row.classList.remove("failed");

        editBtn.classList.remove("d-none");
        projectSelect.classList.remove("d-none");
        $(projectSelect).select2({
          dropdownParent: $(row),
          width: '200px',
          placeholder: 'Search Projects'
        });

        successFiles++;
        updateCounter();
      } else {
        // ❌ failed
        status.innerHTML = `<i class="bi bi-exclamation-circle-fill text-danger me-1"></i> Failed`;
        status.className = "file-status text-danger d-flex align-items-center";
        row.classList.add("failed");
        row.classList.remove("success");

        // Hide edit button and project select on failure
        editBtn.classList.add("d-none");
        projectSelect.classList.add("d-none");
        
        // Show retry button
        retryBtn.classList.remove("d-none");
      }
    }
  }, 300);
}

// Handle file addition
document.addEventListener("FilePond:addfile", e => {
  totalFiles++;
  updateCounter();

  const file = e.detail.file;
  const ext = file.filename.split(".").pop();
  const id = file.id;

  // Fetch projects for dropdown
  //const projects = await fetchProjects();
  //const projects = fetchProjects();

  // Build project options
  /*const projectOptions = projects.map(
    p => `<option value="${p.id}">${p.name}</option>`
  ).join('');*/

  // Build the dropdown HTML
  const projectOptions = projects.map(project => {
    selectProject = "";
    if ($objectType == 1) {
      if ($objectId == project.id) {
        selectProject = "selected='selected'";
      }
    }
    // Base option for the project
    let options = `<option value="project-${project.id}" ${selectProject}>${project.name}</option>`;

    // If folders exist, add them indented under the project
    if (project.folders && project.folders.length > 0) {
      project.folders.forEach(folder => {
        selectFolder = "";
        if ($objectType == 2) {
          if ($objectId == folder.id) {
            selectFolder = "selected='selected'";
          }
        }
        options += `<option value="folder-${folder.id}" ${selectFolder}>— ${folder.name}</option>`;
      });
    }
    return options;
  }).join('');

  const row = document.createElement("div");
  row.className = "custom-file-row";
  row.id = "row-" + id;

  row.innerHTML = `
    <div class="flex-grow-1 ms-0 d-flex align-items-center justify-content-between">
        
        <!-- Left side: Badge + Filename + Edit -->
        <div class="FileInfo d-flex align-items-center">
        <div class="FileBadge">${getFileBadge(ext)}</div>
        <div class="FileDetails ms-2">
            <div class="FileName d-flex align-items-center">
            <strong class="file-name" title="${file.filename}" data-full-name="${file.filename}">${truncateFileName(file.filename)}</strong>
            <button class="btn btn-link p-0 ms-2 text-primary edit-btn d-none">
                <i class="bi bi-pencil-square"></i>
            </button>
            </div>
            <small class="text-muted file-size">${(file.fileSize/1024).toFixed(1)} KB</small>
        </div>
        </div>

        <!-- Middle: Status / Progress -->
        <div class="FileStatus d-flex align-items-center flex-grow-1 mx-3">
        <div class="file-progress flex-grow-1">
            <div class="progress">
            <div class="progress-bar" style="width:0%"></div>
            </div>
        </div>
        <div class="file-status ms-2"></div>
        </div>

        <!-- Right: Retry + Project -->
        <div class="FileProject d-flex align-items-center">
        <button class="btn btn-primary btn-sm ms-2 retry-btn d-none">Retry Upload</button>
        <select class="project-select form-select form-select-sm ms-2 d-none" style="width:220px" id='project${file.id}'>
            <option value='-1' selected>Storage</option>
            ${projectOptions}
        </select>
        </div>

    </div>
    `;

  const fileList = document.getElementById("customFileList");
  fileList.appendChild(row);
  fileList.scrollTop = fileList.scrollHeight; // auto-scroll

  const progBar = row.querySelector(".progress-bar");
  const status = row.querySelector(".file-status");
  const retryBtn = row.querySelector(".retry-btn");
  const editBtn = row.querySelector(".edit-btn");
  const projectSelect = row.querySelector(".project-select");

  // Start upload immediately based on configuration
  if (USE_SIMULATED_PROGRESS) {
    // For simulated progress, we still need to make the actual server request
    // but show our custom progress bar instead of FilePond's
    makeServerRequest(file, row, status, editBtn, projectSelect, retryBtn);
  } else {
    // Set initial status for real uploads
    status.innerHTML = `<i class="bi bi-clock text-warning me-1"></i> Queued`;
    status.className = "file-status text-warning d-flex align-items-center";
    
    // Update status when upload starts
    setTimeout(() => {
      status.innerHTML = `<i class="bi bi-upload text-info me-1"></i> Starting upload...`;
      status.className = "file-status text-info d-flex align-items-center";
    }, 100);
  }

  // Add retry button functionality
  retryBtn.onclick = () => {
    // restore progress bar HTML
    row.querySelector(".file-progress").innerHTML = `
      <div class="progress">
      <div class="progress-bar" style="width:0%"></div>
      </div>
    `;

    // get fresh references
    const newProgBar = row.querySelector(".progress-bar");
    const status = row.querySelector(".file-status");

    status.innerText = ""; // clear text, we only show bar while retrying
    status.className = "file-status text-warning";

    retryBtn.classList.add("d-none");

    // Use the appropriate function based on configuration
    if (USE_SIMULATED_PROGRESS) {
      makeServerRequest(file, row, status, editBtn, projectSelect, retryBtn);
    } else {
      simulateUpload(row, status, editBtn, projectSelect, retryBtn);
    }
  };

  editBtn.onclick = (event) => {
    const serverId = event.currentTarget.getAttribute("data-server-id");
    const fullFileName = row.querySelector('.file-name').getAttribute('data-full-name') || row.querySelector('.file-name').innerText;
    const currentName = fullFileName.split(".")[0];
    const currentProject = $(row).find(".project-select").val();
    const currentExt = row.querySelector(".file-badge").innerText;
    const currentSize = row.querySelector("small").innerText;

    document.querySelector("#renameFileBadge").innerHTML = getFileBadge(currentExt);
    document.querySelector("#renameFileTitle").innerText = `${currentName}.${currentExt.toLowerCase()}`;
    document.querySelector("#renameFileSize").innerText = currentSize;
    document.querySelector("#renameFileName").value = currentName;
    $("#renameFileLocation").val(currentProject).trigger("change");

    document.querySelector("#renameModal").setAttribute("data-file-id", id);
    document.querySelector("#frmrename").setAttribute("action", $app_url + "/file/rename/" + serverId);

    const uploadModal = bootstrap.Modal.getInstance(document.getElementById("uploadModal"));
    uploadModal.hide();

    const renameModal = new bootstrap.Modal(document.getElementById("renameModal"));
    renameModal.show();

    document.getElementById("renameModal").addEventListener("hidden.bs.modal", () => {
      const upload = new bootstrap.Modal(document.getElementById("uploadModal"));
      upload.show();
    }, { once: true });
  };
});

// Handle upload progress - now handled directly in the upload process
// document.addEventListener("FilePond:processfileprogress", e => {
//   // Progress is now handled directly in the XMLHttpRequest onprogress event
// });

// Handle successful upload
document.addEventListener("FilePond:processfile", e => {
  // In simulated mode, we handle success in makeServerRequest; ignore FilePond success
  if (USE_SIMULATED_PROGRESS) return;
  const file = e.detail.file;
  const row = document.getElementById("row-" + file.id);
  
  if (row) {
    const status = row.querySelector(".file-status");
    const editBtn = row.querySelector(".edit-btn");
    const projectSelect = row.querySelector(".project-select");
    const retryBtn = row.querySelector(".retry-btn");
    
    // Remove progress bar
    row.querySelector(".file-progress").innerHTML = "";
    
    // Show success status
    status.innerHTML = `<i class="bi bi-check-circle-fill text-success me-1"></i> Complete`;
    status.className = "file-status text-success d-flex align-items-center";
    row.classList.add("success");
    row.classList.remove("failed");
    
    // Show edit and project select
    editBtn.classList.remove("d-none");
    projectSelect.classList.remove("d-none");
    $(projectSelect).select2({
      dropdownParent: $(row),
      width: '200px',
      placeholder: 'Search Projects'
    });
    
    successFiles++;
    updateCounter();
  }
});

// Handle upload error
document.addEventListener("FilePond:processfileerror", e => {
  // In simulated mode, we handle errors in makeServerRequest; ignore FilePond error
  if (USE_SIMULATED_PROGRESS) return;
  const file = e.detail.file;
  const error = e.detail.error;
  const row = document.getElementById("row-" + file.id);
  
  if (row) {
    const status = row.querySelector(".file-status");
    const retryBtn = row.querySelector(".retry-btn");
    
    // Remove progress bar
    row.querySelector(".file-progress").innerHTML = "";
    
    // Show error status
    status.innerHTML = `<i class="bi bi-exclamation-circle-fill text-danger me-1"></i> Failed`;
    status.className = "file-status text-danger d-flex align-items-center";
    row.classList.add("failed");
    row.classList.remove("success");
    
    // Show retry button
    retryBtn.classList.remove("d-none");
    
    console.error("Upload failed:", error);
  }
});

document.querySelector("#renameSaveBtn").onclick = () => {
  const modal = document.getElementById("renameModal");
  const id = modal.getAttribute("data-file-id");
  const row = document.getElementById("row-" + id);

  const newName = document.querySelector("#renameFileName").value;
  const newLocation = $("#renameFileLocation").val();
  const ext = row.querySelector(".file-badge").innerText.toLowerCase();

  const fullNewName = `${newName}.${ext}`;
  const nameEl = row.querySelector(".file-name");

  const renameSaveBtn = document.querySelector("#renameSaveBtn");
  var html = renameSaveBtn.innerHTML;
  
  // 1️⃣ Change HTML
  renameSaveBtn.innerHTML = "Please wait";
  // 2️⃣ Add attributes
  renameSaveBtn.setAttribute("disabled", "disabled");
  // 3️⃣ Add classes
  renameSaveBtn.classList.add("ld-ext-left", "running", "disabled");
  // 4️⃣ Append a span element
  const span = document.createElement("span");
  span.className = "ld ld-ring ld-cycle";
  renameSaveBtn.appendChild(span);

  const form = document.querySelector("#frmrename");
  const formURL = form.getAttribute("action");
  // Serialize form as array of {name, value}
  const postData = Array.from(form.elements)   // get all form elements
    .filter(el => el.name && !el.disabled)    // skip elements without name or disabled
    .map(el => ({ name: el.name, value: el.value }));

  $.ajax({
      url : formURL,
      type: "POST",
      data : postData,
      dataType:'json',
      cache: false,       
      success:function(data)
      {
          // 1️⃣ Remove 'disabled' attribute
          renameSaveBtn.removeAttribute("disabled");
          // 2️⃣ Remove classes
          renameSaveBtn.classList.remove("ld-over", "running", "disabled");
          // 3️⃣ Remove the loader span inside the button
          const span = renameSaveBtn.querySelector("span");
          if (span) {
            span.remove();
          }
          // 4️⃣ Restore original HTML (assuming you stored it in `html` variable)
          renameSaveBtn.innerHTML = html;

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

                  nameEl.innerText = truncateFileName(fullNewName);
                  nameEl.setAttribute('data-full-name', fullNewName);
                  nameEl.setAttribute('title', fullNewName);
                  $(row).find(".project-select").val(newLocation).trigger("change");
                  bootstrap.Modal.getInstance(modal).hide();
              });
          }

          if (data.url) {
              window.location.href = data.url;
          }
          
          // Trigger
          document.dispatchEvent(new CustomEvent("tableReload", {
              detail: { me: renameSaveBtn, data: data }
          }));
      },
      error: function(jqXHR, textStatus, errorThrown)
      {
          getToken().then(function(token) {
              $csrf_hash = token;
              $.ajaxSettings.headers["X-CSRF-Token"] = token;
          });
          show_sweet_alert("Failed!", $app_error, "error");
          // 1️⃣ Remove 'disabled' attribute
          renameSaveBtn.removeAttribute("disabled");
          // 2️⃣ Remove classes
          renameSaveBtn.classList.remove("ld-over", "running", "disabled");
          // 3️⃣ Remove the loader span inside the button
          const span = renameSaveBtn.querySelector("span");
          if (span) {
            span.remove();
          }
          // 4️⃣ Restore original HTML (assuming you stored it in `html` variable)
          renameSaveBtn.innerHTML = html;
      }
  });
};

const uploadModalEl = document.getElementById("uploadModal");
const openUploadBtn = document.querySelector('[data-bs-target="#uploadModal"]');

try {
  // Mark as "fresh open" when button is clicked
  openUploadBtn.addEventListener("click", () => {
    uploadModalEl.setAttribute("data-fresh", "true");
  });
}
catch(ex){}

// Reset only if opened fresh
uploadModalEl.addEventListener("shown.bs.modal", () => {
  if (uploadModalEl.getAttribute("data-fresh") === "true") {
    document.getElementById("customFileList").innerHTML = "";
    totalFiles = 0;
    successFiles = 0;
    updateCounter();
    pond.removeFiles();

    // Clear the flag so rename flow won't trigger reset
    uploadModalEl.removeAttribute("data-fresh");
  }
});

document.getElementById("renameModal").addEventListener("shown.bs.modal", () => {
  $('#renameFileLocation').select2({
    dropdownParent: $('#renameModal'),
    width: '100%',
    placeholder: 'Select a location'
  });
});


$('#renameFileLocation').select2({
  dropdownParent: $('#renameModal'),
  width: '100%',
  closeOnSelect: false,
  templateResult: function (data) {
    if (!data.id) return data.text;
    return data.selected
      ? $('<span><i class="bi bi-check2 me-2 text-primary"></i>' + data.text + '</span>')
      : $('<span>' + data.text + '</span>');
  },
  templateSelection: function (data) {
    // Always normalize to array
    if (!Array.isArray(data)) {
      data = [data]; // wrap single object into array
    }
    return data.map(d => d.text).join(', ');
  }
});

function attachFileToProject(selectedProjectId, serverFileId) {
  if (selectedProjectId == "") {
    return;
  }
  // Get CSRF token from meta
  var postData = {
      file_id        : serverFileId,
      project_id     : selectedProjectId,
      csrf_test_name : $csrf_hash
  }
  console.log(postData);
  var formURL = $app_url + '/project/attach-file';
  $.ajax({
      url : formURL,
      type: "POST",
      data : postData,
      dataType:'json',
      cache: false,       
      success:function(data)
      {
          if (data.msg) {
              alert(data.msg);
          }

          if (data.title) {
              show_sweet_alert(data.title, data.text, data.class);
          }

          if (data.url) {
              window.location.href = data.url;
          }

          // Trigger
          document.dispatchEvent(new CustomEvent("tableReload", {
              detail: { me: null, data: data }
          }));
      },
      error: function(jqXHR, textStatus, errorThrown)
      {
          getToken().then(function(token) {
              $csrf_hash = token;
              $.ajaxSettings.headers["X-CSRF-Token"] = token;
          });
          show_sweet_alert("Failed!", $app_error, "error");
      }
  });
}

function editFile() {
  const fullFileName = row.querySelector('.file-name').getAttribute('data-full-name') || row.querySelector('.file-name').innerText;
  const currentName = fullFileName.split(".")[0];
  const currentProject = $(row).find(".project-select").val();
  const currentExt = row.querySelector(".file-badge").innerText;
  const currentSize = row.querySelector("small").innerText;

  document.querySelector("#renameFileBadge").innerHTML = getFileBadge(currentExt);
  document.querySelector("#renameFileTitle").innerText = `${currentName}.${currentExt.toLowerCase()}`;
  document.querySelector("#renameFileSize").innerText = currentSize;
  document.querySelector("#renameFileName").value = currentName;
  $("#renameFileLocation").val(currentProject).trigger("change");

  document.querySelector("#renameModal").setAttribute("data-file-id", id);

  const uploadModal = bootstrap.Modal.getInstance(document.getElementById("uploadModal"));
  uploadModal.hide();

  const renameModal = new bootstrap.Modal(document.getElementById("renameModal"));
  renameModal.show();

  document.getElementById("renameModal").addEventListener("hidden.bs.modal", () => {
    const upload = new bootstrap.Modal(document.getElementById("uploadModal"));
    upload.show();
  }, { once: true });
}
// ...existing code...

// ...existing code...

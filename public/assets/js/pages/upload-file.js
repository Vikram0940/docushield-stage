FilePond.create(document.querySelector('.filepond'), {
    allowMultiple: true,
    maxFiles: 34,
    instantUpload: true,  // so you can trigger manually
    name: 'file',
    server: {
        process: (fieldName, file, metadata, load, error, progress, abort) => {
            console.log("🚀 Upload starting:", file.name);

            const formData = new FormData();
            formData.append('file', file, file.name);

            const request = new XMLHttpRequest();
            request.open('POST', $app_url + '/file/upload');

            request.upload.onloadstart = (e) => {
                progress(0, 0, e.total);

                // show progress only when request actually starts
                const item = pond.getFile(file.id).element;
                item.classList.add("show-progress");

                pond.fire('processfilestart', { file: { filename: file.name } });
            };

            request.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    progress(e.loaded / e.total, e.loaded, e.total);
                }
            };

            request.onload = function () {
                if (request.status >= 200 && request.status < 300) {
                    try {
                        const res = JSON.parse(request.responseText);
                        load(res.id);
                    } catch {
                        error('Invalid server response');
                    }
                } else {
                    error('Upload failed');
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
        }
    },
    allowRevert: true,
    onprocessfile: updateCounter,
    onremovefile: updateCounter
});

function updateCounter() {
  const pond = FilePond.find(document.querySelector('.filepond'));
  document.getElementById('uploadCounter').innerText = 
    `(${pond.getFiles().filter(f => f.status === 5).length}/${pond.getFiles().length} uploaded)`;
}

// Add custom UI to each file item
document.addEventListener('FilePond:addfile', e => {
  const item = e.detail.file.getItem().element;
  
  // Add project dropdown
  const dropdown = document.createElement('select');
  dropdown.className = "form-select form-select-sm ms-2";
  dropdown.innerHTML = `
    <option>Jeff Dave vs Typo</option>
    <option>Label vs Title Books</option>
    <option>Other Project</option>
  `;
  
  // Insert into file item row
  const info = item.querySelector('.filepond--file-info-main');
  info.appendChild(dropdown);

  // Add retry button (if failed)
  const retryBtn = document.createElement('button');
  retryBtn.className = "btn btn-outline-primary btn-sm ms-2";
  retryBtn.innerText = "Retry";
  retryBtn.onclick = () => pond.processFile(e.detail.file.id);
  info.appendChild(retryBtn);
});


function getFileIcon(ext) {
  const map = { pdf: "📄", png: "🖼️", jpg: "🖼️", doc: "📘" };
  return map[ext.toLowerCase()] || "📁";
}

document.addEventListener('FilePond:addfile', e => {
  const item = e.detail.file.getItem().element;
  const nameLabel = item.querySelector('.filepond--file-info-main .filepond--file-name span');
  const ext = e.detail.file.filename.split('.').pop();
  nameLabel.innerText = `${getFileIcon(ext)} ${e.detail.file.filename}`;
});

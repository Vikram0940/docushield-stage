CKEDITOR.plugins.add('uploaddocument', {
    icons: 'upload_file',
    init: function(editor) {

        editor.addCommand('addUploadDocumentThread', {
            exec: function() {
                let input = document.createElement('input');
                input.type = 'file';
                input.accept = ".doc,.docx";

                input.onchange = async function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append("file", file);

                    let res = await fetch("http://localhost/docushield-stage/api/documents/upload", {
                        method: "POST",
                        body: formData
                    });

                    let data = await res.json();

                    if (data.status === "success") {
                        editor.insertHtml(data.html + "<br/><hr><br/>");
                        alert("✔ Document uploaded and imported successfully!");
                    } else {
                        alert("❌ Upload/Conversion failed: " + (data.message || data.messages?.error || "Unknown error"));
                    }
                };

                input.click();
            }
        });

        editor.ui.addButton('UploadDocument', {
            label: 'Upload Word Document',
            command: 'addUploadDocumentThread',
            toolbar: 'insert',
            icon: this.path + 'icons/upload_file.svg'
        });
    }
});

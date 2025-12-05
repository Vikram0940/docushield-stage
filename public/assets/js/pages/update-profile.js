$(document).ready(function () {
	function uploadProfileImage(file) {
	    const formData = new FormData();
	    formData.append('profile_image', file);
	    showSpinner(true);
	    fetch($app_url + '/user/upload-avatar', {
	        method: 'POST',
	        body: formData
	    })
	    .then(response => response.json())
	    .then(data => {
	        showSpinner(false);
	        if (data.status === 'success') {
	            // Show uploaded image immediately
	            document.getElementById('profileImage').src = data.image_url;
	            document.getElementById('topProfileImage').src = data.image_url;
	        } else {
	            show_sweet_alert("Sorry!", 'Upload failed: ' + data.message, "error");
	        }
	    })
	    .catch(error => {
	        showSpinner(false);
	        console.error('Error:', error);
	        show_sweet_alert("Sorry!", 'Something went wrong while uploading.', "error");
	    });
	}

	function showSpinner(show) {
	    const spinner = document.getElementById('uploadSpinner');
	    if (show) {
	        spinner.classList.remove('d-none');
	    } else {
	        spinner.classList.add('d-none');
	    }
	}

	$(document).on("change", "#imageUpload", function(event) {
		previewImage(event);
	});

	function previewImage(event) {
	    const file = event.target.files[0];
	    if (!file) return;

	    // Show temporary preview
	    /*const reader = new FileReader();
	    reader.onload = function() {
	        document.getElementById('profileImage').src = reader.result;
	    }
	    reader.readAsDataURL(file);*/
	    // Upload image to server
	    uploadProfileImage(file);
	}

	// Show/hide "Other" input live
    $(document).on("change", "select[name='business_type']", function () {
        if ($(this).val() === "Other") {
            $("#otherBusinessType").removeClass("d-none");   // show input
        } else {
            $("#otherBusinessType").addClass("d-none"); // hide input
        }
    });
});
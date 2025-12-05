console.log('DocuShield loaded');
$.ajaxSetup( {
    headers: {
        'X-CSRF-Token': $csrf_hash
    }
});

function show_sweet_alert(title, text, alert_type, cancel_button = false, confirm_button_text = 'Ok', cancel_button_text = '')
{
    return Swal.fire({
        title: title,
        text: text,
        type: alert_type,
        showCancelButton: cancel_button,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        buttonsStyling: false,
        padding: '20px',
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-secondary ml-2',
        confirmButtonText: confirm_button_text,
        cancelButtonText: cancel_button_text
    }).then((confirmed) => {
        return confirmed;
    });
}

$(document).on('keypress','.onlynumber',function(ev){
    if (ev.which != 8 && ev.which != 0 && (ev.which < 48 || ev.which > 57)) {
        return false;
    }
}); 

$('.onlyamount').keypress(function(event) {
    if (((event.which != 46 || (event.which == 46 && $(this).val() == '')) ||
            $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
        event.preventDefault();
    }
}).on('paste', function(event) {
    event.preventDefault();
});

function getToken() {
    return $.getJSON($base_url + '/get-csrf-token').then(function(data) {
        var csrf_token = data.csrf_token;
        updateCsrfToken(csrf_token);
        return csrf_token;
    });
}

/**
* Updates CSRF token across all forms and meta tag.
* @param {string} newToken - The new CSRF hash value
*/
function updateCsrfToken(newToken) {
    const csrfNameMeta = document.querySelector('meta[name="csrf-name"]');
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');

    if (!csrfNameMeta || !csrfTokenMeta) return;

    console.log(newToken);
    const csrfName = csrfNameMeta.getAttribute('content');
    // Update all CSRF hidden inputs
    document.querySelectorAll(`input[name="${csrfName}"]`).forEach(input => {
        input.value = newToken;
    });

    // Update the meta tag for future AJAX usage
    csrfTokenMeta.setAttribute('content', newToken);
}

/**
 * Returns CSRF object for AJAX data
 * @returns {object} CSRF token key-value pair
 */
function getCsrfData() {
    const csrfName = document.querySelector('meta[name="csrf-name"]').getAttribute('content');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    return { [csrfName]: csrfToken };
}
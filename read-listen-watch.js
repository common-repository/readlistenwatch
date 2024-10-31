// disable and change fields based on media type
// (movies don't have an "author", and music has an "artist")
function rlwUpdateFields(element, value) {
	if (value == 'l') {
		document.getElementById('auth_' + element).disabled = false;
		document.getElementById('lbl_auth_' + element).innerHTML = 'Artist:*';
	} else if (value == 'r') {
		document.getElementById('auth_' + element).disabled = false;
		document.getElementById('lbl_auth_' + element).innerHTML = 'Author:*';
	} else if (value == 'w') {
		document.getElementById('auth_' + element).disabled = true;
		document.getElementById('lbl_auth_' + element).innerHTML = '--';
		document.getElementById('auth_' + element).value = '';
	}
}

document.addEventListener('DOMContentLoaded', function() {
	const dropZone = document.getElementById('srd-drop-zone');
	const fileInput = document.getElementById('srd-file-input');
	const browseBtn = document.getElementById('srd-browse-btn');
	const selectedFileContainer = document.getElementById('srd-selected-file');
	const fileNameSpan = document.getElementById('srd-file-name');
	const removeBtn = document.getElementById('srd-remove-file');
	const submitBtn = document.getElementById('srd-submit-btn');
	const form = document.getElementById('srd-upload-form');
	const messageBox = document.getElementById('srd-message');

	let currentFile = null;

	// Browse button triggers file input
	browseBtn.addEventListener('click', (e) => {
		e.preventDefault();
		fileInput.click();
	});
	
	// Clicking on drop zone also triggers file input (if not clicking the button itself)
	dropZone.addEventListener('click', (e) => {
		if (e.target !== browseBtn) {
			fileInput.click();
		}
	});

	// Drag events
	['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
		dropZone.addEventListener(eventName, preventDefaults, false);
	});

	function preventDefaults(e) {
		e.preventDefault();
		e.stopPropagation();
	}

	['dragenter', 'dragover'].forEach(eventName => {
		dropZone.addEventListener(eventName, () => {
			dropZone.classList.add('dragover');
		}, false);
	});

	['dragleave', 'drop'].forEach(eventName => {
		dropZone.addEventListener(eventName, () => {
			dropZone.classList.remove('dragover');
		}, false);
	});

	// Drop event
	dropZone.addEventListener('drop', (e) => {
		let dt = e.dataTransfer;
		let files = dt.files;
		handleFiles(files);
	});

	// File input change event
	fileInput.addEventListener('change', function() {
		handleFiles(this.files);
	});

	function handleFiles(files) {
		if (files.length > 0) {
			currentFile = files[0];
			
			// Validate basic file type
			const validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
			const validExtensions = ['.pdf', '.doc', '.docx'];
			
			const ext = currentFile.name.substring(currentFile.name.lastIndexOf('.')).toLowerCase();
			
			if (!validTypes.includes(currentFile.type) && !validExtensions.includes(ext)) {
				showMessage('Invalid file type. Please upload a PDF, DOC, or DOCX file.', 'error');
				removeFile();
				return;
			}

			// Validate size (5MB)
			if (currentFile.size > 5 * 1024 * 1024) {
				showMessage('File size exceeds the 5MB limit.', 'error');
				removeFile();
				return;
			}

			// Display selected file
			fileNameSpan.textContent = currentFile.name;
			dropZone.classList.add('hidden');
			selectedFileContainer.classList.remove('hidden');
			submitBtn.disabled = false;
			hideMessage();
		}
	}

	// Remove file event
	removeBtn.addEventListener('click', removeFile);

	function removeFile() {
		currentFile = null;
		fileInput.value = '';
		selectedFileContainer.classList.add('hidden');
		dropZone.classList.remove('hidden');
		submitBtn.disabled = true;
	}

	function showMessage(msg, type) {
		messageBox.textContent = msg;
		messageBox.className = 'srd-message ' + type;
	}

	function hideMessage() {
		messageBox.className = 'srd-message hidden';
	}

	// Form Submission via AJAX
	form.addEventListener('submit', function(e) {
		e.preventDefault();

		if (!currentFile) {
			showMessage('Please select a file to upload.', 'error');
			return;
		}

		submitBtn.disabled = true;
		submitBtn.classList.add('loading');
		hideMessage();

		const formData = new FormData();
		formData.append('action', 'srd_upload_resume');
		formData.append('resume_file', currentFile);
		
		// srd_ajax_obj is localized via wp_localize_script in public class
		if (typeof srd_ajax_obj !== 'undefined') {
			formData.append('srd_nonce', srd_ajax_obj.nonce);
		} else {
			showMessage('System error: Missing security token.', 'error');
			resetSubmitBtn();
			return;
		}

		fetch(srd_ajax_obj.ajax_url, {
			method: 'POST',
			body: formData
		})
		.then(response => response.json())
		.then(data => {
			resetSubmitBtn();
			if (data.success) {
				showMessage(data.data.message, 'success');
				// Optional: Remove file from UI after success
				setTimeout(() => {
					removeFile();
				}, 3000);
			} else {
				showMessage(data.data.message || 'An error occurred during upload.', 'error');
			}
		})
		.catch(error => {
			resetSubmitBtn();
			showMessage('A network error occurred. Please try again.', 'error');
			console.error('Error:', error);
		});
	});

	function resetSubmitBtn() {
		submitBtn.disabled = false;
		submitBtn.classList.remove('loading');
	}
});

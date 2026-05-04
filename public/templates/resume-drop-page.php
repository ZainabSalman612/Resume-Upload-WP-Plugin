<?php
/**
 * The template for displaying the resume drop page.
 *
 * This page intentionally does not load the full WordPress theme 
 * (header/footer) to provide a clean, distraction-free upload experience.
 * If header/footer integration is desired later, get_header() and get_footer() can be added.
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Submit Your Resume - <?php bloginfo( 'name' ); ?></title>
	<?php wp_head(); ?>
</head>
<body class="srd-page-body">

	<div class="srd-container">
		<div class="srd-card">
			<div class="srd-header">
				<h1 class="srd-title">Submit Your Resume</h1>
				<p class="srd-subtitle">Upload your resume securely. Accepted formats: PDF, DOC, DOCX (Max 5MB).</p>
			</div>

			<form id="srd-upload-form" class="srd-form">
				<div id="srd-drop-zone" class="srd-drop-zone">
					<svg class="srd-icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
						<polyline points="17 8 12 3 7 8"></polyline>
						<line x1="12" y1="3" x2="12" y2="15"></line>
					</svg>
					<div class="srd-drop-text">
						<span class="srd-highlight">Drag & drop</span> your file here
					</div>
					<div class="srd-or">or</div>
					<button type="button" id="srd-browse-btn" class="srd-browse-btn">Browse File</button>
					<input type="file" id="srd-file-input" name="resume_file" accept=".pdf,.doc,.docx" hidden>
				</div>
				
				<div id="srd-selected-file" class="srd-selected-file hidden">
					<div class="srd-file-info">
						<svg class="srd-file-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
						<span id="srd-file-name">filename.pdf</span>
					</div>
					<button type="button" id="srd-remove-file" class="srd-remove-file" aria-label="Remove file">&times;</button>
				</div>

				<div id="srd-message" class="srd-message hidden"></div>

				<button type="submit" id="srd-submit-btn" class="srd-submit-btn" disabled>Submit Resume</button>
			</form>
		</div>
	</div>

	<?php wp_footer(); ?>
</body>
</html>

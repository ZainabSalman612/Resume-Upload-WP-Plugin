# Simple Resume Drop

A lightweight plugin to create a dedicated resume upload page (`/resume-drop`) without using the Media Library.

**Requires at least:** 5.0  
**Tested up to:** 6.4  
**Requires PHP:** 7.4  
**License:** GPL-2.0-or-later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

## Description

Simple Resume Drop allows you to instantly spin up a clean, modern, and dedicated webpage where users can securely upload their resumes. It bypasses the standard WordPress Media Library, storing the uploaded files in a separate, secure directory to keep your media library clean and organized.

### Key Features
* **Dedicated Endpoint:** Automatically registers the `/resume-drop` URL. You can link to this page from anywhere on your site.
* **Modern UI:** Features a sleek drag-and-drop file upload interface built entirely without heavy frontend frameworks.
* **Secure Storage:** Uploaded files go directly to `/wp-content/uploads/resume-submissions/`.
* **Built-in Security:** The upload directory is automatically protected via `.htaccess` to prevent direct script execution. Only PDF, DOC, and DOCX files (up to 5MB) are allowed.
* **Admin Dashboard:** Easily view, download, or permanently delete uploaded files and their database records from the new WP Admin menu.

## Installation

1. Upload the `simple-resume-drop` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. The plugin will automatically configure the database table and upload folder.
4. **Important:** If you visit `yoursite.com/resume-drop` and receive a 404 error, navigate to **Settings > Permalinks** in your WP Admin and click **"Save Changes"**. This flushes the routing rules.

## Frequently Asked Questions

### How do I link to the upload page?
Simply add a link or a button pointing to `https://yoursite.com/resume-drop` (replace yoursite.com with your actual domain).

### What file types are allowed?
Currently, the plugin strictly allows `.pdf`, `.doc`, and `.docx` files.

### What is the maximum file size?
The plugin currently restricts uploads to a maximum of 5MB.

### Where do the uploaded files go?
They are stored safely in `/wp-content/uploads/resume-submissions/` and are NOT visible in your standard WordPress Media Library.

## Changelog

### 1.0.0
* Initial release.

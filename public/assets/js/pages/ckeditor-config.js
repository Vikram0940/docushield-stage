CKEDITOR.editorConfig = function (config) {
  // Apply your custom stylesheet globally
  config.contentsCss = [
    CKEDITOR.getUrl('contents.css'), // Default CKEditor content styles
    '/assets/css/ds-ckeditor.css' // Your custom styles
  ];

  // Optional: set a default class for all editors
  config.bodyClass = 'ds-doc-page'; 

  // Optional: consistent defaults
  config.font_defaultLabel = 'Inter';
  config.fontSize_defaultLabel = '14px';
  config.allowedContent = true;
  CKEDITOR.scriptLoader.load('/assets/js/ckeditor-add-comment.js');
};
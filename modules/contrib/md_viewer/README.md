# Microsoft Document Viewer

Microsoft Document Viewer intends to provide a new format for the File 
field type.
This format presents the file as a fully rendered object 
within a web page - i.e.
it displays the contents of the file as appropriate
to its filetype (Adobe Acrobat .pdf,Microsoft Word .doc/.docx, 
Microsoft Excel .xls/.xlsx, 
Microsoft Powerpoint .ppt/.pptx), using the Microsoft Document Viewer.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/md_viewer).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/md_viewer).

## Table of contents

- Requirements
- Installation
- Configuration
- Creator and Maintainer

## Requirements

This module requires the following modules:

- [Core File Module] https://www.drupal.org/docs/8/core/modules/file

## Installation

Install as you would normally install a contributed Drupal module.

## Configuration

To use this field format, add a File field to a new 
or existing content type (such as Basic Page) on 
the content type's Manage Fields form. 
The File field type provides only one widget type 
- File - so select that. On the content type's 'Manage Display' form,
there will be a drop-down select list of available 
display formats for the File field. 
To display the file within the embedded Microsoft Docs viewer,
choose the 'Embedded Microsoft Document Viewer Formatter' format.
The document viewer is styled using the mdoc-viewer-field.html.twig template.
By default, the viewer's width is 100% and its height is 600px,
with a frameborder zero. 
Provided field settings to change the width and height.

## Creator and Maintainer

Current maintainer:

- [Abhilash RS)](https://www.drupal.org/u/rsabhilash)

Supporting organizations:

- [Applab Qatar](https://www.drupal.org/applab-qatar)

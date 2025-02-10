/**
 * @file
 * Postlight parser behaviors.
 */
(function ($, Drupal) {

  'use strict';
  // Argument passed from InvokeCommand.
  $.fn.postlightParser = function (argument) {
    let data = JSON.parse(argument);
    if(data.data){
      let fieldTitle = data.title;
      let fieldContent = data.content;
      let fieldImage = data.image;
      let fieldExcerpt = data.excerpt;
      let fieldLink = data.link;
      $('[name^="' + fieldTitle + '"][name$="[value]"]').val(data.data.title);
      $('[name^="' + fieldLink + '"][name$="[title]"]').val(data.data.title);
      $('[name^="' + fieldContent + '"][name$="[value]"]').val(data.data.content);
      $('[name^="' + fieldExcerpt + '"][name$="[value]"]').val(data.data.excerpt);
      $('[name^="' + fieldExcerpt + '"][name$="[summary]"]').val(data.data.excerpt);

      if(fieldContent != '' && $('[name^="' + fieldContent + '"][name$="[value]"]').length){
        let editorId = $('[name^="' + fieldContent + '"][name$="[value]"]').prop("id");
        if(typeof CKEDITOR !== 'undefined' && CKEDITOR.instances[editorId] != undefined){
          let editor = CKEDITOR.instances[editorId].setData(data.data.content);
        }
        if(typeof CKEditor5 !== 'undefined'){
          editorId = $('[name^="' + fieldContent + '"][name$="[value]"]').data("ckeditor5-id").toString();
          const editor = Drupal.CKEditor5Instances.get(editorId);
          if(editor) {
            editor.setData(data.data.content);
          }
        }
      }
      if(fieldImage != '' && data.data.image_upload){
        $('#' + fieldImage).html(data.data.image_upload);
      }

    }
  };
} (jQuery, Drupal));

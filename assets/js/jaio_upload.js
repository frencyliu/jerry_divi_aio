
jQuery(document).ready(function($){
    // Define a variable wkMedia
    let wkMedia;
    let jaio_upload_input;
    let jaio_thumbnail;
        $('.jaio_upload_btn').each(function(){
            $(this).click(function(e) {
                jaio_upload_input = $(this).siblings('.jaio_upload_input');
                jaio_thumbnail = $(this).siblings('p').children('img.jaio_thumbnail');
                e.preventDefault();
                // If the upload object has already been created, reopen the dialog
                  if (wkMedia) {
                  wkMedia.open();
                  return;
                }
                // Extend the wp.media object
                wkMedia = wp.media.frames.file_frame = wp.media({
                  title: '選擇圖片',
                  button: {
                  text: '選擇圖片'
                }, multiple: false });

                // When a file is selected, grab the URL and set it as the text field's value
                wkMedia.on('select', function() {
                  let attachment = wkMedia.state().get('selection').first().toJSON();
                  jaio_upload_input.val(attachment.url);
                  jaio_thumbnail.attr('src', attachment.url);
                });
                // Open the upload dialog
                wkMedia.open();
              });
        });


  });
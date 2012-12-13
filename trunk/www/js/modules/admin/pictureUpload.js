$(function() {
    $("#pictureUpload").replaceWith("<div id='file-uploader'></div>");
// UPLOADER
    var fileuploader = $("#file-uploader");
    
    if (fileuploader.length > 0) {
        $('#file-uploader').fineUploader({
            request: {
              endpoint: baseUrl + "../upload.php"
            },
            sizeLimit: 5*1024*1024, // max size
            allowedExtensions: ['png','jpg','jpeg','gif','bmp'],
            showMessage: function(message) {
              // Using Twitter Bootstrap's classes and jQuery selector and method
              $('#file-uploader').append('<div class="alert alert-error">' + message + '</div>');
            },
            retry: {
                preventRetryResponseProperty: true
            },
            fileTemplate: '<li>' +
              '<div class="qq-progress-bar"></div>' +
              '<span class="qq-upload-spinner"></span>' +
              '<span class="qq-upload-finished"></span>' + 
              '<span class="qq-upload-file" id="logoimg"></span>' +
              '<span class="qq-upload-size"></span>' +
              '<a class="qq-upload-cancel" href="#">{cancelButtonText}</a>' +
         //     '<a class="remove" href="#">Remove</a>' +
         //     '<a class="qq-upload-retry" href="#">Retry</a>' +
              '<span class="qq-upload-status-text">{statusText}</span>' +
            '</li>',
            debug: false
      })
      .on('complete', function(event, id, filename, responseJSON){ 
        uploaded = true;         
        $("#picture").val(responseJSON.filename);
        $(".qq-upload-list").html("<img src='" + baseUrl + "/img/picture/" + responseJSON.filename + "' alt='" + responseJSON.filename + "' />");
      })
      .on('submit', function(id, filename){     
        uploaded = false;  
      })
      .on('cancel', function(id, filename){
        uploaded = true;  
      });
    }

})

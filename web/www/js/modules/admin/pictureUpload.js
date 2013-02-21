$(function() {
//    $("#picture-label").parent(".row").hide();
    $("body").on("click", "#default-picture", function() {
        $("#event-image").replaceWith('<img id="event-image" src="'+ baseUrl +'/img/action-screen.jpg" width="100px" height="67px" />');
        $("#picture").val("");
        $(".qq-upload-success").remove();
        $("#default-picture").remove();
        return false;
    })

// UPLOADER
    var fileuploader = $("#picture-upload");
    
    if (fileuploader.length) {
        fileuploader.fineUploader({
            request: {
              endpoint: uploadPictureUrl
            },
            sizeLimit: 5*1024*1024, // max size
            allowedExtensions: ['png','jpg','jpeg','gif','bmp'],
            retry: {
                preventRetryResponseProperty: true
            },
            text: {
                    uploadButton: "Nahrát vlastní obrázek",
                    cancelButton: "Zrušit nahrávání",
                    retryButton: "Skusit znovu",
                    failUpload: 'Upload neůspěšný',
                    dragZone: "Přesuňte obrázek sem pro nahrání",
                    formatProgress: "{percent}% ze {total_size}",
                    waitingForResponse: "Čekejte"
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
        $(".picture img").attr("src", baseUrl + "/img/picture/" + responseJSON.filename).attr("alt", responseJSON.filename);
        $("#picture-upload .qq-uploader .qq-upload-button div").html("Nahrát jiný obrázek")
        if ($("#default-picture").length == 0) {
            $("#picture-upload .qq-uploader .qq-upload-button").after("<button id='default-picture' class='qq-upload-button'>Původní obrázek</button>")
        }
      })
      .on('submit', function(id, filename){ 
        $(".qq-upload-success").remove();
        uploaded = false;  
      })
      .on('cancel', function(id, filename){
        uploaded = true;  
      });
    }

    if ($("#picture").val() !== "") {
        $(".picture img").attr("src", baseUrl + "/img/picture/" + $("#picture").val());
        $("#picture-upload .qq-uploader .qq-upload-button div").html("Nahrát jiný obrázek");
        $("#picture-upload .qq-uploader .qq-upload-button").after("<button id='default-picture' class='qq-upload-button'>Původní obrázek</button>")
    }
})

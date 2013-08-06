jQuery(document).ready(function($) {
    Dropzone.options.fileboxUpload = {
        init: function() {
            this.on("success", function(file) {
                //alert("Upl");
                location.reload();
            });
        }
    };
});

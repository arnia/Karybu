jQuery(document).ready(function($) {
    Dropzone.options.fileboxUpload = {
        init: function() {
            var uploaded = 0;
            this.on("success", function(file) {
                uploaded++;
                if(this.files.length == uploaded){
                    location.reload();
                }
            });

        }
    };
});

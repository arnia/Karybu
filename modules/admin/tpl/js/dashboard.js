(function($){

    $(document).ready(function(){

        $("#trash_document").on("click", function(){
                $("#latest_documents[name='type']").val("trash");
            $("#latest_documents").submit();
        });

        $("#delete_document").on("click", function(){
            $("#latest_documents[name='type']").val("delete");
            $("#latest_documents").submit();
        });

    });

}(jQuery));
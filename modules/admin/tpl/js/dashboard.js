(function($){

    /**
     * Dashboard visitors graph
     */
    function kDashboardGraph() {}
    kDashboardGraph.initialize = function(){
        $("#chart").attr('width',$("#chart-container").width());
        RGraph.Redraw();
    };
    kDashboardGraph.redraw = function() {
        kDashboardGraph.initialize();
    }

    $(document).ready(function(){

        $(".trash_document").on("click", function(){
            var form = $(this).closest("form.latest_documents");
            form.find("input[name='type']").val("trash");
            form.submit();
        });

        $(".delete_document").on("click", function(){
            var form = $(this).closest("form.latest_documents");
            form.find("input[name='type']").val("delete");
            form.submit();
        });

    });

    $(window).load(function(){
        kDashboardGraph.initialize();

        $(window).resize(function(){
            kDashboardGraph.redraw();
        });
    });

}(jQuery));
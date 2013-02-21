jQuery(function($){

    var ingredientWrap;
    var ingredientList;
    var paginate;
    var ingredientItemTemplate;
    var cur_page;
    var total_page;
    
    function initVariables(){
        ingredientWrap = jQuery('div.ingredientWrap');
        ingredientList = ingredientWrap.find('div.ingredientList');
        paginate = ingredientWrap.find('div.paginate');
        ingredientItemTemplate = ingredientWrap.find('div.ingredientList > dl').remove();
        
        paginate.find('button.next').click(function(){
            if(parseInt(cur_page) < parseInt(total_page)) loadingredients(parseInt(cur_page) + 1);
        });
        paginate.find('button.prev').click(function(){
            if(parseInt(cur_page) > 1) loadingredients(parseInt(cur_page) - 1);
        });
    }

    function loadingredients(page){
        if (!page) page = 1;
        jQuery.exec_json('material.dispMaterialList',{page:page, list_count:4}, renderingredient);
    }
    
    function renderingredient(data){
        // Show 'No data' msg if the data is empty
        if(!data.page_navigation.total_count){
            ingredientWrap.find('p.noData').css('display','block');
            paginate.css('display','none');
            return;
        }
        
        // Remove existing ingredient list
        ingredientList.children().remove();
        
        // Pagination
        paginate.find('> span').text(data.page_navigation.cur_page+'/'+data.page_navigation.total_page);
        cur_page  = data.page_navigation.cur_page;
        total_page = data.page_navigation.total_page;
        
        // ingredient list
        jQuery.each(data.material_list, function(){
            var tpl = ingredientItemTemplate.clone();
            var tmp_content=this.content;

            tpl.addClass('xe_'+this.type);
            tpl.find('dt').text(this.regdate.substring(0,4)+'.'+this.regdate.substring(4,6)+'.'+this.regdate.substring(6,8)+' '+this.regdate.substring(8,10)+':'+this.regdate.substring(10,12));
            tpl.find('dd > div.ingredientConetnt').html(this.content);
            tpl.find('dd button._insert_item').click(function(){
                insertingredient(tmp_content);
            });

            ingredientList.append(tpl);
        });
    }
    
    function insertingredient(ingredient){
        var iframe_obj = opener.editorGetIFrame(opener.editorPrevSrl)
        opener.editorReplaceHTML(iframe_obj, ingredient);
        window.close();
    }

    // load ingredients
    initVariables();
    loadingredients(1);

});

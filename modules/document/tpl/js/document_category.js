/**
 * @file   modules/document/tpl/js/document_category.js
 * @author Arnia (dev@karybu.org)
 * @brief  document 모듈의 category tree javascript
 **/

function Tree(url){
    // clear tree;
    jQuery('#menu > ul > li > ul').remove();
    if (jQuery("ul.simpleTree > li > a").size() ==0) {
        jQuery('<a href="#" class="add"><img src="../common/js/plugins/ui.tree/images/iconAdd.gif" /></a>').bind("click",function(e){addNode(0,e);}).appendTo("ul.simpleTree > li");
    }

    //ajax get data and transeform ul il
    jQuery.get(url,function(data){
        var $ = jQuery;
        $(data).find("node").each(function(i){
            var text = $(this).attr("text");
            var node_srl = $(this).attr("node_srl");
            var parent_srl = $(this).attr("parent_srl");
            var color = $(this).attr("color");
            var url = $(this).attr("url");

            // node
            var node = '';
            if(color && color !='transparent'){
                node = $('<li id="tree_'+node_srl+'"><span style="color:'+color+';">'+text+'</span></li>');
            }else{
                node = $('<li id="tree_'+node_srl+'"><span>'+text+'</span></li>');
            }

            // button
            $('<a href="#" class="add"><img src="../common/js/plugins/ui.tree/images/iconAdd.gif" /></a>').bind("click",function(e){
                $("#tree_"+node_srl+" > span").click();
                addNode(node_srl,e);
                return false;
            }).appendTo(node);

            $('<a href="#" class="modify"><img src="../common/js/plugins/ui.tree/images/iconModify.gif" /></a>').bind("click",function(e){
                $("#tree_"+node_srl+" > span").click();
                modifyNode(node_srl,e);
                return false;
            }).appendTo(node);

            $('<a href="#" class="delete"><img src="../common/js/plugins/ui.tree/images/iconDel.gif" /></a>').bind("click",function(e){
                deleteNode(node_srl);
                return false;
            }).appendTo(node);

            // insert parent child
            if(parent_srl>0){
                if($('#tree_'+parent_srl+'>ul').length==0) $('#tree_'+parent_srl).append($('<ul>'));
                $('#tree_'+parent_srl+'> ul').append(node);
            }else{
                if($('#menu ul.simpleTree > li > ul').length==0) $("<ul>").appendTo('#menu ul.simpleTree > li');
                $('#menu ul.simpleTree > li > ul').append(node);
            }

        });

        //button show hide
        jQuery("#menu li").each(function(){
            if(jQuery(this).parents('ul').size() > max_menu_depth) jQuery("a.add",this).hide();
            if(jQuery(">ul",this).size()>0) jQuery(">a.delete",this).hide();
        });


        // draw tree
        simpleTreeCollection = jQuery('.simpleTree').simpleTree({
            autoclose: false,
            afterClick:function(node){
                jQuery('#category_info').html("");
                //alert("text-"+jQuery('span:first',node).text());
            },
            afterDblClick:function(node){
                //alert("text-"+jQuery('span:first',node).text());
            },
            afterMove:function(destination, source, pos){
                if(destination.size() == 0){
                    Tree(xml_url);
                    return;
                }
                var module_srl = jQuery("#fo_category input[name=module_srl]").val();
                var parent_srl = destination.attr('id').replace(/.*_/g,'');
                var source_srl = source.attr('id').replace(/.*_/g,'');

                var target = source.prevAll("li:not([class^=line])");
                var target_srl = 0;
                if(target.length >0){
                    target_srl = source.prevAll("li:not([class^=line])").get(0).id.replace(/.*_/g,'');
                    parent_srl = 0;
                }

                jQuery.exec_json("document.procDocumentMoveCategory",{ "module_srl":module_srl,"parent_srl":parent_srl,"target_srl":target_srl,"source_srl":source_srl},
                function(data){
                    jQuery('#category_info').html('');
                   if(data.error > 0) Tree(xml_url);
                });

            },

            // i want you !! made by sol
            beforeMovedToLine : function(destination, source, pos){
                return (jQuery(destination).parents('ul').size() + jQuery('ul',source).size() <= max_menu_depth);
            },

            // i want you !! made by sol
            beforeMovedToFolder : function(destination, source, pos){
                return (jQuery(destination).parents('ul').size() + jQuery('ul',source).size() <= max_menu_depth-1);
            },
            afterAjax:function()
            {
                //alert('Loaded');
            },
            animate:true
            ,docToFolderConvert:true
        });



        // open all node
        nodeToggleAll();
    },"xml")
    .fail(function(jqXHR, textStatus, errorThrown) {
        alert(errorThrown);
    });
}
function addNode(node,e){
    var params ={
            "category_srl":0
            ,"parent_srl":node
            ,"module_srl":jQuery("#fo_category [name=module_srl]").val()
            };

    jQuery.exec_json('document.getDocumentCategoryTplInfo', params, function(data){
        jQuery('#myModal')
            .find('.modal-title').text('Add Category').end()
            .find('#category_info').html(data.tpl).end()
            .find('.btn-primary').on('click', function(){
                jQuery('#insert_cat').submit();
            }).end()
            .modal();
    });
}

function modifyNode(node,e){
    var params ={
            "category_srl":node
            ,"parent_srl":0
            ,"module_srl":jQuery("#fo_category [name=module_srl]").val()
            };

    jQuery.exec_json('document.getDocumentCategoryTplInfo', params, function(data){
        jQuery('#myModal')
            .find('.modal-title').text('Edit Category').end()
            .find('#category_info').html(data.tpl).end()
            .find('.btn-primary').on('click', function(){
                jQuery('#insert_cat').submit();
            }).end()
            .modal();
    });
}


function nodeToggleAll(){
    jQuery("[class*=close]", simpleTreeCollection[0]).each(function(){
        simpleTreeCollection[0].nodeToggle(this);
    });
}

function deleteNode(node){
    if(confirm(lang_confirm_delete)){
        jQuery('#category_info').html("");
        var params ={
                "category_srl":node
                ,"parent_srl":0
                ,"module_srl":jQuery("#fo_category [name=module_srl]").val()
                };

        jQuery.exec_json('document.procDocumentDeleteCategory', params, function(data){
            if(data.error==0) Tree(xml_url);
        });
    }
}

/* After entering the item category */
function completeInsertCategory(ret_obj) {
    jQuery('#category_info').html("");
    Tree(xml_url);
}

function hideCategoryInfo() {
    jQuery('#category_info').html("");
}

/* Updating the list of categories */
function doReloadTreeCategory(module_srl) {
    var params = {'module_srl':module_srl};

    // Request to the server, the node should be able to edit the information.
    var response_tags = new Array('error','message', 'xml_file');
    exec_xml('document', 'procDocumentMakeXmlFile', params, completeInsertCategory, response_tags, params);
}

function doCategoryFormMove() {
	jQuery(function($){ $('#fo_category').appendTo(document.body); });
}

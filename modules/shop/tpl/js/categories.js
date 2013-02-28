function Category()
{
    this.category_srl = null;
    this.module_srl = null;
    this.parent_srl = 0;
    this.file_srl = null;
    this.title = null;
    this.description = null;
    this.product_count = null;
    this.friendly_url = null;
    this.include_in_navigation_menu = 'Y';
    this.regdate = null;
    this.last_update = null;
}


function slugify(text) {
    text = text.replace(/[^-a-zA-Z0-9,&\s]+/ig, '');
    text = text.replace(/-/gi, "_");
    text = text.replace(/\s/gi, "-");
    return text;
}

function fillFormWithCategory($category, $parent_title)
{
    jQuery("#category_srl").val($category.category_srl);
    jQuery("#parent_srl").val($category.parent_srl);

    jQuery("#filename").val($category.filename);
    jQuery("#category_image").attr("src", $category.filename);
    if($category.filename)
    {
        jQuery("#image_container").show();
    }
    else
    {
        jQuery("#image_container").hide();
    }

    jQuery("#title").val($category.title);
    jQuery("#description").val($category.description);
    jQuery("#friendly_url").val($category.friendly_url);
    if($category.include_in_navigation_menu == 'Y')
    {
        jQuery("#include_in_navigation_menu").attr('checked', true);
    }
    else
    {
        jQuery("#include_in_navigation_menu").attr('checked', false);
    }

    jQuery("#regdate").val($category.regdate);
    jQuery("#last_update ").val($category.last_update);

    if($category.parent_srl !== 0)
    {
        jQuery("#parent_srl").parent().show();
    }
    else
    {
        jQuery("#parent_srl").parent().hide();
    }

    if($parent_title !== undefined)
    {
        jQuery("#parent_title").text($parent_title);
    }
}

function showCategoryForm()
{
    if(jQuery("#categoryFormContainer").is(":visible"))
    {
        jQuery("#categoryFormContainer").fadeOut().fadeIn();
    }
    else
    {
        jQuery("#categoryFormContainer").fadeIn();
    }
}


jQuery(document).ready(function($)
{
    //friendly url stuff
    frUrl = $('#friendly_url');
    if ($('#title').val().length) frUrl.data('xe-changed', true);
    $('#title').on('keyup', function() {
        if (!frUrl.data('xe-changed')) {
            frUrl.val(slugify($(this).val()));
        }
    });
    frUrl.on('keyup', function(){
        $(this).data('xe-changed', true);
        if (typeof frTimeout !== 'undefined' && frTimeout) clearTimeout(frTimeout);
        frTimeout = setTimeout(function(){
            $.exec_json('shop.procShopToolCheckFriendlyUrlAvailability', {type: 'category', slug: frUrl.val()}, function(data){
                var av = $('#availability');
                if (data.notAvailable) av.show(300).removeClass('available').text('not available');
                else av.hide(300);
            });
        }, 1000);
    });
    //end friendly url stuff

    $("#tree_0 a.add.root").click(function(){
        fillFormWithCategory(new Category());
        $("#categoryFormContainer h4").text("Add product category");
        showCategoryForm();
    });

    // Add behaviour
    $("#tree_0 ul a.add").click(function(){
        var $category = new Category();
        var $id = $(this).parent().attr("id");
        var $category_srl = $id.replace("tree_", "");
        var $category_title = $(this).parent().find("span:first").text().trim();
        $category.parent_srl = $category_srl;

        fillFormWithCategory($category, $category_title);
        $("#categoryFormContainer h4").text("Add product category");
        showCategoryForm();
    });

    // Edit behaviour
    $("#tree_0 ul a.modify").click(function(){
        var $id = $(this).parent().attr("id");
        var $category_srl = $id.replace("tree_", "");
        var $parent_node = $(this).parent("li").parent("ul").parent("li");
        var $parent_category_title = $parent_node.find("span:first").text().trim();

        $.exec_json('shop.procShopServiceGetCategory'
            , { category_srl : $category_srl}
            , function(data){
                if(data.error != 0)
                {
                    alert("Error " + data.error + " " + data.message);
                    return;
                }
                $category = data.category;
                fillFormWithCategory($category, $parent_category_title);
                $("#categoryFormContainer h4").text("Edit product category");
                showCategoryForm();
            }
        );
    });

    // Delete behaviour
    $("#tree_0 ul a.delete").click(function(){
        if(!confirm(xe.lang.confirm_delete)) return false;

        var $id = $(this).parent().attr("id");
        var $category_srl = $id.replace("tree_", "");

        $.exec_json('shop.procShopServiceDeleteCategory'
            , { category_srl : $category_srl}
            , function(data){
                if(data.error != 0)
                {
                    alert("Error " + data.error + " " + data.message);
                    return;
                }
                location.reload();
            }
        )
    });

    simpleTreeCollection = jQuery('.simpleTree').simpleTree({
        autoclose: false,
        afterClick:function(node){
        },
        afterMove:function(destination, source, pos){
            if(destination.size() == 0){
                location.href = location.href;
                return;
            }
            var module_srl = jQuery("#categoryForm input[name=module_srl]").val();
            var parent_srl = destination.attr('id').replace(/.*_/g,'');
            var source_srl = source.attr('id').replace(/.*_/g,'');

            var target = source.prevAll("li:not([class^=line])");
            var target_srl = 0;
            if(target.length >0){
                target_srl = source.prevAll("li:not([class^=line])").get(0).id.replace(/.*_/g,'');
                parent_srl = 0;
            }

            jQuery.exec_json("shop.procShopServiceMoveCategory",
                { "module_srl":module_srl
                    ,"parent_srl":parent_srl
                    ,"target_srl":target_srl
                    ,"source_srl":source_srl},
                function(data){
                    completeReload(data);
                });

        },
        animate:true,
        docToFolderConvert:true
    });
});
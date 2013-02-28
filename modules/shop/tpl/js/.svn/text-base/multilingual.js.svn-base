/** Represents a piece of translated text; holds all languages that it's been translated too */
function Translation(text)
{
    this.name = null;
    this.text = text;
    this.translations_list = {};

    // region Public methods
    this.isNew = function() {
        return this.name ? false : true;
    }

    this.save = function(callback) {
        var $this = this;
        jQuery.exec_json('module.procModuleAdminInsertLang', this.translations_list, function(data){
            if(data.error) return;

            $this.name = data.name;
            callback();
        });
    }

    this.getKey = function() {
        if(this.isNew())
            return this.text;
        return '$user_lang->'+ this.name;
    }

    this.getValue = function() {
        if(this.isNew())
            return this.text;
        if(this.translations_list)
            return this.translations_list[xe.current_lang];
        return '';
    }

    this.loadTranslationList = function(on_success) {
        var $this = this;
        var callback = function(data){
            if(data.error) return;

            for(var translation_index in data.lang_list)
            {
                $this.translations_list[data.lang_list[translation_index].lang_code] = data.lang_list[translation_index].value;
            }

            on_success();
        };

        if($this.isNew()) {
            getTranslationListByValue($this.text, callback);
        }
        else {
            getTranslationListByName($this.name, callback);
        }
    };
    // endregion

    // region Private methods
    var getTranslationListByName = function(name, callback) {
        if(!name)
        {
            callback({
                    data : { lang_list : [] }
                });
            return;
        }
        jQuery.exec_json('module.getModuleAdminLangListByName', { lang_name: name, site_srl: site_srl }, callback);
    }

    var getTranslationListByValue = function(value, callback) {
        if(!value)
        {
            callback({
                data : { lang_list : [] }
            });
            return;
        }
        jQuery.exec_json('module.getModuleAdminLangListByValue', { value: value, site_srl: site_srl }, callback);
    }
    // endregion

    // region Constructor
    __construct = function($this) {
        // Save translation key, if text has been translated before
        if(/^\$user_lang->(.+)$/.test($this.text))
        {
            $this.name = RegExp.$1;
        }
    }(this);
    // endregion
}

function TranslationDisplayer(multiLanguageFormField)
{
    var translationsContainerID = '#multiLanguageValues';

    // region Public methods
    this.show = function()
    {
        multiLanguageFormField.translation.loadTranslationList(function(){
            // Populate input / textareas for each language with values from the database
            for(var translation_lang_code in multiLanguageFormField.translation.translations_list)
            {
                jQuery(translationsContainerID)
                    .find("li." + translation_lang_code + " " + multiLanguageFormField.field_type)
                    .val(multiLanguageFormField.translation.translations_list[translation_lang_code])
            }

            // For current value, set user entered value instead of database value
            jQuery(translationsContainerID)
                .find("li." + xe.current_lang + " " + multiLanguageFormField.field_type)
                .val(multiLanguageFormField.translation.getValue())

            // If using input, hide textareas and show input and viceversa
            other_field_type = (multiLanguageFormField.field_type == 'input' ? 'textarea' : 'input');
            jQuery(translationsContainerID)
                .find("li " + other_field_type)
                .hide();
            jQuery(translationsContainerID)
                .find("li " + multiLanguageFormField.field_type)
                .show();

            // Show modal dialog
            modal_width = multiLanguageFormField.field_type == 'input' ? 400 : 600;
            modal_height = multiLanguageFormField.field_type == 'input' ? 300 : 400;
            jQuery(translationsContainerID)
                .dialog({
                    modal: true
                    , width: modal_width
                    , height: modal_height})
                .show();
        });
    }

    this.hide = function()
    {
        jQuery(translationsContainerID).dialog("close").hide();
    }

    this.saveCurrentValues = function()
    {
        var currentValues = {};
        jQuery(translationsContainerID + " " + multiLanguageFormField.field_type).each(function(k,v){
            var $this = jQuery(this);
            currentValues[$this.parent('li').attr('class')] = $this.val()
        });

        multiLanguageFormField.translation.translations_list = currentValues;
        multiLanguageFormField.translation.save(function() {
            multiLanguageFormField.update();
        });

    }
    // endregion
}

/**
 * Handles logic for a multiLanguageInput div
 *
 * This kind of container holds two inputs - one hidden and one visible
 * The hidden is the value that gets saved in the database
 * The visible one is what the user sees
 *
 * This class encapsulates the update mechanics for this - updating
 * the hidden div when the other one changes and such
 *
 * @param inputContainer
 * @constructor
 */
function MultiLanguageFormField(inputContainer, field_type)
{
    this.jquery_element = null;
    this.hidden_input = null;
    this.visible_input = null;
    this.translation = null;
    this.field_type = null;

    this.update = function() {
        if(this.translation.isNew())
        {
            this.hidden_input.val(this.visible_input.val());
            this.translation = new Translation(this.hidden_input.val());
        }
        else
        {
            this.visible_input.val(this.translation.getValue());
            this.hidden_input.val(this.translation.getKey());
        }
    }


    // region Constructor
    __construct = function($this) {
        $this.jquery_element = inputContainer;
        $this.hidden_input = inputContainer.find("input[type='hidden']");
        $this.field_type = field_type;

        switch(field_type)
        {
            case "input":
                $this.visible_input = inputContainer.find("input[type='text']");
                break;
            case "textarea":
                $this.visible_input = inputContainer.find("textarea");
                break;
            default:
                return;
        }

        $this.translation = new Translation($this.hidden_input.val());
    }(this);
    // endregion
}

jQuery(document).ready(function($){
    var translationDisplayer = null;

    $(".multiLanguageInput input[type='text']").on("change", function(){
        var multiLanguageInput = new MultiLanguageFormField($(this).parent(), 'input');
        multiLanguageInput.update();
    });

    $(".multiLanguageTextarea textarea").on("change", function(){
        var multiLanguageTextarea = new MultiLanguageFormField($(this).parent(), 'textarea');
        multiLanguageTextarea.update();
    });

    $(".multiLanguageInput a.translate").on("click", function(){
        var multiLanguageInput = new MultiLanguageFormField($(this).parent(), 'input');
        translationDisplayer = new TranslationDisplayer(multiLanguageInput);
        translationDisplayer.show();
    });

    $(".multiLanguageTextarea a.translate").on("click", function(){
        var multiLanguageTextarea = new MultiLanguageFormField($(this).parent(), 'textarea');
        translationDisplayer = new TranslationDisplayer(multiLanguageTextarea);
        translationDisplayer.show();
    });

    $("#saveTranslations").on("click", function(){
        translationDisplayer.saveCurrentValues();
        translationDisplayer.hide();
        translationDisplayer = null;
    });

    $("#closeTranslations").on("click", function(){
        translationDisplayer.hide();
        translationDisplayer = null;
    });
});
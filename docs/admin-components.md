Karybu admin components
=======================

- [Modal windows](#modal-windows)
	- [Filebox](#filebox)
- [Legacy modal windows](#legacy-modal-windows)
	- [Basic modal](#basic-modal)
	- [Differences between Bootstrap and XE modals](#differences-between-bootstrap-and-xe-modals)
	- [Methods](#methods)
	- [Events](#events)
- [Multilanguage inputs](#multilanguage-inputs)
	- [How this works](#how-this-works)
- [Tables](#tables)
	- [Sortable tables](#sortable-tables)
	- [Simple / detailed view tables](#simple--detailed-view-tables)

Modal windows
----------------
Karybu uses [Twitter Bootstrap modals](http://twitter.github.io/bootstrap/javascript.html#modals) for its modal windows. You should first check their documentation in order to get started.

There are a few features added on top of Bootstrap though:

### Filebox

You can automatically insert a file selector in your modal window, that will load all files added through the Filebox feature.

The syntax for adding this is:

```html
<!-- Button to trigger modal -->
<a href="#myModal" class="filebox" data-toggle="modal">Open filebox modal</a>

<!-- Modal -->
<div class="modal hide fade">
    <div class="filebox_list">
        
    </div>
</div>
```

At runtime, a list of files will be injected in the `#filebox_list` element.

Legacy modal windows
--------------
This section is useful if you have an XE module you wish to update so that it will work with Karybu, helping you understand how the legacy modal windows worked and the differences between XE and Boostrap.

### Basic modal

```html
    <!-- Button to trigger modal -->
    <a href="#myModal" class="modalAnchor">Open modal</a> 

    <!-- Modal -->
    <div class="xe-modal" id="myModal">
    Bla bla bla
    </div>
```

For basic usage, the markup XE used is very similar to the way Bootstrap works. However, the main difference between them is related to the way events are handled. In XE, all events are bound to the triggering element, whereas in Bootstrap they are bound to the modal window itself. 

### Differences between Bootstrap and XE modals

 | XE | Bootstrap
 --- | --- | --- 
Triggering element |  `class="modalAnchor"` | `data-toggle="modal"`
Modal container | `class="modal"` / `class="xe-modal"` | `class="modal hide fade"`
Events | bound to triggering element | bound to modal container

### Methods

Methods allow you to manually trigger the opening / closing of a modal window.

Opening / closing a modal in XE

```javascript
$("a.modalAnchor").trigger("open");
```

Opening / closing a modal in Bootstrap 

```javascript 
$("div#myModal").modal('show');
```

 | XE | Bootstrap
--- | --- | ---
Show | `open` | `show`
Hide | `close` | `hide`

### Events

Binding an event in XE
```javascript
$("a.modalAnchor").bind('before-open.mw', function() {
  alert("just opening ...");
})
```

Binding an event with Bootstrap

```javascript
$("div#myModal").on("show", function() {
    alert("just opening ...");
})
```

 | XE | Bootstrap
--- | --- | ---
Before open | `before-open.mw` | `show`
After open  | `after-open.mw` | `shown`
Before close | `before-close.mw` | `hide`
After close | `after-close.mw` | `hidden`

Multilanguage inputs
---------------------

Multilanguage inputs are used when the content edited by the user needs to be saved in several languages. For example, the title of a page, or the text of a product in an e-shop. 

In order to add a multilangauge input the syntax is:

```html
<!-- Input that will support multiple languages -->
<div class="multiLangEdit">
    <input type="hidden" name="browser_title" value="{htmlspecialchars($module_info->browser_title)}" class="vLang" />
    <input type="text" id="browser_title" value="{$module_info->browser_title}" class="vLang" />
    <span class="desc"><a href="#langEdit" class="tgAnchor editUserLang" data-effect="slide">{$lang->cmd_set_multilingual}</a></span>
</div>

<!-- Container showing the list of enabled languages -->
 {@$use_multilang = true}
<include target="../../admin/tpl/common/include.multilang.html" />
```

For textareas, the syntax is very similar:
```html
<!-- Textarea that will support multiple languages -->
<div class="multiLangEdit">
    <input type="hidden" name="product_description" value="<!--@if(strpos($product_description, '$user_lang->') === false)-->{$product_description}<!--@else-->{htmlspecialchars($product_description)}<!--@end-->" class="vLang" />
	<textarea rows="8" cols="42" class="vLang">{$product_description}</textarea>
	<span class="desc"><a href="#langEditTextarea" class="editUserLang tgAnchor">{$lang->cmd_set_multilingual}</a></span>
</div>
<!-- Container showing the list of enabled languages -->
{@$use_multilang_textarea = true}
<include target="../../admin/tpl/common/include.multilang.textarea.html" />
```

### How this works

Say you want to translate the title of one of the pages in your website and that the name of the text input is "browser_title".

If the user doesn't translate the text, but just enters a title in the input, that value will be saved as is for "browser_title".

However, if the user open the "Select language" popup and translates the text, Karybu will generate a unique key for this title, holding the translated value. This is the value that will also be persisted in the database and looks like `$user_lang->abc123`. This is why two inputs are needed: the hidden input contains the value `$user_lang->abc1234` while the text input contains the value assigned to this variable `{$user_lang->abc1234}`;

Tables
================

Tables in admin should be styled according to [Twitter Bootstrap styles](http://twitter.github.io/bootstrap/base-css.html#tables).

Besides the features Bootstrap offers, Karybu also exposes a custom set of functionality:

### Sortable tables

Here is a sample sortable table:

```html
<table class="table sortable span4">
    <caption>Here's a caption</caption>
    <thead>
        <th>Column 1 title</th>
        <th>Column 2 title</th>
    </thead>
    <tbody class="uDrag">
        <tr>
            <td>
            	<div class="kActionIcons">
            		<button type="button" class="dragBtn">
            			<i class="kMove"></i>
            		</button>
            	</div>
            </td>
            <td>Row 1</td>
        </tr>
        
        ...
        
    </tbody>
</table>
```

The only mandatory items for this to work are the `sortable` class on the `table` tag and the button with the `dragBtn` class; all the other ones are for making it look consistent with the rest of the admin.

### Simple / detailed view tables

Karybu uses this component for displaying a list of modules, addons or widgets, for example. In the "simple" view, just the module name is displayed; when the user toggles the "detailed" view, all module info is displayed.

Here is a sample markup:

```html
<table class="table dsTg">
  <caption>
    Here's a caption
    <p>
        <a href="#" class="toggleBtn">
            <span class="hide">Simple</span>
            <span class="show">Detailed</span>
        </a>
    </p>
  </caption>
  <thead>
    <th>Column 1 title</th>
    <th class="title">Column 2 title</th>
  </thead>
  <tbody>
    <tr>
        <td>First row</td>
        <td class="title">
            <p>First paragraphs will always be visible</p>
            <p>Subsequent ones will be hidden</p>
            <p>when the toggle button is pressed</p>
        </td>
    </tr>
    <tr>
        <td>Second row</td>
        <td class="title">
            <p>This text will also always be visible</p>
            <p class="update">This needs updating!</p>
        </td>
    </tr>
    <tr>
        <td>Third row</td>
        <td class="title">
            <p>Lorem ipsum</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed feugiat varius turpis id auctor. </p>
        </td>
    </tr>
  </tbody>
</table>
```

Collapsible sections
--------------------

When viewing an admin page, it is usually divided in sub-sections. For instance, the "Editor settings" view has three sections: "Editor Preview", "Editor Options" and "Editor Components". When sections become too big, it is useful to collapse them, to get faster to the content. 

In order to make a section collapsible, it is enough to add the "h2" or "h3" class to it:

```html
<h2 class="h2">
```

// TODO Rename class to something like "toggle-section" or such


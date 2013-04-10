Karybu admin components
=======================

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
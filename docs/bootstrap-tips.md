---
layout: docs
title: Bootstrap tips
category: docs
---

Bootstrap tips
==============

How to make form elements fluid
-------------------------------

-   create a \<div\> container with the class "row-fluid"

-   put a "span" class on the form element (span12 is full-width, span1 is 1/12
    of 100%)

**Example:**

```html
<div class="row-fluid">
        <select class="span6"> <!-- this means half-width of the container -->
            <option>...</option>
            <option>...</option>
        </select>
</div>
```

---
title: Getting started
layout: upcoming
---

<script type="text/javascript">
    $(document).ready(function(){
        var tabs_nav = $("#toc_1").next("ul");
        tabs_nav.addClass("nav nav-tabs");
        tabs_nav.find("li:first").addClass("active");
        tabs_nav.find("li a").attr("data-toggle", "tab");

        var tabs = $("#toc_1").nextUntil("#toc_2",".tab-pane");
        tabs.each(function(){
            if(!$(this).attr("id")) return;
            var content = $(this).nextUntil(".tab-pane","p, div");
            content.appendTo($(this));
        });
        tabs.wrapAll("<div class='tab-content'></div>");
        tabs.first().addClass("active");

    });
</script>

# Getting started

## 1. Download

* [Archive](#archive)
* [Composer](#composer)
* [Clone git repository](#clone_git)

<div id="archive" class="tab-pane"></div>

Download the latest stable version of Karybu as a [zip](https://github.com/arnia/Karybu/archive/master.zip) or [tar.gz](https://github.com/arnia/Karybu/archive/master.tar.gz) archive.


<div id="composer" class="tab-pane"></div>

Just run the following [Composer](getcomposer.org) command:

```sh
composer create-project karybu/cms your_local_karybu_folder master
```

If you don"t already have Composer installed you can download it from [here](http://getcomposer.org/download/).

<div id="clone_git" class="tab-pane"></div>

In order to get the latest version of Karybu directly from Github, you should already have `git` installed (read [here](https://help.github.com/articles/set-up-git) how to do this).

1\. Clone the Karybu repository

```sh
git clone https://github.com/arnia/Karybu.git karybu
```

2\. Install dependencies

This is done using [Composer](getcomposer.org) - if don"t already have it installed you can download it from [here](http://getcomposer.org/download/).

</span>

<div class="tab-pane"></div>

## 2. Install

1. Move / copy the Karybu files to your web server
2. Create a database for Karybu
3. Navigate to Karybu in your web browser and follow the installation steps!

If you run into any trouble, take a look at the [Troubleshooting](docs/troubleshooting.html) page.

## 3. Create your website

Go ahead and start adding content and customizing your website!

[Drop us a line](contribute.html) for any suggestions, ideas or issues you might have.

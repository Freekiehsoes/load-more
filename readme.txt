=== Plugin Name ===

Contributors: Freek Attema
Plugin Name: Easy load more posts
Tags: wp, js, php, lazyloading
Author URI: https://www.linkedin.com/in/freek-attema-726b9b171/?originalSubdomain=nl
Requires at least: 2.3
Tested up to: 5.7.2
Stable tag: 1.1
Version: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

This wordpress plugin adds an easy way to create a "load more" functionality to a page

== Example ==

`<?php
    $loadMore = new LoadMore('blog', 3, [
        'order'				=> 'DESC'
    ]);
    $loadMore->setOutletSelector('.outlet-element');
    $loadMore->setLoadMoreSelector('.load-more-button');
    $loadMore->setTemplate(function () {
        get_template_part('includes/archive-news-item/archive-news-item');
    });
    $loadMore->init();
?>`

Using the code from above the plugin will load in 3 posts from the post type "blog" into the outlet selector (.outlet-element).
When the users presses the element set by the function setLoadMoreSelector (.load-more-button) the plugin will load in the next page

The plugin also allows custom arguments for the query such as the order you see in the example.

A piece of html can be given in the setTemplate function.
This template will be ran for every post with the post globally available (this makes it possible to use functions like the_title() ).

The plugin will automatically include the javascript that is used.
# Surrealwebs Primary Taxonomy

## About

Often times you find yourself wanting to specify a primary category for you posts. This plugin will allow you to do that and more. As the name implies, you can specify a primary term for any (public) taxonomy. This include custom taxonomies as well as custom post types.

WordPress does not share terms between taxonomies so primary terms are added to a "Primary Taxonomy" custom private taxonomy. Taxonomy terms are related to primary taxonomy terms through the term meta table. Two relationships are setup in the meta table linking the terms, Primary to Original and Original to Primary. These relationships makes it easier to cross-reference terms and is faster than a post meta lookup. 

## Installation

Download the code and drop it in your plugins directory. Login to the WordPress admin and activate the plugin.

## Setup

Once activated, there is an SW Primary Taxonomies menu item under the Settings menu.

On the settings page you will see a list of the current post types and their associated **PUBLIC** taxonomies. Select which taxonomies you would like to have a primary term and save. Now, when you are adding or editing content you will notice a "Primary *INSERT TAXONOMY NAME HERE*" select box. Only the terms you have currently added to the content will appear in the list.

## What to expect

Once you have a primary term assigned to your content you can search for those terms and the content will appear in your results.

## Known Issue

This plugin is not perfect, here are the issues that need to be resolved.

* Gutenberg... The Gutengerg interface completely changed how taxonomies are listed on the edit screens. The markup is different, the field names and ids are different, etc. I have found know issues in the classic editor interface. 
* Multi-site is untested so it may or may not work. There is nothing blog specific in the code so it should be fine, but your mileage may vary. 

## TODO

What's a WordPress plugin without a road map? Here are a few things I'd like to get fixed or added.

* Gutenberg support
* Multi-site support
* Add more automated tests.



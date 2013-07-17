# WooCommerce Subcategories widget

Plugin that allows you to list product subcategories from selected parent category.

#### Update 1.2.4

* "current" class added to current category ("li" element) for styling purposes.

#### Update 1.2.3

* Added new option to output subcategories of selected category with parent category in hierarchy (nested lists)

Example ("Parent" category is selected):

Before option is turned on:

* Subcategory
* Subcategory
* Subcategory

After option is turned on:

* Parent
	* Subcategory
	* Subcategory
	* Subcategory

	
```
wp_show_subcategories_menu( category_id_or_slug, show_subcategories_of_current_active_category , hide_children_of_current_subcategory, show_parent_category )
```


#### Update 1.2.2

* Added ```wp_show_subcategories_menu``` function to output menu in template without using widget (no additional widget area needed)
* Added ```wp_show_subcats``` shortcode
* Minor tweaks

```
wp_show_subcategories_menu( category_id_or_slug, show_subcategories_of_current_active_category , hide_children_of_current_subcategory )
```

```show_subcategories_of_current_active_category``` and ```hide_children_of_current_subcategory``` must be ```true``` or ```false```.

Example (one level menu):

```
wp_show_subcategories_menu('goods', false, true);
``` 

Same example for shortcode:

```
[wp_show_subcats cat='goods' hide_children=true] 
```
Shortcode attributes:

* cat - category id or slug
* subcategories_of_current - shows subcategories of current active category
* hide_children - hides children of current subcategory (one level menu)

*Note:* If you don't need some of the attributes, don't put them like ```attr=false```, just dont put it in the shortcode at all. Less writing is better ;)

#### Update 1.2.1

* Added option to output subcategories of current active category.

#### Update 1.2

* Added option for categories featured images output with customizable width and height
* Added option to choose what to output (title, image, or both)
* Added hierarchical output for subcategories of selected category (can be collapsed with WooTweak accordion widget enhancement) 
* Hierarchical output can be turned off
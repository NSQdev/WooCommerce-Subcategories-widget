<?php
/*
Plugin Name: WooCommerce Subcategories widget
Plugin URI: 
Description: 
Version: 1.0
Author: Dark Delphin
Author URI: 
*/

class woocom_subcats extends WP_Widget {
    
    function __construct()
    {
	$params = array(
		'name' => 'WooCommerce Subcategories widget',
	    'description' => 'Shows subcategories from chosen category' // plugin description that is showed in Widget section of admin panel
	);
	
	parent::__construct('woocom_subcats', '', $params);
    }
    
    function form($instance)
    {
	extract($instance);

	$taxlist = get_terms('product_cat', 'hide_empty=0');
	?>
		<p>
		    <label for="<?php echo $this->get_field_id('title'); ?>">Title: </label>
		    <input
			type="text"
			class="widefat"
			id="<?php echo $this->get_field_id('title'); ?>"
			name="<?php echo $this->get_field_name('title'); ?>"
			value="<?php if(isset($title)) echo esc_attr($title) ?>"
		    />
		</p>
		<p>
			<select
				id="<?php echo $this->get_field_id('catslist'); ?>"
				name="<?php echo $this->get_field_name('catslist'); ?>"
			>
				<?php
				foreach ($taxlist as $tax) 
				{
					if(get_term_children( $tax->term_id, 'product_cat' )) 
					{
						if(isset($catslist) && $catslist == $tax->term_id) $selected = ' selected="selected"';
						else $selected = '';
						echo '<option value="'.$tax->term_id.'"'.$selected.'>'.$tax->name.'</option>';						
					}
				}
				?>
			</select>
		</p>
	    <!--some html with input fields-->
	<?php
    }
    
    function widget($args, $instance)
    {
	extract($args);
	extract($instance);
	
	echo $before_widget;
	    echo $before_title . $title . $after_title;
	    echo '<ul>';
	    if(isset($catslist))
		{
			$args = array(
				'title_li'           => '',
				'show_option_none'   => '',
				'hide_empty'         => 0,
				'child_of'           => $catslist,
				'taxonomy'           => 'product_cat'
			);
			wp_list_categories( $args );
		}
		echo '</ul>';
	    
	echo $after_widget;
    }
}

add_action('widgets_init', 'woocom_subcats_register_function');

function woocom_subcats_register_function()
{
    register_widget('woocom_subcats');
}
?>
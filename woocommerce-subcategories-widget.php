<?php
/*
Plugin Name: WooCommerce Subcategories widget
Plugin URI: 
Description: Shows subcategories from chosen or current active category
Version: 1.2.3
Author: Pavel Burov aka Dark Delphin
Author URI: http://pavelburov.com
*/

class woocom_subcats extends WP_Widget {
    
    function __construct()
    {
	$params = array(
		'name' => 'WooCommerce Subcategories',
	    'description' => 'Shows subcategories from chosen category' // plugin description that is showed in Widget section of admin panel
	);
	
	parent::__construct('woocom_subcats', '', $params);

	add_shortcode( 'wp_show_subcats', array($this, 'shortcode') );
    }
    
    function form($instance)
    {
	extract($instance);
	
	
	
	$taxlist = get_terms('product_cat', 'hide_empty=0');
	?>
		<p>
		    <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title: '); ?></label>
		    <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if(isset($title)) echo esc_attr($title) ?>"/>
		</p>
		<p>
		    <input type="checkbox" id="<? echo $this->get_field_id('show_subcategories_of_current_active_category'); ?>" name="<? echo $this->get_field_name('show_subcategories_of_current_active_category'); ?>" value="1" <?php checked( '1', $show_subcategories_of_current_active_category ); ?>/>
		    <label for="<? echo $this->get_field_id('show_subcategories_of_current_active_category'); ?>"><?php echo __('Show subcategories of current active category'); ?></label>
		</p>
		<p>
		    <input type="checkbox" id="<? echo $this->get_field_id('hide_children_of_current_subcategory'); ?>" name="<? echo $this->get_field_name('hide_children_of_current_subcategory'); ?>" value="1" <?php checked( '1', $hide_children_of_current_subcategory ); ?>/>
		    <label for="<? echo $this->get_field_id('hide_children_of_current_subcategory'); ?>"><?php echo __('Hide children of current subcategory'); ?></label>
		</p>
		<p>
		    <input type="checkbox" id="<? echo $this->get_field_id('show_category_title'); ?>" name="<? echo $this->get_field_name('show_category_title'); ?>" value="1" <?php checked( '1', $show_category_title ); ?>/>
		    <label for="<? echo $this->get_field_id('show_category_title'); ?>"><?php echo __('Show category title'); ?></label>
		</p>
		<p>
		    <input type="checkbox" id="<? echo $this->get_field_id('show_category_thumbnail'); ?>" name="<? echo $this->get_field_name('show_category_thumbnail'); ?>" value="1" <?php checked( '1', $show_category_thumbnail ); ?>/>
		    <label for="<? echo $this->get_field_id('show_category_thumbnail'); ?>"><?php echo __('Show categories thumbnails'); ?></label>
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id('thumb_width'); ?>"><?php echo __('Thumbnail width and height:'); ?></label><br>
		    <input type="number" class="widefat" style="width: 80px;" id="<?php echo $this->get_field_id('thumb_width'); ?>" name="<?php echo $this->get_field_name('thumb_width'); ?>" min="1" value="<?php if(isset($thumb_width)) echo esc_attr($thumb_width); else echo '150'; ?>"/> x 
		
		    <!-- <label for="<?php echo $this->get_field_id('thumb_height'); ?>"><?php echo __('Thumbnail height'); ?></label> -->
		    <input type="number" class="widefat" style="width: 80px;" id="<?php echo $this->get_field_id('thumb_height'); ?>" name="<?php echo $this->get_field_name('thumb_height'); ?>" min="1" value="<?php if(isset($thumb_height)) echo esc_attr($thumb_height); else echo '150'; ?>"/>
		</p>
		<p>
			<select id="<?php echo $this->get_field_id('catslist'); ?>" name="<?php echo $this->get_field_name('catslist'); ?>">
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
		<p>
	    	<input type="checkbox" id="<? echo $this->get_field_id('show_parent_category'); ?>" name="<? echo $this->get_field_name('show_parent_category'); ?>" value="1" <?php checked( '1', $show_parent_category ); ?>/>
	    	<label for="<? echo $this->get_field_id('show_parent_category'); ?>"><?php echo __('Show parent category with subcategories hierarchy'); ?></label>
		</p>
	    <!--some html with input fields-->
	<?php
    }

    function walk($cat , $show_category_thumbnail, $show_category_title)
    {	
    	$args = array(
				'hierarchical'       => 1,
				'show_option_none'   => '',
				'hide_empty'         => 0,
				'parent'			 => $cat,
				'taxonomy'           => 'product_cat'
			);
    	$next = get_categories($args);
    	if( $next )
    	{
    		echo '<ul class="children">';
    		foreach ($next as $n)
    		{
    			$link = get_term_link( $n->slug, $n->taxonomy );
				$output = '<li><a href="'.$link.'">';

				if(isset($show_category_thumbnail))
				{
				$thumbnail_id = get_metadata( 'woocommerce_term', $n->woocommerce_term_id, 'thumbnail_id', true );
				
					   	if ($thumbnail_id) 
					   	{
					   		$image = wp_get_attachment_url( $thumbnail_id );
					   		if(isset($thumb_width) && $thumb_width > 0) $width = ' width="'.$thumb_width.'"';
					   		if(isset($thumb_height) && $thumb_height > 0) $height = ' height="'.$thumb_height.'"';
					   		$output .= '<img src="'.$image.'"'.$width.$height.'>';
					   		// <img src="<?php echo $image; >" />  		
					   	}
				}
				if(isset($show_category_title))
				{
					$output .= $n->name;
				}
				if(!isset($show_category_title) && !isset($show_category_thumbnail))
				{
					$output .= $n->name;
				}
				$output .= '</a></li>';
				echo $output;

				$this->walk($n->term_id, $show_category_thumbnail, $show_category_title);
				
    		}
    		echo '</ul>';
    	}
    }
    
    function widget($args, $instance)
    {
    extract($args);
	
	if(!empty($instance))
	{
		extract($instance);
		echo $before_widget;
	    if($title) echo $before_title . $title . $after_title;
	} 
	    
	    if(isset($catslist) && !isset($show_subcategories_of_current_active_category))
		{
			if(!preg_match('/[0-9]+/', $catslist)) $catslist = get_term_by( 'slug', $catslist, 'product_cat')->term_id;

			$args = array(
				'title_li'           => '',
				'hierarchical'       => 1,
				'show_option_none'   => '',
				'echo'               => 0,
				'depth'				 => 1,
				'hide_empty'         => 0,
				'parent'             => $catslist,
				'child_of'           => $catslist,
				'taxonomy'           => 'product_cat'
			);
		}
		elseif(isset($show_subcategories_of_current_active_category))
		{
			$isproduct = false;

			$args = array(
				'title_li'           => '',
				'hierarchical'       => 1,
				'show_option_none'   => '',
				'echo'               => 0,
				'depth'				 => 1,
				'hide_empty'         => 0,
				// 'parent'			 => $cid,
				'taxonomy'           => 'product_cat'
			);

			$current_tax = get_query_var('product_cat'); // slug

			if(!$current_tax || $current_tax == '')
			{
				$terms = get_the_terms( get_the_ID(), 'product_cat' );

				$isproduct = true;

				if($trems)
				{
					foreach ( $terms as $term ) 
					{
						$ids = $term->term_id;
					}
					$cid = $ids;
				}
			}
			else
			{
				$args['parent'] = get_queried_object()->term_id;
			}	
		}

		if($isproduct)
		{
			$categories = get_the_terms( get_the_ID(), 'product_cat' );
		}
		else
		{
			$categories = get_categories( $args );
		}

		if(!empty($categories))
		{

			if($show_parent_category)
			{
				echo '<ul class="product-categories woosubcats">';
				$link = get_term_link( $catslist, 'product_cat' );
				$parent = get_term( $catslist, 'product_cat' );

				echo '<li><a href="'.$link.'">'.$parent->name.'</a></li>';
					echo '<ul class="children">';				
			}
			else
			{ 
				echo '<ul class="product-categories woosubcats">';
			}

			foreach($categories as $cat)
			{
				$link = get_term_link( $cat->slug, $cat->taxonomy );
				$output = '<li><a href="'.$link.'">';

				if(isset($show_category_thumbnail))
				{
				$thumbnail_id = get_metadata( 'woocommerce_term', $cat->woocommerce_term_id, 'thumbnail_id', true );
				if(!$thumbnail_id) $thumbnail_id = get_metadata( 'woocommerce_term', $cat->term_id, 'thumbnail_id', true );;
				
					   	if ($thumbnail_id) 
					   	{
					   		$image = wp_get_attachment_url( $thumbnail_id );
					   		if(isset($thumb_width)) $width = ' width="'.$thumb_width.'"';
					   		if(isset($thumb_height)) $height = ' height="'.$thumb_height.'"';
					   		$output .= '<img src="'.$image.'"'.$width.$height.'>';
					   	}
				}
				if(isset($show_category_title))
				{
					$output .= $cat->name;
				}
				if(!isset($show_category_title) && !isset($show_category_thumbnail))
				{
					$output .= $cat->name;
				}
				$output .= '</a></li>';
				echo $output;

				if(isset($hide_children_of_current_subcategory)) continue;
				$this->walk($cat->term_id, $show_category_thumbnail, $show_category_title);
			}
			echo '</ul>';

			if($show_parent_category) echo '</ul>';
		}

	if(!empty($instance)) echo $after_widget;
    }

    function shortcode( $atts )
    {
    	extract( shortcode_atts( array(
    	  'cat' => 'default',
	      'subcategories_of_current' => false,
	      'hide_children' => false,
	      'show_parent_category' => false
     	), $atts ) );
     	
     	return wp_show_subcategories_menu($cat, $subcategories_of_current, $hide_children, $show_parent_category);
    }
}

add_action('widgets_init', 'woocom_subcats_register_function');

function woocom_subcats_register_function()
{
    register_widget('woocom_subcats');
}

if(!function_exists('wp_show_subcategories_menu'))
{
	function wp_show_subcategories_menu( $cat, $show_subcategories_of_current_active_category = false, $hide_children_of_current_subcategory = false, $show_parent_category = false)
	{
		$submenu = new woocom_subcats();
		$args = array(
			'catslist' => $cat
			);
		if($show_subcategories_of_current_active_category == true) $args['show_subcategories_of_current_active_category'] = true;

		if($hide_children_of_current_subcategory == true) $args['hide_children_of_current_subcategory'] = true;

		if($show_parent_category == true) $args['show_parent_category'] = true;

		$instance = array(
			'before_title' => '',
			'title' => '',
			'after_title' => ''
			);
		
		echo $submenu->widget($args, $instance);
	}
}
?>
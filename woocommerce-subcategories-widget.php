<?php
/*
Plugin Name: WooCommerce Subcategories widget
Plugin URI: https://github.com/darkdelphin/WooCommerce-Subcategories-widget
Description: Shows subcategories from chosen or current active category
Version: 1.4.0
Author: Pavel Burov (Dark Delphin)
Author URI: http://pavelburov.com
*/

if ( !defined('ABSPATH') ) die;

class Woocommerce_subcategories_widget extends WP_Widget {

	// Constructor
	function Woocommerce_subcategories_widget() {

		$params = array(
			'classname' => 'woocommerce_subcategories_widget',
		    'description' => 'Shows subcategories of chosen category' // plugin description that is showed in Widget section of admin panel
		);

		// id, name, other parameters
		$this->WP_Widget('woocommerce_subcategories_widget', 'WooCommerce Subcategories', $params);
	}

	function widget( $args, $instance ) {

		extract( $args );

		$title = $instance['title'];
		$catslist = $instance['catslist'];
		$show_active = $instance['show_active'];
		$show_same_level = $instance['show_same_level'];

		$input = array(
			'title' => $instance['title'],
			'catslist' => $instance['catslist'],
			'show_active' => $instance['show_active'],
			'show_same_level' => $instance['show_same_level']
			);

		echo $before_widget;

		if ( $title ) echo $before_title . $title . $after_title;

		$this->get_categories($input);

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['catslist'] = strip_tags($new_instance['catslist']);
		$instance['show_active'] = !empty($new_instance['show_active']) ? 1 : 0;
		$instance['show_same_level'] = !empty($new_instance['show_same_level']) ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );

		$title = esc_attr( $instance['title'] );
		$catslist = esc_attr( $instance['catslist'] );

		$show_active = isset( $instance['show_active'] ) ? (bool) $instance['show_active'] : false;
		$show_same_level = isset( $instance['show_same_level'] ) ? (bool) $instance['show_same_level'] : false;

		$taxlist = get_terms('product_cat', 'hide_empty=0');
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('show_active') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_active') ); ?>"<?php checked( $show_active ); ?> />
			<label for="<?php echo $this->get_field_id('show_active'); ?>"><?php _e( 'Show subcategories of current active category' ); ?></label>
		</p>
		<p>
			<?php echo __('Or choose permanent category below:') ?>
		</p>
		<p>
			<select class="widefat" id="<?php echo $this->get_field_id('catslist'); ?>" name="<?php echo $this->get_field_name('catslist'); ?>">
				<?php
				foreach ($taxlist as $tax) 
				{
					if(get_term_children( $tax->term_id, 'product_cat' )) 
					{
						echo '<option value="'.$tax->term_id.'" '.selected($catslist, $tax->term_id).'>'.$tax->name.'</option>';						
					}
				}
				?>
			</select>
		</p>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('show_same_level') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_same_level') ); ?>"<?php checked( $show_same_level ); ?> />
			<label for="<?php echo $this->get_field_id('show_same_level'); ?>"><?php _e( 'Always show categories of the same level' ); ?></label>
		</p>
		<?php
	}

	function get_categories($input) {

		global $wp_query, $post, $woocommerce;

		extract( $input );

		$isproduct = false;
		$groundlevel = false;

		if(isset($catslist) && !$show_active)
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
		elseif($show_active)
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

				if($terms)
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
				if($show_same_level)
				{
					$args['parent'] = get_queried_object()->term_id;
					$categories = get_categories( $args );

					if(empty($categories)) 
					{
						$groundlevel = true;
						
						if(get_queried_object()->parent != 0)
						{
							$args['parent'] = get_queried_object()->parent;
							$categories = get_categories( $args );
						}
						else
						{
							$args['parent'] = get_queried_object()->term_id;
							$categories = get_categories( $args );
						}
					}
					else $groundlevel = false;		
				}
				else $args['parent'] = get_queried_object()->term_id;
			}	
		}

		if($isproduct) $categories = get_the_terms( get_the_ID(), 'product_cat' );
		else $categories = get_categories( $args );

		if(!empty($categories))
		{
			if($show_active)
			{
				if($groundlevel)
				{
					$link = get_term_link( (int)get_queried_object()->parent, 'product_cat' );
					$parent = get_term( (int)get_queried_object()->parent, 'product_cat' );
				}
				else
				{
					if(property_exists(get_queried_object(), 'term_id'))
					{
						$link = get_term_link( (int)get_queried_object()->term_id, 'product_cat' );
						$parent = get_term( (int)get_queried_object()->term_id, 'product_cat' );
					}
				}			
			}
			else
			{
				$link = get_term_link( (int)$catslist, 'product_cat' );
				$parent = get_term( (int)$catslist, 'product_cat' );
			}
			
			$level = 0;

			// if($show_parent_category && !empty($parent) )// && !$parent->errors)
			// {
			// 	if(get_queried_object() && property_exists($wp_query->queried_object, 'slug') && $wp_query->queried_object->slug == $parent->slug) $class = ' class="current"';
			// 	else $class = '';
						
			// 	echo '<ul class="product-categories woosubcats level'.$level.'">';

			// 	if($show_category_thumbnail)
			// 	{

			// 	$thumbnail_id = get_metadata( 'woocommerce_term', $parent->woocommerce_term_id, 'thumbnail_id', true );
			// 	if(!$thumbnail_id) $thumbnail_id = get_metadata( 'woocommerce_term', $parent->term_id, 'thumbnail_id', true );
				
			// 		   	if ($thumbnail_id) 
			// 		   	{
			// 		   		// $image = wp_get_attachment_url( $thumbnail_id );
			// 		   		if($thumbnail_size)
			// 		   		{
			// 		   			$image = wp_get_attachment_image_src( $thumbnail_id, $thumbnail_size );
			// 		   			$image = $image[0];
			// 		   		}
			// 		   		else
			// 		   		{
			// 		   			$image = wp_get_attachment_image_src( $thumbnail_id, 'medium'  );
			// 		   			$image = $image[0];
			// 		   		}
			// 				// $image = wp_get_attachment_image_src( $thumbnail_id, 'medium'  );
			// 				// $image = $image[0];
			// 				// keywords for sizes (thumbnail, medium, large or full) 

			// 		   		if(isset($thumb_width) && $thumb_width > 0) $width = ' width="'.$thumb_width.'"';
			// 		   		if(isset($thumb_height) && $thumb_height > 0) $height = ' height="'.$thumb_height.'"';
			// 		   		// $output .= '<img src="'.$image.'"'.$width.$height.'>';

			// 		   		echo '<li'.$class.'><a href="'.$link.'"><img src="'.$image.'"'.$width.$height.'></a>';

			// 		   		if($show_category_title)
			// 		   		{
			// 		   			echo '<a href="'.$link.'">'.$parent->name.'</a>';
			// 		   		}

			// 		   		echo '</li>';
			// 		   	}
			// 		   	else
			// 		   	{
			// 		   		if(isset($thumb_width) && $thumb_width > 0) $width = ' width="'.$thumb_width.'"';
			// 		   		if(isset($thumb_height) && $thumb_height > 0) $height = ' height="'.$thumb_height.'"';
			// 		   		// $output .= '<img src="'.plugins_url().'/woocommerce/assets/images/placeholder.png"'.$width.$height.'>';

			// 		   		echo '<li'.$class.'><a href="'.$link.'"><img src="'.plugins_url().'/woocommerce/assets/images/placeholder.png"'.$width.$height.'></a>';

			// 		   		if($show_category_title)
			// 		   		{
			// 		   			echo '<a href="'.$link.'">'.$parent->name.'</a>';
			// 		   		}

			// 		   		echo '</li>';
			// 		   	}
			// 	}
			// 	else
			// 	{
			// 		if(!$isproduct) echo '<li'.$class.'><a href="'.$link.'">'.$parent->name.'</a>'; //</li>';
			// 	}

				
			// 	$level++;
			// 		echo '<ul class="children level'.$level.'">';
			// 		$level++;				
			// }
			// else
			// { 
				echo '<ul class="product-categories subcategories level'.$level.'">';
				$level++;
			// }

			foreach($categories as $cat)
			{
				if(get_queried_object() && property_exists($wp_query->queried_object, 'slug') && $wp_query->queried_object->slug == $cat->slug) $class = ' class="current"';
				else $class = '';

				$link = get_term_link( $cat->slug, $cat->taxonomy );
				echo '<li'.$class.'>'; //<a class="img" href="'.$link.'">';

				if($show_category_thumbnail)
				{
					if(property_exists($cat, 'woocommerce_term_id')) $thumbnail_id = get_metadata( 'woocommerce_term', $cat->woocommerce_term_id, 'thumbnail_id', true );
					else $thumbnail_id = get_metadata( 'woocommerce_term', $cat->term_id, 'thumbnail_id', true );;
				
					   	if ($thumbnail_id) 
					   	{
					   		// $image = wp_get_attachment_url( $thumbnail_id );
					   		if($thumbnail_size)
					   		{
					   			$image = wp_get_attachment_image_src( $thumbnail_id, $thumbnail_size );
					   			$image = $image[0];
					   		}
					   		else
					   		{
					   			$image = wp_get_attachment_image_src( $thumbnail_id, 'medium'  );
					   			$image = $image[0];
					   		}

					   		if(isset($thumb_width) && $thumb_width > 0) $width = ' width="'.$thumb_width.'"';
					   		if(isset($thumb_height) && $thumb_height > 0) $height = ' height="'.$thumb_height.'"';
					   		echo '<img src="'.$image.'"'.$width.$height.'>';

					   	}
					   	else
					   	{
					   		if(isset($thumb_width) && $thumb_width > 0) $width = ' width="'.$thumb_width.'"';
					   		if(isset($thumb_height) && $thumb_height > 0) $height = ' height="'.$thumb_height.'"';
					   		echo '<img src="'.plugins_url().'/woocommerce/assets/images/placeholder.png"'.$width.$height.'>';					   		
					   	}
				}
				if($show_category_title)
				{
					echo '<a href="'.$link.'">'.$cat->name.'</a>';
				}
				if(!$show_category_title && !$show_category_thumbnail)
				{
					echo '<a href="'.$link.'">'.$cat->name.'</a>';
				}
				
				if(!$hide_children_of_current_subcategory) $this->walk($cat->term_id, $show_category_thumbnail, $show_category_title, $level, $thumb_width, $thumb_height);
				
				echo '</li>';
			}
			echo '</ul>';

			// if($show_parent_category && !empty($parent)) echo '</li></ul>';
		}
	}

	function walk($cat , $show_category_thumbnail, $show_category_title, $level, $thumb_width = 0, $thumb_height  = 0)
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
    		echo '<ul class="children level'.$level.'">';
    		$level++;
    		foreach ($next as $n)
    		{
    			if(get_queried_object()->slug == $n->slug) $class = ' class="current"';
				else $class = '';

    			$link = get_term_link( $n->slug, $n->taxonomy );
				$output = '<li'.$class.'><a href="'.$link.'">';

				if($show_category_thumbnail)
				{
				$thumbnail_id = get_metadata( 'woocommerce_term', $n->woocommerce_term_id, 'thumbnail_id', true );
				
					   	if ($thumbnail_id) 
					   	{
					   		// $image = wp_get_attachment_url( $thumbnail_id );
					   		if($thumbnail_size)
					   		{
					   			$image = wp_get_attachment_image_src( $thumbnail_id, $thumbnail_size );
					   			$image = $image[0];
					   		}
					   		else
					   		{
					   			$image = wp_get_attachment_image_src( $thumbnail_id, 'medium'  );
					   			$image = $image[0];
					   		}

					   		if(isset($thumb_width) && $thumb_width > 0) $width = ' width="'.$thumb_width.'"';
					   		if(isset($thumb_height) && $thumb_height > 0) $height = ' height="'.$thumb_height.'"';
					   		$output .= '<img src="'.$image.'"'.$width.$height.'>';
					   		// <img src="<?php echo $image; >" />  		
					   	}
					   	else
					   	{
					   		if(isset($thumb_width) && $thumb_width > 0) $width = ' width="'.$thumb_width.'"';
					   		if(isset($thumb_height) && $thumb_height > 0) $height = ' height="'.$thumb_height.'"';
					   		$output .= '<img src="'.plugins_url().'/woocommerce//assets/images/placeholder.png"'.$width.$height.'>';					   		
					   	}
				}
				if($show_category_title)
				{
					$output .= $n->name;
				}
				if(!$show_category_title && !$show_category_thumbnail)
				{
					$output .= $n->name;
				}
				$output .= '</a></li>';
				echo $output;

				$this->walk($n->term_id, $show_category_thumbnail, $show_category_title, $level, $thumb_width = 0, $thumb_height = 0);
				
    		}
    		echo '</ul>';
    	}
    }
}

function woocommerce_subcategories_widget_register() {
    register_widget('woocommerce_subcategories_widget');
}
add_action('widgets_init', 'woocommerce_subcategories_widget_register');


?>
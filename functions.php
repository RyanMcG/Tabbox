<?php
/*
Plugin Name: Tabbox
Plugin URI: http://honors-scholars.osu.edu/studioct/portfolio.html
Description: A simple tabbed box for displaying content and an image.
Version: 1.4
Author: Ryan McGowan
Author URI: http://smi.th-ro.in/
License: GPL2
*/


//This call and the following function enqueue the necessary script and style for this plugin.
add_action('init', 'setupTabbox');
add_shortcode('tabbox', 'shortcodeTabbox');

function setupTabbox()
{
	$tb_vers = '1.3';
	wp_enqueue_script('tabbox', WP_PLUGIN_URL.'/tabbox/tabbox.js', array('jquery'), $tb_vers);
	wp_enqueue_style('tabbox', WP_PLUGIN_URL.'/tabbox/tabbox.css', $tb_vers, 'all');
	wp_enqueue_style('tabbox-mod', get_bloginfo('stylesheet_directory').'/css/tabbox-mod.css');

//UNCOMMENT THE SECTION BELOW TO CREATE THE "TAB" POST TYPE 	

	register_post_type( 'tabbox',
		array(
			'labels' => array(
					'name' => __('Tabs'),
					'singular_name' => __('Tab'),
					'add_new_item' => __('Add New Tab'),
					'edit_item' => __('Edit Tab'),
					'new_item' => __('New Tab'),
					'view_item' => __('View Tab'),
					'search_items' => __('Search Tabs'),
					'not_found' => __('No tabs found'),
					'not_found_in_trash' => __('No tabs found in Trash'),
				),
			'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'page-attributes'),
			'public' => true,
			'publicly_queryable' => true,
			'taxonomies' => array('category')
		  )
	);

}

//This function can be placed anywhere to generate a Tabbox
function placeTabbox($tb_query_args) //If the commented section above is uncommented the $tb_query_args['post_type'] value can be set to 'tab'.
{
	$tb_query_args = array_merge(array('post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1), (array) $tb_query_args);
	$output .= '
	<div class="tb">';
	$output .= placeTabboxContents($tb_query_args);
	$output .= '
		<div class="clear"></div>
	</div>';
	return $output;
}

function printTabbox($tb_query_args)
{
	echo placeTabbox($tb_query_args);
}

function placeTabboxContents($tb_query_args)
{
	//Filters are added (and later removed) to change the output of The Loop function calls.
	$tb_query = new WP_Query($tb_query_args);
	$output;
	//START CONTENT OUTPUT
	$output .= '
		<div class="tb-selector-bar">';
	
	//Add selector bar title filter.
	add_filter('the_title', 'tb_title');
	if($tb_query->have_posts())
	{
		while($tb_query->have_posts())
		{
			$tb_query->the_post();
			$output .= get_the_title($tb_query->post->ID);
		}
	}
	$output .= '
		</div>';
	//Remove selector bar title filter.
	remove_filter('the_title', 'tb_title');
	
	//Add filters for actual content
	add_filter('the_title', 'tbc_title');
	add_filter('the_content', 'tb_content');
	add_filter('post_thumbnail_html', 'tb_thumbnail');
	
	$tb_query->rewind_posts();
	if($tb_query->have_posts())
	{	
		while($tb_query->have_posts())
		{
			$tb_query->the_post();
			global $more;
			$more = 0;
			$output .= '
			<div class="tb-content">';
			$output .= get_the_title($tb_query->post->ID);
			get_the_post_thumbnail($tb_query->post->ID, array(250, 250));
			$content = get_the_content( '<span class="readmore">Read More &raquo;</span>');
			$content = apply_filters('the_content', $content);
			$output .= str_replace(']]>', ']]&gt;', $content);			
			$output .= '
			</div>';
		}
	}
	//END CONTENT OUTPUT
	//Then the filters are removed so they don't change other posts.
	remove_filter('the_title', 'tbc_title');
	remove_filter('the_content', 'tb_content');
	remove_filter('post_thumbnail_html', 'tb_thumbnail');
	
	return $output;
}

function tb_title($title)
{
	return '
	<div class="tb-selector-piece">
		'.$title.'
	</div>';
}

function tbc_title($title)
{
	return '
	<h5 class="tb-content-title">
		<a href="'.get_bloginfo('url').'/undergraduate/area-of-study/'.sanitize_title_with_dashes($title).'">'.$title.'</a>
	</h5>';
}

function tb_thumbnail($thumbnail)
{
	return '
	<div class="tb-image">
		'.$thumbnail.'
	</div>';
}

function tb_content($content)
{
	return '
	<div class="tb-text">
		'.$content.'
	</div>';
}

function shortcodeTabbox($atts)
{
	$atts = (array) $atts;
	unset($a[0]);
	return placeTabbox($atts);
}

?>

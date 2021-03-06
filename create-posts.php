<?php

// Load WordPress
require_once '/insert-path-to-code/wp-load.php';
require_once '/insert-path-to-code/wp-admin/includes/taxonomy.php';

// Set timezone so times are calculated correctly
date_default_timezone_set("America/Chicago");
function buildPost($title, $cat, $content) {
	// Create post
	$id = wp_insert_post(array(
	    'post_title'    => $title,
	    'post_content'  => $content,
	    'post_date'     => date('Y-m-d H:i:s'),
	    'post_author'   => 'znlynn75',
	    'post_type'     => 'post',
	    'post_status'   => 'publish',
	));
	
	if($id) {
	
	    // Set category
	    wp_set_post_terms($id, $cat, 'category');
	
	    // Add meta data
	    //add_post_meta($id, 'meta_key', $metadata);
	
	} else {
	    echo "WARNING: Failed to insert post into WordPress\n";
	}
}

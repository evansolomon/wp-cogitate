<?php
/**
 * @package Cogitate
 */

// Enqueue parent styles instead of @import'ing them
function cogitate_parent_styles() {
	wp_enqueue_style( 'cogitate-parent', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'cogitate_parent_styles', 1 );

// Load Typekit and output it's inline script
function cogitate_typekit() {
	wp_enqueue_script( 'cogitate_typekit', '//use.typekit.net/ohi7ntj.js' );
	add_action( 'wp_head', function() {
		echo "<script type='text/javascript'>try{Typekit.load();}catch(e){}</script>\n";
	} );
}
add_action( 'wp_enqueue_scripts', 'cogitate_typekit' );

// Register menus used on the front page
function cogitate_frontpage_menus() {
	register_nav_menus( [
		'frontpage_profiles' => 'Front page profiles',
		'frontpage_projects' => 'Front page projects',
	] );
}
add_action( 'init', 'cogitate_frontpage_menus' );

// Undo some TwentyTwelve-isms
function cogitate_undo_twentytwelve() {
	unregister_nav_menu( 'primary' );
	add_action( 'wp_enqueue_scripts', function() {
		wp_dequeue_script( 'twentytwelve-navigation' );
	}, 11 );
}
add_action( 'init', 'cogitate_undo_twentytwelve' );

// Link headers to the blog instead of the front page
function cogitate_header_blog_link( $url ) {
	$posts_page_id = get_option( 'page_for_posts');
	if ( ! $posts_page_id )
		return $url;

	$posts_page_url = get_page_uri( $posts_page_id  );
	return trailingslashit( $url . $posts_page_url );
}
add_filter( 'cogitate_header_link', 'cogitate_header_blog_link' );

// Hide next/prev post links
add_filter( 'next_post_link',     '__return_null' );
add_filter( 'previous_post_link', '__return_null' );

// Remove some cruft around the comments form
function cogitate_comment_form( $comment_form_defaults ) {
	$comment_form_defaults['comment_notes_after'] = '';
	$comment_form_defaults['comment_notes_before'] = '';

	return $comment_form_defaults;
}
add_filter( 'comment_form_defaults', 'cogitate_comment_form' );

// Get WP_Query object for the last post, to show on the front page
function cogitate_last_post() {
	static $query;

	if ( $query )
		return $query;

	$query = new WP_Query( [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'no_found_rows'  => true,
	] );

	return $query;
}

// Overload parent function to remove some cruft
function twentytwelve_entry_meta() {
	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', 'twentytwelve' ) );

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', __( ', ', 'twentytwelve' ) );

	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	$author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'twentytwelve' ), get_the_author() ) ),
		get_the_author()
	);

	// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
	if ( ! is_single() ) {
		$utility_text = 'Published on %3$s';
	} elseif ( $tag_list ) {
		$utility_text = __( 'Published in %1$s, and tagged %2$s on %3$s<span class="by-author"> by %4$s</span>.', 'twentytwelve' );
	} elseif ( $categories_list ) {
		$utility_text = __( 'Published in %1$s on %3$s<span class="by-author"> by %4$s</span>.', 'twentytwelve' );
	} else {
		$utility_text = __( 'Published on %3$s<span class="by-author"> by %4$s</span>.', 'twentytwelve' );
	}

	printf(
		$utility_text,
		$categories_list,
		$tag_list,
		$date,
		$author
	);
}

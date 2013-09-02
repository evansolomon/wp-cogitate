<?php
/**
 * @package Cogitate
 */

// Enqueue parent styles instead of @import'ing them
function cogitate_parent_styles() {
	wp_enqueue_style( 'cogitate-parent', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'cogitate_parent_styles', 1 );

// Load Typekit and output its inline script
function cogitate_typekit() {
	wp_enqueue_script( 'cogitate_typekit', '//use.typekit.net/ohi7ntj.js' );
	add_action( 'wp_head', function() {
		echo apply_filters( 'cogitate_typekit_load', "<script type='text/javascript'>try{Typekit.load();}catch(e){}</script>\n" );
	} );
}
add_action( 'wp_enqueue_scripts', 'cogitate_typekit' );

// Register menus used on the front page
function cogitate_frontpage_menus() {
	register_nav_menus( apply_filters( 'cogitate_frontpage_menus', [
		'frontpage_profiles' => 'Front page profiles',
		'frontpage_projects' => 'Front page projects',
	] ) );
}
add_action( 'init', 'cogitate_frontpage_menus' );

// Undo some TwentyTwelve-isms
function cogitate_undo_twentytwelve() {
	unregister_nav_menu( apply_filters( 'cogitate_undo_twentytwelve_nav_menu', 'primary' ) );
	add_action( 'wp_enqueue_scripts', function() {
		wp_dequeue_script( 'twentytwelve-navigation' );
	}, 11 );
}
add_action( 'init', 'cogitate_undo_twentytwelve' );

// Link headers to the blog instead of the front page
function cogitate_header_blog_link( $url ) {
	$posts_page_id = get_option( 'page_for_posts' );
	if ( ! $posts_page_id )
		return $url;

	$posts_page_url = get_page_uri( $posts_page_id );
	return trailingslashit( $url . $posts_page_url );
}
add_filter( 'cogitate_header_link', 'cogitate_header_blog_link' );

// Hide next/prev post links
add_filter( 'next_post_link',     '__return_null' );
add_filter( 'previous_post_link', '__return_null' );

// Remove some cruft around the comments form
function cogitate_comment_form( $comment_form_defaults ) {
	$comment_form_defaults['comment_notes_after']  = '';
	$comment_form_defaults['comment_notes_before'] = '';

	return apply_filters( 'cogitate_comment_form_defaults', $comment_form_defaults );
}
add_filter( 'comment_form_defaults', 'cogitate_comment_form' );

// Get WP_Query object for the last post, to show on the front page
function cogitate_last_post() {
	static $query;

	if ( $query )
		return $query;

	$query = new WP_Query( apply_filters( 'cogitate_last_post_query', [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'no_found_rows'  => true,
	] ) );

	return $query;
}

// Overload parent function to remove some cruft
function twentytwelve_entry_meta() {
	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', 'twentytwelve' ) );

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', __( ', ', 'twentytwelve' ) );

	$author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'twentytwelve' ), get_the_author() ) ),
		get_the_author()
	);

	// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
	if ( $tag_list ) {
		$utility_text = __( 'Published in %1$s, and tagged %2$s<span class="by-author"> by %3$s</span>.', 'twentytwelve' );
	} elseif ( $categories_list ) {
		$utility_text = __( 'Published in %1$s<span class="by-author"> by %3$s</span>.', 'twentytwelve' );
	} else {
		$utility_text = __( 'Published<span class="by-author"> by %3$s</span>.', 'twentytwelve' );
	}

	do_action( 'cogitate_before_entry_meta' );

	printf(
		$utility_text,
		$categories_list,
		$tag_list,
		$author
	);

	do_action( 'cogitate_after_entry_meta' );
}

// Override Twenty Twelve's comments
function twentytwelve_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
		// Display trackbacks differently than normal comments.
	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
		<p><?php _e( 'Pingback:', 'twentytwelve' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'twentytwelve' ), '<span class="edit-link">', '</span>' ); ?></p>
	<?php
			break;
		default :
		// Proceed with normal comments.
		global $post;
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<header class="comment-meta comment-author vcard">
				<?php
					echo get_avatar( $comment, 70 );
					printf( '<cite class="fn">%1$s %2$s</cite>',
						get_comment_author_link(),
						// If current post author is also comment author, make it known visually.
						( $comment->user_id === $post->post_author ) ? '<span> ' . __( 'Post author', 'twentytwelve' ) . '</span>' : ''
					);
					printf( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
						esc_url( get_comment_link( $comment->comment_ID ) ),
						get_comment_time( 'c' ),
						/* translators: 1: date, 2: time */
						sprintf( __( '%1$s at %2$s', 'twentytwelve' ), get_comment_date(), get_comment_time() )
					);
				?>
			</header><!-- .comment-meta -->

			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'twentytwelve' ); ?></p>
			<?php endif; ?>

			<section class="comment-content comment">
				<?php comment_text(); ?>
				<?php edit_comment_link( __( 'Edit', 'twentytwelve' ), '<p class="edit-link">', '</p>' ); ?>
			</section><!-- .comment-content -->

			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'twentytwelve' ), 'after' => ' <span>&darr;</span>', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div><!-- .reply -->
		</article><!-- #comment-## -->
	<?php
		break;
	endswitch; // end comment_type check
}

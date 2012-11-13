<?php
/**
 * Template Name: Front Page Template
 *
 * @package Cogitate
 * Overloads TwentyTwelve's page-templates/front-page.php
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">
			<div class="entry-content">
				<?php
					wp_nav_menu( [
						'items_wrap'      => '<h2>I am</h2><ul class="%2$s">%3$s</ul>',
						'theme_location'  => 'frontpage_profiles',
						'container_class' => 'frontpage-menu',
					] );

					wp_nav_menu( [
						'items_wrap'      => '<h2>I make</h2><ul class="%2$s">%3$s</ul>',
						'theme_location'  => 'frontpage_projects',
						'container_class' => 'frontpage-menu',
					] );
				?>
			</div><!-- .entry-content -->

			<article class="last-post">
				<div class="teaser">Just a quick taste from the blog...</div>
				<?php
					if ( cogitate_last_post()->have_posts() ):
						cogitate_last_post()->the_post();
				?>
						<div class="title">
							<?= sprintf( '<a href="%s">%s</a>', get_permalink(), get_the_title() ); ?>
						</div>
						<div class="excerpt">
							<?php the_excerpt(); ?>
						</div>
				<?php
					endif;
					wp_reset_postdata();
				?>
			</article>

		</div><!-- #content -->
	</div><!-- #primary .site-content -->

<?php get_footer(); ?>
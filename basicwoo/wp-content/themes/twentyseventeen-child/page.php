<?php get_header(); ?>
	
	<div class="wrap">
		<?php while(have_posts()): the_post(); ?>
	<div class="hero" style="background-image: url(<?php echo get_the_post_thumbnail_url(); ?>);">
		<div class="hero-content">
			<div class="hero-text">
				<h2><?php the_title(); ?></h2>			
			</div>
		</div>
	</div>

	<div class="main-content container">
		<main class="text-center content-text clear">
			<?php the_content(); ?>			
		</main>
	</div>

	<?php endwhile; wp_reset_postdata(); ?>	
	</div>
	
<?php get_footer(); ?>
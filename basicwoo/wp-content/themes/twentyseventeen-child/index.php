<?php 
	get_header();
?>
<div class="product-container">
<?php 
	echo do_shortcode('[show_lat_product_slider]');
?>
</div>
<br/><br/><br/>
<!-- <?php 
$args = array(
'post_type'=> 'post',
'orderby'    => 'ID',
'post_status' => 'publish',
'order'    => 'DESC',
'posts_per_page' => -1 // this will retrive all the post that is published 
);
$result = new WP_Query( $args );
if ( $result-> have_posts() ) : ?>
<?php while ( $result->have_posts() ) : $result->the_post(); ?>
<?php the_title(); ?>   
<?php the_content(); ?>   
<?php endwhile; ?>
<?php endif; wp_reset_postdata(); ?>	 
 -->
<?php 
	get_footer();
?>
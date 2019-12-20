<?php
/*
Template name: Page - Tracking Page Preview
*/
get_header(); ?>

<div id="content" role="main" class="content-area">

	<style>
		button.customize-partial-edit-shortcut-button {
			display: none;
		}	
	</style>
	<?php
	wcast_tracking_page_customizer::preview_tracking_page();
	?>		
</div>

<?php get_footer(); ?>
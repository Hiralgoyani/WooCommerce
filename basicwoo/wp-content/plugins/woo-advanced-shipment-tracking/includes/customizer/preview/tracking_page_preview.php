<?php 
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
  get_header();  
  ?>  
  <style>
	button.customize-partial-edit-shortcut-button {
		display: none;
	}
	
  </style>   
  <?php
  wcast_tracking_page_customizer::preview_tracking_page();
  get_footer();
  ?>  
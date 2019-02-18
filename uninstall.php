<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die();
}

require 'clear-content.php';

vote_clear_content();

delete_option( 'yoojeen_vote' );



<?php

// функция очищает контент от шорткода при деактивации или удалении плагина
function vote_clear_content() {

	global $wpdb;

	$results = $wpdb->get_results( "SELECT ID, post_content FROM
	 {$wpdb->prefix}posts WHERE post_content LIKE '[vote %' LIMIT 1", ARRAY_A );

	$id = $results[ 0 ][ 'ID' ];

	$pattern = '/\[vote\s{1,}.{1,}\]/';
	$content = "";

	foreach ( $results as $item ) {
		$content .= preg_replace( $pattern, '', $item[ 'post_content' ] );
		$content = esc_sql( $content );
		$wpdb->query( "UPDATE {$wpdb->prefix}posts SET post_content = '$content' WHERE ID='$id'" );
	}
}

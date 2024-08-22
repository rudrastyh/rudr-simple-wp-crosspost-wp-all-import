<?php
/*
 * Plugin name: Simple WP Crossposting – WP All Import
 * Author: Misha Rudrastyh
 * Author URI: https://rudrastyh.com
 * Description: Allows to automatically crosspost posts when running the import in WP All Import
 * Plugin URI: https://rudrastyh.com/support/wp-all-import-crossposting
 * Version: 1.0
 */

add_action( 'pmxi_saved_post', function( $post_id, $xml_node, $is_update ) {

	if( ! class_exists( 'Rudr_Simple_WP_Crosspost' ) ) {
		return;
	}

	//file_put_contents( __DIR__ . '/log.txt', $post_id, FILE_APPEND );

	$post = get_post( $post_id );
	if( ! $post ) {
		return;
	}

	$allowed_post_statuses = ( $allowed_post_statuses = get_option( 'rudr_sac_post_statuses' ) ) ? $allowed_post_statuses : array( 'publish' );
	if ( ! in_array( $post->post_status, $allowed_post_statuses ) ) {
		return;
	}

	if( function_exists( 'wc_get_product' ) && 'product' === $post->post_type ) {
		$c = new Rudr_Simple_Woo_Crosspost();
	} else {
		$c = new Rudr_Simple_WP_Crosspost();
	}

	$blogs = array();
	if( $c->is_auto_mode() ) {
		$blogs = apply_filters( 'rudr_crosspost_auto_mode_blogs', $c->get_blogs(), $post_id );
		foreach( $blogs as $blog ) {
			update_post_meta( $post_id, '_crsspst_to_' . $c->get_blog_id( $blog ), true );
		}
	} else {
		foreach( $c->get_blogs() as $blog ) {
			if( true == get_post_meta( $post_id, '_crsspst_to_' . $c->get_blog_id( $blog ), true ) ) {
				$blogs[] = $blog;
			}
		}
	}

	if( function_exists( 'wc_get_product' ) && 'product' === $post->post_type ) {
		$c->crosspost_product( $post_id, $blogs );
	} else {
		$c->crosspost( $post, $blogs );
	}

}, 999, 3 );

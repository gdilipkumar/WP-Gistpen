<?php
/**
 * @package   WP_Gistpen
 * @author    James DiGioia <jamesorodig@gmail.com>
 * @license   GPL-2.0+
 * @link      http://jamesdigioia.com/wp-gistpen/
 * @copyright 2014 James DiGioia
 */

/**
 * This class manipulates the Gistpen post content.
 *
 * @package WP_Gistpen_Content
 * @author  James DiGioia <jamesorodig@gmail.com>
 */
class WP_Gistpen_Content {

	/**
	 * Remove extra filters from the Gistpen content
	 *
	 * @since    0.1.0
	 */
	public static function remove_filters( $content ) {

		if( 'gistpen' == get_post_type() ) {
			remove_filter( 'the_content', 'wpautop' );
			remove_filter( 'the_content', 'wptexturize' );
			remove_filter( 'the_content', 'capital_P_dangit' );
			remove_filter( 'the_content', 'convert_chars' );
			remove_filter( 'get_the_excerpt', 'wp_trim_excerpt' );
		}

		return $content;
	}

	/**
	 * Add the Gistpen content field to the_content
	 *
	 * @param string $atts shortcode attributes
	 * @return string post_content
	 * @since    0.1.0
	 */
	public static function post_content( $content = '' ) {
		global $post;

		if( 'gistpen' == $post->post_type ) {
			$post = WP_Gistpen::get_instance()->query->get( $post );

			if( is_wp_error( $post ) ) {
				// @todo handle each error
				return;
			}

			return $post->post_content;
		}

		return $content;
	}

	/**
	 * Register the shortcode to embed the Gistpen
	 *
	 * @param    array      $atts    attributes passed into the shortcode
	 * @return   string
	 * @since    0.1.0
	 */
	public static function add_shortcode( $atts ) {

		$args = shortcode_atts( array(
			'id' => null,
			'highlight' => null),
			$atts,
			'gistpen'
		);

		// If the user didn't provide an ID, raise an error
		if( $args['id'] === null ) {
			return '<div class="wp-gistpen-error">No Gistpen ID was provided.</div>';
		}

		$post = WP_Gistpen::get_instance()->query->get( $args['id'] );

		if( is_wp_error( $post ) ) {
			// @todo handle each error
			return;
		}

		return $post->get_shortcode_content( $args['highlight'] );

	}

}


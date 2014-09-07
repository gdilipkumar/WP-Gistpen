<?php
/**
 * @group  updater
 */
class WP_Gistpen_Updater_Test extends WP_UnitTestCase {

	public $posts;
	public $gistpens;

	function setUp() {
		parent::setUp();
	}

	function set_up_0_4_0_test_posts() {
		$terms = get_terms( 'language', 'hide_empty=0' );

		foreach ($terms as $term) {
			$languages[] = $term->term_id;
		}
		$num_posts = count( $languages );
		$this->gistpens = $this->factory->post->create_many( $num_posts, array(
			'post_type' => 'gistpens',
		), array(
			'post_title' => new WP_UnitTest_Generator_Sequence( 'Post title %s' ),
			'post_name' => new WP_UnitTest_Generator_Sequence( 'Post title %s' ),
			'post_content' => new WP_UnitTest_Generator_Sequence( 'Post content %s' )
		));

		foreach ( $this->gistpens as $gistpen_id ) {
			// Pick a random language
			$num_posts = $num_posts - 1;
			$lang_num = rand( 0, ( $num_posts ) );

			// Get the language's id
			$lang_id = $languages[$lang_num];

			// Remove the language and reindex the languages array
			unset( $languages[$lang_num] );
			$languages = array_values( $languages );

			// Give the post a description
			update_post_meta( $gistpen_id, '_wpgp_gistpen_description', 'This is a description of the Gistpen.' );

			// Give the post the language
			wp_set_object_terms( $gistpen_id, $lang_id, 'language', false );
		}
	}

	function test_update_to_0_4_0() {
		$this->set_up_0_4_0_test_posts();

		WP_Gistpen_Updater::update_to_0_4_0();

		foreach ($this->gistpens as $gistpen_id) {
			$post = get_post( $gistpen_id );

			// The post should have no content
			$this->assertEmpty( $post->post_content );

			// The post should have no description
			$this->assertEmpty( get_post_meta( $post->ID, '_wpgp_gistpen_description', true ) );

			// The post should have no language
			$this->assertEmpty( wp_get_object_terms( $post->ID, 'language' ) );

			// The post title should be "This is a decription of the Gistpen."
			$this->assertEquals( 'This is a description of the Gistpen.', $post->post_title );

			$children = get_children( array( 'post_parent' => $gistpen_id ) );

			// The post should have one child post
			$this->assertCount( 1, $children );

			$child = array_pop( $children );

			// The child post should have content
			$this->assertContains( 'Post content', $child->post_content );

			// The child post should have a filename
			$this->assertContains( 'Post-title', $child->post_title );
			$this->assertContains( 'post-title', $child->post_name );

			// The child post should have a language
			$language = WP_Gistpen_Content::get_the_language( $child->ID );
			$this->assertNotEquals( 'nonce', $language );
		}

	}

	function tearDown() {
		parent::tearDown();
	}
}


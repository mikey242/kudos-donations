<?php
/**
 * MapperTrait tests
 */

namespace Domain;

use IseardMedia\Kudos\Domain\PostType\CampaignPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use WP_Post;
use WP_UnitTestCase;

/**
 * Sample test case.
 */
class MapperTrait extends WP_UnitTestCase {

	protected static array $post_ids;

	/**
	 * Create posts for use in following tests.
	 */
	public static function setUpBeforeClass(): void {
		self::$post_ids = self::factory()->post->create_many( 5, [ 'post_type' => TransactionPostType::get_slug() ] );

		$values = [ 10, 20, 20, 40, 50 ];

		foreach ( self::$post_ids as $key => $post_id ) {
			update_post_meta( $post_id, TransactionPostType::META_FIELD_VALUE, $values[ $key ] );
		}
	}

	/**
	 * Test get_posts() returns all posts of type.
	 */
	public function test_get_all_posts() {

		$posts = TransactionPostType::get_posts();
		$this->assertCount( 5, $posts );
	}

	/**
	 * Test get_post(['ID' => $id]) returns correct post type.
	 */
	public function test_get_post_by_id() {
		$post = TransactionPostType::get_post( [ 'ID' => self::$post_ids[0] ] );
		$this->assertTrue( $post instanceof \WP_Post );
		$this->assertSame( $post->post_type, TransactionPostType::get_slug() );
	}

	/**
	 * Test get_post(['value' => $value]) returns correct post type.
	 */
	public function test_get_post_by_meta() {
		$post = TransactionPostType::get_post( [ 'value' => 20 ] );
		$this->assertTrue( $post instanceof \WP_Post );
		$this->assertSame( '20', $post->{TransactionPostType::META_FIELD_VALUE} );
	}

	/**
	 * Test get_post(['ID' => $id]) returns null when post not found.
	 */
	public function test_get_post_by_id_not_found() {
		$post = TransactionPostType::get_post( [ 'ID' => 100 ] );
		$this->assertNull( $post );
	}

	/**
	 * Test get_post(['value' => $value]) returns null when post not found.
	 */
	public function test_get_post_by_meta_not_found() {
		$post = TransactionPostType::get_post( [ 'value' => 60 ] );
		$this->assertNull( $post );
	}

	/**
	 * Test get_posts(['value' => $value]) returns correct posts.
	 */
	public function test_get_posts_by_meta() {
		$posts = TransactionPostType::get_posts( [ 'value' => 20 ] );
		$this->assertCount( 2, $posts );
		$this->assertSame( '20', $posts[0]->{TransactionPostType::META_FIELD_VALUE} );
	}

	/**
	 * Test get_posts(['value' => $value]) returns empty array when posts not found.
	 */
	public function test_get_posts_by_meta_not_found() {
		$posts = TransactionPostType::get_posts( [ 'value' => 100 ] );
		$this->assertEmpty( $posts );
	}

	/**
	 * Test save with no parameters creates blank post.
	 */
	public function test_save_empty_parameters() {
		$post = CampaignPostType::save();
		$this->assertTrue( $post instanceof WP_Post );
		$this->assertSame( $post->post_type, CampaignPostType::get_slug() );
		wp_delete_post( $post->ID, true );
	}

	/**
	 * Test that post is saved with metadata.
	 */
	public function test_save_with_meta() {
		$post = CampaignPostType::save(
			[
				CampaignPostType::META_FIELD_ADDRESS_TITLE => 'Your Address',
				CampaignPostType::META_FIELD_ADDRESS_DESCRIPTION => 'Please enter your address below',
			]
		);
		$this->assertTrue( $post instanceof WP_Post );
		$this->assertSame( $post->post_type, CampaignPostType::get_slug() );
		$this->assertSame( $post->{CampaignPostType::META_FIELD_ADDRESS_TITLE}, 'Your Address' );
		wp_delete_post( $post->ID, true );
	}

	/**
	 * Test that post is updated with metadata.
	 */
	public function test_save_updates_existing_post() {
		$post = CampaignPostType::save();
		CampaignPostType::save(
			[
				'ID'                                       => $post->ID,
				CampaignPostType::META_FIELD_ADDRESS_TITLE => 'Your Address',
			]
		);
		$this->assertTrue( $post instanceof WP_Post );
		$this->assertSame( $post->post_type, CampaignPostType::get_slug() );
		$this->assertSame( $post->{CampaignPostType::META_FIELD_ADDRESS_TITLE}, 'Your Address' );
		wp_delete_post( $post->ID, true );
	}

	/**
	 * Test getting post by id or slug.
	 */
	public function test_get_post_by_id_or_slug() {
		$post = TransactionPostType::save(
			[
				'post_name'                             => 'post-slug-1',
				TransactionPostType::META_FIELD_MESSAGE => 'My message',
			]
		);
		$this->assertTrue( TransactionPostType::get_post_by_id_or_slug( $post->ID ) instanceof WP_Post );
		$this->assertTrue( TransactionPostType::get_post_by_id_or_slug( 'post-slug-1' ) instanceof WP_Post );
		$this->assertNull( TransactionPostType::get_post_by_id_or_slug( 500 ) );
		wp_delete_post( $post->ID, true );
	}
}

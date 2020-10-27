<?php

namespace Entity;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Service\MailerService;
use Kudos\Service\MapperService;
use WP_UnitTestCase;

class AbstractEntityTests extends WP_UnitTestCase {

	public function test_actions_correctly_registered() {

		$this->assertEquals(10, has_action('kudos_donors_remove_secret_action', ['Kudos\Entity\DonorEntity','remove_secret_action']));
		$this->assertEquals(10, has_action('kudos_transactions_remove_secret_action', ['Kudos\Entity\TransactionEntity','remove_secret_action']));
		$this->assertEquals(10, has_action('kudos_subscriptions_remove_secret_action', ['Kudos\Entity\SubscriptionEntity','remove_secret_action']));

	}

	public function test_factory_method() {

		$object1 = MailerService::factory();
		$object2 = MailerService::factory();
		$id1 = spl_object_hash($object1);
		$id2 = spl_object_hash($object2);

		$this->assertEquals($id1, $id2);

		$object3 = new MailerService();
		$object4 = new MailerService();
		$id3 = spl_object_hash($object3);
		$id4 = spl_object_hash($object4);

		$this->assertNotEquals($id3, $id4);

	}

	public function test_can_construct_without_parameters() {

		$donor = new DonorEntity();
		$this->assertInstanceOf( DonorEntity::class, $donor );

	}

	public function test_can_construct_with_valid_parameters() {

		$donor = new DonorEntity( [
			'email' => 'test@test.com',
			'name'  => 'Michael',
		] );
		$this->assertEquals( 'Michael', $donor->name );

	}

	public function test_does_not_set_invalid_parameters() {

		$donor = new DonorEntity( [
			'email'   => 'test@test.com',
			'name'    => 'Michael',
			'invalid' => 'Property does not exist',
			'postcode' => '1234RE'
		] );
		$this->assertFalse( property_exists( $donor, 'invalid' ) );
		$this->assertEquals('1234RE', $donor->postcode );

	}

	public function test_can_set_valid_fields() {

		$subscription = new SubscriptionEntity();
		$subscription->set_fields([
			'value' => 12,
			'years' => 3,
			'status' => 'active'
		]);

		$this->assertEquals(12, $subscription->value);

	}

	public function test_cannot_set_invalid_fields() {

		$subscription = new SubscriptionEntity();
		$subscription->set_fields([
			'value' => 12,
			'years' => 3,
			'status' => 'active',
			'invalid_field' => 'This should not be set'
		]);

		$this->assertFalse( property_exists( $subscription, 'invalid_field' ) );

	}

	public function test_correct_table_name_without_prefix() {

		$this->assertEquals( 'kudos_subscriptions', SubscriptionEntity::get_table_name(false) );

	}

	public function test_correct_table_name_with_prefix() {

		$this->assertEquals( 'wptests_kudos_donors', DonorEntity::get_table_name() );

	}

	public function test_create_new_secret() {

		$donor = new DonorEntity([
			'name' => 'Michael',
			'email' => 'test@email.com',
		]);

		$this->assertIsString($donor->create_secret());

	}

	public function test_verify_secret_success() {

		$donor = new DonorEntity();
		$secret = $donor->create_secret();
		$this->assertTrue($donor->verify_secret($secret));

	}

	public function test_verify_secret_empty() {

		$donor = new DonorEntity();
		$this->assertFalse($donor->verify_secret('12345'));

	}

	public function test_verify_secret_wrong() {

		$donor = new DonorEntity();
		$donor->create_secret();
		$this->assertFalse($donor->verify_secret('12345'));

	}

	public function test_create_secret_not_overwritten() {

		$donor = new DonorEntity([
			'email' => 'test@test.com',
			'name' => 'Michael'
		]);
		$mapper = new MapperService();
		$mapper->save($donor);

		$secret1 = $donor->create_secret();
		$clone = clone $donor;
		$secret2 = $clone->create_secret();

		$this->assertEquals($donor->verify_secret($secret1), $donor->verify_secret($secret2));

	}

	public function test_clear_secret() {

		$donor = new DonorEntity();
		$secret = $donor->create_secret();
		$donor->clear_secret();
		$this->assertFalse($donor->verify_secret($secret));

	}

	public function test_to_array_returns_array() {

		$transaction = new TransactionEntity([
			'value' => 32,
			'mode' => 'live',
			'currency' => 'EUR'
		]);

		$this->assertIsArray( $transaction->to_array() );

	}

	public function test_to_string_returns_id() {

		$transaction = new TransactionEntity([
			'id' => 12,
			'mode' => 'live',
			'currency' => 'EUR'
		]);

		$this->assertEquals(12, (string) $transaction );

	}
}
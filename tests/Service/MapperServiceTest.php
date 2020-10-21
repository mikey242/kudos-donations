<?php

namespace Service;

use Kudos\Entity\TransactionEntity;
use Kudos\Exceptions\MapperException;
use Kudos\Service\MapperService;
use Kudos\Service\MollieService;
use ReflectionException;
use WP_UnitTestCase;

class MapperServiceTest extends WP_UnitTestCase {

	public function test_can_instantiate_with_no_parameters() {

		$mapper = new MapperService();
		$this->assertNull($mapper->get_repository());
	}

	public function test_can_instantiate_with_valid_entity() {

		$mapper = new MapperService(TransactionEntity::class);
		$this->assertEquals(TransactionEntity::class, $mapper->get_repository());

	}

	public function test_repository_null_when_instantiated_with_invalid_class() {

		$mapper = new MapperService('InvalidClass');
		$this->assertEquals(NULL, $mapper->get_repository());

	}

	public function test_repository_null_when_instantiated_with_incompatible_class() {

		$mapper = new MapperService(MollieService::class);
		$this->assertEquals(NULL, $mapper->get_repository());

	}

	public function test_correct_table_name_returned() {

		$mapper = new MapperService(TransactionEntity::class);
		$this->assertEquals('wptests_kudos_transactions', $mapper->get_table_name());

	}

	/**
	 * @throws MapperException
	 * @throws ReflectionException
	 */
	public function test_set_repository_throws_reflection_exception() {

		$this->expectException(ReflectionException::class);
		$mapper = new MapperService();
		$mapper->set_repository('InvalidClass');

	}

	/**
	 * @throws MapperException
	 * @throws ReflectionException
	 */
	public function test_set_repository_throws_mapper_exception() {

		$this->expectException(MapperException::class);
		$mapper = new MapperService();
		$mapper->set_repository(MollieService::class);

	}

	/**
	 * @throws MapperException
	 * @throws ReflectionException
	 */
	public function test_set_and_get_repository_works_for_entity() {

		$mapper = new MapperService();
		$mapper->set_repository(TransactionEntity::class);
		$this->assertEquals(TransactionEntity::class, $mapper->get_repository());

	}

	public function test_save_entity() {

		$entity = new TransactionEntity([
			'currency' => 'EUR',
			'value' => 20,
			'status' => 'open',
			'transactions_id' => 't_12345',
			'order_id' => 'kdo_12345',
		]);
		$mapper = new MapperService();
		$save = $mapper->save($entity);

		$this->assertEquals(1, $save );

	}

	public function test_get_one_no_result() {

		$mapper = new MapperService(TransactionEntity::class);
		$entity = new TransactionEntity([
			'order_id' => 'kdo_12345',
			'currency' => 'EUR',
			'value' => 20,
			'status' => 'open'
		]);
		$mapper->save($entity);

		$transaction = $mapper->get_one_by(['order_id' => 'kdo_67890']);

		$this->assertNull($transaction );

	}

	public function test_get_all_no_result() {

		$mapper = new MapperService(TransactionEntity::class);
		$entity = new TransactionEntity([
			'order_id' => 'kdo_12345',
			'currency' => 'EUR',
			'value' => 20,
			'status' => 'open'
		]);
		$mapper->save($entity);

		$transaction = $mapper->get_all_by(['order_id' => 'kdo_67890']);

		$this->assertNull($transaction );

	}

	public function test_get_one() {

		$transaction = new TransactionEntity( [
			'order_id'       => 'kdo_12345',
			'customer_id'    => '1',
			'value'          => '20',
			'currency'       => 'EUR',
			'status'         => 'open',
			'mode'           => 'test',
			'sequence_type'  => 'oneoff',
			'campaign_label' => 'label',
		] );

		$mapper = new MapperService( TransactionEntity::class );
		$mapper->save( $transaction );

		/** @var TransactionEntity $transaction */
		$transaction = $mapper->get_one_by([ 'order_id' => 'kdo_12345']);
		$this->assertEquals('kdo_12345', $transaction->order_id );

	}

	public function test_get_all_with_no_parameters() {

		$mapper = new MapperService( TransactionEntity::class );

		$transaction = new TransactionEntity( [
			'order_id'       => 'kdo_12345',
			'customer_id'    => '1',
			'value'          => '20',
			'currency'       => 'EUR',
			'status'         => 'open',
			'mode'           => 'test',
			'sequence_type'  => 'oneoff',
			'campaign_label' => 'label',
		] );
		$mapper->save( $transaction );

		$transaction = new TransactionEntity( [
			'order_id'       => 'kdo_67890',
			'customer_id'    => '2',
			'value'          => '10',
			'currency'       => 'EUR',
			'status'         => 'open',
			'mode'           => 'test',
			'sequence_type'  => 'oneoff',
			'campaign_label' => 'label',
		] );
		$mapper->save( $transaction );

		/** @var TransactionEntity $transaction */
		$transactions = $mapper->get_all_by();
		$this->assertCount(2, $transactions );

	}

	public function test_get_all_with_parameters() {

		$mapper = new MapperService( TransactionEntity::class );

		$transaction = new TransactionEntity( [
			'order_id'       => 'kdo_12345',
			'customer_id'    => '1',
			'value'          => '20',
			'currency'       => 'EUR',
			'status'         => 'open',
			'mode'           => 'test',
			'sequence_type'  => 'oneoff',
			'campaign_label' => 'label',
		] );
		$mapper->save( $transaction );

		$transaction = new TransactionEntity( [
			'order_id'       => 'kdo_67890',
			'customer_id'    => '2',
			'value'          => '10',
			'currency'       => 'EUR',
			'status'         => 'open',
			'mode'           => 'test',
			'sequence_type'  => 'oneoff',
			'campaign_label' => 'label',
		] );
		$mapper->save( $transaction );

		$transaction = new TransactionEntity( [
			'order_id'       => 'kdo_54321',
			'customer_id'    => '2',
			'value'          => '10',
			'currency'       => 'EUR',
			'status'         => 'open',
			'mode'           => 'test',
			'sequence_type'  => 'oneoff',
			'campaign_label' => 'label',
		] );
		$mapper->save( $transaction );

		/** @var TransactionEntity $transaction */
		$transactions = $mapper->get_all_by(['value' => 10]);
		$this->assertCount(2, $transactions );

	}

	public function test_get_all_with_parameters_no_value() {

		$mapper = new MapperService( TransactionEntity::class );

		$transaction = new TransactionEntity( [
			'order_id'       => 'kdo_12345',
			'customer_id'    => '1',
			'value'          => '20',
			'status'         => 'open',
			'mode'           => 'test',
			'sequence_type'  => 'oneoff',
			'campaign_label' => 'label',
		] );
		$mapper->save( $transaction );

		$transaction = new TransactionEntity( [
			'order_id'       => 'kdo_67890',
			'customer_id'    => '2',
			'value'          => '10',
			'status'         => 'open',
			'mode'           => 'test',
			'sequence_type'  => 'oneoff',
			'campaign_label' => 'label',
		] );
		$mapper->save( $transaction );

		$transaction = new TransactionEntity( [
			'order_id'       => 'kdo_54321',
			'customer_id'    => '2',
			'value'          => '10',
			'currency'       => 'EUR',
			'status'         => 'open',
			'mode'           => 'test',
			'sequence_type'  => 'oneoff',
			'campaign_label' => 'label',
		] );
		$mapper->save( $transaction );

		$mapper = new MapperService(TransactionEntity::class);
		/** @var TransactionEntity $transaction */
		$transactions = $mapper->get_all_by(['currency']);
		$this->assertCount(1, $transactions );

	}

	public function test_delete_record() {

		$mapper = new MapperService( TransactionEntity::class );

		$transaction = new TransactionEntity( [
			'order_id'       => 'kdo_12345',
			'customer_id'    => '1',
			'value'          => '20',
			'status'         => 'open',
			'mode'           => 'test',
			'sequence_type'  => 'oneoff',
			'campaign_label' => 'label',
		] );
		$mapper->save( $transaction );

		$mapper = new MapperService(TransactionEntity::class);
		/** @var TransactionEntity $transaction */
		$result = $mapper->delete('order_id', 'kdo_12345');
		$this->assertEquals( 1, $result );
	}

	public function test_delete_record_fail() {

		$mapper = new MapperService( TransactionEntity::class );

		$transaction = new TransactionEntity( [
			'order_id'       => 'kdo_12345',
			'customer_id'    => '1',
			'value'          => '20',
			'status'         => 'open',
			'mode'           => 'test',
			'sequence_type'  => 'oneoff',
			'campaign_label' => 'label',
		] );
		$mapper->save( $transaction );

		$mapper = new MapperService(TransactionEntity::class);
		/** @var TransactionEntity $transaction */
		$result = $mapper->delete('order_id', 'kdo_654321');
		$this->assertEquals(0, $result );
	}

	public function test_delete_all_records() {

		$mapper = new MapperService( TransactionEntity::class );

		$transaction = new TransactionEntity( [
			'order_id'       => 'kdo_12345',
			'customer_id'    => '1',
			'value'          => '20',
			'status'         => 'open',
			'mode'           => 'test',
			'sequence_type'  => 'oneoff',
			'campaign_label' => 'label',
		] );
		$mapper->save( $transaction );

		$transaction = new TransactionEntity( [
			'order_id'       => 'kdo_67890',
			'customer_id'    => '2',
			'value'          => '10',
			'status'         => 'open',
			'mode'           => 'test',
			'sequence_type'  => 'oneoff',
			'campaign_label' => 'label',
		] );
		$mapper->save( $transaction );

		$transaction = new TransactionEntity( [
			'order_id'       => 'kdo_54321',
			'customer_id'    => '2',
			'value'          => '10',
			'currency'       => 'EUR',
			'status'         => 'open',
			'mode'           => 'test',
			'sequence_type'  => 'oneoff',
			'campaign_label' => 'label',
		] );
		$mapper->save( $transaction );

		$mapper = new MapperService(TransactionEntity::class);
		$result = $mapper->delete_all();
		$this->assertEquals(3, $result );
	}

}

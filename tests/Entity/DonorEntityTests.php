<?php

namespace Entity;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Service\MapperService;
use WP_UnitTestCase;

class DonorEntityTests extends WP_UnitTestCase {

	public function test_return_null_when_no_transactions() {

		$mapper = new MapperService();

		$donor = new DonorEntity([
			'name' => 'Michael',
			'email' => 'test@email.com',
			'customer_id' => '1'
		]);
		$mapper->save( $donor );

		$this->assertNull($donor->get_transactions());

	}

	public function test_can_get_transactions() {

		$mapper = new MapperService();

		$donor = new DonorEntity([
			'name' => 'Michael',
			'email' => 'test@email.com',
			'customer_id' => '1'
		]);
		$mapper->save( $donor );

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
			'customer_id'    => '1',
			'value'          => '10',
			'currency'       => 'EUR',
			'status'         => 'open',
			'mode'           => 'test',
			'sequence_type'  => 'oneoff',
			'campaign_label' => 'label',
		] );
		$mapper->save( $transaction );

		$this->assertCount(2, $donor->get_transactions());

	}

}
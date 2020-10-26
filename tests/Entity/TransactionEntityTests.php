<?php

namespace Entity;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Service\MapperService;
use WP_UnitTestCase;

class TransactionEntityTests extends WP_UnitTestCase {

	public function test_get_donor_returns_donor() {

		$mapper = new MapperService();

		$mapper->save(new DonorEntity([
			'customer_id' => 12345,
			'email' => 'test@email.com',
			'name' => 'Michael'
		]));

		$transaction = new TransactionEntity([
			'customer_id' => 12345,
			'order_id' => 'kdo_12345',
			'value' => 20,
			'status' => 'open',
			'mode' => 'test',
			'sequence_type' => 'oneoff'
		]);
		$mapper->save($transaction);

		/** @var DonorEntity $donor */
		$donor = $transaction->get_donor();

		$this->assertEquals('Michael', $donor->name);

	}

	public function test_get_refund() {

		$transaction = new TransactionEntity([
			'refunds' => serialize([
				'refunded' => 5,
				'remaining' => 20
			])
		]);

		$this->assertEquals( 20, $transaction->get_refund()['remaining']);

	}

	public function test_get_refund_not_serialized() {

		$transaction = new TransactionEntity([
			'refunds' => [
				'refunded' => 5,
				'remaining' => 20
			]
		]);

		$this->assertFalse($transaction->get_refund());

	}

}
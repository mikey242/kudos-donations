<?php

namespace Entity;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Service\MapperService;
use WP_UnitTestCase;

class SubscriptionEntityTests extends WP_UnitTestCase {

	public function test_get_donor_returns_donor() {

		$mapper = new MapperService();

		$mapper->save(new DonorEntity([
			'customer_id' => 12345,
			'email' => 'test@email.com',
			'name' => 'Michael'
		]));

		$subscription = new SubscriptionEntity([
			'customer_id' => 12345,
			'value' => 20,
			'frequency' => '12 months',
			'years' => 3
		]);
		$mapper->save($subscription);

		/** @var DonorEntity $donor */
		$donor = $subscription->get_donor();

		$this->assertEquals('Michael', $donor->name);

	}

}
<?php

namespace Kudos\Entity;

use Kudos\Service\MapperService;

class CampaignEntity extends AbstractEntity {

	/**
	 * Table name without prefix
	 *
	 * @var string
	 */
	protected const TABLE = 'kudos_campaigns';

	/**
	 * @var string
	 */
	public $slug;
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $modal_title;
	/**
	 * @var string
	 */
	public $welcome_text;
	/**
	 * @var string
	 */
	public $donation_type;
	/**
	 * @var string
	 */
	public $amount_type;
	/**
	 * @var string
	 */
	public $fixed_amounts;
	/**
	 * @var bool
	 */
	public $protected;

	/**
	 * Gets all transactions for current user
	 *
	 * @return array|null
	 */
	public function get_transactions(): ?array {

		$mapper = new MapperService( TransactionEntity::class );

		return $mapper->get_all_by( [ 'campaign_label' => $this->slug ] );

	}

}

<?php

namespace Kudos\Entity;

use DateTime;
use Kudos\Service\LoggerService;
use Kudos\Service\MapperService;
use Throwable;

class DonorEntity extends AbstractEntity {

	/**
	 * Table name without prefix
	 * @var string
	 */
	public const TABLE = "kudos_donors";
	/**
	 * @var string
	 */
	public $email;
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $street;
	/**
	 * @var string
	 */
	public $postcode;
	/**
	 * @var string
	 */
	public $city;
	/**
	 * @var string
	 */
	public $country;
	/**
	 * @var string
	 */
	public $customer_id;
	/**
	 * @var DateTime
	 */
	public $last_updated;
	/**
	 * @var string
	 */
	public $secret;


	/**
	 * Add donor_created
	 *
	 * @param $atts
	 *
	 * @since   2.0.0
	 */
	public function __construct( $atts = null ) {
		parent::__construct( $atts );
	}

	/**
	 * Gets all transactions for current user
	 *
	 * @return array|null
	 */
	public function get_transactions() {
		$mapper = new MapperService( TransactionEntity::class );

		return $mapper->get_all_by( [ 'customer_id' => $this->customer_id ] );
	}

	/**
	 * Set the donor's secret
	 *
	 * @param string $timeout
	 *
	 * @return string
	 * @since   2.0.0
	 */
	public function create_secret( $timeout = '+10 minutes' ) {

		$logger = new LoggerService();

		try {

			// Schedule for secret to be removed after timeout
			if ( class_exists( 'ActionScheduler' ) ) {

				// Remove existing action if exists
				as_unschedule_action( 'kudos_remove_secret_action', [ $this->customer_id ] );
				$timestamp = strtotime( $timeout );

				// Create new action to remove secret
				as_schedule_single_action( $timestamp, 'kudos_remove_secret_action', [ $this->customer_id ] );
				$logger->debug( 'Action "kudos_remove_secret_action" scheduled',
					[
						'datetime' => date_i18n( 'Y-m-d H:i:s', $timestamp ),
					] );

			}

			// Create secret if none set
			if ( null === $this->secret ) {
				$this->secret = bin2hex( random_bytes( 10 ) );
			}

		} catch ( Throwable $e ) {
			$logger->error( 'Unable to create secret for user. ' . $e->getMessage(), [ 'id' => $this->id ] );
		}

		return $this->secret;

	}

	/**
	 * Verify donor's secret
	 *
	 * @param string $hash
	 *
	 * @return bool
	 * @since   2.0.0
	 */
	public function verify_secret( string $hash ) {

		return password_verify( $this->secret, $hash );

	}

	/**
	 * Clears the donor's secret
	 *
	 * @since   2.0.0
	 */
	public function clear_secret() {

		$this->secret = '';

	}

}
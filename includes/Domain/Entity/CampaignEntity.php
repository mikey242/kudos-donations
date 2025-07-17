<?php
/**
 * CampaignEntity class.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Entity;

class CampaignEntity extends BaseEntity {

	public ?string $wp_post_slug = null;
	public string $currency;
	public ?float $goal = null;
	public bool $show_goal;
	public ?float $additional_funds = null;
	public string $amount_type;
	public ?array $fixed_amounts;
	public float $minimum_donation;
	public float $maximum_donation;
	public string $donation_type;
	public ?array $frequency_options;
	public bool $email_enabled;
	public bool $email_required;
	public bool $name_enabled;
	public bool $name_required;
	public bool $address_enabled;
	public bool $address_required;
	public bool $message_enabled;
	public bool $message_required;
	public string $theme_color;
	public ?string $terms_link   = null;
	public ?string $privacy_link = null;
	public bool $show_return_message;
	public bool $use_custom_return_url;
	public ?string $custom_return_url = null;
	public string $payment_description_format;
	public ?string $custom_styles = null;
	public string $initial_title;
	public string $initial_description;
	public string $subscription_title;
	public string $subscription_description;
	public string $address_title;
	public string $address_description;
	public string $message_title;
	public string $message_description;
	public string $payment_title;
	public string $payment_description;
	public string $return_message_title;
	public string $return_message_text;
	public ?float $total = null;

	/**
	 * {@inheritDoc}
	 */
	protected function defaults(): array {
		return [
			'currency'                   => 'EUR',
			'show_goal'                  => false,
			'amount_type'                => 'fixed',
			'fixed_amounts'              => [ '5', '10', '25', '50' ],
			'minimum_donation'           => 1.0,
			'maximum_donation'           => 5000.0,
			'donation_type'              => 'oneoff',
			'frequency_options'          => [
				'12 months' => __( 'Yearly', 'kudos-donations' ),
				'3 months'  => __( 'Quarterly', 'kudos-donations' ),
				'1 month'   => __( 'Monthly', 'kudos-donations' ),
			],
			'email_enabled'              => true,
			'email_required'             => true,
			'name_enabled'               => true,
			'name_required'              => true,
			'address_enabled'            => false,
			'address_required'           => false,
			'message_enabled'            => false,
			'message_required'           => false,
			'theme_color'                => '#ff9f1c',
			'show_return_message'        => false,
			'use_custom_return_url'      => false,
			'payment_description_format' => __( 'Donation ({{campaign_name}}) - {{order_id}}', 'kudos-donations' ),
			'initial_title'              => __( 'Support us!', 'kudos-donations' ),
			'initial_description'        => __( 'Your support is greatly appreciated and will help to keep us going.', 'kudos-donations' ),
			'subscription_title'         => __( 'Subscription', 'kudos-donations' ),
			'subscription_description'   => __( 'How often would you like to donate?', 'kudos-donations' ),
			'address_title'              => __( 'Address', 'kudos-donations' ),
			'address_description'        => __( 'Please fill in your address', 'kudos-donations' ),
			'message_title'              => __( 'Message', 'kudos-donations' ),
			'message_description'        => __( 'Leave a message.', 'kudos-donations' ),
			'payment_title'              => __( 'Payment', 'kudos-donations' ),
			'payment_description'        => __( 'By clicking donate you agree to the following payment:', 'kudos-donations' ),
			'return_message_title'       => __( 'Payment received', 'kudos-donations' ),
			'return_message_text'        => __( 'Thank you for your donation!', 'kudos-donations' ),
		];
	}
}

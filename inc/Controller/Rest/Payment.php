<?php

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Infrastructure\Controller\AbstractRestController;
use IseardMedia\Kudos\Service\PaymentService;
use WP_REST_Server;

class Payment extends AbstractRestController
{
    /**
     * @var PaymentService
     */
    private PaymentService $payment_service;

    /**
     * PaymentRoutes constructor.
     */
    public function __construct(PaymentService $payment_service)
    {
	    parent::__construct();

	    $this->rest_base = 'payment';
        $this->payment_service = $payment_service;
    }

    /**
     * Payment service routes.
     *
     * @return array
     */
    public function get_routes(): array
    {
        $payment = $this->payment_service;

        return [
            '/create' => [
                'methods'             => 'POST',
                'callback'            => [$payment, 'submit_payment'],
                'permission_callback' => '__return_true',
                'args'                => [
                    'return_url'    => [
                        'type'              => 'string',
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'campaign_id'   => [
                        'type'              => 'string',
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'value'         => [
                        'type'              => 'integer',
                        'required'          => true,
                        'sanitize_callback' => 'absint',
                    ],
                    'name'          => [
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'email'         => [
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_email',
                    ],
                    'recurring'     => [
                        'type'              => 'boolean',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'business_name' => [
                        'type'              => 'string',
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'street'        => [
                        'type'              => 'string',
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'postcode'      => [
                        'type'              => 'string',
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'city'          => [
                        'type'              => 'string',
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'country'       => [
                        'type'              => 'string',
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'message'       => [
                        'type'              => 'string',
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'privacy'       => [
                        'type'              => 'boolean',
                        'required'          => false,
                        'sanitize_callback' => 'rest_sanitize_boolean',
                    ],
                    'terms'         => [
                        'type'              => 'boolean',
                        'required'          => false,
                        'sanitize_callback' => 'rest_sanitize_boolean',
                    ],
                ],
            ],

            '/webhook' => [
                'methods'             => 'POST',
                'callback'            => [$payment, 'handle_webhook'],
                'args'                => [
                    'id' => [
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'permission_callback' => '__return_true',
            ],

            '/test' => [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$payment, 'check_api_keys'],
                'args'                => [
                    'keys' => [
                        'type'     => 'object',
                        'required' => false,
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            ],
        ];
    }
}

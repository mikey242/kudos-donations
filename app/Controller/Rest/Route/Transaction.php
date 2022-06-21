<?php

namespace Kudos\Controller\Rest\Route;

use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\CustomPostType;
use Kudos\Service\MapperService;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Transaction extends Base
{
    /**
     * Base route.
     */
    protected $base = 'transaction';

    /**
     * @var MapperService
     */
    protected $mapper_service;

    /**
     * Route constructor.
     *
     * @param MapperService $mapper_service
     */
    public function __construct(MapperService $mapper_service)
    {
        $this->mapper_service = $mapper_service;
    }

    /**
     * Transaction routes.
     */
    public function get_routes(): array
    {
        $this->mapper_service->get_repository(TransactionEntity::class);

        return [
            $this->get_base()                                          => [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_all'],
                'permission_callback' => '__return_true',
            ],
            $this->get_base() . '/get'                                 => [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_one'],
                'args'                => [
                    'id' => [
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'absint',
                    ],
                ],
                'permission_callback' => '__return_true',
            ],
            $this->get_base() . '/between'                             => [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_all_between'],
                'args'                => [
                    'start' => [
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_title',
                    ],
                    'end'   => [
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_title',
                    ],
                ],
                'permission_callback' => '__return_true',
            ],
            $this->get_base() . '/campaign/(?P<campaign_id>\d+)'       => [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_all_campaign'],
                'args'                => [
                    'campaign_id' => [
                        'type'              => 'int',
                        'required'          => true,
                        'sanitize_callback' => 'absint',
                    ],
                ],
                'permission_callback' => '__return_true',
            ],
            $this->get_base() . '/campaign/total/(?P<campaign_id>\d+)' => [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_total_campaign'],
                'args'                => [
                    'campaign_id' => [
                        'type'              => 'int',
                        'required'          => true,
                        'sanitize_callback' => 'absint',
                    ],
                ],
                'permission_callback' => '__return_true',
            ],
        ];
    }

    /**
     * Get one by id.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function get_one(WP_REST_Request $request): WP_REST_Response
    {
        $mapper = $this->mapper_service;

        return new WP_REST_Response(
            $mapper->get_one_by([
                'id' => $request['id'],
            ])
        );
    }

    /**
     * Get all records.
     *
     * @return WP_REST_Response
     */
    public function get_all(): WP_REST_Response
    {
        $mapper = $this->mapper_service;

        return new WP_REST_Response(
            $mapper->get_all_by([
                'status' => 'paid',
            ])
        );
    }

    /**
     * Get all records between specified dates.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function get_all_between(WP_REST_Request $request): WP_REST_Response
    {
        $mapper   = $this->mapper_service;
        $response = new WP_REST_Response();

        if ($request->has_valid_params()) {
            $params = $request->get_query_params();
            if ( ! empty($params['start']) && ! empty($params['end'])) {
                $start = $params['start'] . ' 00:00:00';
                $end   = $params['end'] . ' 23:59:59';

                $response->set_data($mapper->get_all_between($start, $end));

                return $response;
            }

            $response->set_data($mapper->get_all_by());

            return $response;
        }

        return $response;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     */
    public function get_all_campaign(WP_REST_Request $request): WP_REST_Response
    {
        $mapper   = $this->mapper_service;
        $response = new WP_REST_Response();
        if ($request->has_valid_params()) {
            $param = $request->get_param('campaign_id');
            if ( ! empty($param)) {
                $response->set_data(
                    $mapper->get_all_by([
                        'campaign_id' => $param,
                        'status'      => 'paid',
                    ])
                );

                return $response;
            }
        }

        return $response;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     * @throws \Exception
     */
    public function get_total_campaign(WP_REST_Request $request): WP_REST_Response
    {
        $mapper   = $this->mapper_service;
        $response = new WP_REST_Response();
        if ($request->has_valid_params()) {
            $param = $request->get_param('campaign_id');
            if ( ! empty($param)) {
                $transactions = $mapper->get_all_by([
                    'campaign_id' => $param,
                    'status'      => 'paid',
                ]);

                $values     = array_column($transactions, 'value');
                $total      = array_sum($values);
                $additional = CustomPostType::get_post($param)['additional_funds'];
                if ( ! empty($additional[0])) {
                    $total = (int)$total + (int)$additional[0];
                }

                $response->set_data($total);
            }
        }

        return $response;
    }
}

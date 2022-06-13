<?php

namespace Kudos\Controller\Table;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Utils;
use Kudos\Service\MapperService;
use WP_List_Table;

class DonorsTable extends WP_List_Table
{
    use TableTrait;

    /**
     * An instance of the MapperService.
     *
     * @var MapperService
     */
    private $mapper;

    /**
     * Class constructor
     *
     * @since   2.0.0
     */
    public function __construct(MapperService $mapper_service)
    {
        $this->mapper = $mapper_service;
        $this->table  = DonorEntity::get_table_name();

        $this->search_columns = [
            'name'        => __('Name', 'kudos-donations'),
            'email'       => __('Email', 'kudos-donations'),
            'address'     => __('Address', 'kudos-donations'),
            'order_id'    => __('Order ID', 'kudos-donations'),
            'customer_id' => __('Customer ID', 'kudos-donations'),
        ];

        $this->export_columns = [
            'name'     => __('Name', 'kudos-donations'),
            'email'    => __('Email', 'kudos-donations'),
            'street'   => __('Street', 'kudos-donations'),
            'postcode' => __('Postcode', 'kudos-donations'),
            'city'     => __('City', 'kudos-donations'),
            'country'  => __('Country', 'kudos-donations'),
            'mode'     => __('Mode', 'kudos-donations'),
        ];

        parent::__construct(
            [
                'orderBy'  => 'created',
                'singular' => __('Donor', 'kudos-donations'),
                'plural'   => __('Donors', 'kudos-donations'),
                'ajax'     => false,
            ]
        );
    }

    /**
     * Call this function where the table is to be displayed
     *
     * @since      1.0.0
     */
    public function display()
    {
        $this->views();
        $this->search_box(__('Search') . ' ' . $this->_args['plural'], 'search_records');
        parent::display();
    }

    /**
     * Get the table data
     *
     * @return array
     * @since   2.0.0
     */
    public function fetch_table_data(): array
    {
        $search       = $this->get_search_data();
        $search_field = ! empty($search['field']) ? $search['field'] : null;
        $search_term  = ! empty($search['term']) ? $search['term'] : null;
        $donors       = $this->mapper
            ->get_repository(DonorEntity::class)
            ->get_all_by([$search_field => $search_term]);

        return array_map(function ($donor) {
            return $donor->to_array();
        },
            $donors);
    }

    /**
     * Returns a list of columns to include in table
     *
     * @return array
     * @since   2.0.0
     */
    public function column_names(): array
    {
        return [
            'email'       => __('E-mail', 'kudos-donations'),
            'name'        => __('Name', 'kudos-donations'),
            'address'     => __('Address', 'kudos-donations'),
            'donations'   => __('Donations', 'kudos-donations'),
            'mode'        => __('Mode', 'kudos-donations'),
            'customer_id' => __('Customer ID', 'kudos-donations'),
            'created'     => __('Date', 'kudos-donations'),
        ];
    }

    /**
     * Define which columns are hidden
     *
     * @return array
     */
    public function get_hidden_columns(): array
    {
        return [
            'subscription_id',
            'id',
        ];
    }

    /**
     * Define the sortable columns
     *
     * @return array
     */
    public function get_sortable_columns(): array
    {
        return [
            'created' => [
                'created',
                false,
            ],
            'value'   => [
                'value',
                false,
            ],
        ];
    }

    /**
     * Process cancel and bulk-cancel actions.
     */
    public function process_bulk_action()
    {
        // Detect when a bulk action is being triggered.
        switch ($this->current_action()) {
            case 'delete':
                // In our file that handles the request, verify the nonce.
                if (
                    ! isset($_REQUEST['_wpnonce']) || ! wp_verify_nonce(
                        sanitize_key($_REQUEST['_wpnonce']),
                        'bulk-' . $this->_args['singular']
                    )
                ) {
                    die();
                }

                if (isset($_GET['id'])) {
                    self::delete_record('id', sanitize_text_field(wp_unslash($_GET['id'])));
                }

                break;

            case 'bulk-delete':
                // In our file that handles the request, verify the nonce.
                if (
                    ! isset($_REQUEST['_wpnonce']) || ! wp_verify_nonce(
                        sanitize_key($_REQUEST['_wpnonce']),
                        'bulk-' . $this->_args['plural']
                    )
                ) {
                    die();
                }

                if (isset($_REQUEST['bulk-action'])) {
                    $donor_ids = array_map('sanitize_text_field', wp_unslash($_REQUEST['bulk-action']));
                    foreach ($donor_ids as $id) {
                        self::delete_record('id', $id);
                    }
                }
                break;
        }
    }

    /**
     * Delete a donor.
     *
     * @param string $column Column name to search.
     * @param string $id Value to search for.
     *
     * @return false|int
     */
    protected function delete_record(string $column, string $id)
    {
        return $this->mapper
            ->get_repository(DonorEntity::class)
            ->delete($column, $id);
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item Array of results.
     *
     * @return string
     */
    protected function column_cb($item): string
    {
        return sprintf(
            '<input type="checkbox" name="bulk-action[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * Time (date) column
     *
     * @param array $item Array of results.
     *
     * @return string
     */
    protected function column_created(array $item): string
    {
        return __('Added', 'kudos-donations') . '<br/>' .
               wp_date(
                   get_option('date_format') . ' ' . get_option('time_format'),
                   strtotime($item['created'])
               );
    }

    /**
     * Email column
     *
     * @param array $item Array of results.
     *
     * @return string
     */
    protected function column_email(array $item): string
    {
        $url = add_query_arg([
            'page'     => esc_attr($_REQUEST['page']),
            'action'   => 'delete',
            'id'       => sanitize_text_field($item['id']),
            '_wpnonce' => wp_create_nonce('bulk-' . $this->_args['singular']),
        ]);

        $title = sprintf(
            '<a href="mailto: %1$s" />%1$s</a>',
            $item['email']
        );

        $actions = [
            'delete' => sprintf(
                '<a href="%s">%s</a>',
                $url,
                __('Delete', 'kudos-donations')
            ),
        ];

        return $title . $this->row_actions($actions);
    }

    /**
     * Address column
     *
     * @param array $item Array of results.
     *
     * @return string
     */
    protected function column_address(array $item): string
    {
        $address = [
            $item['business_name'],
            $item['street'],
            $item['postcode'] . ' ' . $item['city'],
            $item['country'],
        ];

        $address = array_filter(
            $address,
            function ($item) {
                return ! empty($item) ? wp_unslash($item) : null;
            }
        );

        return implode('<br/>', $address);
    }

    /**
     * Donations column
     *
     * @param array $item Array of results.
     *
     * @return string
     */
    protected function column_donations(array $item)
    {
        $transactions = $this->mapper
            ->get_repository(TransactionEntity::class)
            ->get_all_by(['customer_id' => $item['customer_id']]);

        if ($transactions) {
            $number = count($transactions);
            $total  = 0;
            /** @var TransactionEntity $transaction */
            foreach ($transactions as $transaction) {
                if ('paid' === $transaction->status) {
                    $refunds = $transaction->get_refund();
                    if ($refunds) {
                        $total = $total + $refunds->remaining;
                    } else {
                        $total = $total + $transaction->value;
                    }
                }
            }

            return '<a href="' . admin_url(
                    'admin.php?page=kudos-transactions&search-field=customer_id&s=' . rawurlencode(
                        $item['customer_id']
                    ) . ''
                ) . '">
						' . $number . ' ( ' . Utils::get_currency_symbol($transactions[0]->currency) . $total . ' )' .
                   '</a>';
        }

        return false;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    protected function get_bulk_actions(): array
    {
        return [
            'bulk-delete' => __('Delete', 'kudos-donations'),
        ];
    }
}

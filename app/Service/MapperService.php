<?php

namespace Kudos\Service;

use Kudos\Entity\AbstractEntity;
use Kudos\Helpers\Settings;
use Kudos\Helpers\WpDb;

class MapperService
{
    /**
     * Repository class.
     *
     * @var AbstractEntity
     */
    protected $repository;
    /**
     * @var \Kudos\Service\LoggerService
     */
    private $logger;
    /**
     * @var \Kudos\Helpers\WpDb|\wpdb
     */
    private $wpdb;

    /**
     * Entity object constructor.
     *
     * @param \Kudos\Service\LoggerService $logger_service
     * @param \Kudos\Helpers\WpDb $wpdb
     */
    public function __construct(LoggerService $logger_service, WpDb $wpdb)
    {
        $this->wpdb   = $wpdb;
        $this->logger = $logger_service;
    }

    /**
     * Commit Entity to database.
     *
     * @param AbstractEntity $entity Instance of AbstractEntity to save.
     *
     * @param bool $ignore_null Whether to remove NULL or empty fields from
     *                          the save queries.
     *
     * @return false|int Returns the id of the record if successful
     *                   and false if not.
     */
    public function save(AbstractEntity $entity, bool $ignore_null = true)
    {
        $entity->last_updated = gmdate('Y-m-d H:i:s', time());

        // Set repository if not already set.
        if (! $this->repository) {
            $this->get_repository(get_class($entity));
        }

        if ($entity->id) {
            // If we have an id, then update row.
            $result = $this->update_record($entity, $ignore_null);
        } else {
            // Otherwise, create new record.
            $result = $this->add_record($entity);
        }

        // Invalidate cache if database updated.
        if ($result) {
            $this->get_cache_incrementer(true);
        }

        return $result;
    }

    /**
     * Updates existing record.
     *
     * @param AbstractEntity $entity An instance of AbstractEntity.
     * @param bool $ignore_null Whether to ignore null properties.
     *
     * @return false|int Returns the id of the record if successful
     *                  and false if not.
     */
    private function update_record(AbstractEntity $entity, bool $ignore_null)
    {
        $wpdb       = $this->wpdb;
        $table_name = $entity::get_table_name();
        $id         = $entity->id;

        $this->logger->debug("Updating entity.", [
            'entity' => $entity::get_entity_name(),
            'id'     => $entity->id,
        ]);

        $result = $wpdb->update(
            $table_name,
            $ignore_null ? array_filter($entity->to_array(), [$this, 'remove_empty']) : $entity->to_array(),
            ['id' => $id]
        );

        if ($result) {
            do_action($entity::get_table_name(false) . '_updated', 'id', $id);

            return $id;
        }

        return $result;
    }

    /**
     * Adds new record to the database.
     *
     * @param AbstractEntity $entity Instance of AbstractEntity to add.
     *
     * @return false|int Returns the id of the record if successful
     *                   and false if not.
     */
    private function add_record(AbstractEntity $entity)
    {
        $wpdb       = $this->wpdb;
        $table_name = $entity::get_table_name();

        $entity->created = $entity->created ?? gmdate('Y-m-d H:i:s', time());

        $result = $wpdb->insert(
            $table_name,
            $entity->to_array()
        );

        $id         = $wpdb->insert_id;
        $entity->id = $id;
        $this->logger->debug("Creating entity.", [
            'entity' => $entity::get_entity_name(),
            'id'     => $entity->id,
        ]);

        // If successful do action.
        if ($result) {
            do_action($entity::get_table_name(false) . '_added', 'id', $id);

            return $id;
        }

        return $result;
    }

    /**
     * Cache incrementer for invalidating cache. Splits into groups
     * by current table name.
     *
     * @param false $refresh Whether to invalidate cache.
     *
     * @return mixed
     */
    private function get_cache_incrementer(bool $refresh = false)
    {
        // Override refresh with setting
        if (Settings::get_setting('disable_object_cache')) {
            $refresh = true;
        }

        $key   = 'kudos';
        $group = $this->get_table_name(false);
        $value = wp_cache_get($key, $group);

        if (false === $value || true === $refresh) {
            $value = time();
            wp_cache_set($key, $value, $group);
        }

        return $value;
    }

    /**
     * Returns current repository table name.
     *
     * @param bool $prefix Whether to return the prefix or not.
     *
     * @return string
     */
    public function get_table_name(bool $prefix = true): string
    {
        return $this->repository::get_table_name($prefix);
    }

    /**
     * Gets the current repository.
     *
     * @param string $class
     *
     * @return self|null
     */
    public function get_repository(string $class): ?MapperService
    {
        if (is_subclass_of($class, AbstractEntity::class)) {
            $this->repository = $class;

            return $this;
        }

        return null;
    }

    /**
     * Get row by $query_fields array.
     *
     * @param array $query_fields Key-value pair of fields to query
     *                             e.g. ['email' => 'john.smith@gmail.com'].
     * @param string $operator Operator to use to join array items. Can be AND or OR.
     *
     * @return AbstractEntity|null
     */
    public function get_one_by(array $query_fields, string $operator = 'AND'): ?AbstractEntity
    {
        $query_string = $this->array_to_where($query_fields, $operator);
        $table        = $this->get_table_name();
        $query        = "SELECT $table.* FROM $table $query_string LIMIT 1";

        $result = $this->get_results($query);

        if ($result) {
            // Return result as Entity specified in repository.
            return new $this->repository($result[0]);
        }

        return null;
    }

    /**
     * Converts an associative array into a query string.
     *
     * @param array $query_fields Array of key (column) and value pairs.
     * @param string $operator Accepts AND or OR.
     *
     * @return string
     */
    private function array_to_where(array $query_fields, string $operator = 'AND'): string
    {
        $wpdb = $this->wpdb;

        array_walk(
            $query_fields,
            function (&$field, $key) use ($wpdb) {
                if (empty($key)) {
                    $field = $wpdb->prepare('%s IS NOT NULL', $field);
                } else {
                    $key   = esc_sql($key);
                    $field = $wpdb->prepare("$key = %s", $field);
                }
            }
        );

        return 'WHERE ' . implode(' ' . $operator . ' ', $query_fields);
    }

    /**
     * Gets query results from cache or database.
     *
     * @param $query
     * @param string $output
     *
     * @return mixed|object|array|bool|null
     */
    public function get_results($query, string $output = ARRAY_A)
    {
        $wpdb        = $this->wpdb;
        $cache_key   = 'get_results-' . md5($query);
        $cache_group = 'kudos_' . $this->get_cache_incrementer();
        $result      = wp_cache_get($cache_key, $cache_group);

        if (false === $result) {
            $result = $wpdb->get_results($query, $output);
            wp_cache_set($cache_key, $result, $cache_group, 300);
        }

        return $result;
    }

    /**
     * Deletes all the records for the current repository.
     *
     * @return bool|int Returns the number of records deleted if successful
     *                  and false if not.
     */
    public function delete_all()
    {
        $records = $this->get_all_by();

        if ($records) {
            $total = 0;
            foreach ($records as $record) {
                if ($this->delete('id', $record->id)) {
                    $total++;
                }
            }

            return $total;
        }

        return false;
    }

    /**
     * Get all results from table.
     *
     * @param array|null $query_fields Array of columns and their values. The array is converted to
     *                                 a MYSQL WHERE statement as "key = value". If no value is
     *                                 specified it uses "key IS NOT NULL". If array is empty it
     *                                 returns all values in table.
     *
     * @param string $operator AND or OR.
     *
     * @return array
     */
    public function get_all_by(array $query_fields = null, string $operator = 'AND'): ?array
    {
        $table        = $this->get_table_name();
        $query_string = $query_fields ? $this->array_to_where($query_fields, $operator) : null;
        $query        = "SELECT $table.* FROM $table $query_string";

        $results = $this->get_results($query);

        if (! empty($results)) {
            return $this->map_to_class($results);
        }

        return [];
    }

    public function get_all_between($start, $end): array
    {
        $wpdb         = $this->wpdb;
        $table        = $this->get_table_name();
        $query_string = $wpdb->prepare("created BETWEEN %s AND %s", $start, $end);
        $query        = "SELECT $table.* FROM $table WHERE $query_string";
        $results      = $this->get_results($query);

        if (! empty($results)) {
            return $this->map_to_class($results);
        }

        return [];
    }

    /**
     * Maps array of current repository objects to instance
     * of current repository.
     *
     * @param array $results Array of properties and values to map.
     *
     * @return array
     */
    private function map_to_class(array $results): array
    {
        return array_map(
            function ($result) {
                return new $this->repository($result);
            },
            $results
        );
    }

    /**
     * Deletes selected record.
     *
     * @param string $column Column name to search for value.
     * @param string $value Value to search for.
     *
     * @return false|int
     */
    public function delete(string $column, string $value)
    {
        $wpdb = $this->wpdb;

        $deleted = $wpdb->delete(
            $this->get_table_name(),
            [$column => $value]
        );

        if (false !== $deleted) {
            // Invalidate cache if database updated.
            $this->get_cache_incrementer(true);
            $this->logger->info('Record deleted.', ['table' => $this->get_table_name(), $column => $value]);
            do_action($this->get_table_name(false) . '_delete', $column, $value);
        } else {
            $this->logger->error(
                'Error deleting record.',
                ['table' => $this->get_table_name(), $column => $value]
            );
        }

        return $deleted;
    }

    /**
     * Removes the specified table from the database.
     *
     * @param $table_name
     *
     * @return bool|int
     */
    public function delete_table($table_name)
    {
        return $this->wpdb->query(
            "DROP TABLE IF EXISTS $table_name"
        );
    }

    /**
     * Removes empty values from array.
     *
     * @param string|null $value Array value to check.
     *
     * @return bool
     */
    private function remove_empty(?string $value): bool
    {
        return ! is_null($value) && '' !== $value;
    }
}

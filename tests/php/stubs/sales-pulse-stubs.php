<?php

namespace EC_Sales_Pulse {
    /**
     * WC_SMA_Loader
     *
     * @since x.x.x
     */
    class WC_SMA_Loader
    {
        /**
         * Constructor
         *
         * @since x.x.x
         */
        public function __construct()
        {
        }
        /**
         * Suppress translation error.
         *
         * @param bool   $status       Status.
         * @param string $function_name Function name.
         * @param string $message      Message.
         * @param string $version      Version.
         *
         * @return bool
         * @since x.x.x
         */
        public function suppress_translation_error($status, $function_name, $message, $version)
        {
        }
        /**
         * Prevent Query Monitor from collecting textdomain errors.
         *
         * @param string $function_name The function that was called.
         * @param string $message The error message.
         * @param string $version The version.
         * @return void
         * @since x.x.x
         */
        public function prevent_qm_collection($function_name, $message, $version): void
        {
        }
        /**
         * Initiator
         *
         * @since x.x.x
         * @return object initialized object of class.
         */
        public static function get_instance()
        {
        }
        /**
         * Autoload classes.
         *
         * @param string $class class name.
         */
        public function autoload($class): void
        {
        }
        /**
         * Activation Reset
         *
         * @return void
         * @since x.x.x
         */
        public function activation_redirect(): void
        {
        }
        /**
         * Define the constants which will be used throughout the plugin.
         *
         * @since x.x.x
         * @return void
         */
        public function define_constants(): void
        {
        }
        /**
         * Plugin Activation actions.
         *
         * @since x.x.x
         */
        public function activation_actions(): void
        {
        }
        /**
         * Plugin Deactivation actions.
         *
         * @since x.x.x
         */
        public function deactivation_actions(): void
        {
        }
        /**
         * Enqueue required classes after plugins loaded.
         *
         * @since x.x.x
         * @return void
         */
        public function load_plugin(): void
        {
        }
        /**
         * Add meta link for the SureDash under the plugin description row.
         *
         * @param array<int,string> $links Array of plugin meta links.
         * @param string            $file Plugin file path.
         * @return array<int,string> Modified plugin meta links.
         * @since x.x.x
         */
        public function add_meta_links($links, $file)
        {
        }
    }
}
namespace EC_Sales_Pulse\Inc\Traits {
    /**
     * Trait Get_Instance.
     *
     * @phpstan-consistent-constructor
     * @since x.x.x
     */
    trait Get_Instance
    {
        /**
         * Instance object.
         *
         * Protected (not private) so `static::$instance` late-binding works
         * correctly with subclassing. Pro extends some of these singletons
         * via subclass + factory swap.
         *
         * @var static|null
         */
        protected static $instance = null;
        /**
         * Initiator.
         *
         * @since x.x.x
         * @return static Initialized instance of the using class.
         */
        public static function get_instance()
        {
        }
    }
    /**
     * Trait Get_Instance.
     *
     * @since x.x.x
     */
    trait API_Base
    {
        /**
         * Endpoint namespace.
         *
         * @var string
         */
        protected $namespace = 'sales-pulse/v1';
        /**
         * Constructor
         *
         * @since x.x.x
         */
        public function __construct()
        {
        }
        /**
         * Register API routes.
         *
         * @return string
         */
        public function get_api_namespace()
        {
        }
    }
    /**
     * Trait Ajax.
     *
     * @since x.x.x
     */
    trait Rest_Errors
    {
        /**
         * Errors
         *
         * @access private
         * @var array<string, string> Errors strings.
         * @since x.x.x
         */
        public $errors = [];
        /**
         * Creates an array of default ajax action related error messages.
         *
         * @since x.x.x
         * @return void
         */
        public function set_rest_event_errors(): void
        {
        }
        /**
         * Get error message.
         *
         * @param string $type Message type.
         * @return string
         * @since x.x.x
         */
        public function get_rest_event_error($type)
        {
        }
    }
}
namespace EC_Sales_Pulse\Inc\Utils {
    /**
     * Update Compatibility
     *
     * @package EC_Sales_Pulse
     */
    class Maintenance
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         *  Constructor
         */
        public function __construct()
        {
        }
        /**
         * Init
         *
         * @since x.x.x
         * @return void
         */
        public static function init(): void
        {
        }
        /**
         * Manage backward compatibility.
         *
         * @since x.x.x
         * @return void
         */
        public static function manage_backward(): void
        {
        }
    }
    /**
     * This class will holds the code related to the managing of settings of the plugin.
     *
     * @class Settings
     */
    class Settings
    {
        /**
         * Cache the DB options
         *
         * @since x.x.x
         * @access public
         * @var array<string, mixed>
         */
        public static $dashboard_options = [];
        /**
         * Returns all default portal settings.
         *
         * @return array<string, array<string, mixed>>
         * @since x.x.x
         */
        public static function get_settings_dataset()
        {
        }
        /**
         * Returns an option from the default options.
         *
         * @param  string $key     The option key.
         * @param  mixed  $default Option default value if option is not available.
         * @return mixed   Returns the option value
         *
         * @since x.x.x
         */
        public static function get_default_option($key, $default = false)
        {
        }
        /**
         * As per the settings dataset, return the default settings.
         *
         * @return array<string, mixed>
         * @since x.x.x
         */
        public static function get_default_settings()
        {
        }
        /**
         * Returns all portal settings.
         *
         * @param bool $use_cache Whether to use cached settings.
         *
         * @return array<string, mixed>
         * @since x.x.x
         */
        public static function get_wc_sma_settings($use_cache = true)
        {
        }
        /**
         * Update portal all settings.
         *
         * @param array<string, mixed> $settings The settings to update.
         * @return void
         * @since x.x.x
         */
        public static function update_wc_sma_settings($settings): void
        {
        }
        /**
         * Decrypt the keys of the settings array.
         *
         * @return array<string, mixed>
         * @since x.x.x
         */
        public static function get_settings()
        {
        }
        /**
         * Get the type of the setting.
         *
         * @param string $key The setting key.
         * @return string
         * @since x.x.x
         */
        public static function get_setting_type($key)
        {
        }
    }
    /**
     * This class setup all sanitization methods
     *
     * @class Sanitizer
     */
    class Sanitizer
    {
        /**
         * Sanitize JSON data with support for various data structures.
         *
         * @access public
         *
         * @param string               $json_data JSON string to sanitize.
         * @param array<string,string> $field_types Optional array mapping field names to their data types.
         * @param bool                 $preserve_structure Whether to preserve the original structure or extract specific fields.
         * @param array<string>        $extract_fields Fields to extract if not preserving structure.
         *
         * @since 1.0.0
         * @return array|mixed Sanitized data.
         */
        public static function sanitize_json_data($json_data, $field_types = [], $preserve_structure = true, $extract_fields = [])
        {
        }
        /**
         * Settings sanitizer for wc_sma settings.
         *
         * @access public
         *
         * @param mixed $dataset from AJAX.
         * @since 1.0.0
         * @return mixed Sanitized data.
         */
        public static function sanitize_settings_data($dataset)
        {
        }
    }
    /**
     * This class setup all helper action
     *
     * @class Helper
     */
    class Helper
    {
        /**
         * Returns an option from the database for the admin settings.
         *
         * @param  string $key     The option key.
         * @param  mixed  $default Option default value if option is not available.
         * @return mixed   Returns the option value
         *
         * @since x.x.x
         */
        public static function get_option($key, $default = false)
        {
        }
        /**
         * Update option from the database for the admin settings.
         *
         * @param  string $key      The option key.
         * @param  mixed  $value    Option value to update.
         * @return string           Return the option value
         *
         * @since x.x.x
         */
        public static function update_option($key, $value = true)
        {
        }
        /**
         * Delete option from the database for the admin settings.
         *
         * @param  string $key The option key.
         * @return bool        Returns true if the option was deleted, false otherwise.
         *
         * @since 1.0.0
         */
        public static function delete_option($key)
        {
        }
    }
}
namespace EC_Sales_Pulse\Inc\Services {
    class Query
    {
        /**
         * @var string
         */
        protected $id;
        /**
         * @var array<mixed>
         */
        protected $select = [];
        /**
         * @var string
         */
        protected $from = null;
        /**
         * @var array<mixed>
         */
        protected $join = [];
        /**
         * @var array<mixed>
         */
        protected $where = [];
        /**
         * @var array<mixed>
         */
        protected $order = [];
        /**
         * @var array<mixed>
         */
        protected $group = [];
        /**
         * @var string
         */
        protected $having = null;
        /**
         * @var int
         */
        protected $limit = null;
        /**
         * @var int
         */
        protected $offset = 0;
        /**
         * Static constructor.
         *
         * @since x.x.x
         *
         * @param string $id
         *
         * @return object
         */
        public static function init($id = null)
        {
        }
        /**
         * Adds select statement.
         *
         * @param string $statement
         *
         * @return $this
         * @since x.x.x
         */
        public function select($statement)
        {
        }
        /**
         * Adds from statement.
         *
         * @param string $name
         * @param bool   $add_prefix
         *
         * @since x.x.x
         *
         * @return Query
         */
        public function table($name, $add_prefix = true)
        {
        }
        /**
         * Adds from statement.
         *
         * @param string $from
         * @param bool   $add_prefix Should DB prefix be added.
         *
         * @return Query this for chaining.
         * @global object $wpdb
         *
         * @since x.x.x
         */
        public function from($from, $add_prefix = true)
        {
        }
        /**
         * Adds search statement.
         *
         * @param string       $search
         * @param array<mixed> $columns
         * @param string       $joint
         *
         * @since x.x.x
         *
         * @return Query
         */
        public function search($search, $columns, $joint = 'AND')
        {
        }
        /**
         * Create a where statement.
         *
         *     ->where('name', 'sultan')
         *     ->where('age', '>', 18)
         *     ->where('name', 'in', array('ayaan', 'ayaash', 'anaan'))
         *        ->where(function($q){
         *       $q->where('ID', '>', 21);
         * })
         *
         * @param string $column The SQL column
         * @param mixed  $param1 Operator or value depending if $param2 isset.
         * @param mixed  $param2 The value if $param1 is an operator.
         * @param string $joint the where type ( and, or )
         *
         * @return Query The current query builder.
         */
        public function where($column, $param1 = null, $param2 = null, $joint = 'and')
        {
        }
        /**
         * Create a where statement with matching parameters.
         *
         *   ->matchWhere('name', 'sultan')
         *   ->matchWhere('age', 18)
         *
         * @param string $column The SQL column
         * @param mixed  $param1
         * @param bool   $is_numeric
         *
         * @return Query The current query builder.
         * @since 0.0.2
         */
        public function matchWhere($column, $param1, $is_numeric = false)
        {
        }
        /**
         * Create an or where statement
         *
         * This is the same as the normal where just with a fixed type
         *
         * @param string $column The SQL column
         * @param mixed  $param1
         * @param mixed  $param2
         *
         * @return Query The current query builder.
         */
        public function orWhere($column, $param1 = null, $param2 = null)
        {
        }
        /**
         * Create an and where statement
         *
         * This is the same as the normal where just with a fixed type
         *
         * @param string $column The SQL column
         * @param mixed  $param1
         * @param mixed  $param2
         *
         * @return Query The current query builder.
         */
        public function andWhere($column, $param1 = null, $param2 = null)
        {
        }
        /**
         * Creates a where in statement
         *
         *     ->whereIn('id', [42, 38, 12])
         *
         * @param string       $column
         * @param array<mixed> $options
         *
         * @return Query The current query builder.
         */
        public function whereIn($column, array $options = [])
        {
        }
        /**
         * Creates a where not in statement
         *
         *     ->whereNotIn('id', [42, 38, 12])
         *
         * @param string       $column
         * @param array<mixed> $options
         *
         * @return Query The current query builder.
         */
        public function whereNotIn($column, array $options = [])
        {
        }
        /**
         * Creates a where something is null statement
         *
         *     ->whereNull('modified_at')
         *
         * @param string $column
         *
         * @return Query The current query builder.
         */
        public function whereNull($column)
        {
        }
        /**
         * Creates a where something is not null statement
         *
         *     ->whereNotNull('created_at')
         *
         * @param string $column
         *
         * @return Query The current query builder.
         */
        public function whereNotNull($column)
        {
        }
        /**
         * Creates a or where something is null statement
         *
         *     ->orWhereNull('modified_at')
         *
         * @param string $column
         *
         * @return Query The current query builder.
         */
        public function orWhereNull($column)
        {
        }
        /**
         * Creates a or where something is not null statement
         *
         *     ->orWhereNotNull('modified_at')
         *
         * @param string $column
         *
         * @return Query The current query builder.
         */
        public function orWhereNotNull($column)
        {
        }
        /**
         * Creates a where between statement
         *
         *     ->whereBetween('user_id', 1, 2000)
         *
         * @param string $column
         * @param mixed  $min
         * @param mixed  $max
         *
         * @return Query The current query builder.
         */
        public function whereBetween($column, $min, $max)
        {
        }
        /**
         * Creates a where not between statement
         *
         *     ->whereNotBetween('user_id', 1, 2000)
         *
         * @param string $column
         * @param mixed  $min
         * @param mixed  $max
         *
         * @return Query The current query builder.
         */
        public function whereNotBetween($column, $min, $max)
        {
        }
        /**
         * Creates a where date between statement
         *
         *     ->whereDateBetween('date', '2014-02-01', '2014-02-28')
         *
         * @param string $column
         * @param string $start
         * @param string $end
         *
         * @return Query The current query builder.
         */
        public function whereDateBetween($column, $start, $end)
        {
        }
        /**
         * @param string $query
         * @param string $joint
         *
         * @since 1.0.1
         *
         * @return Query
         */
        public function whereRaw($query, $joint = 'AND')
        {
        }
        /**
         * Add a join statement to the current query
         *
         *     ->join('avatars', 'users.id', '=', 'avatars.user_id')
         *
         * @param array<mixed>|string $table The table to join. (can contain an alias definition.)
         * @param string              $localKey
         * @param string              $operator The operator (=, !=, <, > etc.)
         * @param string              $referenceKey
         * @param string              $type The join type (inner, left, right, outer)
         * @param string              $joint The join AND or Or
         * @param bool                $add_prefix Add table prefix or not
         *
         * @return Query The current query builder.
         */
        public function join($table, $localKey, $operator = null, $referenceKey = null, $type = 'left', $joint = 'AND', $add_prefix = true)
        {
        }
        /**
         * Left join same as join with special type
         *
         * @param array<mixed>|string $table The table to join. (can contain an alias definition.)
         * @param string              $localKey
         * @param string              $operator The operator (=, !=, <, > etc.)
         * @param string              $referenceKey
         *
         * @return Query The current query builder.
         */
        public function leftJoin($table, $localKey, $operator = null, $referenceKey = null)
        {
        }
        /**
         * Alias of the `join` method with join type right.
         *
         * @param array<mixed>|string $table The table to join. (can contain an alias definition.)
         * @param string              $localKey
         * @param string              $operator The operator (=, !=, <, > etc.)
         * @param string              $referenceKey
         *
         * @return Query The current query builder.
         */
        public function rightJoin($table, $localKey, $operator = null, $referenceKey = null)
        {
        }
        /**
         * Alias of the `join` method with join type inner.
         *
         * @param array<mixed>|string $table The table to join. (can contain an alias definition.)
         * @param string              $localKey
         * @param string              $operator The operator (=, !=, <, > etc.)
         * @param string              $referenceKey
         *
         * @return Query The current query builder.
         */
        public function innerJoin($table, $localKey, $operator = null, $referenceKey = null)
        {
        }
        /**
         * Alias of the `join` method with join type outer.
         *
         * @param array<mixed>|string $table The table to join. (can contain an alias definition.)
         * @param string              $localKey
         * @param string              $operator The operator (=, !=, <, > etc.)
         * @param string              $referenceKey
         *
         * @return Query The current query builder.
         */
        public function outerJoin($table, $localKey, $operator = null, $referenceKey = null)
        {
        }
        /**
         * @param string $query
         * @param string $joint
         *
         * @since 1.0.1
         *
         * @return Query
         */
        public function joinRaw($query, $joint = 'AND')
        {
        }
        /**
         * Adds group by statement.
         *     ->groupBy('category')
         *     ->gorupBy(['category', 'price'])
         *
         * @param string $field
         *
         * @return Query this for chaining.
         * @since x.x.x
         */
        public function group_by($field)
        {
        }
        /**
         * Adds having statement.
         *
         *  ->group_by('user.id')
         *  ->having('count(user.id)>1')
         *
         * @param string $statement
         *
         * @return Query this for chaining.
         * @since x.x.x
         */
        public function having($statement)
        {
        }
        /**
         * Adds order by statement.
         *
         *     ->order_by('created_at')
         *     ->order_by('modified_at', 'desc')
         *
         * @param string $key
         * @param string $direction
         *
         * @return Query this for chaining.
         *
         * @since x.x.x
         */
        public function order_by($key, $direction = 'ASC')
        {
        }
        /**
         * Set the query limit
         *
         *     // limit(<limit>)
         *     ->limit(20)
         *
         *     // limit(<offset>, <limit>)
         *     ->limit(60, 20)
         *
         * @param int $limit
         * @param int $limit2
         *
         * @return Query The current query builder.
         */
        public function limit($limit, $limit2 = null)
        {
        }
        /**
         * Adds offset statement.
         *
         * ->offset(20)
         *
         * @param int $offset
         *
         * @return Query this for chaining.
         */
        public function offset($offset)
        {
        }
        /**
         * Create a query limit based on a page and a page size
         *
         * // page(<page>, <size>)
         *  ->page(2, 20)
         *
         * @param int $page
         * @param int $size
         *
         * @return Query The current query builder.
         * @since x.x.x
         */
        public function page($page, $size = 20)
        {
        }
        /**
         * Find something, means select one item by key
         *
         * ->find('navanathb@bsf.io', 'email')
         *
         * @param int    $id
         * @param string $key
         *
         * @return mixed
         */
        public function find($id, $key = 'id')
        {
        }
        /**
         * Get the first result ordered by the given key.
         *
         * @param string $key By what should the first item be selected? Default is: 'id'
         *
         * @return mixed The first result.
         */
        public function first($key = 'id')
        {
        }
        /**
         * Get the last result by key
         *
         * @param string $key
         *
         * @return mixed the last result.
         */
        public function last($key = 'id')
        {
        }
        /**
         * Pluck item.
         * ->find('post_title')
         *
         * @return mixed
         * @since 1.0.1
         */
        public function pluck()
        {
        }
        /**
         * Returns results from builder statements.
         *
         * @param mixed    $output WPDB output type.
         * @param callable $row_map Function callable to filter or map results to.
         * @param bool     $calc_rows Flag that indicates to SQL if rows should be calculated or not.
         *
         * @since x.x.x
         *
         * @global object $wpdb
         *
         * @return mixed
         */
        public function get($output = OBJECT, $row_map = null, $calc_rows = false)
        {
        }
        /**
         * Sets the limit to 1, executes and returns the first result using get.
         *
         * @param string $output
         *
         * @return mixed The single result.
         */
        public function one($output = OBJECT)
        {
        }
        /**
         * Just return the number of results
         *
         * @param string|int $column
         *
         * @return int
         */
        public function count($column = 1)
        {
        }
        /**
         * Just get a single value from the result
         *
         * @param int  $column The index of the column.
         * @param bool $calc_rows Flag that indicates to SQL if rows should be calculated or not.
         *
         * @return array<mixed> The columns value
         */
        public function column($column = 0, $calc_rows = false)
        {
        }
        /**
         * Returns a value.
         *
         * @param int $x Column of value to return. Indexed from 0.
         * @param int $y Row of value to return. Indexed from 0.
         *
         * @return mixed
         * @global object $wpdb
         *
         * @since x.x.x
         */
        public function value($x = 0, $y = 0)
        {
        }
        /**
         * Update or insert.
         *
         * @param array<mixed> $data
         *
         * @return bool|int
         */
        public function updateOrInsert($data)
        {
        }
        /**
         * Find or insert.
         *
         * @param array<mixed> $data
         *
         * @return bool|int
         */
        public function findOrInsert($data)
        {
        }
        /**
         * Get max value.
         *
         * @param string $column
         *
         * @return int
         * @since 1.0.1
         */
        public function max($column)
        {
        }
        /**
         * Get min value.
         *
         * @param string $column
         *
         * @return int
         * @since 1.0.1
         */
        public function min($column)
        {
        }
        /**
         * Get avg value.
         *
         * @param string $column
         *
         * @return int
         * @since 1.0.1
         */
        public function avg($column)
        {
        }
        /**
         * Get sum value.
         *
         * @param string $column
         *
         * @return int
         * @since 1.0.1
         */
        public function sum($column)
        {
        }
        /**
         * Returns flag indicating if query has been executed.
         *
         * @param string $sql
         *
         * @return bool
         * @since x.x.x
         *
         * @global object $wpdb
         */
        public function query($sql = '')
        {
        }
        /**
         * Returns query from builder statements.
         *
         * @return string
         * @since x.x.x
         */
        public function toSql()
        {
        }
        /**
         * Returns found rows in last query, if SQL_CALC_FOUND_ROWS is used and is supported.
         *
         * @return array<mixed>
         * @global object $wpdb
         *
         * @since x.x.x
         */
        public function rows_found()
        {
        }
        /**
         * Returns flag indicating if delete query has been executed.
         *
         * @return bool
         * @global object $wpdb
         *
         * @since x.x.x
         */
        public function delete()
        {
        }
        /**
         * Update
         *
         * @return bool
         * @global object $wpdb
         *
         * @param array<mixed> $data
         *
         * @since x.x.x
         */
        public function update($data)
        {
        }
        /**
         * Insert data.
         *
         * @param array<mixed> $data
         * @param array<mixed> $format
         *
         * @return bool|int
         * @since 1.0.1
         */
        public function insert($data, $format = [])
        {
        }
        /**
         * Return a cloned object from current builder.
         *
         * @return Query
         * @since x.x.x
         */
        public function copy()
        {
        }
        /**
         * Builds query's where statement.
         *
         * @param string &$query
         *
         * @since x.x.x
         */
        public function _query_where(&$query): void
        {
        }
    }
}
namespace EC_Sales_Pulse\Core\Database {
    /**
     * Option-backed persistence for the dirty-dates repair queue. Public API
     * mirrors the table-era model so callers (`OrderHooks`, `SnapshotBuilder`,
     * `ImpactSummary`) work unchanged.
     *
     * @phpstan-consistent-constructor
     */
    class DirtyDates
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * `wp_options` row that holds the pending date set as a `[ date => reason ]`
         * map. Non-autoloaded - hooked path only.
         */
        public const OPTION_KEY = 'salespulse_dirty_dates';
        /**
         * Returns an empty string. Retained so anything that asks the model for
         * its CREATE TABLE statement (Schema iterator, accidental typing) gets a
         * no-op instead of a fatal.
         *
         * @return string
         */
        public function get_schema(): string
        {
        }
        /**
         * Mark a date as dirty so the nightly rebuilder picks it up. Idempotent:
         * a date already in the set keeps its earlier `detected_at` to satisfy
         * "first time we noticed" semantics; reason is overwritten with the
         * latest signal.
         *
         * @param string $date   YYYY-MM-DD.
         * @param string $reason Why it's dirty (order_update | refund | status_change).
         * @return bool True on successful persist.
         */
        public function mark_dirty(string $date, string $reason = 'order_update'): bool
        {
        }
        /**
         * Pending dates in detection order, capped at `$limit`. Returned as
         * stdClass casts so callers can read `$row->stat_date` / `->reason` /
         * `->detected_at` as before.
         *
         * @param int $limit Max rows to return.
         * @return array<int, \stdClass>
         */
        public function get_pending(int $limit = 10): array
        {
        }
        /**
         * Drain a single date from the pending set + increment the repaired
         * counter so the Free Impact tile reflects the repair.
         *
         * @param string $date YYYY-MM-DD.
         * @return bool True if the date was previously in the set.
         */
        public function mark_resolved(string $date): bool
        {
        }
        /**
         * Count of all-time repaired edits. Reads the `system_state` counter
         * that replaces the v2-era `WHERE resolved_at IS NOT NULL` table scan.
         *
         * @return int
         */
        public function count_resolved(): int
        {
        }
    }
    /**
     * Shared base for every Sales Pulse / Store Copilot DB table model.
     *
     * Concrete subclasses declare a table name and prefix; this class supplies
     * the wpdb connection, charset_collate, and standard insert/update/delete
     * helpers so subclasses focus on schema and domain queries.
     */
    abstract class Base
    {
        /**
         * Table name without prefix.
         *
         * @var string
         */
        protected $table_name = '';
        /**
         * Table prefix.
         *
         * @var string
         */
        protected $prefix = 'salespulse_';
        /**
         * Primary key column.
         *
         * @var string
         */
        protected $primary_key = 'id';
        /**
         * WordPress database instance.
         *
         * @var \wpdb
         */
        protected $wpdb;
        /**
         * Constructor.
         */
        public function __construct()
        {
        }
        /**
         * Get the full table name with WordPress prefix.
         *
         * @return string
         */
        public function get_table_name(): string
        {
        }
        /**
         * Check if the table exists in the database.
         *
         * @return bool
         */
        public function table_exists(): bool
        {
        }
        /**
         * Get the CREATE TABLE SQL for this model.
         * Must be implemented by child classes.
         *
         * @return string SQL CREATE TABLE statement compatible with dbDelta().
         */
        abstract public function get_schema(): string;
        /**
         * Insert a row into the table.
         *
         * @param array<string, mixed> $data   Column => value pairs.
         * @param array<string>        $format Optional format array (%s, %d, %f).
         * @return int|false Insert ID on success, false on failure.
         */
        public function insert(array $data, array $format = [])
        {
        }
        /**
         * Update rows matching conditions.
         *
         * @param array<string, mixed> $data         Column => value pairs to update.
         * @param array<string, mixed> $where        Column => value pairs for WHERE clause.
         * @param array<string>        $format       Optional format for data.
         * @param array<string>        $where_format Optional format for where.
         * @return int|false Number of rows updated, or false on error.
         */
        public function update(array $data, array $where, array $format = [], array $where_format = [])
        {
        }
        /**
         * Insert or update a row (REPLACE INTO).
         *
         * @param array<string, mixed> $data   Column => value pairs.
         * @param array<string>        $format Optional format array.
         * @return int|false Rows affected or false on error.
         */
        public function replace(array $data, array $format = [])
        {
        }
        /**
         * Delete rows matching conditions.
         *
         * @param array<string, mixed> $where        Column => value pairs for WHERE clause.
         * @param array<string>        $where_format Optional format for where.
         * @return int|false Number of rows deleted, or false on error.
         */
        public function delete(array $where, array $where_format = [])
        {
        }
        /**
         * Get a single row by primary key.
         *
         * @param mixed $id Primary key value.
         * @return \stdClass|null Row object or null.
         */
        public function find($id)
        {
        }
        /**
         * Get all rows, optionally ordered.
         *
         * @param string $order_by Column to order by.
         * @param string $order    ASC or DESC.
         * @param int    $limit    Max rows to return. 0 = unlimited.
         * @return array<int, \stdClass>
         */
        public function all(string $order_by = '', string $order = 'ASC', int $limit = 0): array
        {
        }
        /**
         * Count rows, optionally with conditions.
         *
         * @param array<string, mixed> $where Optional WHERE conditions.
         * @return int
         */
        public function count(array $where = []): int
        {
        }
        /**
         * Truncate the table.
         *
         * @return bool
         */
        public function truncate(): bool
        {
        }
        /**
         * Get the charset collate for table creation.
         *
         * @return string
         */
        protected function get_charset_collate(): string
        {
        }
    }
    /**
     * `campaigns` table model. Stores merchant-tagged date windows that adjust
     * the diagnosis tone but never the underlying numbers (a Black Friday sale
     * shouldn't read as an "anomalous spike", for example).
     */
    class Campaigns extends \EC_Sales_Pulse\Core\Database\Base
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * Valid campaign goals.
         */
        public const GOAL_ORDERS = 'orders';
        public const GOAL_AOV = 'aov';
        public const GOAL_CLEARANCE = 'clearance';
        public const GOAL_LAUNCH = 'launch';
        /**
         * Table name without prefix.
         *
         * @var string
         */
        protected $table_name = 'campaigns';
        /**
         * Get the CREATE TABLE SQL.
         *
         * @return string
         */
        public function get_schema(): string
        {
        }
        /**
         * Get valid campaign goals.
         *
         * @return array<string, string>
         */
        public static function get_valid_goals(): array
        {
        }
        /**
         * Get the currently active campaign (if any).
         *
         * @return \stdClass|null Campaign object or null.
         */
        public function get_active()
        {
        }
        /**
         * Check if a campaign is active for a specific date.
         *
         * @param string $date Date in Y-m-d format.
         * @return \stdClass|null Active campaign or null.
         */
        public function get_active_for_date(string $date)
        {
        }
        /**
         * Create a new campaign.
         *
         * @param string      $name      Campaign name.
         * @param string      $goal      Campaign goal (orders, aov, clearance, launch).
         * @param string      $start_date Start date (Y-m-d).
         * @param string|null $end_date   End date (Y-m-d) or null for ongoing.
         * @return int|false Campaign ID or false.
         */
        public function create(string $name, string $goal, string $start_date, ?string $end_date = null)
        {
        }
        /**
         * End a campaign by setting its end date to today.
         *
         * @param int $campaign_id Campaign ID.
         * @return int|false Rows affected or false.
         */
        public function end_campaign(int $campaign_id)
        {
        }
        /**
         * Get all campaigns, ordered by most recent first.
         *
         * @param int $limit Max campaigns to return.
         * @return array<int, \stdClass>
         */
        public function get_all(int $limit = 50): array
        {
        }
    }
    /**
     * Plugin-level key/value state store.
     *
     * Tracks one-shot markers like the last snapshot date, last digest send
     * timestamp, db_version, backfill cursor, and similar bookkeeping that
     * doesn't fit a per-day table.
     */
    class SystemState extends \EC_Sales_Pulse\Core\Database\Base
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * Known state keys.
         */
        public const KEY_LAST_SNAPSHOT_DATE = 'last_snapshot_date';
        public const KEY_BACKFILL_START = 'backfill_start';
        public const KEY_BACKFILL_CURSOR = 'backfill_cursor';
        public const KEY_BACKFILL_COMPLETE = 'backfill_complete';
        public const KEY_DB_VERSION = 'db_version';
        public const KEY_PLUGIN_VERSION = 'plugin_version';
        public const KEY_LAST_DIGEST_SENT_DATE = 'last_digest_sent_date';
        public const KEY_LAST_DIGEST_SENT_AT = 'last_digest_sent_at';
        /**
         * Counters that replaced the `digest_history` table in v2 of the free
         * schema. The table stored 1 row per send attempt purely to power the
         * Free Impact tile's all-time "morning briefings delivered" count -
         * incrementing two keys does the same job in 1/10th the storage.
         */
        public const KEY_DIGEST_SENT_TOTAL = 'digest_sent_total';
        public const KEY_DIGEST_FAILED_TOTAL = 'digest_failed_total';
        public const KEY_DIGEST_LAST_ERROR = 'digest_last_error';
        /**
         * Counter that replaced the `dirty_dates.resolved_at` count read in v3
         * of the free schema. Powers the Free Impact "edits caught and repaired"
         * tile without keeping a per-edit row forever.
         */
        public const KEY_REPAIRED_TOTAL = 'repaired_total';
        /**
         * Table name without prefix.
         *
         * @var string
         */
        protected $table_name = 'system_state';
        /**
         * Primary key column.
         *
         * @var string
         */
        protected $primary_key = 'state_key';
        /**
         * Get the CREATE TABLE SQL.
         *
         * @return string
         */
        public function get_schema(): string
        {
        }
        /**
         * Get a state value by key.
         *
         * @param string $key     State key.
         * @param mixed  $default Default value if not found.
         * @return mixed
         */
        public function get(string $key, $default = null)
        {
        }
        /**
         * Set a state value (insert or update).
         *
         * @param string $key   State key.
         * @param string $value State value.
         * @return bool
         */
        public function set(string $key, string $value): bool
        {
        }
        /**
         * Increment an integer counter stored under `$key`. Treats a missing or
         * non-numeric existing value as 0. Returns the new total.
         *
         * @param string $key  Counter key.
         * @param int    $step Amount to add (default 1).
         * @return int
         */
        public function increment(string $key, int $step = 1): int
        {
        }
        /**
         * Remove a state key.
         *
         * @param string $key State key.
         * @return bool
         */
        public function remove(string $key): bool
        {
        }
        /**
         * Check if backfill is complete.
         *
         * @return bool
         */
        public function is_backfill_complete(): bool
        {
        }
        /**
         * Get the last snapshot date.
         *
         * @return string|null Date in Y-m-d format.
         */
        public function get_last_snapshot_date()
        {
        }
        /**
         * Set the last snapshot date.
         *
         * @param string $date Date in Y-m-d format.
         * @return bool
         */
        public function set_last_snapshot_date(string $date): bool
        {
        }
        /**
         * Get the `updated_at` timestamp of the last-snapshot record.
         *
         * Used by the dashboard header to surface a LIVE vs STALE badge.
         *
         * @return string|null ISO8601-compatible MySQL datetime, or null if never set.
         */
        public function get_last_snapshot_timestamp()
        {
        }
    }
    /**
     * `daily_stats` table model. One row per day with the rolled-up store
     * metrics (revenue, orders, items, AOV, refunds, new-vs-returning) that
     * every diagnosis, comparison, and chart reads from.
     */
    class DailyStats extends \EC_Sales_Pulse\Core\Database\Base
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * Table name without prefix.
         *
         * @var string
         */
        protected $table_name = 'daily_stats';
        /**
         * Primary key column.
         *
         * @var string
         */
        protected $primary_key = 'stat_date';
        /**
         * Get the CREATE TABLE SQL.
         *
         * @return string
         */
        public function get_schema(): string
        {
        }
        /**
         * Get snapshot for a specific date.
         *
         * @param string $date Date in Y-m-d format.
         * @return \stdClass|null
         */
        public function get_by_date(string $date)
        {
        }
        /**
         * Get snapshots for a date range.
         *
         * @param string $start_date Start date (Y-m-d).
         * @param string $end_date   End date (Y-m-d).
         * @return array<int, \stdClass>
         */
        public function get_range(string $start_date, string $end_date): array
        {
        }
        /**
         * Get aggregated metrics for a date range (for weekly/monthly comparison).
         *
         * @param string $start_date Start date (Y-m-d).
         * @param string $end_date   End date (Y-m-d).
         * @return \stdClass|null Aggregated metrics.
         */
        public function get_aggregated(string $start_date, string $end_date)
        {
        }
        /**
         * Upsert a daily snapshot (insert or update if date exists).
         *
         * @param array<string, mixed> $data Snapshot data.
         * @return int|false
         */
        public function upsert(array $data)
        {
        }
        /**
         * Get the most recent snapshot date.
         *
         * @return string|null Date in Y-m-d format, or null.
         */
        public function get_latest_date()
        {
        }
        /**
         * Get the oldest snapshot date.
         *
         * @return string|null Date in Y-m-d format, or null.
         */
        public function get_oldest_date()
        {
        }
        /**
         * Check if a snapshot exists for a given date.
         *
         * @param string $date Date in Y-m-d format.
         * @return bool
         */
        public function has_snapshot(string $date): bool
        {
        }
        /**
         * Get paginated snapshots ordered by date descending.
         *
         * @param int $limit  Number of rows.
         * @param int $offset Row offset.
         * @return array<int, \stdClass>
         */
        public function get_paginated(int $limit, int $offset = 0): array
        {
        }
        /**
         * Get missing dates in a range (dates without snapshots).
         *
         * @param string $start_date Start date (Y-m-d).
         * @param string $end_date   End date (Y-m-d).
         * @return array<string> Array of date strings.
         */
        public function get_missing_dates(string $start_date, string $end_date): array
        {
        }
    }
    /**
     * Plugin schema manager. Owns DB_VERSION + the list of table model classes,
     * runs `dbDelta()` for each one on install/upgrade, and stamps the version
     * in `system_state`.
     */
    class Schema
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * Current database schema version.
         *
         * Bumps:
         *   v3 - drops `digest_history` (replaced by two `system_state` counters
         *        and a last-error key) and `dirty_dates` (replaced by an
         *        option-backed set + repaired counter). Both tables existed
         *        purely to power one all-time count tile each on the Free
         *        Impact dashboard, which counters do at 1/10th the storage.
         */
        public const DB_VERSION = 3;
        /**
         * Create or update all plugin tables.
         *
         * @return void
         */
        public function install(): void
        {
        }
        /**
         * Check if schema needs update and run migrations.
         *
         * Pre-install hooks run version-targeted migrations (e.g. seeding the
         * v3 digest-sent counter from the soon-to-be-dropped table) before
         * dbDelta + the table drops.
         *
         * @return void
         */
        public function maybe_upgrade(): void
        {
        }
        /**
         * Check if all required tables exist.
         *
         * @return bool
         */
        public function tables_exist(): bool
        {
        }
        /**
         * Drop all plugin tables.
         * Only call during uninstall, NOT deactivation.
         *
         * @return void
         */
        public function uninstall(): void
        {
        }
        /**
         * Get status of all tables (for data readiness check).
         *
         * @return array<string, array<string, mixed>>
         */
        public function get_tables_status(): array
        {
        }
    }
}
namespace EC_Sales_Pulse\Core\Models {
    /**
     * Class Query Model.
     */
    class Controller
    {
        /**
         * Cache key.
         */
        public const DB_CACHE_KEY = 'wc_sma_query_data';
        /**
         * Base models.
         */
        public const BASE_MODEL = 'EC_Sales_Pulse\Core\Models\\';
        /**
         * Get query data.
         *
         * @param string       $query Query.
         * @param array<mixed> $args  Args.
         *
         * @return array<mixed>
         */
        public static function get_query_data($query, $args = []): array
        {
        }
        /**
         * Get query data.
         *
         * @param string       $query Query.
         * @param array<mixed> $args  Args.
         *
         * @return array<mixed>
         */
        public static function get_user_query_data($query, $args = []): array
        {
        }
        /**
         * Get query post data.
         *
         * @param string       $query Query.
         * @param array<mixed> $args  Args.
         *
         * @return array<mixed>
         */
        public static function get_query_post_data($query, $args = []): array
        {
        }
        /**
         * Get uncategorized items data.
         *
         * @param string       $query Query.
         * @param array<mixed> $args  Args.
         *
         * @return array<mixed>
         */
        public static function get_query_uncategorized_items($query, $args = []): array
        {
        }
        /**
         * Update query data.
         *
         * @param string       $query Query.
         * @param array<mixed> $data  Data.
         *
         * @return void
         */
        public static function update_query_data($query, $data): void
        {
        }
        /**
         * Update checksum.
         *
         * @param string $query Query.
         *
         * @return void
         */
        public static function update_checksum($query): void
        {
        }
    }
}
namespace EC_Sales_Pulse\Core\Hooks {
    /**
     * WooCommerce order-event listener.
     *
     * Marks affected dates as dirty when orders are created, updated, status-changed
     * or refunded. Aggregation and snapshot rebuild happen later in the nightly cron;
     * this class only writes lightweight `dirty_dates` rows.
     */
    class OrderHooks
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * Constructor - register WooCommerce hooks.
         */
        public function __construct()
        {
        }
        /**
         * Handle new order creation.
         *
         * @param int            $order_id Order ID.
         * @param \WC_Order|null $order    Order object.
         */
        public function on_order_created($order_id, $order = null): void
        {
        }
        /**
         * Handle order update.
         *
         * @param int            $order_id Order ID.
         * @param \WC_Order|null $order    Order object.
         */
        public function on_order_updated($order_id, $order = null): void
        {
        }
        /**
         * Handle order status change.
         *
         * @param int       $order_id   Order ID.
         * @param string    $old_status Old status.
         * @param string    $new_status New status.
         * @param \WC_Order $order      Order object.
         */
        public function on_status_changed($order_id, $old_status, $new_status, $order): void
        {
        }
        /**
         * Handle order refund.
         * Marks the ORIGINAL order date dirty (not the refund date).
         *
         * @param int $order_id  Original order ID.
         * @param int $refund_id Refund ID.
         */
        public function on_order_refunded($order_id, $refund_id): void
        {
        }
    }
}
namespace EC_Sales_Pulse\Core\Controllers {
    /**
     * Shared base for every Sales Pulse REST controller.
     *
     * Centralises the `sales-pulse/v2` namespace, the `manage_woocommerce`
     * permission check, and the success/error response envelope so subclasses
     * only need to declare a `rest_base` and register their routes.
     */
    abstract class BaseController
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * REST namespace.
         *
         * @var string
         */
        protected $namespace = 'sales-pulse/v2';
        /**
         * Route base (override in each controller).
         *
         * @var string
         */
        protected $rest_base = '';
        /**
         * Constructor - hook into rest_api_init.
         */
        public function __construct()
        {
        }
        /**
         * Register controller routes. Must be implemented by each controller.
         *
         * @return void
         */
        abstract public function register_routes(): void;
        /**
         * Permission check: manage_woocommerce capability.
         *
         * @param \WP_REST_Request $request Request object.
         * @return bool|\WP_Error
         */
        public function admin_permission_check($request)
        {
        }
        /**
         * Success response helper.
         *
         * @param mixed $data    Response data.
         * @param int   $status  HTTP status code.
         * @return \WP_REST_Response
         */
        protected function success($data, int $status = 200): \WP_REST_Response
        {
        }
        /**
         * Error response helper.
         *
         * @param string $message Error message.
         * @param int    $status  HTTP status code.
         * @return \WP_REST_Response
         */
        protected function error(string $message, int $status = 400): \WP_REST_Response
        {
        }
        /**
         * Get sanitized date parameter from request.
         *
         * @param \WP_REST_Request $request Request object.
         * @param string           $key     Parameter key.
         * @param string           $default Default value.
         * @return string Date in Y-m-d format.
         */
        protected function get_date_param(\WP_REST_Request $request, string $key, string $default = ''): string
        {
        }
        /**
         * Get sanitized integer parameter from request.
         *
         * @param \WP_REST_Request $request Request object.
         * @param string           $key     Parameter key.
         * @param int              $default Default value.
         * @return int
         */
        protected function get_int_param(\WP_REST_Request $request, string $key, int $default = 0): int
        {
        }
    }
    /**
     * REST controller for the free Impact tab. Exposes a single read-only
     * `summary` endpoint backed by ImpactSummary; Pro overrides nothing here
     * and instead mounts its richer endpoints under `copilot/impact/*`.
     */
    class ImpactController extends \EC_Sales_Pulse\Core\Controllers\BaseController
    {
        /**
         * Route base.
         *
         * @var string
         */
        protected $rest_base = 'impact';
        /**
         * Register the free Impact REST routes.
         *
         * @return void
         */
        public function register_routes(): void
        {
        }
        /**
         * GET /sales-pulse/v2/impact/summary - returns the data-foundation stats
         * payload assembled by ImpactSummary.
         *
         * @return \WP_REST_Response
         */
        public function get_summary(): \WP_REST_Response
        {
        }
    }
    /**
     * Data-readiness REST controller. Surfaces a "is the dashboard ready to
     * render" probe (WC active, analytics tables present, snapshots have run)
     * plus a manual snapshot trigger for admin-side troubleshooting.
     */
    class DataReadiness extends \EC_Sales_Pulse\Core\Controllers\BaseController
    {
        /**
         * Route base.
         *
         * @var string
         */
        protected $rest_base = 'system';
        /**
         * Register routes.
         */
        public function register_routes(): void
        {
        }
        /**
         * Check data readiness - all prerequisites for dashboard to function.
         *
         * @param \WP_REST_Request $request Request object.
         * @return \WP_REST_Response
         */
        public function get_readiness(\WP_REST_Request $request): \WP_REST_Response
        {
        }
        /**
         * Manually trigger a snapshot for a specific date, or build an initial batch.
         *
         * - If `date` param is provided, builds that single date (existing behavior).
         * - Otherwise, builds an initial batch of recent days for dashboard readiness.
         *
         * @param \WP_REST_Request $request Request object.
         * @return \WP_REST_Response
         */
        public function trigger_snapshot(\WP_REST_Request $request): \WP_REST_Response
        {
        }
        /**
         * Get backfill progress.
         *
         * @param \WP_REST_Request $request Request object.
         * @return \WP_REST_Response
         */
        public function get_backfill_status(\WP_REST_Request $request): \WP_REST_Response
        {
        }
        /**
         * Trigger a single backfill batch manually.
         *
         * @param \WP_REST_Request $request Request object.
         * @return \WP_REST_Response
         */
        public function trigger_backfill(\WP_REST_Request $request): \WP_REST_Response
        {
        }
    }
    /**
     * REST controller for tagging, listing, and ending campaigns. Mounted at
     * `sales-pulse/v2/campaigns/*`. Campaigns colour the diagnosis but never
     * change `daily_stats` numbers.
     */
    class CampaignsController extends \EC_Sales_Pulse\Core\Controllers\BaseController
    {
        /**
         * Route base.
         *
         * @var string
         */
        protected $rest_base = 'campaigns';
        /**
         * Register routes.
         */
        public function register_routes(): void
        {
        }
        /**
         * List all campaigns (most recent first).
         *
         * @param \WP_REST_Request $request Request object.
         * @return \WP_REST_Response
         */
        public function get_campaigns(\WP_REST_Request $request): \WP_REST_Response
        {
        }
        /**
         * Create a new campaign.
         *
         * @param \WP_REST_Request $request Request object.
         * @return \WP_REST_Response
         */
        public function create_campaign(\WP_REST_Request $request): \WP_REST_Response
        {
        }
        /**
         * End a campaign early (set end_date to today).
         *
         * @param \WP_REST_Request $request Request object.
         * @return \WP_REST_Response
         */
        public function end_campaign(\WP_REST_Request $request): \WP_REST_Response
        {
        }
        /**
         * Delete a campaign permanently.
         *
         * @param \WP_REST_Request $request Request object.
         * @return \WP_REST_Response
         */
        public function delete_campaign(\WP_REST_Request $request): \WP_REST_Response
        {
        }
    }
    /**
     * REST controller for the morning-briefing Overview page. Composes the
     * KPI cards, diagnosis, and suggested action for a given period
     * (daily / weekly / monthly) into one response. Filterable via
     * `salespulse_overview_response` so Pro can append forecast + anomaly
     * data to the same payload.
     */
    class Overview extends \EC_Sales_Pulse\Core\Controllers\BaseController
    {
        /**
         * Route base.
         *
         * @var string
         */
        protected $rest_base = 'overview';
        /**
         * Register routes.
         */
        public function register_routes(): void
        {
        }
        /**
         * Get overview / morning briefing data.
         *
         * @param \WP_REST_Request $request Request object.
         * @return \WP_REST_Response
         */
        public function get_overview(\WP_REST_Request $request): \WP_REST_Response
        {
        }
        /**
         * Get trend data for sparkline chart.
         *
         * @param \WP_REST_Request $request Request object.
         * @return \WP_REST_Response
         */
        public function get_trend(\WP_REST_Request $request): \WP_REST_Response
        {
        }
    }
    /**
     * REST controller for the History tab. Returns paginated daily snapshots
     * with diagnoses so the merchant can scrub backwards through time.
     */
    class History extends \EC_Sales_Pulse\Core\Controllers\BaseController
    {
        /**
         * Route base.
         *
         * @var string
         */
        protected $rest_base = 'history';
        /**
         * Register routes.
         */
        public function register_routes(): void
        {
        }
        /**
         * Get paginated history of daily diagnoses.
         *
         * @param \WP_REST_Request $request Request object.
         * @return \WP_REST_Response
         */
        public function get_history(\WP_REST_Request $request): \WP_REST_Response
        {
        }
    }
    /**
     * REST controller for the Settings page. Reads/writes the `salespulse_settings`
     * option (digest schedule, recipient, sensitivity, etc.) under the
     * `sales-pulse/v2/settings` namespace.
     */
    class SettingsController extends \EC_Sales_Pulse\Core\Controllers\BaseController
    {
        /**
         * Option key for all plugin settings.
         */
        public const OPTION_KEY = 'salespulse_settings';
        /**
         * Default settings.
         */
        public const DEFAULTS = [
            'snapshot_hour' => 2,
            // 0-23.
            'snapshot_min' => 10,
            // 0-59.
            'email_enabled' => false,
            'email_address' => '',
            // Defaults to admin email.
            'diagnosis_sensitivity' => 'balanced',
            // One of calm, balanced, vigilant.
            'last_digest_error' => null,
        ];
        /**
         * Allowed values for the diagnosis_sensitivity setting.
         */
        public const SENSITIVITY_VALUES = ['calm', 'balanced', 'vigilant'];
        /**
         * Route base.
         *
         * @var string
         */
        protected $rest_base = 'settings';
        /**
         * Register routes.
         */
        public function register_routes(): void
        {
        }
        /**
         * Get current settings.
         *
         * @param \WP_REST_Request $request Request object.
         * @return \WP_REST_Response
         */
        public function get_settings(\WP_REST_Request $request): \WP_REST_Response
        {
        }
        /**
         * Update settings (partial update - only provided keys are changed).
         *
         * @param \WP_REST_Request $request Request object.
         * @return \WP_REST_Response
         */
        public function update_settings(\WP_REST_Request $request): \WP_REST_Response
        {
        }
        /**
         * Get all settings merged with defaults.
         *
         * @return array<string, mixed>
         */
        public static function get_all(): array
        {
        }
        /**
         * Get a single setting value.
         *
         * @param string $key     Setting key.
         * @param mixed  $default Default value.
         * @return mixed
         */
        public static function get(string $key, $default = null)
        {
        }
    }
    /**
     * REST controller for the morning-briefing email digest. Surfaces a
     * "send test" endpoint for the Settings UI to verify mail configuration
     * before the daily cron fires.
     */
    class DigestController extends \EC_Sales_Pulse\Core\Controllers\BaseController
    {
        /**
         * Route base.
         *
         * @var string
         */
        protected $rest_base = 'system/digest';
        /**
         * Register routes.
         */
        public function register_routes(): void
        {
        }
        /**
         * Send a one-off test digest.
         *
         * @param \WP_REST_Request $request Request object.
         */
        public function send_test(\WP_REST_Request $request): \WP_REST_Response
        {
        }
    }
}
namespace EC_Sales_Pulse\Core\Services {
    /**
     * Nightly daily-stats snapshot builder.
     *
     * Builds a `daily_stats` row from `wc_order_stats` for a given date and
     * processes the dirty-dates queue so any post-edit corrections are
     * reflected in the next morning's briefing.
     */
    class SnapshotBuilder
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * Build and store a snapshot for a specific date.
         *
         * @param string $date Date in Y-m-d format.
         * @return bool True on success, false on failure.
         */
        public function build_snapshot(string $date): bool
        {
        }
        /**
         * Build yesterday's snapshot (primary nightly operation).
         *
         * @return bool
         */
        public function build_yesterday(): bool
        {
        }
        /**
         * Process and repair all dirty dates.
         *
         * @param int $max_dates Maximum dirty dates to process per run.
         * @return int Number of dates repaired.
         */
        public function repair_dirty_dates(int $max_dates = 5): int
        {
        }
        /**
         * Run the full nightly snapshot process.
         * Step 1: Build yesterday.
         * Step 2: Repair dirty dates.
         *
         * @return array<string, mixed> Summary of operations.
         */
        public function run_nightly(): array
        {
        }
        /**
         * Backfill historical snapshots (reverse chronological).
         * Processes a limited batch per call to avoid timeouts.
         *
         * @param int $batch_size Number of days to process per batch.
         * @return array<string, mixed> Backfill progress info.
         */
        public function run_backfill(int $batch_size = 3): array
        {
        }
        /**
         * Build snapshots for the last N days (for initial setup).
         * Skips dates that already have snapshots.
         *
         * @param int $days Number of days to build (from yesterday going backwards).
         * @return array<string, int> Summary with days_requested and days_built.
         */
        public function build_initial_batch(int $days = 14): array
        {
        }
        /**
         * Check if yesterday's snapshot exists (for cron fallback).
         *
         * @return bool
         */
        public function has_yesterday_snapshot(): bool
        {
        }
    }
    /**
     * Reads aggregate metrics from WooCommerce analytics tables for a given
     * date and returns them in the shape `daily_stats` expects. Wraps the WC
     * query so the SnapshotBuilder stays free of WC-table SQL.
     */
    class DataCollector
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * Constructor.
         */
        public function __construct()
        {
        }
        /**
         * Check if WooCommerce Analytics tables exist and are usable.
         *
         * @return bool
         */
        public function are_analytics_tables_available(): bool
        {
        }
        /**
         * Get the oldest order date in WooCommerce.
         *
         * @return string|null Date in Y-m-d format, or null if no orders.
         */
        public function get_oldest_order_date()
        {
        }
        /**
         * Get the total number of valid orders in WooCommerce.
         *
         * @return int
         */
        public function get_total_order_count(): int
        {
        }
        /**
         * Collect all metrics for a single day.
         * This is the primary method called by SnapshotBuilder.
         *
         * @param string $date Date in Y-m-d format.
         * @return array<string, mixed> Structured metrics array ready for daily_stats table.
         */
        public function collect_day_metrics(string $date): array
        {
        }
    }
    /**
     * WC_Email subclass for the morning-briefing digest. Hooked into the
     * WooCommerce email manager so merchants can configure subject/template
     * via the standard WC Settings → Emails surface.
     */
    class DigestEmail extends \WC_Email
    {
        /**
         * Payload assembled by DigestMailer; available to templates as `$email->payload`.
         *
         * @var array<string, mixed>
         */
        public $payload = [];
        /**
         * Wire up the WC_Email metadata used by the WC mailer registry.
         */
        public function __construct()
        {
        }
        /**
         * Triggered by DigestMailer. Renders templates and sends in one call.
         *
         * @param string               $recipient Validated recipient address.
         * @param string               $subject   Final subject line.
         * @param array<string, mixed> $payload   Data payload for the templates.
         */
        public function trigger_digest(string $recipient, string $subject, array $payload): bool
        {
        }
        /**
         * From-name override applied to the digest email.
         *
         * @param string $from_name Default WC value (unused; we always provide our own name).
         * @return string
         */
        public function get_from_name($from_name = '')
        {
        }
        /**
         * From-address override applied to the digest email. Defaults to the
         * site admin_email option; overridable via the salespulse filter.
         *
         * @param string $from_email Default WC value (unused; we always provide our own address).
         * @return string
         */
        public function get_from_address($from_email = '')
        {
        }
        /**
         * Render the HTML template into a string.
         *
         * @return string
         */
        public function get_content_html()
        {
        }
        /**
         * Render the plain-text template into a string.
         *
         * @return string
         */
        public function get_content_plain()
        {
        }
        /**
         * No editable fields. Render a notice instead with a deep link to our Settings page.
         *
         * @return void
         */
        public function init_form_fields(): void
        {
        }
    }
    /**
     * Deterministic revenue diagnosis.
     *
     * Compares a current period to a prior period, decomposes the revenue
     * delta into orders and AOV factors, weighs them against a sensitivity
     * threshold, and returns a "What changed and why" verdict. The output is
     * filterable via `salespulse_diagnosis_result` so Pro can layer LLM
     * explanations on top.
     */
    class DiagnosisEngine
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * Minimum revenue change percentage to trigger diagnosis (base; scaled by sensitivity).
         */
        public const CHANGE_THRESHOLD = 5.0;
        /**
         * Absolute floor: anything below this is treated as "no revenue" rather
         * than a real signal. Used by the new-store / dead-store edge cases.
         */
        public const MIN_REVENUE_THRESHOLD = 1.0;
        /**
         * Below this revenue, comparisons are statistically meaningless even
         * when both days have orders. STRATEGY.md Section 6: "Suppress strong
         * diagnosis, mark 'low sample size'." A jump from $7 to $76 is a
         * one-order-vs-one-order spike, not a trend.
         */
        public const LOW_SAMPLE_REVENUE_THRESHOLD = 50.0;
        /**
         * Below this order count on either side, the diagnosis is downgraded to
         * "low sample size" regardless of the dollar swing. Three orders is the
         * minimum where a primary-factor decomposition starts to mean something.
         */
        public const MIN_ORDERS_FOR_CONFIDENCE = 3;
        /**
         * Multipliers applied to CHANGE_THRESHOLD for each sensitivity level.
         *
         * Calm    - larger threshold, only flag major shifts.
         * Balanced - base threshold (5%).
         * Vigilant - tighter threshold, surface smaller movements.
         */
        public const SENSITIVITY_MULTIPLIERS = ['calm' => 1.5, 'balanced' => 1.0, 'vigilant' => 0.6];
        /**
         * Run full diagnosis comparing current vs previous period.
         *
         * @param \stdClass|array<string, mixed>|null $current     Current period metrics (from daily_stats or aggregated).
         * @param \stdClass|array<string, mixed>|null $previous    Previous period metrics.
         * @param string                              $sensitivity Diagnosis sensitivity (calm|balanced|vigilant).
         * @return array<string, mixed> Diagnosis result.
         */
        public function diagnose($current, $previous, string $sensitivity = 'balanced'): array
        {
        }
        /**
         * Get human-readable confidence label.
         *
         * @param float $confidence Confidence score (0-1).
         * @return string
         */
        public function get_confidence_label(float $confidence): string
        {
        }
    }
    /**
     * Builds and sends the morning-briefing digest email. Composes the
     * payload from yesterday's snapshot, dispatches via WC_Email when
     * available (and falls back to wp_mail), and logs every send into
     * `digest_history` with `sent`/`failed`/`skipped` status.
     */
    class DigestMailer
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * Constructor - listens for the nightly snapshot completion event.
         */
        public function __construct()
        {
        }
        /**
         * Decide whether to send today's nightly digest, and dispatch if so.
         *
         * Gates: snapshot built, toggle on, valid recipient, not already sent today.
         *
         * @param array<string, mixed> $summary Snapshot summary from SnapshotBuilder::run_nightly().
         */
        public function maybe_send_nightly($summary): void
        {
        }
        /**
         * Send the digest immediately.
         *
         * @param string|null $override_recipient Optional recipient to use instead of the stored email_address.
         * @param bool        $is_test            When true, bypass the once-per-day idempotency guard.
         * @return array{sent:bool, recipient:string, reason:?string}
         */
        public function send(?string $override_recipient = null, bool $is_test = false): array
        {
        }
        /**
         * Build the data payload used by both subject composer and templates.
         *
         * @return array<string, mixed>
         */
        public function build_payload(): array
        {
        }
        /**
         * Compose the subject line. Daily-first signal preference; falls back to 7d, then 30d.
         *
         * @param array<string, mixed> $payload Output of build_payload().
         */
        public function compose_subject(array $payload): string
        {
        }
    }
    /**
     * Read-only stat builder for the free Impact tab. Composes the six
     * data-foundation numbers (days of trustworthy data, order edits caught
     * & repaired, campaigns tracked, briefings delivered, latest refresh,
     * yesterday at a glance) without touching any AI surface.
     */
    class ImpactSummary
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * Build the keyed stat payload consumed by the free Impact tab.
         *
         * @return array<string, mixed>
         */
        public function build(): array
        {
        }
    }
    /**
     * Deterministic action recommender.
     *
     * Given a diagnosis + optional active campaign, picks one of a curated
     * set of action scenarios (winback, AOV-booster, abandonment-recovery,
     * etc.) and returns the merchant-facing recommendation. No AI calls;
     * the Pro plugin extends this to layer LLM-tailored copy on top.
     */
    class ActionEngine
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * Constructor - register scenarios.
         */
        public function __construct()
        {
        }
        /**
         * Get action recommendation from diagnosis result.
         *
         * @param array<string, mixed>                $diagnosis Diagnosis result from DiagnosisEngine.
         * @param \stdClass|array<string, mixed>|null $campaign  Active campaign (if any).
         * @return array<string, string> Action recommendation.
         */
        public function recommend(array $diagnosis, $campaign = null): array
        {
        }
    }
}
namespace EC_Sales_Pulse\Core\Cron {
    /**
     * Cron registration + dispatch. Schedules the nightly snapshot at 02:10
     * site-time and the digest send at the merchant's configured hour, and
     * fires the corresponding services when WP triggers the event.
     */
    class CronManager
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * Hook names.
         */
        public const HOOK_NIGHTLY = 'salespulse_nightly_snapshot';
        public const HOOK_BACKFILL = 'salespulse_backfill_runner';
        /**
         * Constructor - register cron hooks and schedules.
         */
        public function __construct()
        {
        }
        /**
         * Add custom cron schedules.
         *
         * @param array<string, array<string, mixed>> $schedules Existing schedules.
         * @return array<string, array<string, mixed>>
         */
        public function add_cron_schedules(array $schedules): array
        {
        }
        /**
         * Schedule all cron jobs if not already scheduled.
         */
        public function schedule_jobs(): void
        {
        }
        /**
         * Run the nightly snapshot job.
         * Builds yesterday's snapshot and repairs dirty dates.
         */
        public function run_nightly_snapshot(): void
        {
        }
        /**
         * Run the backfill job.
         * Processes a batch of historical dates.
         */
        public function run_backfill(): void
        {
        }
        /**
         * Fallback: build yesterday + day-before-yesterday on admin visit if missing.
         * Ensures the daily view has the minimum 2 snapshots needed for comparison.
         * Only runs once per hour using a transient guard.
         */
        public function maybe_fallback_snapshot(): void
        {
        }
        /**
         * Unschedule the backfill runner.
         */
        public function unschedule_backfill(): void
        {
        }
        /**
         * Unschedule all plugin cron jobs.
         * Called on plugin deactivation.
         */
        public static function unschedule_all(): void
        {
        }
    }
}
namespace EC_Sales_Pulse\Admin {
    /**
     * Notices
     *
     * @since x.x.x
     */
    class Notices
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * Constructor
         *
         * @since x.x.x
         */
        public function __construct()
        {
        }
        /**
         * Check if the current screen is the admin screen to display the notice.
         *
         * @return bool
         */
        public function should_notice_be_visible(): bool
        {
        }
        /**
         * Display admin notice if premium incompatible version is activated.
         *
         * @since x.x.x
         */
        public function minimum_pro_version_requirement(): void
        {
        }
    }
    /**
     * Menu
     *
     * @since x.x.x
     */
    class Menu
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * Settings page ID for Plugin settings.
         */
        public const PAGE_ID = 'sales-pulse';
        /**
         * Constructor
         *
         * @since x.x.x
         *
         * @return void
         */
        public function __construct()
        {
        }
        /**
         * Function to load the admin area actions.
         *
         * @since x.x.x
         */
        public function initialize_hooks(): void
        {
        }
        /**
         * Check if the current page is a plugin page.
         *
         * @since x.x.x
         */
        public function is_plugin_page(): bool
        {
        }
        /**
         *  Initialize Admin Setup.
         *
         * @since x.x.x
         */
        public function settings_admin_scripts(): void
        {
        }
        /**
         * Add submenu to admin menu.
         *
         * V2 navigation: Overview (default morning briefing), History (daily
         * explanation list), Campaigns (start/stop active campaigns), Impact
         * (data foundation stats), Settings (timezone, revenue basis, email
         * digest).
         *
         * @since x.x.x
         */
        public function register_plugin_menus(): void
        {
        }
        /**
         * Renders the WC SMA screen canvas.
         *
         * @since x.x.x
         */
        public function render_main_page(): void
        {
        }
        /**
         * Enqueue the Admin's build files for plugin to work.
         *
         * @since x.x.x
         */
        public function app_build_scripts(): void
        {
        }
        /**
         * Get plugin status
         *
         * @since x.x.x
         *
         * @param  string $plugin_init_file plugin init file.
         * @return string
         */
        public function get_plugin_status($plugin_init_file)
        {
        }
    }
    /**
     * API
     *
     * @since x.x.x
     */
    class API
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        use \EC_Sales_Pulse\Inc\Traits\API_Base;
        /**
         * Route base.
         *
         * @var string $rest_base REST base.
         */
        protected string $rest_base = '/dataset/';
        /**
         * Constructor
         *
         * @since x.x.x
         *
         * @return void
         */
        public function __construct()
        {
        }
        /**
         * Register API routes.
         *
         * @since x.x.x
         */
        public function register_routes(): void
        {
        }
        /**
         * Get common settings.
         *
         * @return array<string, mixed> $updated_option defaults + set DB option data.
         *
         * @since x.x.x
         */
        public function get_admin_settings(): array
        {
        }
        /**
         * Check whether a given request has permission to read notes.
         *
         * @since x.x.x
         *
         * @return bool|\WP_Error
         */
        public function get_permissions_check()
        {
        }
        /**
         * Update an value of a key,
         * from the settings database option for the admin settings page.
         *
         * @param string $key       The option key.
         * @param mixed  $value     The value to update.
         *
         * @return void             Return the option value based on provided key
         *
         * @since x.x.x
         */
        public static function update_admin_settings_option(string $key, $value): void
        {
        }
    }
}
namespace {
    /**
     * Check if pro version is active.
     *
     * @return bool
     * @since x.x.x
     */
    function wc_sma_is_pro_active()
    {
    }
    /**
     * Clean variables using sanitize_text_field.
     *
     * @param mixed $var Data to sanitize.
     * @return mixed
     *
     * @since x.x.x
     */
    function wc_sma_clean_data($var)
    {
    }
    /**
     * Get the ORM query instance.
     *
     * @return Query
     */
    function wc_sma_query()
    {
    }
    /**
     * Set constants
     */
    \define('EC_SALES_PULSE_VER', '0.0.1');
    \define('EC_SALES_PULSE_FILE', __FILE__);
    \define('EC_SALES_PULSE_PRO_MINIMUM_VER', '0.0.1');
    /**
     * Format a metric_card value.
     *
     * @param mixed  $value
     * @param string $format currency|number|decimal
     * @param string $symbol Currency symbol.
     */
    $fmt_value = static function ($value, string $format, string $symbol): string {
        $value = (float) $value;
        return number_format_i18n($value, 0);
    };
}
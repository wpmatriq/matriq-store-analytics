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
         * Load Plugin Text Domain.
         * This will load the translation textdomain depending on the file priorities.
         *      1. Global Languages /wp-content/languages/sales-pulse/ folder
         *      2. Local directory /wp-content/plugins/sales-pulse/languages/ folder
         *
         * @since x.x.x
         * @return void
         */
        public function load_textdomain(): void
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
     * @since x.x.x
     */
    trait Get_Instance
    {
        /**
         * Instance object.
         *
         * @var object Class Instance.
         */
        private static $instance = null;
        /**
         * Initiator
         *
         * @since x.x.x
         * @return object initialized object of class.
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
    /**
     * Router class.
     */
    class Router extends \WP_REST_Controller
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * Namespace for the API.
         *
         * @var string
         */
        protected $namespace = 'sales-pulse/v1';
        // Default namespace, can be overridden.
        /**
         * Routes.
         *
         * @var array<mixed> $routes routes.
         */
        protected $routes = [];
        /**
         * Dynamic method handler for HTTP methods.
         *
         * @param string       $name name.
         * @param array<mixed> $arguments arguments.
         * @return void
         * @throws \BadMethodCallException If HTTP method is not supported.
         * @throws \InvalidArgumentException If a valid callback is not provided.
         */
        public static function __callStatic($name, $arguments): void
        {
        }
        /**
         * Register a REST route.
         *
         * @param string                $method HTTP method (GET, POST, etc.).
         * @param string                $endpoint Endpoint URL (e.g., '/example').
         * @param callable|array<mixed> $callback Callback function or array (Controller::method).
         * @param callable|null         $permission_callback Custom permission callback.
         * @param array<mixed>          $args Argument schema for validation.
         */
        public function addRoute($method, $endpoint, $callback, $permission_callback = null, $args = []): void
        {
        }
        /**
         * Default permission callback.
         *
         * @return bool
         */
        public function default_permission_callback()
        {
        }
        /**
         * Admin permission callback.
         *
         * @return bool
         */
        public function admin_permission_callback()
        {
        }
        /**
         * User permission callback.
         *
         * @return bool
         */
        public function user_permission_callback()
        {
        }
        /**
         * Default permission callback.
         *
         * @return bool
         */
        public function allowPermission()
        {
        }
        /**
         * Register all defined routes with WordPress.
         */
        public function registerRoutes(): void
        {
        }
        /**
         * Standardized success response.
         *
         * @param array<mixed> $data data.
         * @param int          $status status.
         * @return \WP_REST_Response
         */
        public static function success($data, $status = 200)
        {
        }
        /**
         * Standardized error response.
         *
         * @param string $message message.
         * @param int    $status status.
         * @return \WP_REST_Response
         */
        public static function error($message, $status = 400)
        {
        }
    }
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
namespace EC_Sales_Pulse\Core\Routers {
    /**
     * Class Misc Router.
     */
    class Misc
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        use \EC_Sales_Pulse\Inc\Traits\Rest_Errors;
        /**
         * Handler to get topic submitted.
         *
         * @param \WP_REST_Request $request The request object.
         * @since x.x.x
         * @return void
         */
        public function submit_topic($request): void
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
namespace EC_Sales_Pulse\Core {
    /**
     * Class CPTs.
     */
    class Routes
    {
        use \EC_Sales_Pulse\Inc\Traits\Get_Instance;
        /**
         * Constructor
         *
         * @since 1.0.0
         */
        public function __construct()
        {
        }
        /**
         * Init Hooks.
         *
         * @return void
         * @since 1.0.0
         */
        public function initialize_actions(): void
        {
        }
        /**
         * Return the rest response.
         *
         * @param mixed $response The response.
         * @param int   $status The status code.
         * @return \WP_Error|\WP_REST_Response
         */
        public static function rest_response($response, $status = 200)
        {
        }
        /**
         * Get SureDash routes.
         *
         * @return array<string, array<string, array<int, callable>>>
         */
        public function get_wc_sma_routes(): array
        {
        }
        /**
         * Register REST API routes.
         *
         * @return void
         */
        public function register_rest_routes(): void
        {
        }
        /**
         * Register route.
         *
         * @param string $method Method.
         * @param string $route Route.
         * @param array  $callback Callback.
         * @param bool   $permission_callback Permission callback.
         * @return void
         * @since 0.0.2
         * @phpstan-ignore-next-line
         */
        public function register_route($method, $route, $callback, $permission_callback = ''): void
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
         * @since x.x.x
         */
        public function register_plugin_menus(): void
        {
        }
        /**
         * Add the CSS to design the main side-bar menu of the plugin.
         *
         * @since x.x.x
         */
        public function admin_menu_css(): void
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
     * Get the Router instance.
     *
     * @return Router
     */
    function wc_sma_route()
    {
    }
    /**
     * Set constants
     */
    \define('EC_Sales_Pulse_VER', '0.0.1');
    \define('EC_Sales_Pulse_FILE', __FILE__);
    \define('EC_Sales_Pulse_PRO_MINIMUM_VER', '0.0.1');
}

<?php
/**
 * Plugin Name: WooCommerce Package ready for shipment
 * Plugin URI:  https://spletodrom.si
 * Description: Sets a custom WooCommerce order status "Package ready" and sends a custom email when a WooCommerce order status changes to this status
 * Version: 1.1.1
 * Author:      Elvis Sedić
 * Author URI:  https://spletodrom.si
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woocommerce-package-ready
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Main Plugin Class
 */
class WC_Package_Ready {
    /**
     * Plugin version.
     *
     * @var string
     */
    public $version = '1.1.1';

    /**
     * The single instance of the class.
     *
     * @var WC_Package_Ready
     */
    protected static $_instance = null;
    
    /**
     * Custom status slug - used consistently throughout the plugin.
     * 
     * @var string
     */
    public $status_slug = 'package-ready';
    
    /**
     * Custom status with wc prefix - used for registration.
     * 
     * @var string
     */
    public $wc_status_slug = 'wc-package-ready';

    /**
     * Main WC_Package_Ready Instance.
     * 
     * Ensures only one instance of WC_Package_Ready is loaded or can be loaded.
     * 
     * @return WC_Package_Ready - Main instance.
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        // Define constants
        $this->define_constants();
        
        // Check if WooCommerce is active
        add_action('plugins_loaded', array($this, 'on_plugins_loaded'));
    }

    /**
     * Define constants.
     */
    private function define_constants() {
        $this->define('WC_PACKAGE_READY_VERSION', $this->version);
        $this->define('WC_PACKAGE_READY_PLUGIN_DIR', plugin_dir_path(__FILE__));
        $this->define('WC_PACKAGE_READY_PLUGIN_URL', plugin_dir_url(__FILE__));
    }

    /**
     * Define constant if not already set.
     *
     * @param string $name  Constant name.
     * @param mixed  $value Constant value.
     */
    private function define($name, $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        // Initialize plugin
        add_action('init', array($this, 'init'), 0);
        
        // Register custom order status
        add_action('init', array($this, 'register_package_ready_status'));
        
        // Add custom status to WooCommerce list
        add_filter('wc_order_statuses', array($this, 'add_package_ready_status'));
        
        // Add custom email class
        add_filter('woocommerce_email_classes', array($this, 'add_package_ready_email_class'));
        
        // Send email when order status changes to package-ready
        add_action('woocommerce_order_status_changed', array($this, 'send_package_ready_email_notification'), 10, 3);

        // Add bulk action for orders
        add_filter('bulk_actions-edit-shop_order', array($this, 'register_bulk_actions'));
        
        // Handle the custom bulk action
        add_filter('handle_bulk_actions-edit-shop_order', array($this, 'handle_bulk_actions'), 10, 3);
        
        // Display admin notice after bulk action
        add_action('admin_notices', array($this, 'bulk_action_admin_notice'));

        // Add email to PDF Invoices & Packing Slips "Send order email" meta box select
        add_filter( 'wpo_wcpdf_resend_order_emails_available', function ( $emails ) {
            $emails[] = 'email_package_ready';
            return $emails;
        }, 10 );
    }

    /**
     * Initialize plugin.
     */
    public function init() {
        // Load plugin text domain
        $this->load_plugin_textdomain();
    }

    /**
     * Load plugin textdomain.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'woocommerce-package-ready',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    /**
     * Check plugin dependencies.
     */
    public function on_plugins_loaded() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }

        // Load text domain
        $this->load_plugin_textdomain();

        // Register email class BEFORE WooCommerce initializes emails
        add_filter('woocommerce_email_classes', array($this, 'add_package_ready_email_class'));


        // Initialize hooks
        $this->init_hooks();
    }

    /**
     * WooCommerce missing notice.
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="error">
            <p><?php _e('WooCommerce Package ready requires WooCommerce to be installed and active.', 'woocommerce-package-ready'); ?></p>
        </div>
        <?php
    }

    /**
     * Register the custom order status.
     */
    public function register_package_ready_status() {
        register_post_status($this->wc_status_slug, array(
            'label'                     => _x('Package ready for shipment', 'Order status', 'woocommerce-package-ready'),
            'public'                    => true,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list'    => true,
            'exclude_from_search'       => false,
            'label_count'               => _n_noop('Package ready for shipment <span class="count">(%s)</span>', 'Package ready for shipment <span class="count">(%s)</span>', 'woocommerce-package-ready'),
        ));
    }

    /**
     * Add custom status to WooCommerce list.
     *
     * @param array $order_statuses Order statuses.
     * @return array
     */
    public function add_package_ready_status($order_statuses) {
        $order_statuses[$this->wc_status_slug] = __('Package ready for shipment', 'woocommerce-package-ready');
        return $order_statuses;
    }

    /**
     * Add custom email class.
     *
     * @param array $email_classes Email classes.
     * @return array
     */
    public function add_package_ready_email_class($email_classes) {
        // Make sure the file exists before including it
        $email_class_path = WC_PACKAGE_READY_PLUGIN_DIR . 'class-wc-email-package-ready.php';
        if (file_exists($email_class_path)) {
            require_once($email_class_path);
            $email_classes['WC_Email_Package_Ready'] = new WC_Email_Package_Ready();
        }
        return $email_classes;
    }

    /**
     * Send email when order status changes to package-ready.
     *
     * @param int    $order_id Order ID.
     * @param string $old_status Old status.
     * @param string $new_status New status.
     */
    public function send_package_ready_email_notification($order_id, $old_status, $new_status) {
        // If we only got the order_id (for backward compatibility)
        if (empty($new_status)) {
            $order = wc_get_order($order_id);
            if (!$order) {
                return;
            }
            $new_status = $order->get_status();
        }
        
        // Check if the new status is our custom status (without wc- prefix)
        if ($new_status !== $this->status_slug) {
            return;
        }
        
        $order = wc_get_order($order_id);
        
        // Check if order exists
        if (!$order) {
            return;
        }
        
        // Get all WC emails
        $emails = WC()->mailer()->get_emails();
        
        // Loop through email classes
        foreach ($emails as $email) {
            if ($email->id === 'wc_email_package_ready') {
                // Send the email
                $email->trigger($order_id, $order);
                break;
            }
        }
    }

    /**
     * Add custom bulk action for orders
     * 
     * @param array $actions Bulk actions.
     * @return array
     */
    public function register_bulk_actions($actions) {
        $actions['mark_package_ready'] = __('Change status to Package ready', 'woocommerce-package-ready');
        return $actions;
    }

    /**
     * Handle the custom bulk action
     * 
     * @param string $redirect_to Redirect URL.
     * @param string $action      Bulk action.
     * @param array  $post_ids    Selected order IDs.
     * @return string
     */
    public function handle_bulk_actions($redirect_to, $action, $post_ids) {
        if ($action !== 'mark_package_ready') {
            return $redirect_to;
        }

        $changed = 0;
        
        foreach ($post_ids as $post_id) {
            $order = wc_get_order($post_id);
            if ($order) {
                $order->update_status($this->status_slug, __('Order status changed to Package ready via bulk action.', 'woocommerce-package-ready'));
                $changed++;
            }
        }

        return add_query_arg([
            'bulk_action' => 'marked_package_ready',
            'changed'     => $changed,
        ], $redirect_to);
    }

    /**
     * Display admin notice after bulk action
     */
    public function bulk_action_admin_notice() {
        // Check if we're displaying the bulk action notice and user has proper permissions
        if (empty($_REQUEST['bulk_action']) || $_REQUEST['bulk_action'] !== 'marked_package_ready') {
            return;
        }
        
        if (!current_user_can('edit_shop_orders')) {
            return;
        }

        $count = isset($_REQUEST['changed']) ? intval($_REQUEST['changed']) : 0;
        
        // Security: Escape the output
        echo '<div id="message" class="updated fade"><p>';
        echo esc_html(sprintf(
            _n('%s order status changed to "Package ready".', '%s order statuses changed to "Package ready".', $count, 'woocommerce-package-ready'),
            $count
        ));
        echo '</p></div>';
    }
}

/**
 * Returns the main instance of WC_Package_Ready.
 *
 * @return WC_Package_Ready
 */
function WC_Package_Ready() {
    return WC_Package_Ready::instance();
}

// Start the plugin
WC_Package_Ready();
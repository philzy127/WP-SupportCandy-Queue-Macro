<?php
/**
 * Plugin Name: SupportCandy Queues
 * Description: Adds a real-time queue count macro to SupportCandy emails with configurable non-closed statuses.
 * Version: 1.0
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SupportCandyQueues {

    private $table = 'wpya_psmsc_tickets'; // Your ticket table

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'wpsc_register_email_macros', array( $this, 'register_queue_macro' ) );
        add_filter( 'wpsc_replace_email_macros', array( $this, 'replace_queue_macro' ), 10, 3 );
        register_activation_hook( __FILE__, array( $this, 'set_default_options' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    public function set_default_options() {
        if ( get_option('scq_non_closed_statuses') === false ) {
            update_option('scq_non_closed_statuses', array(1,2) ); // default statuses
        }
        if ( get_option('scq_ticket_type_field') === false ) {
            update_option('scq_ticket_type_field', 'category');
        }
        if ( get_option('scq_debug') === false ) {
            update_option('scq_debug', 1);
        }
    }

    // Enqueue scripts for dual-list UI
    public function enqueue_scripts($hook) {
        if (strpos($hook,'scq_main') !== false) {
            wp_enqueue_script('scq-admin-js', plugin_dir_url(__FILE__).'admin.js', array('jquery'), null, true);
            wp_enqueue_style('scq-admin-css', plugin_dir_url(__FILE__).'admin.css');
        }
    }

    // Admin menu
    public function add_admin_menu() {
        add_menu_page(
            'SupportCandy Queues',
            'SupportCandy Queues',
            'manage_options',
            'scq_main',
            array( $this, 'settings_page' ),
            'dashicons-tickets',
            25
        );

        add_submenu_page(
            'scq_main',
            'How to Use',
            'How to Use',
            'manage_options',
            'scq_how_to',
            array( $this, 'how_to_page' )
        );
    }

    // Settings page
    public function settings_page() {
        global $wpdb;

        if ( isset($_POST['scq_save']) && check_admin_referer('scq_save_settings') ) {
            $statuses = isset($_POST['scq_non_closed_statuses']) ? array_map('intval', $_POST['scq_non_closed_statuses']) : array();
            update_option('scq_non_closed_statuses', $statuses);
            $type_field = sanitize_text_field($_POST['scq_ticket_type_field']);
            update_option('scq_ticket_type_field', $type_field);
            echo '<div class="updated"><p>Settings saved!</p></div>';
        }

        $selected_statuses = get_option('scq_non_closed_statuses', array());
        $type_field = get_option('scq_ticket_type_field','category');

        // Get all unique statuses in the ticket table
        $all_statuses = $wpdb->get_col("SELECT DISTINCT status FROM {$wpdb->prefix}{$this->table} ORDER BY status ASC");
        $available_statuses = array_diff($all_statuses, $selected_statuses);
        ?>
        <div class="wrap">
            <h1>SupportCandy Queues Settings</h1>
            <form method="post">
                <?php wp_nonce_field('scq_save_settings'); ?>

                <h2>Non-Closed Statuses</h2>
                <p>Select which ticket statuses should count toward the queue:</p>
                <div class="dual-list">
                    <select multiple id="available_statuses">
                        <?php foreach($available_statuses as $s) echo "<option value='{$s}'>$s</option>"; ?>
                    </select>
                    <div class="dual-buttons">
                        <button type="button" id="add_status">→ Add</button>
                        <button type="button" id="remove_status">← Remove</button>
                    </div>
                    <select multiple name="scq_non_closed_statuses[]" id="selected_statuses">
                        <?php foreach($selected_statuses as $s) echo "<option value='{$s}'>$s</option>"; ?>
                    </select>
                </div>

                <h2>Ticket Type Field</h2>
                <input type="text" name="scq_ticket_type_field" value="<?php echo esc_attr($type_field); ?>" />
                <p>The field name in the ticket table that represents ticket type (default: category).</p>

                <p><input type="submit" name="scq_save" class="button button-primary" value="Save Settings" /></p>
            </form>
        </div>
        <?php
    }

    public function how_to_page() {
        ?>
        <div class="wrap">
            <h1>SupportCandy Queues - How to Use</h1>
            <ol>
                <li>Go to the main settings page and select the ticket statuses that should count toward the queue.</li>
                <li>Optionally, adjust the ticket type field if it’s not <code>category</code>.</li>
                <li>Edit your SupportCandy email templates and insert <code>{{queue_count}}</code> where you want the count displayed.</li>
                <li>When a new ticket email is sent, the macro will automatically be replaced with the number of open tickets of the same type.</li>
            </ol>
            <p>If debug is enabled, debug messages will appear in <code>error_log</code> to verify correct operation.</p>
        </div>
        <?php
    }

    // Register macro
    public function register_queue_macro($macros) {
        $macros['queue_count'] = array(
            'label' => __('Queue Count', 'supportcandy'),
            'description' => __('Number of open tickets of the same type.', 'supportcandy')
        );
        return $macros;
    }

    // Replace macro
    public function replace_queue_macro($content, $ticket, $macro) {
        if ($macro === 'queue_count') {
            $content = $this->get_queue_count($ticket);
        }
        return $content;
    }

    // Get queue count
    private function get_queue_count($ticket) {
        global $wpdb;
        $type_field = get_option('scq_ticket_type_field','category');
        $statuses = get_option('scq_non_closed_statuses', array(1,2));

        if (!isset($ticket->$type_field)) {
            if (get_option('scq_debug',1)) error_log("[SCQ] Ticket type field '{$type_field}' missing");
            return 0;
        }

        $placeholders = implode(',', array_fill(0,count($statuses),'%d'));
        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}{$this->table} WHERE {$type_field} = %d AND status IN ($placeholders)",
            array_merge([ intval($ticket->$type_field) ], $statuses)
        );

        $count = (int) $wpdb->get_var($sql);

        if (get_option('scq_debug',1)) {
            error_log("[SCQ] Ticket type: {$ticket->$type_field}, Count: $count");
        }

        return $count;
    }
}

new SupportCandyQueues();

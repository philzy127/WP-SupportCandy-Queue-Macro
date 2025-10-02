<?php
/**
 * Plugin Name: SupportCandy Queues
 * Description: Adds a real-time queue count macro to SupportCandy emails with configurable non-closed statuses.
 * Version: 2.0
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SupportCandyQueues {

    private $table_name = 'psmsc_tickets';
    private $status_table_name = 'psmsc_statuses';
    private $custom_fields_table_name = 'wpya_psmsc_custom_fields';

    public function __construct() {
        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        register_activation_hook( __FILE__, array( $this, 'set_default_options' ) );
        register_uninstall_hook( __FILE__, array( 'SupportCandyQueues', 'uninstall' ) );

        // Correct hooks for adding and replacing macros
        add_filter( 'wpsc_macros', array( $this, 'register_macro' ) );
        add_filter( 'wpsc_replace_macros', array( $this, 'replace_macro' ), 10, 3 );
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'supportcandy-queues', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    public static function uninstall() {
        delete_option('scq_non_closed_statuses');
        delete_option('scq_ticket_type_field');
        delete_option('scq_debug');
    }

    public function set_default_options() {
        if ( get_option('scq_non_closed_statuses') === false ) {
            update_option('scq_non_closed_statuses', array(1, 2) );
        }
        if ( get_option('scq_ticket_type_field') === false ) {
            update_option('scq_ticket_type_field', 'category');
        }
        if ( get_option('scq_debug') === false ) {
            update_option('scq_debug', 1);
        }
    }

    public function enqueue_scripts($hook) {
        if (strpos($hook, 'scq_main') !== false) {
            wp_enqueue_script('scq-admin-js', plugin_dir_url(__FILE__) . 'admin.js', array('jquery'), null, true);
            wp_enqueue_style('scq-admin-css', plugin_dir_url(__FILE__) . 'admin.css');
        }
    }

    public function add_admin_menu() {
        add_menu_page(
            __('SupportCandy Queues', 'supportcandy-queues'),
            __('SupportCandy Queues', 'supportcandy-queues'),
            'manage_options',
            'scq_main',
            array($this, 'settings_page'),
            'dashicons-tickets',
            25
        );
    }

    public function settings_page() {
        global $wpdb;

        if (isset($_POST['scq_save']) && check_admin_referer('scq_save_settings')) {
            $statuses = isset($_POST['scq_non_closed_statuses']) ? array_map('intval', $_POST['scq_non_closed_statuses']) : array();
            update_option('scq_non_closed_statuses', $statuses);
            $type_field = sanitize_text_field($_POST['scq_ticket_type_field']);
            update_option('scq_ticket_type_field', $type_field);
            echo '<div class="updated"><p>' . esc_html__('Settings saved!', 'supportcandy-queues') . '</p></div>';
        }

        $selected_statuses = get_option('scq_non_closed_statuses', array());
        $type_field = get_option('scq_ticket_type_field', 'category');

        $status_table = $wpdb->prefix . $this->status_table_name;
        $all_statuses = $wpdb->get_results("SELECT id, name FROM {$status_table} ORDER BY name ASC");

        $available_statuses_map = array();
        $selected_statuses_map = array();

        if ($all_statuses) {
            foreach ($all_statuses as $status) {
                if (in_array($status->id, $selected_statuses)) {
                    $selected_statuses_map[$status->id] = $status->name;
                } else {
                    $available_statuses_map[$status->id] = $status->name;
                }
            }
        }
        ?>
        <div class="wrap">
            <h1><?php _e('SupportCandy Queues Settings', 'supportcandy-queues'); ?></h1>
            <form method="post">
                <?php wp_nonce_field('scq_save_settings'); ?>

                <h2><?php _e('Non-Closed Statuses', 'supportcandy-queues'); ?></h2>
                <p><?php _e('Select which ticket statuses should count toward the queue:', 'supportcandy-queues'); ?></p>
                <div class="dual-list-container">
                    <div class="dual-list-box">
                        <h3><?php _e('Available Statuses', 'supportcandy-queues'); ?></h3>
                        <select multiple id="available_statuses">
                            <?php foreach ($available_statuses_map as $id => $name) echo '<option value="' . esc_attr($id) . '">' . esc_html($name) . '</option>'; ?>
                        </select>
                    </div>
                    <div class="dual-buttons">
                        <button type="button" id="add_status"><?php _e('→ Add', 'supportcandy-queues'); ?></button>
                        <button type="button" id="remove_status"><?php _e('← Remove', 'supportcandy-queues'); ?></button>
                    </div>
                    <div class="dual-list-box">
                        <h3><?php _e('Selected Statuses', 'supportcandy-queues'); ?></h3>
                        <select multiple name="scq_non_closed_statuses[]" id="selected_statuses">
                            <?php foreach ($selected_statuses_map as $id => $name) echo '<option value="' . esc_attr($id) . '">' . esc_html($name) . '</option>'; ?>
                        </select>
                    </div>
                </div>

                <h2><?php _e('Ticket Type Field', 'supportcandy-queues'); ?></h2>
                <?php
                $custom_fields_table = $this->custom_fields_table_name;
                $custom_fields = $wpdb->get_results("SELECT name, slug FROM {$custom_fields_table} WHERE `field` = 'ticket'");
                $default_fields = array(
                    (object) array('slug' => 'category', 'name' => __('Category', 'supportcandy-queues')),
                    (object) array('slug' => 'priority', 'name' => __('Priority', 'supportcandy-queues')),
                    (object) array('slug' => 'status', 'name' => __('Status', 'supportcandy-queues')),
                );
                $all_type_fields = array_merge($default_fields, $custom_fields ? $custom_fields : array());
                usort($all_type_fields, function($a, $b) { return strcmp($a->name, $b->name); });
                ?>
                <select name="scq_ticket_type_field">
                    <?php foreach ($all_type_fields as $field) {
                        echo '<option value="' . esc_attr($field->slug) . '" ' . selected($type_field, $field->slug, false) . '>' . esc_html($field->name) . '</option>';
                    } ?>
                </select>
                <p><?php _e('The field in the ticket table that represents ticket type.', 'supportcandy-queues'); ?></p>

                <p><input type="submit" name="scq_save" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'supportcandy-queues'); ?>" /></p>
            </form>
        </div>
        <?php
    }

    /**
     * Add custom macros to the list.
     */
    public function register_macro($macros) {
        $macros[] = array(
            'tag'   => '{{queue_count}}',
            'title' => esc_attr__('Queue Count', 'supportcandy-queues'),
        );
        return $macros;
    }

    /**
     * Replace custom macro tags with their values.
     */
    public function replace_macro($str, $ticket, $macro) {
        if ($macro === 'queue_count') {
            global $wpdb;

            $type_field = get_option('scq_ticket_type_field', 'category');
            $statuses = get_option('scq_non_closed_statuses', array());

            if (empty($type_field) || empty($statuses) || !isset($ticket->$type_field)) {
                return str_replace('{{queue_count}}', '0', $str);
            }

            // Whitelist the type field to prevent SQL injection
            $custom_fields_table = $this->custom_fields_table_name;
            $custom_field_slugs = $wpdb->get_col("SELECT slug FROM {$custom_fields_table} WHERE `field` = 'ticket'");
            $default_fields = array('category', 'priority', 'status');
            $allowed_fields = array_merge($default_fields, $custom_field_slugs ? $custom_field_slugs : array());

            if (!in_array($type_field, $allowed_fields, true)) {
                return str_replace('{{queue_count}}', '0', $str);
            }

            $table = $wpdb->prefix . $this->table_name;
            $type_value = $ticket->$type_field;
            $placeholders = implode(',', array_fill(0, count($statuses), '%d'));

            $sql = $wpdb->prepare(
                "SELECT COUNT(*) FROM `{$table}` WHERE `{$type_field}` = %s AND `status` IN ($placeholders)",
                array_merge(array($type_value), $statuses)
            );
            $count = $wpdb->get_var($sql);

            $str = str_replace('{{queue_count}}', $count, $str);
        }
        return $str;
    }
}

new SupportCandyQueues();
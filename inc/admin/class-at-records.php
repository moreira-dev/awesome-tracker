<?php

defined('ABSPATH') or die();

if (!class_exists('AwesomeTrackerPageRecords')):
    class AwesomeTrackerPageRecords {

        /**
         * Page hook.
         *
         * @var string
         */
        protected static $page_hook;

        public static function init_menu() {

            self::$page_hook = add_submenu_page('awesome-tracker', __('Tracked Records', 'awesome-tracker-td'), __('Tracked Records', 'awesome-tracker-td'), 'manage_options', 'awesome-tracker', 'AwesomeTrackerPageRecords::generate');
            add_action('load-' . self::$page_hook, 'AwesomeTrackerPageRecords::init');
        }

        public static function init() {

            $option = 'per_page';
            $args = array(
                'label' => __('Records per page', 'awesome-tracker-td'),
                'default' => 10,
                'option' => 'edit_per_page'
            );
            add_screen_option($option, $args);

            self::enqueue();

            if (isset($_REQUEST['at_csv_export']) && !empty($_REQUEST['at_csv_export'])) {
                AwesomeTrackerExport::csv_pageRecords_table();
            }
        }

        private static function enqueue() {

            wp_enqueue_script('chosen-js', AwesomeTracker::$plugin_url . '/js/external/chosen/chosen.jquery.min.js');

            wp_enqueue_style('chosen-css', AwesomeTracker::$plugin_url . '/js/external/chosen/chosen.min.css');

            wp_enqueue_script('at-admin-js', AwesomeTracker::$plugin_url . '/js/admin.js', array('chosen-js'));

            $translation_array = array(
                'all_users' => __('All the users', 'awesome-tracker-td'),
                'no_users' => __('There are no users', 'awesome-tracker-td')
            );
            wp_localize_script('at-admin-js', 'ati18n', $translation_array);

        }

        public static function generate() {

            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'view' && !empty($_REQUEST['record']) && is_numeric($_REQUEST['record']))
                self::render_record_view($_REQUEST['record']);
            else {
                self::render_list();
            }
        }

        /**
         * WP Table with tracked records
         */
        public static function render_list() {

            $_SERVER['REQUEST_URI'] = remove_query_arg('_wp_http_referer', $_SERVER['REQUEST_URI']);
            $tableLog = new AwesomeTrackerLogTable();
            $tableLog->prepare_items();
            ?>
            <form name="records_form" id="records_form" method="get">
                <input type="hidden" name="page" value="awesome-tracker">
                <?php if (isset($_REQUEST['filtering'])) { ?>
                    <input type="hidden" name="filtering" value="<?php echo esc_attr($_REQUEST['filtering']); ?>">
                <?php } ?>
                <div class="wrap">
                    <h2><?php _e('Records', 'awesome-tracker-td'); ?></h2>
                    <div class="alignleft actions">
                        <?php $tableLog->views(); ?>
                    </div>
                    <div class="alignleft actions">
                        <?php do_action('restrict_manage_posts'); ?>
                    </div>
                    <?php

                    $tableLog->search_box(__('Search', 'awesome-tracker-td'), 'at-search-records');
                    $tableLog->display();
                    ?>
                </div>
            </form>
            <?php

        }

        private static function render_field($key, $field) {

            switch ($field['type']) {
                case 'input':
                    $htmlField = "<input type=\"text\" name=\"{$key}\" id=\"{$key}\" readonly
                                                               value=\"{$field['value']}\"
                                                               class=\"regular-text ltr readonly\">";
                    break;
                case 'link':
                    $htmlField = "<a href=\"{$field['href']}\"
                                                           target=\"_blank\">{$field['value']}</a>";
                    break;
                case 'textarea':
                    $htmlField = "<textarea name=\"{$key}\" class=\"regular-text ltr readonly\"
                                            id=\"{$key}\" readonly>{$field['value']}</textarea>";
                    break;
                default:
                    $htmlField = '';
            }

            echo "<div class=\"at-wrap at-{$key}-wrap\">";
                echo "<table>";
                    echo "<tr>";
                        echo "<th><label for=\"{$key}\">{$field['label']}</label></th>";
                        echo "<td>{$htmlField}</td>";
                    echo "</tr>";
                echo "</table>";
            echo "</div>";
        }

        private static function get_visit_fields($record) {

            $visitFields = array(
                'page_type' => array(
                    'label' => __('Type of page', 'awesome-tracker-td'),
                    'type' => 'input',
                    'value' => $record->label
                ),
                'details' => array(
                    'label' => __('Details', 'awesome-tracker-td'),
                    'type' => 'textarea',
                    'value' => $record->description
                ),
                'ip' => array(
                    'label' => __('IP', 'awesome-tracker-td'),
                    'type' => 'input',
                    'value' => $record->ip
                ),
                'country' => array(
                    'label' => __('Country', 'awesome-tracker-td'),
                    'type' => 'input',
                    'value' => $record->country_name
                )
            );

            if ($record->post_id) {
                $visitFields = array(
                    'to_post' => array(
                        'label' => __('View complete Post', 'awesome-tracker-td'),
                        'type' => 'link',
                        'href' => get_edit_post_link($record->post_id),
                        'value' => __('Go to Post / Page', 'awesome-tracker-td')
                    ),
                ) + $visitFields;
            }

            if ($record->taxonomy) {
                $visitFields['taxonomy'] = array(
                    'label' => __('Taxonomy', 'awesome-tracker-td'),
                    'type' => 'input',
                    'value' => $record->taxonomy
                );
            }

            $visitFields['visit_time'] = array(
                'label' => __('Time of the visit', 'awesome-tracker-td'),
                'type' => 'input',
                'value' => $record->visited_formatted
            );

            return $visitFields;
        }

        private static function get_user_fields($user) {

            $userFields = array();

            if (!$user instanceof WP_User)
                return $userFields;


            $fullName = trim($user->first_name . ' ' . $user->last_name);
            if (empty($fullName))
                $fullName = $user->display_name;

            $userFields = array(
                'toficha' => array(
                    'label' => __('View complete profile', 'awesome-tracker-td'),
                    'type' => 'link',
                    'href' => get_edit_user_link($user->ID),
                    'value' => __('Go to user profile', 'awesome-tracker-td')
                ),
                'fullname' => array(
                    'label' => __('Full Name', 'awesome-tracker-td'),
                    'type' => 'input',
                    'value' => $fullName
                ),
                'login' => array(
                    'label' => __('Login', 'awesome-tracker-td'),
                    'type' => 'input',
                    'value' => $user->user_login
                ),
                'email' => array(
                    'label' => __('Email', 'awesome-tracker-td'),
                    'type' => 'input',
                    'value' => $user->user_email
                ),
                'registered' => array(
                    'label' => __('Date Registered', 'awesome-tracker-td'),
                    'type' => 'input',
                    'value' => date(get_option('date_format') . ' '
                                    . get_option('time_format'),
                                    strtotime($user->user_registered, current_time('timestamp')))
                )
            );

            return $userFields;

        }

        public static function render_record_view($id) {

            $_SERVER['REQUEST_URI'] = remove_query_arg('_wp_http_referer', $_SERVER['REQUEST_URI']);
            if (!is_numeric($id))
                return;

            $record = new AwesomeTracker_Record($id);

            $user = false;
            if (is_numeric($record->user_id) && !empty($record->user_id)) {
                $user = get_user_by('id', $record->user_id);
                if (is_wp_error($user) || !$user)
                    $user = false;
            }

            $ipToShow = $record->ip;

            if (AwesomeTrackerHelper::is_valid_ip($ipToShow)) {
                $ipToShow = "<a href=\"https://ipalyzer.com/{$ipToShow}\" target=\"_blank\">{$ipToShow}</a>";
            }

            $userFields = self::get_user_fields($user);
            $visitFields = self::get_visit_fields($record);

            $title = sprintf(
                __('Visit from %s at %s', 'awesome-tracker-td'),
                $ipToShow, $record->visited_formatted
            );

            ?>
            <div class="wrap"><h2><?php echo $title ?></h2>
                <form name="record_form" id="record_form" method="get">
                    <input type="hidden" name="page" value="awesome-tracker">
                    <input type="hidden" name="action" value="view">

                    <div id="visit-data" class="form-table">
                        <?php if ($user) { ?>
                            <div class="col">
                                <div class="at-info">
                                    <h2><?php _e('User information', 'awesome-tracker-td'); ?></h2>
                                    <div class="at-body">
                                        <?php

                                        foreach ($userFields as $key => $field)
                                            self::render_field($key, $field);

                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col">
                            <div class="at-info">
                                <h2><?php _e('Visit information', 'awesome-tracker-td'); ?></h2>
                                <div class="at-body">
                                    <?php

                                    foreach ($visitFields as $key => $field)
                                        self::render_field($key, $field);

                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <?php
        }

    }
endif;

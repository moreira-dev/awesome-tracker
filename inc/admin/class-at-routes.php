<?php

defined('ABSPATH') or die();

if (!class_exists('AwesomeTrackerPageRoutes')):
    class AwesomeTrackerPageRoutes {

        /**
         * Page hook.
         *
         * @var string
         */
        protected static $page_hook;


        public static function init_menu() {

            self::$page_hook = add_submenu_page('awesome-tracker', __('Awesome Tracker API Routes', 'awesome-tracker-td'), __('API Routes', 'awesome-tracker-td'), 'manage_options', 'at-routes', 'AwesomeTrackerPageRoutes::render');

            add_action('load-' . self::$page_hook, 'AwesomeTrackerPageRoutes::init');

        }

        public static function render() {

            ?>
            <div id="at-routes"></div>
            <?php

        }

        public static function init() {

            self::add_js_globals_react();

            wp_enqueue_script('awesome_tracker-block-js');

            wp_enqueue_style('awesome_tracker-block-editor-css');

            /**
             * Styles for WordPress core Block components
             */
            wp_enqueue_style('wp-list-reusable-blocks');
        }

        public static function add_js_globals_react() {

            wp_localize_script(
                'awesome_tracker-block-js',
                'atRoutesGlobal', // Array containing dynamic data for a JS Global.
                array(
                    'apiRoutes' => AwesomeTrackerApi::get_api_routelist(),
                    'currentRoutes' => AwesomeTracker_Route::get_current_routes(),
                )
            );
        }

    }
endif;

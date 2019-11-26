<?php

defined('ABSPATH') or die();

if(!class_exists('AwesomeTrackerPageSettings')):
    class AwesomeTrackerPageSettings {

        /**
         * @var string
         */
        protected static $page_hook;

        const PAGE_SLUG = 'awesometracker-settings';

        public static function init_menu() {

            self::$page_hook = add_submenu_page(
                'awesome-tracker',
                __('Awesome Tracker Settings',
                   AwesomeTracker::TEXT_DOMAIN),
                __('Settings', AwesomeTracker::TEXT_DOMAIN),
                'manage_options',
                self::PAGE_SLUG,
                'AwesomeTrackerPageSettings::render'
            );

            add_action('load-' . self::$page_hook, 'AwesomeTrackerPageSettings::init');

        }

        public static function render() {

            ?>
            <div id="at-settings">
            </div>
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
                'atSettingsGlobal',
                array(
                    'fields' => array(
                        'recordsDB' => get_option('at_settings_recordsDB', 0)
                    )
                )
            );
        }

    }
endif;

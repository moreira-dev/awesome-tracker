<?php
defined( 'ABSPATH' ) or die();

if( !class_exists ('AwesomeTrackerPageMain') ):
    class AwesomeTrackerPageMain{

        /**
         * Page hook.
         * @var string
         */
        protected static $page_hook;


        public static function init_menu(){

            self::$page_hook = add_menu_page( __('Awesome Tracker','awesome-tracker-td'), __('Awesome Tracker','awesome-tracker-td'), 'manage_options', 'awesome-tracker', '','dashicons-welcome-view-site',67 );
            add_action('load-'.self::$page_hook, 'AwesomeTrackerPageMain::load');

            self::init_react();
            self::enqueue_styles();
        }


        public static function load(){
            $option = 'per_page';
            $args = array(
                'label' => __('Records per page','awesome-tracker-td'),
                'default' => 10,
                'option' => 'edit_per_page'
            );
            add_screen_option( $option, $args );
        }

        private static function enqueue_styles(){
            wp_enqueue_style('at-admin-css', AwesomeTracker::$plugin_url . '/css/admin.css', array(), AWESOME_TRACKER_VERSION);
        }

        private static function init_react(){
            // Register block editor script for backend.
            wp_register_script(
                'awesome_tracker-block-js', // Handle.
                AwesomeTracker::$plugin_url . '/js/dist/blocks.build.js', // Block.build.js: We register the block here. Built with Webpack.
                array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-api-fetch', 'wp-components' ), // Dependencies, defined above.
                AWESOME_TRACKER_VERSION,
                true // Enqueue the script in the footer.
            );

            // Register block editor styles for backend.
            wp_register_style(
                'awesome_tracker-block-editor-css', // Handle.
                AwesomeTracker::$plugin_url .'/js/dist/blocks.editor.build.css', // Block editor CSS.
                array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
                AWESOME_TRACKER_VERSION
            );

            wp_localize_script(
                'awesome_tracker-block-js',
                'atGlobal', // Array containing dynamic data for a JS Global.
                array(
                    'textDomain' => 'awesome-tracker-td',
                    'nameSpace' => AwesomeTrackerApi::NAME_SPACE
                )
            );
            wp_localize_script(
                'awesome_tracker-block-js',
                'atRoutesGlobal',
                array()
            );

            wp_localize_script(
                'awesome_tracker-block-js',
                'atSettingsGlobal',
                array()
            );

            wp_set_script_translations('awesome_tracker-block-js','awesome-tracker-td', AwesomeTracker::$plugin_dir . 'languages' );
        }


    }
endif;

<?php

namespace TheStart\Startle;

if (! function_exists('add_action')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

define('STARTLE_VERSION', '1.0.1');

if (! class_exists('Startle')) {
    final class Startle
    {
        /**
         * Admin interfaces and settings
         *
         * @var admin
         * @since 1.0
         */
        public $admin;

        /**
         * @var Startle The one true Startle
         * @since 1.0
         */
        private static $instance;

        /**
         * @var error_levels Define PHP error levels available for reporting
         * @since 1.0
         */
        public $error_levels = array(
            E_ERROR,
            E_WARNING,
            E_PARSE,
            E_NOTICE,
            E_USER_ERROR,
            E_USER_WARNING,
            E_DEPRECATED,
        );

        /**
         * Main Startle Instance
         *
         * Insures that only one instance of Startle exists in memory at any one
         * time. Also prevents needing to define globals all over the place.
         *
         * @since 1.0
         * @static
         * @return The one true Startle
         */
        public static function instance()
        {
            if (! isset(self::$instance) && ! (self::$instance instanceof Startle)) {
                self::$instance = new Startle();
                self::$instance->admin = new StartleAdmin();
            }

            return self::$instance;
        }

        /**
         * Map error code to error string
         *
         * @return string Error type
         */
        public function mapErrorCodeToType($code)
        {
            switch ($code) {
                case E_ERROR: // 1 //
                    return 'E_ERROR';
                case E_WARNING: // 2 //
                    return 'E_WARNING';
                case E_PARSE: // 4 //
                    return 'E_PARSE';
                case E_NOTICE: // 8 //
                    return 'E_NOTICE';
                case E_CORE_ERROR: // 16 //
                    return 'E_CORE_ERROR';
                case E_CORE_WARNING: // 32 //
                    return 'E_CORE_WARNING';
                case E_COMPILE_ERROR: // 64 //
                    return 'E_COMPILE_ERROR';
                case E_COMPILE_WARNING: // 128 //
                    return 'E_COMPILE_WARNING';
                case E_USER_ERROR: // 256 //
                    return 'E_USER_ERROR';
                case E_USER_WARNING: // 512 //
                    return 'E_USER_WARNING';
                case E_USER_NOTICE: // 1024 //
                    return 'E_USER_NOTICE';
                case E_RECOVERABLE_ERROR: // 4096 //
                    return 'E_RECOVERABLE_ERROR';
                case E_DEPRECATED: // 8192 //
                    return 'E_DEPRECATED';
                case E_USER_DEPRECATED: // 16384 //
                    return 'E_USER_DEPRECATED';
            }
        }

        /**
         * Throw error on object clone
         *
         * The whole idea of the singleton design pattern is that there is a single
         * object therefore, we don't want the object to be cloned.
         *
         * @access protected
         * @return void
         */
        public function __clone()
        {
            // Cloning instances of the class is forbidden
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'fatal-error-notify'), '1.0');
        }

        /**
         * Disable unserializing of the class
         *
         * @access protected
         * @return void
         */
        public function __wakeup()
        {
            // Unserializing instances of the class is forbidden
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'fatal-error-notify'), '1.0');
        }
    }
}

/**
 * The main function responsible for returning the one true Startle
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 */
if (! function_exists('Startle')) {
    function Startle()
    {
        return Startle::instance();
    }

    Startle();
}

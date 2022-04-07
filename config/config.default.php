<?php

return array(
    /**
     * Default Parameters
     *
     * These parameters are required for your JMAP server to operate.
     * Do not edit this file. Copy + paste it to config/config.php and then edit instead.
     */

    // ********************** //
    /// General configuration
    // ********************** //

    // Admin users for Webclient admin auth. Users should exist on the webclient.
    'adminUsers' => array('yourchosenadminuser'),

    // Enabled capabilities for this endpoint
    'capabilities' => array('calendars', 'contacts', 'mail', 'tasks', 'notes', 'sieve'),

    // ********************** //
    /// Logging configuration
    // ********************** //
    // NOTE: Only a single logger will be used

    // Allow FileLogger (also as fallback in case no other is working)
    'allowFileLog' => false,

    // PSR 3 minimum log level
    'logLevel' => \Psr\Log\LogLevel::WARNING,

    // FileLogger's path to log file relative to this dir
    'fileLogPath' => __DIR__ . '/../log.log',
);

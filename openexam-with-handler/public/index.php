<?php

//BISMILLAH

error_reporting(E_ALL);

try {

        /**
         * Read the configuration
         */
        $config = include dirname(__DIR__) . '/app/config/config.php';

        /**
         * Read auto-loader
         */
        include APP_DIR . "/config/loader.php";

        /**
         * Read services
         */
        include APP_DIR . "/config/services.php";

        $application = new \Phalcon\Mvc\Application();
        $application->setDI($di);
        echo $application->handle()->getContent();
} catch (\Exception $e) {
        echo $e->getMessage();
}
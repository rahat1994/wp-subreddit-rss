<?php

/**
 * Plugin Name: WPRB Subreddit RSS
 * Plugin URI: github.com/rahat1994/wp-subreddit-rss
 * Description: A simple plugin to show subreddit rss feeds
 * Author: Rahat Baksh
 * Text Domain: wprb-subreddit-rss
 */



defined('ABSPATH') or die;

define('WPRBSUBREDDITRSS_URL', plugin_dir_url(__FILE__));
define('WPRBSUBREDDITRSS_DIR', plugin_dir_path(__FILE__));

require WPRBSUBREDDITRSS_DIR . '/vendor/autoload.php';

define('WPRBSUBREDDITRSS_VERSION', '1.0.5');

// This will automatically update, when you run dev or production
define('WPRBSUBREDDITRSS_DEVELOPMENT', true);

class WPRBSUBREDDITRSS
{

    public function boot()
    {


        // $this->registerClasses();
        $this->registerActivationHook();
        $this->registerDeactivationHook();
        $this->renderAdminMenu();
        $this->registerRedditFeedCPT();
    }

    public function registerClasses()
    {
        require WPRBSUBREDDITRSS_DIR . 'includes/autoload.php';
    }

    public function registerActivationHook()
    {
        register_activation_hook(__FILE__, function ($newWorkWide) {
            $activator = new \WPRBSubRedditRSS\Classes\PluginActivator();
            $activator->migrateDatabases($newWorkWide);
        });
    }

    public function registerRedditFeedCPT()
    {
        require_once(WPRBSUBREDDITRSS_DIR . 'includes/Hooks/FeedCPT.php');
    }

    public function registerDeactivationHook() {}

    public function renderAdminMenu() {}
}

(new WPRBSUBREDDITRSS())->boot();

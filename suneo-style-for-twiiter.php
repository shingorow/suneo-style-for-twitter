<?php
/*
  Plugin Name: SuneoStyleForTwitter
  Plugin URI:
  Description: Gianismプラグインのアドオンとして動作するTwitter用プラグイン
  Version: 1.0.0
  Author: Shingo Matsui
  Author URI: https://github.com/shingorow
  License: GPLv2
 */

/*
    Copyright 2020 Shingo Matsui (email : s.matsui@engrowth.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
require_once __DIR__ . "/vendor/autoload.php";

add_action('init', 'SuneoStyleForTwitter::init');

class SuneoStyleForTwitter
{
    const VERSION           = '1.0.0';
    const PLUGIN_ID         = 'suneo-style-for-twitter';
    const CREDENTIAL_ACTION = self::PLUGIN_ID . '-nonce-action';
    const CREDENTIAL_NAME   = self::PLUGIN_ID . '-nonce-key';
    const PLUGIN_DB_PREFIX  = self::PLUGIN_ID . '_';
    const SETTING_MENU_SLUG  = self::PLUGIN_ID . '-setting';
    const KEYS = [
        'protected',
        'screen_name',
        'twitter_name',
        'description',
        'redirect_logged_in',
        'redirect_kicked_out',
    ];

    public static function init()
    {
        return new self();
    }

    function __construct()
    {
        $subscriber = get_role('subscriber');
        $subscriber->add_cap('read_private_posts');

        if (is_admin() && is_user_logged_in()) {
            add_action('admin_menu', [$this, 'set_plugin_menu']);
            add_action('admin_menu', [$this, 'set_plugin_sub_menu']);
            add_action('admin_init', [$this, 'save_settings']);
            wp_enqueue_style('suneo-style', plugins_url(basename(__DIR__) . '/assets/css/app.css'));
        }
        if (!current_user_can('edit_posts') && is_user_logged_in()) {
            add_action('wp_loaded', [$this, 'after_logged_in']);
            add_action('wp_logout', [$this, 'redirect_logout']);
        }

        add_shortcode('twitter_login_button', [$this, 'twitter_login_shortcode']);
    }

    function set_plugin_menu()
    {
        add_menu_page(
            'Suneo Style For Twitter',
            'Suneo Style TW',
            'manage_options',
            'suneo-style-for-twitter',
            [$this, 'show_about_plugin'],
            'dashicons-twitter-alt',
            99
        );
    }


    function set_plugin_sub_menu()
    {

        add_submenu_page(
            'suneo-style-for-twitter',
            '設定',
            '設定',
            'manage_options',
            'suneo-style-for-twitter-settings',
            [$this, 'show_setting_form']
        );
    }

    function show_about_plugin()
    {
        $page = file_get_contents(__DIR__ . '/templates/suneo-style-for-twitter.php');
        echo $page;
    }

    function show_setting_form()
    {
        include __DIR__ . '/app/sushi-templa.php';

        $templa = new SushiTempla();

        foreach (self::KEYS as $key) {
            $templa->$key = get_option(self::PLUGIN_DB_PREFIX . $key, '');
        }

        $templa->action = self::CREDENTIAL_ACTION;
        $templa->name = self::CREDENTIAL_NAME;
        $templa->show(__DIR__ . '/templates/suneo-style-settings.php');
    }

    function after_logged_in()
    {
        $user = wp_get_current_user();
        $user_id = $user->ID;
        $_wpg_twitter_screen_name = get_the_author_meta('_wpg_twitter_screen_name', $user_id);

        // Twitterのとき
        if ($_wpg_twitter_screen_name) {
            if (class_exists('Twitter_Controller')) {
                $wp_gianism_option = get_option('wp_gianism_option');
                $Twitter_Controller = new Twitter_Controller(array(
                    "tw_screen_name" => $user_id,
                    "tw_consumer_key" => $wp_gianism_option['tw_consumer_key'],
                    "tw_consumer_secret" => $wp_gianism_option['tw_consumer_secret'],
                    "tw_access_token" => $wp_gianism_option['tw_access_token'],
                    "tw_access_token_secret" => $wp_gianism_option['tw_access_token_secret'],
                ));

                $t = $Twitter_Controller->request('users/show', array(
                    'screen_name' => $_wpg_twitter_screen_name
                ));
            } else {
                $twitter = \Gianism\Service\Twitter::get_instance();
                $t = $twitter->call_api('users/show', array(
                    'screen_name' => $_wpg_twitter_screen_name
                ));
            }
        }

        foreach (self::KEYS as $key) {
            $$key = get_option(self::PLUGIN_DB_PREFIX . $key, '');
        }

        $flag = true;

        $flag = $flag && [
            'ignore' => true,
            'open' => !$t->protected,
            'protected' => $t->protected,
        ][$protected];

        foreach (explode("\n", $twitter_name) as $word) {
            $pattern = '/' . $word . '/';
            $flag = $flag && preg_match($pattern, $t->name);
        }

        foreach (explode("\n", $screen_name) as $word) {
            $pattern = '/' . $word . '/';
            $flag = $flag && preg_match($pattern, $t->screen_name);
        }

        foreach (explode("\n", $description) as $word) {
            $pattern = '/' . $word . '/';
            $flag = $flag && preg_match($pattern, $t->description);
        }

        if (!$flag) {
            wp_logout();
        }
    }

    function save_settings()
    {
        if (isset($_POST[self::CREDENTIAL_NAME]) && $_POST[self::CREDENTIAL_NAME]) {
            if (check_admin_referer(self::CREDENTIAL_ACTION, self::CREDENTIAL_NAME)) {
                foreach (self::KEYS as $key) {
                    update_option(self::PLUGIN_DB_PREFIX . $key, $_POST[$key]);
                }

                wp_safe_redirect(menu_page_url(self::SETTING_MENU_SLUG), false);
            }
        }
    }

    function redirect_logout()
    {
        $url = get_option(self::PLUGIN_DB_PREFIX . 'redirect_kicked_out');
        wp_safe_redirect($url);
        exit();
    }

    function twitter_login_shortcode()
    {
        if (function_exists('gianism_login')) {
            gianism_login('<div>', '</div>');
        }
    }
}

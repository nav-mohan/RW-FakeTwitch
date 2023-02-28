<?php
/**
 * Plugin Name: RW-FakeTwitch
 * Plugin URI: https://navmohan.site
 * Author: Navaneeth Mohan
 * Author URI: https://navmohan.site
 * Description: A plugin that can be used to start/stop/restart the fake_twitch Daemon service and also creates the frontend page for 'Fake Twitch'
 * Version: 0.1.0
 * License: GPL2
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: prefix-plugin-name
*/

// create what_song_was_that page 
add_action("init",'rw_faketwitch_plugin_custom_page_creator');
function rw_faketwitch_plugin_custom_page_creator(){
    $faketwitch_page_title = "RW-FakeTwitch";
    $faketwitch_page_slug = "fake_twitch";
    if(get_page_by_title($faketwitch_page_title) == NULL && get_page_by_path($faketwitch_page_slug)==NULL){
        $faketwitch_page_args = array(
            "post_title" => $faketwitch_page_title,
            "post_content" => "",
            "post_status" => "publish",  
            "post_type" => "page",
            "post_name" => $faketwitch_page_slug // this is teh slug
        );   
        $create_faketwitch_page = wp_insert_post($faketwitch_page_args);
    }
}

// creating the menu entries
add_action('admin_menu', 'rw_faketwitch_plugin_create_menu_entry');
function rw_faketwitch_plugin_create_menu_entry()
{
    // icon image path that will appear in the menu
    $icon = plugins_url('/images/rw-faketwitch-plugin-icon-16X16.png', __FILE__);

    // adding the main menu entry
    add_menu_page(
        'RW-FakeTwitch Plugin',
        'RW-FakeTwitch',
        'manage_options',
        'main-page-rw-faketwitch-plugin',
        'rw_faketwitch_plugin_show_main_page',
        $icon
    );
}

function rw_faketwitch_plugin_show_main_page()
{
    require_once('templates/dashboard.php');
}

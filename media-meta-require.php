<?php
/*
Plugin Name: Media Meta Require
Plugin URI: http://github.com/rpkoller/media-meta-constraints
Description: Choose which Media Meta elements are required to post a media object; otherwise leave it in draft status.
Version: 0.1
Author: Ralf Koller
Text Domain: mmrequest
Domain Path: /lang
License: GPLv2
*/

if( ! defined( 'ABSPATH' ) ) exit;

function mmr_install() {
    global $wp_version;
    if ( version_compare( $wp_version, '4.0', '<' ) ) {
        wp_die( __('This plugin requires WordPress version 4.0 or later', 'mmrequest' ) );
    }
}
register_activation_hook( __FILE__, 'mmr_install' );

function mmr_textdomain_init() {
  load_plugin_textdomain( 'mmrequest', false, plugin_basename( dirname( __FILE__ ) . '/lang' ) );
}
add_action( 'init', 'mmr_textdomain_init' );

function initialize_media_meta_require_options() {
    register_setting( 'media', 'mmr_options' );

    //add_media_page(
    add_settings_section(
        'media_settings_section',
        __( 'Which media fields are required for publishing', 'mmrequest'),
        'initialize_media_meta_require_options_callback',
        'media'
    );

    add_settings_field(
        'mmr_options_title',
        __( 'Title', 'mmrequest' ),
        'mmr_toggle_title_callback',
        'media',
        'media_settings_section'
    );

     add_settings_field(
        'mmr_options_caption',
        __( 'Caption', 'mmrequest' ),
        'mmr_toggle_caption_callback',
        'media',
        'media_settings_section'
    );

    add_settings_field(
        'mmr_options_alt',
        __( 'Alt Text', 'mmrequest' ),
        'mmr_toggle_alt_callback',
        'media',
        'media_settings_section'
    );

    add_settings_field(
        'mmr_options_desc',
        __( 'Description', 'mmrequest' ),
        'mmr_toggle_desc_callback',
        'media',
        'media_settings_section'
    );
}
add_action('admin_init', 'initialize_media_meta_require_options');

function initialize_media_meta_require_options_callback() {
    $html = '<p>' . _e( 'Please choose the descriptive media fields you would like to require to be filled out. Otherwise the user is prohibited to publish the media object.', 'mmrequest' ) . '</p>';
    echo $html;
}

function mmr_toggle_title_callback($args) {

    //$moo = get_option('mmr_options[title]' );
    $html = '<input type="checkbox" id="mmr_options_title" name="mmr_options[title]" value="1" ' . checked( 1, get_option('mmr_options[title]' ), false) . '/>';
    echo $html;
}

function mmr_toggle_caption_callback($args) {
    $html = '<input type="checkbox" id="mmr_options_caption" name="mmr_options[caption]" value="1" ' . checked(1, get_option('mmr_options[caption]'), false) . '/>';
    echo $html;
}

function mmr_toggle_alt_callback($args) {
    $html = '<input type="checkbox" id="mmr_options_alt" name="mmr_options[alt]" value="1" ' . checked(1, get_option('mmr_options[alt]'), false) . '/>';
    echo $html;
}

function mmr_toggle_desc_callback($args) {
    $html = '<input type="checkbox" id="mmr_options_desc" name="mmr_options[desc]" value="1" ' . checked(1, get_option('mmr_options[desc]'), false) . '/>';
    echo $html;
}
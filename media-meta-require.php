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

/*  Copyright 2015  Ralf Koller  (email : r.koller@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//WordPress version check - WP 4.0 is minimum requirement
function mmr_install() {
    global $wp_version;
    if ( version_compare( $wp_version, '4.0', '<' ) ) {
        wp_die( __('This plugin requires WordPress version 4.0 or higher', 'mmrequest' ) );
    }
}
register_activation_hook( __FILE__, 'mmr_install' );



//Set up and register a section and fields inside the media options page
function initialize_media_meta_require_options() {
     add_settings_section(
        'media_settings_section',
        __( 'Which media fields are required for publishing', 'mmrequest'),
        'initialize_media_meta_require_options_callback',
        'media'
    );

    add_settings_field( 
        'mmr_require_title',
        __( 'Title', 'mmrequest' ),
        'mmr_toggle_title_callback',
        'media',
        'media_settings_section'
    );
    
     add_settings_field( 
        'mmr_require_caption',
        __( 'Caption', 'mmrequest' ),
        'mmr_toggle_caption_callback',
        'media',
        'media_settings_section'
    );

    add_settings_field( 
        'mmr_require_alt',                     
        __( 'Alt Text', 'mmrequest' ),              
        'mmr_toggle_alt_callback',  
        'media',                          
        'media_settings_section'
    );
     
    add_settings_field( 
        'mmr_require_desc',                      
        __( 'Description', 'mmrequest' ),               
        'mmr_toggle_desc_callback',   
        'media',                          
        'media_settings_section'
    );

    register_setting(
        'media',
        'mmr_require_title'
    );
     
    register_setting(
        'media',
        'mmr_require_caption'
    );
     
    register_setting(
        'media',
        'mmr_require_alt'
    );

    register_setting(
        'media',
        'mmr_require_desc'
    );

    // settings mit get_option('ID') abrufbar
}
add_action('admin_init', 'initialize_media_meta_require_options');

//TODO create options array instead of four single options

//About text for the new media section
function initialize_media_meta_require_options_callback() {
    echo '<p>' . _e( 'Please choose the descriptive media fields you would like to require to be filled out. Otherwise the user is prohibited to publish the media object.', 'mmrequest' ) . '</p>';
}

//Creation of the four checkboxes
function mmr_toggle_title_callback($args) {
    $html = '<input type="checkbox" id="mmr_require_title" name="mmr_require_title" value="1" ' . checked(1, get_option('mmr_require_title'), false) . '/>';     
    echo $html;
}

function mmr_toggle_caption_callback($args) {
    $html = '<input type="checkbox" id="mmr_require_caption" name="mmr_require_caption" value="1" ' . checked(1, get_option('mmr_require_caption'), false) . '/>'; 
    echo $html;
}
 
function mmr_toggle_alt_callback($args) {
    $html = '<input type="checkbox" id="mmr_require_alt" name="mmr_require_alt" value="1" ' . checked(1, get_option('mmr_require_alt'), false) . '/>'; 
    echo $html;
}
 
function mmr_toggle_desc_callback($args) {
    $html = '<input type="checkbox" id="mmr_require_desc" name="mmr_require_desc" value="1" ' . checked(1, get_option('mmr_require_desc'), false) . '/>'; 
    echo $html;
}
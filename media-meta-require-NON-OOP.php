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

function preprint($s, $return=false) {
     $x = "<pre>";
     $x .= print_r($s, 1);
     $x .= "</pre>";
     if ($return) return $x;
     else print $x;
 }

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
    $mmr_options_array = get_option( 'mmr_options' );
    $checked = isset( $mmr_options_array['title'] ) ? checked( $mmr_options_array['title'], 1, false) : '';
    $html = '<input type="checkbox" id="mmr_options_title" name="mmr_options[title]" value="1" ' . $checked . '/>';
    echo $html;
}

function mmr_toggle_caption_callback($args) {
    $mmr_options_array = get_option( 'mmr_options' );
    $checked = isset( $mmr_options_array['caption'] ) ? checked( $mmr_options_array['caption'], 1, false) : '';
    $html = '<input type="checkbox" id="mmr_options_caption" name="mmr_options[caption]" value="1" ' . $checked . '/>';
    echo $html;
}

function mmr_toggle_alt_callback($args) {
    $mmr_options_array = get_option( 'mmr_options' );
    $checked = isset( $mmr_options_array['alt'] ) ? checked( $mmr_options_array['alt'], 1, false) : '';
    $html = '<input type="checkbox" id="mmr_options_alt" name="mmr_options[alt]" value="1" ' . $checked . '/>';
    echo $html;
}

function mmr_toggle_desc_callback($args) {
    $mmr_options_array = get_option( 'mmr_options' );
    $checked = isset( $mmr_options_array['desc'] ) ? checked( $mmr_options_array['desc'], 1, false) : '';
    $html = '<input type="checkbox" id="mmr_options_desc" name="mmr_options[desc]" value="1" ' . $checked . '/>';
    echo $html;
}

function initialize_media_meta_require_media_subsettings() {
    add_media_page(
        __( 'Detached Media', 'mmrequest'),
        __( 'Detached Media', 'mmrequest' ),
        'manage_options',
        'mmr_detached',
        'mmr_detached_callback'
    );
}
add_action( 'admin_menu', 'initialize_media_meta_require_media_subsettings');

function mmr_detached_callback() {
    $html = '<h2>' . _e( 'Detached Media', 'mmrequest' ) . '</h2>';
    echo $html;
}

function mmr_attachments( $post_id ) {
    $query = array(
        'post_parent'       => $post_id,
        'post_type'         => 'attachment',
        'post_status'       => 'inherit',
        'post_mime_type'    => 'image',
        'numberposts'       => '-1'
        );
    $images = get_children( $query );
    return $images;
}

function mmr_attachments_id_array( $arrayofobject ) {
    $arrayofids = array();
    foreach( $arrayofobject as $key ) {
        array_push($arrayofids, $key->ID);
    }
    return $arrayofids;
}

function mmr_attachment_meta( $attachment_id ) {
    $attachment = get_post( $attachment_id );
    return array(
        'alt'           => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
        'caption'       => $attachment->post_excerpt,
        'desc'          => $attachment->post_content,
        'href'          => get_permalink( $attachment->ID ),
        'src'           => $attachment->guid,
        'title'         => $attachment->post_title
    );
}

function mmr_attachment_check( $post_id, $post ) {
    $prevent_publish = false;
    $mmr_options_array = get_option( 'mmr_options' );
    $media = mmr_attachments( $post_id );
    $attachmentids = mmr_attachments_id_array( $media );
    $attachment_check_results = array();
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if( isset($post->post_status) && 'publish' == $post->post_status ) {
        foreach( $attachmentids as $index ) {
            $attachementmeta = mmr_attachment_meta( $index );
            $attachment_check_results[ $index ]['title'] = ( !isset( $mmr_options_array['title'] ) || ( isset( $mmr_options_array['title'] ) && !empty( $attachementmeta['title'] ) ) ) ? $attachment_check_results[ $index ]['title'] = "True" : $attachment_check_results[ $index ]['title'] = "False";
            $attachment_check_results[ $index ]['caption'] = ( !isset( $mmr_options_array['caption'] ) || ( isset( $mmr_options_array['caption'] ) && !empty( $attachementmeta['caption'] ) ) ) ? $attachment_check_results[ $index ]['caption'] = "True" : $attachment_check_results[ $index ]['caption'] = "False" ;
            $attachment_check_results[ $index ]['alt'] = ( !isset( $mmr_options_array['alt'] ) || ( isset( $mmr_options_array['alt'] ) && !empty( $attachementmeta['alt'] ) ) ) ? $attachment_check_results[ $index ]['alt'] = "True" : $attachment_check_results[ $index ]['alt'] = "False";
            $attachment_check_results[ $index ]['desc'] = ( !isset( $mmr_options_array['desc'] ) || ( isset( $mmr_options_array['desc'] ) && !empty( $attachementmeta['desc'] ) ) ) ? $attachment_check_results[ $index ]['desc'] = "True" : $attachment_check_results[ $index ]['desc'] = "False";
            if( in_array( false, $attachment_check_results[ $index ] ) ) {
                $attachment_check_fail_array = array_keys( $attachment_check_results[ $index ], "False" );
                $attachment_check_fail_output = implode( ', ', $attachment_check_fail_array);
                $erroroutput = 'Prevent Publish: The fields ' . $attachment_check_fail_output . ' in the image with the ID of ' . $index . ' need some care and love. Afterwards you will be able to publish them.';
                //echo $erroroutput;
                $prevent_publish = true;
            }
        }
        if ( $prevent_publish) {
            remove_action('save_post', 'my_save_post');
            wp_update_post(array('ID' => $post_id, 'post_status' => 'draft'));

            //add_settings_error( 'mmr-notices', 'mmr-attachment-check-fail', __( 'Failed', 'mmrequest'), 'error' );

            add_action('save_post', 'save_post');
        }
        // else {
        //     add_settings_error( 'mmr-notices', 'mmr-attachment-check-success', __('Worked', 'mmrequest'), 'updated' );
        // }
        //settings_errors( 'mmr-notices' );

    }
}

add_action( 'save_post', 'mmr_attachment_check', 1, 2);



function myplugin_admin_messages() {
    if ( $prevent_publisher ) {
        add_settings_error( 'mmr-notices', 'mmr-attachment-check-fail', __(  'Failed', 'mmrequest'), 'error' );
    }
    else {
        add_settings_error( 'mmr-notices', 'mmr-attachment-check-success', __('Worky Work', 'mmrequest'), 'updated' );
    }
    settings_errors( 'mmr-notices' );
}
add_action('admin_notices', 'myplugin_admin_messages');

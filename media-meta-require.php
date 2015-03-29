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

if( ! class_exists( 'mmr' ) ) :

class mmr {
    protected $prevent_publish = False;

    function __construct() {
        register_activation_hook( __FILE__, array( $this, 'mmr_init_checks' ) );
        load_plugin_textdomain( 'mmrequest', false, plugin_basename( dirname( __FILE__ ) . '/lang' ) );

        add_action('admin_init', array( $this, 'initialize_media_meta_require_options' ) );
        add_action( 'save_post', array( $this, 'mmr_attachment_check' ), 1, 2);
        add_action('admin_notices', array( $this, 'mmr_attachment_check_notices' ) );
    }

    public function preprint($s, $return=false) {
         $x = "<pre>";
         $x .= print_r($s, 1);
         $x .= "</pre>";
         if ($return) return $x;
         else print $x;
     }

    public function mmr_init_checks( $wp = '4.0', $php = '5.2.4' ) {
        global $wp_version;
        if ( version_compare( PHP_VERSION, $php, '<' ) ) {
            $flag = 'PHP';
        }
        elseif ( version_compare( $wp_version, $wp, '<' ) ) {
            $flag = 'WordPress';
        }
        else {
            return;
        }
        $version = 'PHP' == $flag ? $php : $wp;
        deactivate_plugins( basename( __FILE__ ) );
        wp_die('<p>The <strong>Insert Plugin Name Here</strong> plugin requires'.$flag.'  version '.$version.' or greater.</p>','Plugin Activation Error',  array( 'response'=>200, 'back_link'=>True ) );
    }

    public function initialize_media_meta_require_options() {
        register_setting( 'media', 'mmr_options' );

        add_settings_section(
            'media_settings_section',
            __( 'Which media fields are required for publishing', 'mmrequest'),
            array( $this, 'initialize_media_meta_require_options_callback' ),
            'media'
        );

        add_settings_field(
            'mmr_options_title',
            __( 'Title', 'mmrequest' ),
            array( $this, 'mmr_toggle_title_callback' ),
            'media',
            'media_settings_section'
        );

         add_settings_field(
            'mmr_options_caption',
            __( 'Caption', 'mmrequest' ),
            array( $this, 'mmr_toggle_caption_callback' ),
            'media',
            'media_settings_section'
        );

        add_settings_field(
            'mmr_options_alt',
            __( 'Alt Text', 'mmrequest' ),
            array( $this, 'mmr_toggle_alt_callback' ),
            'media',
            'media_settings_section'
        );

        add_settings_field(
            'mmr_options_desc',
            __( 'Description', 'mmrequest' ),
            array( $this, 'mmr_toggle_desc_callback' ),
            'media',
            'media_settings_section'
        );
    }
    public function initialize_media_meta_require_options_callback() {
        $html = '<p>' . _e( 'Please choose the descriptive media fields you would like to require to be filled out. Otherwise the user is prohibited to publish the media object.', 'mmrequest' ) . '</p>';
        echo $html;
    }

    public function mmr_toggle_title_callback($args) {
        $mmr_options_array = get_option( 'mmr_options' );
        $checked = isset( $mmr_options_array['title'] ) ? checked( $mmr_options_array['title'], 1, false) : '';
        $html = '<input type="checkbox" id="mmr_options_title" name="mmr_options[title]" value="1" ' . $checked . '/>';
        echo $html;
    }

    public function mmr_toggle_caption_callback($args) {
        $mmr_options_array = get_option( 'mmr_options' );
        $checked = isset( $mmr_options_array['caption'] ) ? checked( $mmr_options_array['caption'], 1, false) : '';
        $html = '<input type="checkbox" id="mmr_options_caption" name="mmr_options[caption]" value="1" ' . $checked . '/>';
        echo $html;
    }

    public function mmr_toggle_alt_callback($args) {
        $mmr_options_array = get_option( 'mmr_options' );
        $checked = isset( $mmr_options_array['alt'] ) ? checked( $mmr_options_array['alt'], 1, false) : '';
        $html = '<input type="checkbox" id="mmr_options_alt" name="mmr_options[alt]" value="1" ' . $checked . '/>';
        echo $html;
    }

    public function mmr_toggle_desc_callback($args) {
        $mmr_options_array = get_option( 'mmr_options' );
        $checked = isset( $mmr_options_array['desc'] ) ? checked( $mmr_options_array['desc'], 1, false) : '';
        $html = '<input type="checkbox" id="mmr_options_desc" name="mmr_options[desc]" value="1" ' . $checked . '/>';
        echo $html;
    }

    public function mmr_attachments( $post_id ) {
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

    public function mmr_attachments_id_array( $arrayofobject ) {
        $arrayofids = array();
        foreach( $arrayofobject as $key ) {
            array_push($arrayofids, $key->ID);
        }
        return $arrayofids;
    }

    public function mmr_attachment_meta( $attachment_id ) {
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

    public function mmr_attachment_check( $post_id, $post ) {
        $mmr_options_array = get_option( 'mmr_options' );
        $media = $this->mmr_attachments( $post_id );
        $attachmentids = $this->mmr_attachments_id_array( $media );
        $attachment_check_results = array();
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if( isset($post->post_status) && 'publish' == $post->post_status ) {
            foreach( $attachmentids as $index ) {
                $attachementmeta = $this->mmr_attachment_meta( $index );
                $attachment_check_results[ $index ]['title'] = ( !isset( $mmr_options_array['title'] ) || ( isset( $mmr_options_array['title'] ) && !empty( $attachementmeta['title'] ) ) ) ? $attachment_check_results[ $index ]['title'] = True : $attachment_check_results[ $index ]['title'] = False;
                $attachment_check_results[ $index ]['caption'] = ( !isset( $mmr_options_array['caption'] ) || ( isset( $mmr_options_array['caption'] ) && !empty( $attachementmeta['caption'] ) ) ) ? $attachment_check_results[ $index ]['caption'] = True : $attachment_check_results[ $index ]['caption'] = False;
                $attachment_check_results[ $index ]['alt'] = ( !isset( $mmr_options_array['alt'] ) || ( isset( $mmr_options_array['alt'] ) && !empty( $attachementmeta['alt'] ) ) ) ? $attachment_check_results[ $index ]['alt'] = True : $attachment_check_results[ $index ]['alt'] = False;
                $attachment_check_results[ $index ]['desc'] = ( !isset( $mmr_options_array['desc'] ) || ( isset( $mmr_options_array['desc'] ) && !empty( $attachementmeta['desc'] ) ) ) ? $attachment_check_results[ $index ]['desc'] = True : $attachment_check_results[ $index ]['desc'] = False;
                if( in_array( False, $attachment_check_results[ $index ] ) ) {
                    $attachment_check_fail_array = array_keys( $attachment_check_results[ $index ], False );
                    $attachment_check_fail_output = implode( ', ', $attachment_check_fail_array);
                    $erroroutput = 'Prevent Publish: The fields ' . $attachment_check_fail_output . ' in the image with the ID of ' . $index . ' need some care and love. Afterwards you will be able to publish them.';
                    $this->preprint($erroroutput);
                    $this->prevent_publish = True;
                }
                else {
                    $this->prevent_publish = False;
                }
            }
            $this->preprint($attachment_check_results);
            if ( $this->prevent_publish) {
                remove_action( 'save_post', 'my_save_post' );
                wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
                add_action( 'save_post', 'save_post' );
            }
        }
    }

    public function mmr_attachment_check_notices() {
        if ( $this->prevent_publish ) {
            add_settings_error( 'mmr-notices', 'mmr-attachment-check-fail', __(  'Failed', 'mmrequest'), 'error' );
        }
        else {
            add_settings_error( 'mmr-notices', 'mmr-attachment-check-success', __('Worky Work', 'mmrequest'), 'updated' );
        }
        settings_errors( 'mmr-notices' );
    }
}

function media_media_require_launch() {
    global $media_media_require_instance;
    if( !isset($media_media_require_instance) ) {
        $media_media_require_instance = new mmr();
    }
    return $media_media_require_instance;
}

media_media_require_launch();

endif; // class_exists check

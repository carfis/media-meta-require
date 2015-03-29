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

function preprint($s, $return=false) {
     $x = "<pre>";
     $x .= print_r($s, 1);
     $x .= "</pre>";
     if ($return) return $x;
     else print $x;
 }

if( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'WPINC' ) ) die;

if( version_compare( PHP_VERSION, '5.0.0', '<' ) ) {
    add_action( 'admin_notices', 'put_version_require' );
    function put_version_require() {
        if( current_user_can( 'manage_options' ) )
            echo '<div class="error"><p>The Post Tabs UI plugin requires at least PHP 5.</p></div>';
    }
    return;
}


if( ! class_exists( 'mmr' ) ) :


class mmr {

    function __construct() {

    }

    function initialize() {

    }

    function complete() {

    }

    function wp_init() {

    }

}

function mmr() {
    global $mmr;

    if( !isset($mmr) ) {

        $mmr = new mmr();

        $mmr->initialize();

    }

    return $mmr;
}


// initialize
mmr();


endif; // class_exists check


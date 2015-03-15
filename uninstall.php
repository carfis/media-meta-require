<?php
if( !defined( 'ABSPATH' ) && !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();
delete_option( 'mmr_require_title' );
delete_option( 'mmr_require_caption' );
delete_option( 'mmr_require_alt' );
delete_option( 'mmr_require_desc' );
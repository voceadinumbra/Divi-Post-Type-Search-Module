<?php
/*
Plugin Name: Post Type Search Module for Divi
Plugin URI:  https://wpwebaid.com/
Description: The plugin adds a new module, the Search By Post Type module
Version:     1.0.0
Author:      wpwebaid.com
Author URI:  https://wpwebaid.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: dpts-search-for-Divi

Post Type Search Module for Divi is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Post Type Search Module for Divi is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Post Type Search Module for Divi. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/


if ( ! function_exists( 'dpts_initialize_extension' ) )
{
/**
 * Creates the extension's main class instance.
 *
 * @since 1.0.0
 */
        function dpts_initialize_extension() 
        {
                require_once plugin_dir_path( __FILE__ ) . 'includes/PostTypeSearchforDivi.php';
        }
        add_action( 'divi_extensions_init', 'dpts_initialize_extension' );
}


if ( ! function_exists( 'dpts_custom_search_posttype_filter' ) ){

// Hook into pre_get_posts with high priority to override other plugins
add_action('pre_get_posts', 'dpts_custom_search_posttype_filter', 1);

// Also hook into parse_query as a backup
add_action('parse_query', 'dpts_custom_search_posttype_filter_backup', 1);

function dpts_custom_search_posttype_filter( $query ) 
{ 
    // Only modify search queries on frontend, not admin queries
    if ( ! is_admin() && $query->is_search() ) {
        
        // Check if we have a post type parameter
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Search parameters are public GET requests
        if ( isset( $_GET['posttype_search'] ) && ! empty( $_GET['posttype_search'] ) ) {
            
            // Sanitize the post type parameter
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Search parameters are public GET requests
            $posttype_search = sanitize_text_field( wp_unslash( $_GET['posttype_search'] ) );
            
            // Convert comma-separated string to array
            $posttype_array = array_map( 'trim', explode( ',', $posttype_search ) );
            $valid_post_types = array();
            
            // Validate each post type
            foreach( $posttype_array as $posttype ) {
                if( ! empty( $posttype ) && post_type_exists( $posttype ) ) {
                    $valid_post_types[] = $posttype;
                }
            }
            
            if( ! empty( $valid_post_types ) ) {
                // Force set the post types to search - use multiple methods to ensure it sticks
                $query->set( 'post_type', $valid_post_types );
                $query->query_vars['post_type'] = $valid_post_types;
                
                // Also set it directly in the query object
                if ( property_exists( $query, 'query' ) ) {
                    $query->query['post_type'] = $valid_post_types;
                }
            } else {
                // No valid post types found, return no results
                $query->set( 'post__in', array(0) );
                $query->query_vars['post__in'] = array(0);
            }
        }
    }
}

function dpts_custom_search_posttype_filter_backup( $query ) 
{
    // Backup function in case the first one doesn't work
    if ( ! is_admin() && $query->is_search() ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Search parameters are public GET requests
        if ( isset( $_GET['posttype_search'] ) && ! empty( $_GET['posttype_search'] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Search parameters are public GET requests
            $posttype_search = sanitize_text_field( wp_unslash( $_GET['posttype_search'] ) );
            $posttype_array = array_map( 'trim', explode( ',', $posttype_search ) );
            $valid_post_types = array();
            
            foreach( $posttype_array as $posttype ) {
                if( ! empty( $posttype ) && post_type_exists( $posttype ) ) {
                    $valid_post_types[] = $posttype;
                }
            }
            
            if( ! empty( $valid_post_types ) ) {
                $query->query_vars['post_type'] = $valid_post_types;
            }
        }
    }
}

// Add a filter to modify the SQL query directly if needed
add_filter( 'posts_where', 'dpts_modify_search_where', 10, 2 );

function dpts_modify_search_where( $where, $query ) {
    if ( ! is_admin() && $query->is_search() && $query->is_main_query() ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Search parameters are public GET requests
        if ( isset( $_GET['posttype_search'] ) && ! empty( $_GET['posttype_search'] ) ) {
            global $wpdb;
            
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Search parameters are public GET requests
            $posttype_search = sanitize_text_field( wp_unslash( $_GET['posttype_search'] ) );
            $posttype_array = array_map( 'trim', explode( ',', $posttype_search ) );
            $valid_post_types = array();
            
            foreach( $posttype_array as $posttype ) {
                if( ! empty( $posttype ) && post_type_exists( $posttype ) ) {
                    $valid_post_types[] = "'" . esc_sql( $posttype ) . "'";
                }
            }
            
            if( ! empty( $valid_post_types ) ) {
                $post_types_in = implode( ',', $valid_post_types );
                $where .= " AND {$wpdb->posts}.post_type IN ({$post_types_in})";
            }
        }
    }
    
    return $where;
}

if ( ! function_exists( 'dpts_divi_module_dependencies' ) )
{

function dpts_divi_module_dependencies() 
{
    if( ! function_exists('et_builder_options') ) {
        echo '<div class="notice notice-warning"><p>' . esc_html(__( 'Post Type Search Module for Divi needs the Divi Theme or the Divi Plugin to function', 'dpts-search-for-Divi' ) ) . '</p></div>';
    }
}

add_action( 'admin_notices', 'dpts_divi_module_dependencies' );

}

}
<?php
/*
Plugin Name: Post Type Search Module for Divi
Plugin URI:  https://andreisim.com/
Description: The plugin adds a new module, the Search By Post Type module
Version:     1.0.0
Author:      andreisim.com
Author URI:  https://andreisim.com
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


if ( ! function_exists( 'dpts_get_custom_post_type_search' ) ){

add_action( 'wp_ajax_dpts_get_custom_post_type_search', 'dpts_get_custom_post_type_search' );
add_action('pre_get_posts', 'jswj_custom_search_module_posttype_filter2', 1);
function jswj_custom_search_module_posttype_filter2( $query ) 
{ 
        if ( isset( $_GET['posttype_search'], $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'posttype_search_action' ) ) 
        {
                if ( ! empty( $_GET['posttype_search'] ) && $query->is_search() ) 
                {            
                    // Sanitize $_GET['posttype_search']
                    $posttype_search = sanitize_text_field( wp_unslash( $_GET['posttype_search'] ) );
                        # Validate - Array Items Are Post Types
                        $posttype_search = explode( ',', $posttype_search );
                        foreach( $posttype_search as $key => $posttype ) 
                        {
                                # Remove From Array If Value Is Not A Valid Post Type
                                if( false === post_type_exists( $posttype ) ) 
                                {
                                        unset( $posttype_search[$key] );
                                }
                         } #END foreach $posttype_search

                        if( empty( $posttype_search ) ) 
                        {
                                # Modify Query To Return No Results If No Valid Post Types Are Specified
                                $query->set( 'post__in', array(0) );
                        } 
                        else 
                        {
                                # Modify Query To Search Selected Post Types
                                $query->set('post_type', $posttype_search);
                        }
                }
        }
        return $query;
} #END jswj_custom_search_module_posttype_filter2()

function dpts_get_custom_post_type_search()
{
    $post_id=0;      

        if ( isset( $_GET['posttype_search'], $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash( $_GET['_wpnonce'] )), 'posttype_search_action' ) ) 
        {
                if ( ! empty( $_GET['posttype_search'] ) && $query->is_search() ) 
                {
                # Sanitize $_GET
                $posttype_search = sanitize_text_field( wp_unslash( $_GET['posttype_search'] ) );

                if(isset($_POST['post_id'])  && is_int(intval($_POST['post_id'])))
                {        
                $post_id = sanitize_text_field( wp_unslash( $_POST['post_id'] ) );
                }                

                $result = [
                        'title' => get_the_title( $post_id ),	//Title of the Post
                        'html'=> dpts_divi_search() 	
                ];
                echo wp_json_encode( $result );
                wp_die();
                }
        }
}

if ( ! function_exists( 'dpts_divi_module_dependencies' ) )
{

//et_builder_options();
function dpts_divi_module_dependencies() 
{
        if( ! function_exists('et_builder_options'))
        {
        echo '<div class="notice notice-warning"><p>' . esc_html(__( 'Post Type Search Module for Divi needs the Divi Theme or the Divi Plugin to function', 'dpts-search-for-Divi' ) ) . '</p></div>';
        }
                
}

add_action( 'admin_notices', 'dpts_divi_module_dependencies' );

}
}
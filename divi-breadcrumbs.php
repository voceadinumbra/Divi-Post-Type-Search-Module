<?php
/*
Plugin Name: Breadcrumbs Divi Module
Plugin URI:  http://www.learnhowwp.com/divi-breadcrumbs-module
Description: The plugin adds a new module, the Breadcrumbs module in the Divi Builder
Version:     1.2.3
Author:      learnhowwp.com
Author URI:  http://www.learnhowwp.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: lwp-divi-breadcrumbs
Domain Path: /languages

Divi Breadcrumbs is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Divi Breadcrumbs is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Divi Breadcrumbs. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/


if ( ! function_exists( 'lwp_initialize_extension' ) ):
/**
 * Creates the extension's main class instance.
 *
 * @since 1.0.0
 */
function lwp_initialize_extension() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/DiviBreadcrumbs.php';
}
add_action( 'divi_extensions_init', 'lwp_initialize_extension' );
endif;


if ( ! function_exists( 'lwp_get_breadcrumbs' ) ):

add_action( 'wp_ajax_lwp_get_breadcrumbs', 'lwp_get_breadcrumbs' );

add_action('pre_get_posts', 'jswj_custom_search_module_posttype_filter2', 1);
function jswj_custom_search_module_posttype_filter2( $query ) {
    if( isset( $_GET['posttype_search'] ) && !empty( $_GET['posttype_search'] ) && $query->is_search() ) {
            # Sanitize $_GET
            $posttype_search = sanitize_text_field( $_GET['posttype_search'] );
            # Validate - Array Items Are Post Types
            $posttype_search = explode( ',', $posttype_search );
            foreach( $posttype_search as $key => $posttype ) {
                    # Remove From Array If Value Is Not A Valid Post Type
                    if( false === post_type_exists( $posttype ) ) {
                            unset( $posttype_search[$key] );
                    }
            } #END foreach $posttype_search
            if( empty( $posttype_search ) ) {
                    # Modify Query To Return No Results If No Valid Post Types Are Specified
                    $query->set( 'post__in', array(0) );
            } else {
                    # Modify Query To Search Selected Post Types
                    $query->set('post_type', $posttype_search);
            }
    }
    return $query;
} #END jswj_custom_search_module_posttype_filter2()

function lwp_get_breadcrumbs(){

    $post_id=0;

	




    if(isset($_POST['post_id'])  && is_int(intval($_POST['post_id'])))
	    $post_id = $_POST['post_id'];

    $result = [
		'title' => get_the_title( $post_id ),	//Title of the Post
		'html'=> lwp_get_hansel_and_gretel_breadcrumbs() 	//Breadcrumbs for Post
    ];
    echo json_encode( $result );
    wp_die();
}
endif;

if ( ! function_exists( 'lwp_get_hansel_and_gretel_breadcrumbs' ) ):

//Function that generates the HTML from breadcrumbs
function lwp_get_hansel_and_gretel_breadcrumbs( $_delimiter='&#x39;') {
    // Set variables for later use

    $placeholder      = "";
    $link_before      = '<span property="itemListElement" typeof="ListItem">';
    $link_after       = '</span>';
    $link_attr        = ' property="item" typeof="WebPage"';
    $link             = $link_before . '<a' . $link_attr . ' href="%1$s"><span property="name">%2$s<span></a><meta property="position" content="positionhere">' . $link_after;
    $delimiter        = $_delimiter;              // Delimiter between crumbs
    $before           = '<span class="current">'; // Tag before the current crumb
    $after            = '</span>';                // Tag after the current crumb
    $page_addon       = '';                       // Adds the page number if the query is paged
    $breadcrumb_trail = '';
    $category_links   = '';
    $position         =2;

	$delimiter = ' <span class="separator et-pb-icon">'.$delimiter.'</span> ';

    /**
     * Set our own $wp_the_query variable. Do not use the global variable version due to
     * reliability
     */
    $wp_the_query   = $GLOBALS['wp_the_query'];
    $queried_object = $wp_the_query->get_queried_object();

    // Handle single post requests which includes single pages, posts and attatchments
    if ( is_singular() )
    {
        /**
         * Set our own $post variable. Do not use the global variable version due to
         * reliability. We will set $post_object variable to $GLOBALS['wp_the_query']
         */
        $post_object = sanitize_post( $queried_object );

        // Set variables
        $title          = apply_filters( 'the_title', $post_object->post_title );
        $parent         = $post_object->post_parent;
        $post_type      = $post_object->post_type;
        $post_id        = $post_object->ID;
        $post_link      = $before . $title . $after;
        $parent_string  = '';
        $post_type_link = '';

        if ( 'post' === $post_type )
        {
            // Get the post categories
            $categories = get_the_category( $post_id );
            if ( $categories ) {
                // Lets grab the first category
                $category  = $categories[0];

                $category_names = get_category_parents( $category);
                $category_names_array = explode('/',$category_names);

                $category_links = get_category_parents( $category, true, $delimiter );
                $category_links = str_replace( '<a',   $link_before . '<a' . $link_attr, $category_links );
                $category_links = str_replace( '</a>', '</a>' . $link_after, $category_links );
                foreach ($category_names_array as $category_loop_name) {
                    if($category_loop_name=='')
                        continue;
                    $category_links = str_replace( $category_loop_name.'</a>', '<span property="name">' .$category_loop_name.'</span></a>',$category_links );   //</a> included in str_replace to avoid replacing the word if it is part of another category
                    $category_links = str_replace( '<span property="name">' .$category_loop_name.'</span></a>','<span property="name">' .$category_loop_name.'</span></a><meta property="position" content="'.$position++.'">' ,$category_links );
                }
            }
        }

        if ( !in_array( $post_type, ['post', 'page', 'attachment'] ) )
        {
            $post_type_object = get_post_type_object( $post_type );
            $archive_link     = esc_url( get_post_type_archive_link( $post_type ) );

            $post_type_link   = sprintf( $link, $archive_link, $post_type_object->labels->singular_name );
            $post_type_link = str_replace( 'positionhere', $position++, $post_type_link );
        }

        // Get post parents if $parent !== 0
        if ( 0 !== $parent )
        {
            $parent_links = [];
            while ( $parent ) {
                $post_parent = get_post( $parent );

                $temp_link = sprintf( $link, esc_url( get_permalink( $post_parent->ID ) ), get_the_title( $post_parent->ID ) );
                $temp_link = str_replace( 'positionhere', $position++, $temp_link );

                $parent_links[] = $temp_link;

                $parent = $post_parent->post_parent;
            }

            $parent_links = array_reverse( $parent_links );

            $parent_string = implode( $delimiter, $parent_links );
        }

        // Lets build the breadcrumb trail
        if ( $parent_string ) {
            $breadcrumb_trail = $parent_string . $delimiter . $post_link;
        } else {
            $breadcrumb_trail = $post_link;
        }

        if ( $post_type_link )
            $breadcrumb_trail = $post_type_link . $delimiter . $breadcrumb_trail;

        if ( $category_links )
            $breadcrumb_trail = $category_links . $breadcrumb_trail;
    }

    // Handle archives which includes category-, tag-, taxonomy-, date-, custom post type archives and author archives
    if( is_archive() )
    {
        if (    is_category()
             || is_tag()
             || is_tax()
        ) {
            // Set the variables for this section
            $term_object        = get_term( $queried_object );
            $taxonomy           = $term_object->taxonomy;
            $term_id            = $term_object->term_id;
            $term_name          = $term_object->name;
            $term_parent        = $term_object->parent;
            $taxonomy_object    = get_taxonomy( $taxonomy );
            //Categories: Tags: is set there
            $current_term_link  = $before . $taxonomy_object->labels->singular_name . ': ' . $term_name . $after;
            $parent_term_string = '';

            if ( 0 !== $term_parent )
            {
                // Get all the current term ancestors
                $parent_term_links = [];
                while ( $term_parent ) {
                    $term = get_term( $term_parent, $taxonomy );

                    $temp_link = sprintf( $link, esc_url( get_term_link( $term ) ), $term->name );
                    $temp_link = str_replace( 'positionhere', $position++, $temp_link );

                    $parent_term_links[] = $temp_link;

                    $term_parent = $term->parent;
                }

                $parent_term_links  = array_reverse( $parent_term_links );
                $parent_term_string = implode( $delimiter, $parent_term_links );
            }

            if ( $parent_term_string ) {
                $breadcrumb_trail = $parent_term_string . $delimiter . $current_term_link;
            } else {
                $breadcrumb_trail = $current_term_link;
            }

        } elseif ( is_author() ) {

            $breadcrumb_trail = __( 'Author archive for ') .  $before . $queried_object->data->display_name . $after;

        } elseif ( is_date() ) {
            // Set default variables
            $year     = $wp_the_query->query_vars['year'];
            $monthnum = $wp_the_query->query_vars['monthnum'];
            $day      = $wp_the_query->query_vars['day'];

            // Get the month name if $monthnum has a value
            if ( $monthnum ) {
                $date_time  = DateTime::createFromFormat( '!m', $monthnum );
                $month_name = $date_time->format( 'F' );
            }

            if ( is_year() ) {

                $breadcrumb_trail = $before . $year . $after;

            } elseif( is_month() ) {

                $year_link        = sprintf( $link, esc_url( get_year_link( $year ) ), $year );
                $year_link = str_replace( 'positionhere', $position++, $year_link );

                $breadcrumb_trail = $year_link . $delimiter . $before . $month_name . $after;

            } elseif( is_day() ) {

                $year_link        = sprintf( $link, esc_url( get_year_link( $year ) ),             $year       );
                $year_link = str_replace( 'positionhere', $position++, $year_link );

                $month_link       = sprintf( $link, esc_url( get_month_link( $year, $monthnum ) ), $month_name );
                $month_link = str_replace( 'positionhere', $position++, $month_link );

                $breadcrumb_trail = $year_link . $delimiter . $month_link . $delimiter . $before . $day . $after;
            }

        } elseif ( is_post_type_archive() ) {

            $post_type        = $wp_the_query->query_vars['post_type'];
            $post_type_object = get_post_type_object( $post_type );

            $breadcrumb_trail = $before . $post_type_object->labels->singular_name . $after;

        }
    }

    // Handle the search page
    if ( is_search() ) {
        $breadcrumb_trail = __( 'Search query for: ' ) . $before . get_search_query() . $after;
    }

    // Handle 404's
    if ( is_404() ) {
        $breadcrumb_trail = $before . __( 'Error 404' ) . $after;
    }

    // Handle paged pages
    if ( is_paged() ) {
        $current_page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' );
        // If $current_page is false or an empty string, parse the URL manually
        if ( empty ( $current_page) ) {
            $current_url = home_url( add_query_arg(array()) );
            $parsed_url = parse_url( $current_url );
            parse_str( $parsed_url['query'], $query_params );

            if ( isset( $query_params['paged'] ) ) {
                $current_page = $query_params['paged'];
            } elseif ( isset( $query_params['page'] ) ) {
                $current_page = $query_params['page'];
            }

            // If 'paged' or 'page' is not found in the query parameters, try to get it from the URL path
            if ( empty( $current_page ) && isset( $parsed_url['path'] ) ) {
                $matches = array();
                preg_match( '/\/page\/([0-9]+)/', $parsed_url['path'], $matches );
                if ( isset( $matches[1] ) ) {
                    $current_page = $matches[1];
                }
            }            
        }

        if ( $current_page ) {
            $page_addon = $before . sprintf( __( ' ( Page %s )' ), number_format_i18n( $current_page ) ) . $after;
        }
    }

    $breadcrumb_output_link  = '';
    
    $breadcrumb_output_link .= '';

    // XYZ

    if (    is_home()
         || is_front_page()
    ) {
        // Do not show breadcrumbs on page one of home and frontpage
        if ( is_paged() ) {
            $breadcrumb_output_link .= '<span class="before">'.$here_text.'</span> ';
            $breadcrumb_output_link .= '<span vocab="https://schema.org/" typeof="BreadcrumbList">';
            $breadcrumb_output_link .= '<span property="itemListElement" typeof="ListItem"><meta property="position" content="1"></a><meta property="position" content="1"></span>';
            $breadcrumb_output_link .= $page_addon;
            $breadcrumb_output_link .= '</span>';
        }
    } else {
        $breadcrumb_output_link .= '<span class="before">'.$here_text.'</span> ';
        $breadcrumb_output_link .= '<span vocab="https://schema.org/" typeof="BreadcrumbList">';
        $breadcrumb_output_link .= '<span property="itemListElement" typeof="ListItem"><meta property="position" content="1"></span>';
        $breadcrumb_output_link .= $delimiter;
        $breadcrumb_output_link .= $breadcrumb_trail;
        $breadcrumb_output_link .= $page_addon;
        $breadcrumb_output_link .= '</span>';
    }

    $render_slug = 'render_slug';
    $render_id = 'render_id';

    $video_background ='video_background';

    $parallax_image_background = 'parallax_image_background';

    $breadcrumb_output_link .= sprintf(
        '<div %3$s class="%2$s"%12$s%13$s>
        111
        %2$s
        2222

        %11$s
        %10$s
        <form role="search" method="get" class="et_pb_searchform" action="%1$s">
                <div>
                        <label class="screen-reader-text" for="s">%8$s</label>
                        %7$s
                        
                        <input type="hidden" name="posttype_search" value="%14$s" />
                        %4$s
                        %5$s
                        %6$s
                        <input type="submit" value="%9$s" class="et_pb_searchsubmit">
                </div>
        </form>
</div>',
        esc_url( home_url( '/' ) ),
        $render_slug,
        $render_id,
        '',
        '', // #5
        '',
        $placeholder,
        esc_html__( 'Search for:', 'et_builder' ),
        esc_attr__( 'Search', 'et_builder' ),
        $video_background, // #10
        $parallax_image_background,
        et_core_esc_previously( $data_background_layout ),
        $multi_view_show_button_data_attr,
        /**
         * JSWJ - POST TYPE SEARCH MODULE FOR DIVI
         **/
        htmlspecialchars_decode( $search_types ) # %14$s
);

    // XYZ
    

    return $breadcrumb_output_link;
}

endif;


if ( ! function_exists( 'lwp_divi_breadcrumbs_dependencies' ) ):

//et_builder_options();
function lwp_divi_breadcrumbs_dependencies() {
    if( ! function_exists('et_builder_options'))
      echo '<div class="notice notice-warning"><p>' . __( 'The Divi Breadcrums Module needs the Divi Theme or the Divi Plugin to function', 'lwp-divi-breadcrumbs' ) . '</p></div>';
  }

add_action( 'admin_notices', 'lwp_divi_breadcrumbs_dependencies' );

endif; 

if ( ! function_exists( 'lwp_breadcrumbs_add_icons' ) ):
    add_filter( 'et_global_assets_list', 'lwp_breadcrumbs_add_icons', 10 );
    function lwp_breadcrumbs_add_icons( $assets ) {
        if ( isset( $assets['et_icons_all'] ) && isset( $assets['et_icons_fa'] ) ) {
            return $assets;
        }
        $assets_prefix = et_get_dynamic_assets_path();
        $assets['et_icons_all'] = array(
            'css' => "{$assets_prefix}/css/icons_all.css",
        );
        $assets['et_icons_fa'] = array(
            'css' => "{$assets_prefix}/css/icons_fa_all.css",
        );
        return $assets;
    }
    endif;
<?php

class DPTS_DiviSearch extends ET_Builder_Module_Search {

	public $slug       = 'dpts_divi_module';
	public $vb_support = 'on';
	public $icon;

	 


	public function init() {
		$this->name = esc_html__( 'Post Types Search', 'dpts-search-for-Divi' );
		$this->icon = '=';
		$this->main_css_element = '%%order_class%%';
		$this->whitelisted_fields = array(
			'include_posttypes',
		);
	}

	
	function get_posttypes_array() {
		$posttypes = get_post_types( array( 'exclude_from_search'       => false ), 'objects' );
		unset( $posttypes['attachment'] );
		unset( $posttypes['revision'] );
		unset( $posttypes['nav_menu_item'] );
		unset( $posttypes['custom_css'] );
		unset( $posttypes['customize_changeset'] );
		unset( $posttypes['oembed_cache'] );
		unset( $posttypes['et_pb_layout'] );
		return $posttypes;
	}

	public function get_fields() {


		$fields = array(
			'placeholder'        => array(
				'label'           => esc_html__( 'Input Placeholder', 'dpts-search-for-Divi' ),
				'type'            => 'text',
				'description'     => esc_html__( 'Type the text you want to use as placeholder for the search field.', 'dpts-search-for-Divi' ),
				'toggle_slug'     => 'main_content',
				'dynamic_content' => 'text',
				'mobile_options'  => true,
				'hover'           => 'tabs',
			),
			
			/**
			 * JSWJ - POST TYPE SEARCH MODULE FOR DIVI
			 * Include The Post Type Options
			 **/
			'include_posttypes' => array(
				'label'            => esc_html__( 'Include Post Types', 'dpts-search-for-Divi' ),
				'type'             => 'multiple_checkboxes',
				'option_category'  => 'basic_option',
				'depends_show_if'  => 'off',
				'description'      => esc_html__( 'Select the post types that you would like to include in the search. If none are selected, all post types will be included in the search.', 'dpts-search-for-Divi' ),
				'toggle_slug'      => 'main_content',
			),
		);
		/**
		 * Build The Post Type Checkboxes
		 **/
		$posttypes = $this->get_posttypes_array();
		foreach( $posttypes as $key => $posttype ) {
				$fields['include_posttypes']['options'][$key] = $posttype->label;
		}
		return $fields;
		
	}
 

	public function render( $attrs, $content, $render_slug ) {

		$multi_view                = et_pb_multi_view_options( $this );

		/**
		 * DPTS - POST TYPE SEARCH MODULE FOR DIVI
		 **/
		$include_posttypes         = $this->props['include_posttypes'];
		# Get Comma Separated Post Types To Search
		$index  = 0;
		$posttypes = array_keys( $this->get_posttypes_array() );
		foreach ( explode( '|', $include_posttypes ) as $checkbox_value ) {
				if ( 'off' === $checkbox_value ) { unset( $posttypes[$index] ); }
				$index++;
		}

		$search_types = implode( ',', $posttypes );
		
		
 
		$placeholder               = $multi_view->render_element(
			array(
					'tag'   => 'input',
					'attrs' => array(
							'type'        => 'text',
							'name'        => 's',
							'class'       => 'et_pb_s',
							'placeholder' => '{{placeholder}}',
					),
			)
		);

		$before_html='';	

		if ( ! function_exists( 'dpts_divi_search' ) )
		{

			//Function that generates the HTML
			function dpts_divi_search($placeholder , $search_types) {
				// Set variables for later use
			
			//	$placeholder      = "";
				$link_before      = ' ';
				$link_after       = ' ';
				$link_attr        = ' property="item" typeof="WebPage"';
				$link             = $link_before . '<a' . $link_attr . ' href="%1$s"><span property="name">%2$s<span></a><meta property="position" content="positionhere">' . $link_after;
				$delimiter        = ' ';              // Delimiter between crumbs
				$before           = ' '; // Tag before the current crumb
				$after            = ' ';                // Tag after the current crumb
				$category_links   = '';
				$position         = 2;
			
				$delimiter = ' ';   	

				$dpts_search_output = ''; //Variable that will store the HTML output 
			
				$dpts_search_output .= $delimiter;		
			
				$render_slug = ' '; //Needs to be checked if it can be deleted;
				$render_id = ' '; //Needs to be checked if it can be deleted;
				$video_background =' '; //Needs to be checked if it can be deleted;
				$parallax_image_background = ' '; //Needs to be checked if it can be deleted;
				$data_background_layout = ' '; //Needs to be checked if it can be deleted;
				$here_text = ' '; //Needs to be checked if it can be deleted;
			
				$dpts_search_output .= sprintf(
					'<div %3$s class="%2$s" %12$s >        
					%2$s      
					%11$s
					%10$s
					<form role="search" method="get" class="et_pb_searchform" action="%1$s">
							<div>
									<label class="screen-reader-text" for="s">%8$s</label>
									%7$s                        
									<input type="hidden" name="posttype_search" aq value="%13$s" aqw />
									%4$s
									%5$s
									%6$s
									<input type="submit" value="%9$s" class="et_pb_searchsubmit">
							</div>
					</form>
			</div>',
					esc_url( home_url( '/' ) ), // #1
					$render_slug, // #2
					$render_id, // #3
					'', // #4
					'', // #5
					'', // #6
					$placeholder, // #7
					esc_html__( 'Search for:', 'dpts-search-for-Divi' ), // #8
					esc_attr__( 'Search', 'dpts-search-for-Divi' ), // #9
					$video_background, // #10
					$parallax_image_background, // #11
					et_core_esc_previously( $data_background_layout ), // #12
					htmlspecialchars_decode( $search_types ) // #13
					

			);					
			
				return $dpts_search_output;
			}			
		}

		$dpts_divi_search = dpts_divi_search(  $placeholder , $search_types ); //Generating the module
 
		if ( ! empty( $dpts_divi_search ) ) {
			return sprintf(
			'<div class="et_pb_search">%2$s %1$s</div>'
			, $dpts_divi_search, $before_html );
		} else {
			return '';
		}
	}
}

new DPTS_DiviSearch;



	
	
	

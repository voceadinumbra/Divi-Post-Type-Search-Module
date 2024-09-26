<?php

class LWP_DiviBreadcrumbs extends ET_Builder_Module_Search {

	public $slug       = 'lwp_divi_breadcrumbs';
	public $vb_support = 'on';
	public $icon;

	 


	public function init() {
		$this->name = esc_html__( 'Breadcrumbs', 'lwp-divi-breadcrumbs' );
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

		$et_accent_color = et_builder_accent_color();

		$fields = array(
			'placeholder'        => array(
				'label'           => esc_html__( 'Input Placeholder', 'lwp-divi-breadcrumbs' ),
				'type'            => 'text',
				'description'     => esc_html__( 'Type the text you want to use as placeholder for the search field.', 'lwp-divi-breadcrumbs' ),
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
				'label'            => esc_html__( 'Include Post Types', 'et_builder' ),
				'type'             => 'multiple_checkboxes',
				'option_category'  => 'basic_option',
				'depends_show_if'  => 'off',
				'description'      => esc_html__( 'Select the post types that you would like to include in the search. If none are selected, all post types will be included in the search.', 'et_builder' ),
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
		 * JSWJ - POST TYPE SEARCH MODULE FOR DIVI
		 **/
		$include_posttypes         = $this->props['include_posttypes'];
		# Get Comma Separated Post Types To Search
		$index  = 0;
		$posttypes = array_keys( $this->get_posttypes_array() );
		foreach ( explode( '|', $include_posttypes ) as $checkbox_value ) {
				if ( 'off' === $checkbox_value ) { unset( $posttypes[$index] ); }
				$index++;
		}

 
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

		$breadcrumbs = lwp_get_hansel_and_gretel_breadcrumbs(  $placeholder); //Generating the Breadcrumbs

		if ( ! empty( $breadcrumbs ) ) {
			return sprintf(
			'<div class="lwp-breadcrumbs">%2$s %1$s</div>'
			, $breadcrumbs, $before_html );
		} else {
			return '';
		}
	}
}

new LWP_DiviBreadcrumbs;

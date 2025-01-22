<?php
/**
 * Post Type Search Module For Divi
 *
 * @package     post-type-search-module-for-divi
 * @author      Jerry Simmons <jerry@ferventsolutions.com>
 * @copyright   2021 Jerry Simmons
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name:  Post Type Search Module For Divi
 * Description:  Custom Module To Enable Users To Search Selected Post Types
 * Version:      1.2.1
 * Author:       Jerry Simmons <jerry@ferventsolutions.com>
 * Author URI:   https://ferventsolutions.com
 * Text Domain:  post-type-search-module-for-divi
 * License:      GPL-2.0+
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 **/

if ( ! defined( 'ABSPATH' ) ) { exit; }


add_action('et_builder_ready', 'jswj_custom_search_module_posttype');
add_action('et_builder_ready', 'jswj_custom_search_module_posttype_shortcode');
add_action('pre_get_posts', 'jswj_custom_search_module_posttype_filter', 1);


/**
 * Modify the search query if this search module is used
 **/
function jswj_custom_search_module_posttype_filter( $query ) {
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
} #END jswj_custom_search_module_posttype_filter()


/**
 * Register Custom Module Shortcode
 **/
function jswj_custom_search_module_posttype_shortcode() {
	$jswj_Custom_ET_Builder_Module_Search_posttype = new jswj_Custom_ET_Builder_Module_Search_posttype();
	add_shortcode(
		'et_pb_search_posttype',
		array($jswj_Custom_ET_Builder_Module_Search_posttype, '_render')
	);
}

/**
 * Force load Search module styles.
 *
 * @return array
 */
function jswj_load_search_assets( $modules ) {
	array_push( $modules, 'et_pb_search' );
	return $modules;
}
add_filter( 'et_required_module_assets', 'jswj_load_search_assets' );

/**
 * Force load Contact Form module styles.
 *
 * @return array
 */
function smpl_load_contact_form_assets( $modules ) {
	array_push( $modules, 'et_pb_search' );
	return $modules;
}
add_filter( 'et_required_module_assets', 'smpl_load_contact_form_assets' );

/**
 * Force load Contact Form module styles above the fold.
 *
 * @return array
 */
function smpl_load_contact_form_assets_atf( $atf_modules ) {
	array_push( $atf_modules, 'et_pb_search' );
	return $atf_modules;
}
add_filter( 'et_dynamic_assets_modules_atf', 'smpl_load_contact_form_assets_atf', 20 );

/**
 * Custom Module
 **/
function jswj_custom_search_module_posttype() {

	class jswj_Custom_ET_Builder_Module_Search_posttype extends ET_Builder_Module_Search {
		function init() {
			$this->name       = esc_html__( 'Post Type Search', 'et_builder' );
			$this->plural     = esc_html__( 'Post Type Searches', 'et_builder' );
			$this->slug       = 'et_pb_search_posttype';
			$this->vb_support = 'partial';

			$this->fields_defaults = array(
				'background_layout' => array( 'light' ),
				'text_orientation'  => array( 'left' ),
				'show_button'       => array( 'on' ),
			);

			$this->main_css_element = '%%order_class%%';

			$this->whitelisted_fields = array(
				'include_posttypes',
			);

			$this->settings_modal_toggles = array(
				'general'  => array(
					'toggles' => array(
						'main_content' => esc_html__( 'Text', 'et_builder' ),
						'elements'     => esc_html__( 'Elements', 'et_builder' ),
						'exceptions'   => esc_html__( 'Exceptions', 'et_builder' ),
						'background'   => esc_html__( 'Background', 'et_builder' ),
					),
				),
				'advanced' => array(
					'toggles' => array(
						'field' => esc_html__( 'Search Field', 'et_builder' ),
						'text'  => array(
							'title'    => esc_html__( 'Text', 'et_builder' ),
							'priority' => 49,
						),
						'width' => array(
							'title'    => esc_html__( 'Sizing', 'et_builder' ),
							'priority' => 65,
						),
					),
				),
			);

			$this->advanced_fields        = array(
				'fonts'          => array(
					'input' => array(
						'label'    => esc_html__( 'Input', 'et_builder' ),
						'css'      => array(
							'main'        => "{$this->main_css_element} input.et_pb_s",
							'placeholder' => true,
							'important'   => array( 'line-height', 'text-shadow' ),
						),
						'line_height'    => array(
							'default' => '1em',
						),
						'font_size'      => array(
							'default' => '14px',
						),
						'letter_spacing' => array(
							'default' => '0px',
						),
					),
					'button' => array(
						'label'           => et_builder_i18n( 'Button' ),
						'css'             => array(
							'main'      => "{$this->main_css_element} input.et_pb_searchsubmit",
							'important' => array( 'line-height', 'text-shadow' ),
						),
						'line_height'     => array(
							'default' => '1em',
						),
						'font_size'       => array(
							'default' => '14px',
						),
						'letter_spacing'  => array(
							'default' => '0px',
						),
						'hide_text_align' => true,
					),
				),
				'margin_padding' => array(
					'css'            => array(
						'padding'   => "{$this->main_css_element} input.et_pb_s,{$this->main_css_element} input.et_pb_searchsubmit",
						'important' => 'all',
					),
					'custom_padding' => array(
						'default' => '0.715em|0.715em|0.715em|0.715em|false|false',
					),
				),
				'background'     => array(
					'css' => array(
						'main' => "{$this->main_css_element} input.et_pb_s",
					),
				),
				'borders'        => array(
					'default' => array(
						'css'      => array(
							'main' => array(
								'border_radii'  => "{$this->main_css_element}.et_pb_search, {$this->main_css_element} input.et_pb_s",
								'border_styles' => "{$this->main_css_element}.et_pb_search",
							),
						),
						'defaults' => array(
							'border_radii'  => 'on|3px|3px|3px|3px',
							'border_styles' => array(
								'width' => '1px',
								'color' => '#dddddd',
								'style' => 'solid',
							),
						),
					),
				),
				'text'           => array(
					'use_background_layout' => true,
					'css'                   => array(
						'main'        => "{$this->main_css_element} input.et_pb_searchsubmit, {$this->main_css_element} input.et_pb_s",
						'text_shadow' => "{$this->main_css_element} input.et_pb_searchsubmit, {$this->main_css_element} input.et_pb_s",
					),
					'text_orientation'      => array(
						'exclude_options' => array( 'justified' ),
					),
					'options'               => array(
						'text_orientation'  => array(
							'default' => 'left',
						),
						'background_layout' => array(
							'default' => 'light',
							'hover'   => 'tabs',
						),
					),
				),
				'button'         => false,
				'link_options'   => false,
				'form_field'     => array(
					'form_field' => array(
						'label'          => esc_html__( 'Field', 'et_builder' ),
						'css'            => array(
							'main'        => '%%order_class%% form input.et_pb_s',
							'hover'       => '%%order_class%% form input.et_pb_s:hover',
							'focus'       => '%%order_class%% form input.et_pb_s:focus',
							'focus_hover' => '%%order_class%% form input.et_pb_s:focus:hover',
						),
						'placeholder'    => false,
						'margin_padding' => false,
						'box_shadow'     => false,
						'border_styles'  => false,
						'font_field'     => array(
							'css'            => array(
								'main'        => implode(
									', ',
									array(
										'%%order_class%% form input.et_pb_s',
										'%%order_class%% form input.et_pb_s::placeholder',
										'%%order_class%% form input.et_pb_s::-webkit-input-placeholder',
										'%%order_class%% form input.et_pb_s::-ms-input-placeholder',
										'%%order_class%% form input.et_pb_s::-moz-placeholder',
									)
								),
								'placeholder' => true,
								'important'   => array( 'line-height', 'text-shadow' ),
							),
							'line_height'    => array(
								'default' => '1em',
							),
							'font_size'      => array(
								'default' => '14px',
							),
							'letter_spacing' => array(
								'default' => '0px',
							),
						),
					),
				),
				'overflow'       => array(
					'default' => 'hidden',
				),
			);


			$this->custom_css_fields = array(
				'input_field' => array(
					'label'    => esc_html__( 'Input Field', 'et_builder' ),
					'selector' => 'input.et_pb_s',
				),
				'button'      => array(
					'label'    => et_builder_i18n( 'Button' ),
					'selector' => 'input.et_pb_searchsubmit',
				),
			);

			$this->help_videos = array(
				array(
					'id'   => 'HNmb20Mdvno',
					'name' => esc_html__( 'An introduction to the Search module', 'et_builder' ),
				),
			);
		}

		function get_posttypes_array() {
			$posttypes = get_post_types( array( 'exclude_from_search'	=> false ), 'objects' );
			unset( $posttypes['attachment'] );
			unset( $posttypes['revision'] );
			unset( $posttypes['nav_menu_item'] );
			unset( $posttypes['custom_css'] );
			unset( $posttypes['customize_changeset'] );
			unset( $posttypes['oembed_cache'] );
			unset( $posttypes['et_pb_layout'] );
			return $posttypes;
		}

		function get_fields() {

			$fields = array(
				'show_button'        => array(
					'label'           => esc_html__( 'Show Button', 'et_builder' ),
					'type'            => 'yes_no_button',
					'option_category' => 'configuration',
					'options'         => array(
						'on'  => et_builder_i18n( 'Yes' ),
						'off' => et_builder_i18n( 'No' ),
					),
					'default'         => 'on',
					'toggle_slug'     => 'elements',
					'description'     => esc_html__( 'Turn this on to show the Search button', 'et_builder' ),
					'mobile_options'  => true,
					'hover'           => 'tabs',
				),
				'placeholder'        => array(
					'label'           => esc_html__( 'Input Placeholder', 'et_builder' ),
					'type'            => 'text',
					'description'     => esc_html__( 'Type the text you want to use as placeholder for the search field.', 'et_builder' ),
					'toggle_slug'     => 'main_content',
					'dynamic_content' => 'text',
					'mobile_options'  => true,
					'hover'           => 'tabs',
				),
				'button_color'       => array(
					'label'          => esc_html__( 'Button and Border Color', 'et_builder' ),
					'type'           => 'color-alpha',
					'custom_color'   => true,
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'button',
					'hover'          => 'tabs',
					'mobile_options' => true,
					'sticky'         => true,
				),
				'placeholder_color'  => array(
					'label'          => esc_html__( 'Placeholder Color', 'et_builder' ),
					'description'    => esc_html__( 'Pick a color to be used for the placeholder written inside input fields.', 'et_builder' ),
					'type'           => 'color-alpha',
					'custom_color'   => true,
					'tab_slug'       => 'advanced',
					'toggle_slug'    => 'form_field',
					'hover'          => 'tabs',
					'mobile_options' => true,
					'sticky'         => true,
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

		public function get_transition_fields_css_props() {
			$fields = parent::get_transition_fields_css_props();

			$fields['button_color'] = array(
				'background-color' => '%%order_class%% input.et_pb_searchsubmit',
				'border-color'     => array(
					'%%order_class%% input.et_pb_searchsubmit',
					'%%order_class%% input.et_pb_s'
				),
			);

			$fields['placeholder_color'] = array(
				'color' => array(
					'%%order_class%% form input.et_pb_s::placeholder',
					'%%order_class%% form input.et_pb_s::-webkit-input-placeholder',
					'%%order_class%% form input.et_pb_s::-ms-input-placeholder',
					'%%order_class%% form input.et_pb_s::-moz-placeholder',
				),
			);

			return $fields;
		}

		/**
		 * JSWJ - POST TYPE SEARCH MODULE FOR DIVI
		 * Add et_pb_search to Divi's dynamic search assets
		 *
		 * Used with add_filter in the render() function
		 *
		 * @return array
		 **/
		public function jswj_ptsm_load_search_assets( $modules ) {
			array_push( $modules, 'et_pb_search' );
			return $modules;
		}

		public function render( $attrs, $content, $render_slug ) {
			$multi_view                = et_pb_multi_view_options( $this );
			$show_button               = $this->props['show_button'];

			/**
			 * JSWJ - POST TYPE SEARCH MODULE FOR DIVI
			 *
			 * Load Divi's dynamic search assets
			 **/
			add_filter( 'et_required_module_assets', [$this,'jswj_ptsm_load_search_assets'] );

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
			$input_line_height         = $this->props['form_field_line_height'];
			$video_background          = $this->video_background();
			$parallax_image_background = $this->get_parallax_image_background();

			$this->content = et_builder_replace_code_content_entities( $this->content );

			# Button Color.
			$this->generate_styles(
				array(
					'base_attr_name'                  => 'button_color',
					'selector'                        => '%%order_class%% input.et_pb_searchsubmit',
					'hover_pseudo_selector_location'  => 'suffix',
					'sticky_pseudo_selector_location' => 'prefix',
					'css_property'                    => array( 'background-color', 'border-color' ),
					'important'                       => true,
					'render_slug'                     => $render_slug,
					'type'                            => 'color',
				)
			);
			$this->generate_styles(
				array(
					'base_attr_name'                  => 'button_color',
					'selector'                        => '%%order_class%% input.et_pb_s',
					'hover_pseudo_selector_location'  => 'suffix',
					'sticky_pseudo_selector_location' => 'prefix',
					'css_property'                    => 'border-color',
					'important'                       => true,
					'render_slug'                     => $render_slug,
					'type'                            => 'color',
				)
			);

			# Placeholder Color.
			$placeholder_selectors = array(
				'%%order_class%% form input.et_pb_s::-webkit-input-placeholder',
				'%%order_class%% form input.et_pb_s::-moz-placeholder',
				'%%order_class%% form input.et_pb_s:-ms-input-placeholder',
			);

			$this->generate_styles(
				array(
					'base_attr_name'                  => 'placeholder_color',
					'selector'                        => join( ', ', $placeholder_selectors ),
					'hover_pseudo_selector_location'  => 'suffix',
					'sticky_pseudo_selector_location' => 'prefix',
					'css_property'                    => 'color',
					'important'                       => true,
					'render_slug'                     => $render_slug,
					'type'                            => 'color',
				)
			);

			if ( '' !== $input_line_height ) {
				$el_style = array(
					'selector'    => '%%order_class%% input.et_pb_s',
					'declaration' => 'height: auto; min-height: 0;',
				);
				ET_Builder_Element::set_style( $render_slug, $el_style );
			}

			# Module classnames

			/**
			 * JSWJ - POST TYPE SEARCH MODULE FOR DIVI
			 *
			 * Add class name to module div to apply built in styling
			 **/
			$this->add_classname( 'et_pb_search' );

			$this->add_classname(
				array(
					$this->get_text_orientation_classname( true ),
				)
			);

			# Background layout class names.
			$background_layout_class_names = et_pb_background_layout_options()->get_background_layout_class( $this->props );
			$this->add_classname( $background_layout_class_names );

			if ( 'on' !== $show_button ) {
				$this->add_classname( 'et_pb_hide_search_button' );
			}

			# Background layout data attributes.
			$data_background_layout = et_pb_background_layout_options()->get_background_layout_attrs( $this->props );

			$multi_view_show_button_data_attr = $multi_view->render_attrs(
				array(
					'classes' => array(
						'et_pb_hide_search_button' => array(
							'show_button' => 'off',
						),
					),
				)
			);

			$output = sprintf(
				'<div%3$s class="%2$s"%12$s%13$s>
				%11$s
				%10$s
				<form role="search" method="get" class="et_pb_searchform" action="%1$s">
					<div>
						<label class="screen-reader-text" for="s">%8$s</label>
						%7$s
						<!-- JSWJ - POST TYPE SEARCH MODULE FOR DIVI -->
						<input type="hidden" name="posttype_search" value="%14$s" />
						%4$s
						%5$s
						%6$s
						<input type="submit" value="%9$s" class="et_pb_searchsubmit">
					</div>
				</form>
			</div>',
				esc_url( home_url( '/' ) ),
				$this->module_classname( $render_slug ),
				$this->module_id(),
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

			return $output;
		}

	}
} # END jswj_custom_search_module_posttype()
<?php
/**
 * CCTM_relationmeta
 *
 * Implements a special AJAX form element used to store a wp_posts.ID representing
 * another post of some kind
 *
 * @package CCTM_FormElement
 */


class CCTM_relationmeta extends CCTM_FormElement
{
	public $props = array(
		'label' => '',
		'button_label' => '',
		'name' => '',
		'description' => '',
		'class' => '',
		'extra' => '',
		'is_repeatable' => '',
		'required' => '',
		'default_value' => '',
		'search_parameters' => '',
		'output_filter' => '',
		'metafields' => array()
		// 'type' => '', // auto-populated: the name of the class, minus the CCTM_ prefix.
	);


	//------------------------------------------------------------------------------
	//! Public Functions
	//------------------------------------------------------------------------------
	/**
	 * Thickbox support
	 *
	 * @param unknown $fieldlist (optional)
	 */
	public function admin_init($fieldlist=array()) {
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_register_script('cctm_relation', CCTM_URL.'/js/relation.js', array('jquery', 'media-upload', 'thickbox'));
		wp_enqueue_script('cctm_relation');

		// Bit of a wormhole here: Load up the children's req's, organize into fieldtypes
		$fieldtypes = array();
		foreach ($fieldlist as $f) {
			$metafields = CCTM::get_value(CCTM::$data['custom_field_defs'][$f], 'metafields');
			foreach ($metafields as $mf) {
				$type = CCTM::get_value(CCTM::$data['custom_field_defs'][$mf], 'type');
				$fieldtypes[$type][] = $mf;
			}
		}
		foreach ($fieldtypes as $ft => $list) {
			if ($FieldObj = CCTM::load_object($ft, 'fields')) {
				$FieldObj->admin_init($list);
			}
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * Get the standard fields
	 *
	 * @param array   current def
	 * @param unknown $def
	 * @param unknown $show_repeatable (optional)
	 * @return strin HTML
	 */
	public function format_standard_fields($def, $show_repeatable=true) {
		$is_checked = '';
		if (isset($def['is_repeatable']) && $def['is_repeatable'] == 1) {
			$is_checked = 'checked="checked"';
		}

		$out = '<div class="postbox">
			<div class="handlediv" title="Click to toggle"><br /></div>
			<h3 class="hndle"><span>'. __('Standard Fields', CCTM_TXTDOMAIN).'</span></h3>
			<div class="inside">';

		// Label
		$out .= '<div class="'.self::wrapper_css_class .'" id="label_wrapper">
			 		<label for="label" class="'.self::label_css_class.'">'
			.__('Label', CCTM_TXTDOMAIN).'</label>
			 		<input type="text" name="label" class="'.self::css_class_prefix.'text" id="label" value="'.htmlspecialchars($def['label']) .'"/>
			 		' . $this->get_translation('label').'
			 	</div>';
		// Name
		$out .= '<div class="'.self::wrapper_css_class .'" id="name_wrapper">
				 <label for="name" class="cctm_label cctm_text_label" id="name_label">'
			. __('Name', CCTM_TXTDOMAIN) .
			'</label>
				 <input type="text" name="name" class="cctm_text" id="name" value="'.htmlspecialchars($def['name']) .'"/>'
			. $this->get_translation('name') .'
			 	</div>';

		// Default Value
		$out .= '<div class="'.self::wrapper_css_class .'" id="default_value_wrapper">
			 	<label for="default_value" class="cctm_label cctm_text_label" id="default_value_label">'
			.__('Default Value', CCTM_TXTDOMAIN) .'</label>
			 		<input type="text" name="default_value" class="cctm_text" id="default_value" value="'. htmlspecialchars($def['default_value'])
			.'"/>
			 	' . $this->get_translation('default_value') .'
			 	</div>';

		// Extra
		$out .= '<div class="'.self::wrapper_css_class .'" id="extra_wrapper">
			 		<label for="extra" class="'.self::label_css_class.'">'
			.__('Extra', CCTM_TXTDOMAIN) .'</label>
			 		<input type="text" name="extra" class="cctm_text" id="extra" value="'
			.htmlspecialchars($def['extra']).'"/>
			 	' . $this->get_translation('extra').'
			 	</div>';

		// Class
		$out .= '<div class="'.self::wrapper_css_class .'" id="class_wrapper">
			 	<label for="class" class="'.self::label_css_class.'">'
			.__('Class', CCTM_TXTDOMAIN) .'</label>
			 		<input type="text" name="class" class="cctm_text" id="class" value="'
			.htmlspecialchars($def['class']).'"/>
			 	' . $this->get_translation('class').'
			 	</div>';

		if ($show_repeatable) {
			// Is Repeatable?
			$out .= '<div class="'.self::wrapper_css_class .'" id="is_repeatable_wrapper">
					 <label for="is_repeatable" class="cctm_label cctm_checkbox_label" id="is_repeatable_label">'
				. __('Is Repeatable?', CCTM_TXTDOMAIN) .
				'</label>
					 <br />
					 <input type="checkbox" name="is_repeatable" class="cctm_checkbox" id="is_repeatable" value="1" '. $is_checked.'/> <span>'.$this->descriptions['is_repeatable'].'</span>
				 	</div>';
		}

		// Description
		$out .= '<div class="'.self::wrapper_css_class .'" id="description_wrapper">
			 	<label for="description" class="'.self::label_css_class.'">'
			.__('Description', CCTM_TXTDOMAIN) .'</label>
			 	<textarea name="description" class="cctm_textarea" id="description" rows="5" cols="60">'. htmlspecialchars($def['description']).'</textarea>
			 	' . $this->get_translation('description').'
			 	</div>';

		$out .= '</div><!-- /inside -->
			</div><!-- /postbox -->';

		return $out;
	}


	//------------------------------------------------------------------------------
	/**
	 * This function provides a name for this type of field. This should return plain
	 * text (no HTML). The returned value should be localized using the __() function.
	 *
	 * @return string
	 */
	public function get_name() {
		return __('Relation-Meta (EXPERIMENTAL)', CCTM_TXTDOMAIN);
	}


	//------------------------------------------------------------------------------
	/**
	 * This function gives a description of this type of field so users will know
	 * whether or not they want to add this type of field to their custom content
	 * type. The returned value should be localized using the __() function.
	 *
	 * @return string text description
	 */
	public function get_description() {
		return __('Relation-Meta fields are advanced fields that allow you to add meta data to a selected relation, allowing you to create complex data models.  You can select a related page, for example, and then add a page-rank to that relation.', CCTM_TXTDOMAIN);
	}


	//------------------------------------------------------------------------------
	/**
	 * This function should return the URL where users can read more information about
	 * the type of field that they want to add to their post_type. The string may
	 * be localized using __() if necessary (e.g. for language-specific pages)
	 *
	 * @return string  e.g. http://www.yoursite.com/some/page.html
	 */
	public function get_url() {
		return 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/RelationMeta';
	}


	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param mixed   $current_value current value for this field (an integer ID).
	 * @return string
	 */
	public function get_edit_field_instance($current_value) {

		require_once CCTM_PATH.'/includes/GetPostsQuery.php';

		$Q = new GetPostsQuery();

		// Populate the values (i.e. properties) of this field
		$this->id      = str_replace(array('[', ']', ' '), '_', $this->name);
		$this->content  = '';

		if (empty($this->button_label)) {
			$this->button_label = __('Choose Relation', CCTM_TXTDOMAIN);
		}

		$this->post_id = $this->value;

		$fieldtpl = '';
		$wrappertpl = '';
        $relationmeta_tpl = CCTM::load_tpl(
            array('fields/options/'.$this->name.'.tpl'
                , 'fields/options/_relationmeta.tpl'
            )
        );

		// Multi field?
		if ($this->is_repeatable) {
			$fieldtpl = CCTM::load_tpl(
				array('fields/elements/'.$this->name.'.tpl'
					, 'fields/elements/_relationmeta_multi.tpl'
				)
			);

			$wrappertpl = CCTM::load_tpl(
				array('fields/wrappers/'.$this->name.'.tpl'
					, 'fields/wrappers/_relation_multi.tpl'
				)
			);
		}
		// Regular old Single-selection
		else {

			$fieldtpl = CCTM::load_tpl(
				array('fields/elements/'.$this->name.'.tpl'
					, 'fields/elements/_relationmeta.tpl'
				)
			);

			$wrappertpl = CCTM::load_tpl(
				array('fields/wrappers/'.$this->name.'.tpl'
					, 'fields/wrappers/_relation.tpl'
				)
			);
        }
        
        $data = $this->get_value($current_value, 'ignored');

		if ($data) {
			foreach ($data as $post_id => $metafields) {
				// Look up all the data on those foriegn keys
				// We gotta watch out: what if the related post has custom fields like "description" or
				// anything that would conflict with the definition?
				$post = $Q->get_post($post_id);
				$this->thumbnail_url = CCTM::get_thumbnail($post_id);
				if (empty($post)) {
					$this->content = '<div class="cctm_error"><p>'.sprintf(__('Post %s not found.', CCTM_TXTDOMAIN), $post_id).'</p></div>';
				}
				else {
					foreach ($post as $k => $v) {
						// Don't override the def's attributes!
						if (!isset($this->$k)) {
							$this->$k = $v;
						}
					}
					$this->post_id = $post_id;

					// Look up data for each of the metafields
					$content = '';
					foreach ($metafields as $mf => $v) {
						if (!isset(CCTM::$data['custom_field_defs'][$mf])) {
							continue;
						}
						$d = CCTM::$data['custom_field_defs'][$mf];
						if (!$FieldObj = CCTM::load_object($d['type'], 'fields')) {
							continue;
						}
						$d['name'] = $this->name.'['.$post_id.']['.$d['name'].']';
						$d['value'] = $v;
						$d['is_repeatable'] = false; // override
						$FieldObj->set_props($d);
						$output_this_field = $FieldObj->get_edit_field_instance($v);
						$content .= CCTM::parse($relationmeta_tpl, array('content'=>$output_this_field));
					}
					$this->set_prop('metafields', $content);
					$this->content .= CCTM::parse($fieldtpl, $this->get_props());
				}
			}  // endforeach
		} // end $data

		if (empty($this->button_label)) {
			$this->button_label = __('Choose Relation', CCTM_TXTDOMAIN);
		}

		return CCTM::parse($wrappertpl, $this->get_props());
	}


	//------------------------------------------------------------------------------
	/**
	 * This should return (not print) form elements that handle all the controls required to define this
	 * type of field.  The default properties correspond to this class's public variables,
	 * e.g. name, label, etc. The form elements you create should have names that correspond
	 * with the public $props variable. A populated array of $props will be stored alongside
	 * the custom-field data for the containing post-type.
	 *
	 * @param array   $def
	 * @return string HTML input fields
	 */
	public function get_edit_field_definition($def) {

		// Used to fetch the default value.
		require_once CCTM_PATH.'/includes/GetPostsQuery.php';

		// So we can arrange the metafields
		$out = '<script>
          jQuery(function() {
            jQuery( "#sortable" ).sortable();
            jQuery( "#sortable" ).disableSelection();
          });
          </script>';

		// Standard
		$out .= $this->format_standard_fields($def);

		// Options
		$Q = new GetPostsQuery();

		$out .= '
			<div class="postbox">
				<div class="handlediv" title="Click to toggle"><br /></div>
				<h3 class="hndle"><span>'. __('Options', CCTM_TXTDOMAIN).'</span></h3>
				<div class="inside">';

		// Note fieldtype: used to set the default value on new fields
		$out .= '<input type="hidden" id="fieldtype" value="image" />';

		// Initialize / defaults
		$preview_html = '';
		$click_label = __('Choose Relation');
		$label = __('Default Value', CCTM_TXTDOMAIN);
		$remove_label = __('Remove');


		// Handle the display of the default value
		if ( !empty($def['default_value']) ) {

			$hash = CCTM::get_thumbnail($def['default_value']);

			$fieldtpl = CCTM::load_tpl(
				array('fields/elements/'.$this->name.'.tpl'
					, 'fields/elements/_'.$this->type.'.tpl'
					, 'fields/elements/_relation.tpl'
				)
			);
			$preview_html = CCTM::parse($fieldtpl, $hash);
		}

		// Button Label
		$out .= '<div class="'.self::wrapper_css_class .'" id="button_label_wrapper">
			 		<label for="button_label" class="'.self::label_css_class.'">'
			.__('Button Label', CCTM_TXTDOMAIN).'</label>
			 		<input type="text" name="button_label" class="'.self::css_class_prefix.'text" id="button_label" value="'.htmlspecialchars($def['button_label']) .'"/>
			 		' . $this->get_translation('button_label').'
			 	</div>';

		// Set Search Parameters
		$seach_parameters_str = '';
		if (isset($def['search_parameters'])) {
			$search_parameters_str = $def['search_parameters'];
		}
		$search_parameters_visible = $this->_get_search_parameters_visible($seach_parameters_str);


		$out .= '
			<div class="cctm_element_wrapper" id="search_parameters_wrapper">
				<label for="name" class="cctm_label cctm_text_label" id="search_parameters_label">'
			. __('Search Parameters', CCTM_TXTDOMAIN) .
			'</label>
				<span class="cctm_description">'.__('Define which posts are available for selection by narrowing your search parameters.', CCTM_TXTDOMAIN).'</span>
				<br/>
				<span class="button" onclick="javascript:search_form_display(\''.$def['name'].'\',\''.$def['type'].'\');">'.__('Set Search Parameters', CCTM_TXTDOMAIN) .'</span>
				<div id="cctm_thickbox"></div>
				<span id="search_parameters_visible">'.
			$search_parameters_visible
			.'</span>
				<input type="hidden" id="search_parameters" name="search_parameters" value="'.$search_parameters_str.'" />
				<br/>
			</div>';

		$out .= '</div><!-- /inside -->
			</div><!-- /postbox -->';

		// Validations / Required
		$out .= $this->format_validators($def, false);

		$defs = CCTM::get_custom_field_defs();
		$li = '<li><input type="checkbox"
			     name="metafields[]" class="cctm_checkbox" id="metafield_%s" value="%s"%s/>
			 <label for="metafield_%s"><strong>%s</strong> (%s)</label>
			 </li>';
		//$out .= '<pre>'.print_r($defs,true).'</pre>';
		$out .= '<div class="postbox">
			<div class="handlediv" title="Click to toggle"><br /></div>
			<h3 class="hndle"><span>'. __('Meta Fields', CCTM_TXTDOMAIN).'</span></h3>
			<div class="inside">
                <p>'.__('Select which fields should appear as meta data for this relation.', CCTM_TXTDOMAIN).'</p>
                <ul id="sortable">';
		// First show the ones already assigned here
		foreach ($this->props['metafields'] as $fieldname) {
			$out .= sprintf($li, $fieldname, $fieldname, ' checked="checked"', $fieldname, $defs[$fieldname]['label'], $fieldname);
		}
		// Grab all the others
		foreach ($defs as $fieldname => $d) {
			if ($d['type'] == 'relationmeta' || in_array($fieldname, $this->props['metafields'])) {
				continue;
			}
			$out .= sprintf($li, $fieldname, $fieldname, '', $fieldname, $d['label'], $fieldname);
		}
		$out .= '</ul>
            </div><!-- /inside -->
		</div><!-- /postbox -->';

		// Output Filter
		$out .= $this->format_available_output_filters($def);

		return $out;
	}


	//------------------------------------------------------------------------------
	/**
	 * Options here are any search criteria
	 *
	 * @return unknown
	 */
	public function get_options_desc() {
		return $this->_get_search_parameters_visible($this->props['search_parameters']);
	}


	/**
	 * RelationMeta data is ALWAYS stored as JSON: it's a complex data structure.
	 *
	 * @param string  $str
	 * @param string  $conversion (optional) to_string|to_array (ignored)
	 * @return mixed (a string or an array, depending on the $conversion)
	 */
	public function get_value($str, $conversion='to_array') {
		if (empty($str) || $str=='[""]') {
			return array();
		}

		$out = (array) json_decode($str, true );
		// the $str was not JSON encoded
		if (empty($out)) {
			return array($str);
		}
		else {
			return $out;
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * We are always storing a data object in this case.
	 *
	 * @param mixed   $posted_data $_POST data
	 * @param string  $field_name: the unique name for this instance of the field
	 * @return string whatever value you want to store in the wp_postmeta table where meta_key = $field_name
	 */
	public function save_post_filter($posted_data, $field_name) {
		if ( isset($posted_data[ CCTM_FormElement::post_name_prefix . $field_name ]) ) {
			return addslashes(json_encode($posted_data[ CCTM_FormElement::post_name_prefix . $field_name ]));
		}
		else {
			return '';
		}
	}


}


/*EOF*/
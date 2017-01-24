<?php
/**
 * This file describes hooks provided by entityconnect.
 */


/**
 * hook_entityconnect_exclude_forms_alter().
 *
 * Allow modules to alter the list of exclude forms.
 * If you don't want a specific forms to be proceeded, or if Entityconnect affects
 *
 * @param $exclude_forms
 * @return array of forms that not be proceeded.
 *
 * @see entityconnect_child_form_alter()
 */
function hook_entityconnect_exclude_forms_alter(&$exclude_forms) {
  $exclude_forms = array(
    'search_block_form',
    'page_node_form'
  );
}

/**
 * hook_entityconnect_ref_fields_alter().
 *
 * Allow modules to add fields as able to be connect.
 * That will display the "add" and "edit" buttons.
 *
 * Only entity_reference fields are supported at this time.
 *
 * @param $ref_fields
 */
function hook_entityconnect_ref_fields_alter(&$ref_fields) {
}

/**
 * hook_entityconnect_return_form_alter().
 *
 * Allow modules to specify returned values by widget.
 *
 * @param $data
 *
 *  $data = array(
 *    'data' => $data,
 *    'widget_containter' => $widget_container,
 *    'widget_container_type' => $widget_container_type,
 *    'field_info' => $field_info,
 *    'element_value' => NULL
 *    );
 *
 * @return mixed $data['element_value'] need to be set.
 *
 */
function hook_entityconnect_return_form_alter(&$data) {
  /**
   * $data['data'] : The cached data.
   * $data['element_value'] : Defaut value to apply on field.
   */
}

/**
 * hook_entityconnect_child_form_alter().
 *
 * @param $data
 */
function hook_entityconnect_child_form_alter(&$data) {}

/**
 * hook_entityconnect_child_form_submit_alter().
 *
 * @param $data
 */
function hook_entityconnect_child_form_submit_alter(&$data) {}

/**
 * hook_entityconnect_add_alter().
 *
 * @param $output
 */
function hook_entityconnect_add_alter(&$output) {}

/**
 * hook_entityconnect_edit_alter().
 *
 * @param $output
 */
function hook_entityconnect_edit_alter(&$output) {}

/**
 * hook_entityconnect_add_info().
 *
 * @param $cache_id
 */
function hook_entityconnect_add_info($cache_id, $entity_type, $acceptable_types) {}

/**
 * hook_entityconnect_add_info_alter().
 *
 * @param $add_info
 * @param $cache_id
 */
function hook_entityconnect_add_info_alter(&$info, &$context) {}

/**
 * hook_entityconnect_edit_info().
 *
 * @param $cache_id
 */
function hook_entityconnect_edit_info($cache_id, $entity_type, $target_id) {}

/**
 * hook_entityconnect_edit_info_alter().
 *
 * @param $add_info
 * @param $cache_id
 */
function hook_entityconnect_edit_info_alter(&$add_info, &$cache_id, &$entity_type, &$target_id) {}

/**
 * hook_entityconnect_field_attach_form_alter().
 *
 * @param $data
 */
function hook_entityconnect_field_attach_form_alter(&$data) {}

/**
 * hook_entityconnect_add_edit_button_submit_alter().
 *
 * @param $data
 */
function hook_entityconnect_add_edit_button_submit_alter(&$data) {}

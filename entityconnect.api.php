<?php
/**
 * That file describes hooks provided by entityconnect.
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
 * Following example shows how to add buttons for bean entity.
 *
 * @param $ref_fields
 */
function hook_entityconnect_ref_fields_alter(&$ref_fields) {
  // We are parsing all fields.
  foreach (field_info_fields() as $id => $field) {
    // We want to be sure that bean fields are provided by entityreference module.
    if ($field['type'] == 'entityreference' && $field['module'] == 'entityreference') {
      $entity_reference_info = entityreference_get_selection_handler($field);
      $entity_type = $entity_reference_info->field['settings']['target_type'];
      // Check if the module is enabled.
      if (module_exists('bean_admin_ui')) {
        $entity_info = entity_get_info($entity_type);
        if (!empty($entity_info['module']) && $entity_info['module'] == 'bean') {
          // Check user access
          if (user_access('entityconnect add button') || user_access('entityconnect edit button')) {
            // Add field to the list.
            $ref_fields[$id] = $field;
          }
        }
      }
    }
  }
}

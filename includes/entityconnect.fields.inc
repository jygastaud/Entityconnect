<?php

/**
 * Add settings to an instance field settings form.
 *
 * Invoked from field_ui_field_edit_form() to allow the module defining the
 * field to add settings for a field instance.
 *
 * @return array
 *   The form definition for the field instance settings.
 */
function entityconnect_field_instance_settings_form($field, $instance) {
  $form = array();
  $settings = $instance;

  // Retrieve default values to apply for settings.
  $defaults = array(
      'buttons' => array(
          'button_add' => !isset($settings['entityconnect']['buttons']['button_add']) ? variable_get('entityconnect_unload_add_default', 1) : $settings['entityconnect']['buttons']['button_add'],
          'button_edit' => !isset($settings['entityconnect']['buttons']['button_edit']) ? variable_get('entityconnect_unload_edit_default', 1) : $settings['entityconnect']['buttons']['button_edit'],
      ),
      'icons' => array(
          'icon_add' => !isset($settings['entityconnect']['icons']['icon_add']) ? variable_get('entityconnect_show_add_icon_default', 0) : $settings['entityconnect']['icons']['icon_add'],
          'icon_edit' => !isset($settings['entityconnect']['icons']['icon_edit']) ? variable_get('entityconnect_show_edit_icon_default', 0) : $settings['entityconnect']['icons']['icon_edit'],
      ),
  );

  Drupal\entityconnect\Form\AdministrationForm::attach($form, $defaults);

  return $form;
}

/**
 * Implements hook_FORM_ID_form_alter().
 *
 * @param $form
 * @param $form_state
 * @param $form_id
 */
function entityconnect_form_field_ui_field_edit_form_alter(&$form, &$form_state, $form_id) {

  $field_types = Drupal\entityconnect\Entityconnect::getReferencesFieldTypeList();

  // Use to add choice field.
  if (in_array($form['#field']['type'], $field_types)) {
    $instance = $form['#instance'];
    $field = $form['#field'];
    $additions = module_invoke('entityconnect', 'field_instance_settings_form', $field, $instance);
    if (is_array($additions) && isset($form['instance'])) {
      $form['instance'] += $additions;
    }
  }
}

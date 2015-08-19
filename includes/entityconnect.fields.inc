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
  $settings = $instance;

  // Add choice for user to not load entity connect "add" button
  // on the field.
  $form['entityconnect_unload_add'] = array(
    '#type' => 'radios',
    '#title' => t('Display Entity Connect "add" button.'),
    '#default_value' => !isset($settings['entityconnect_unload_add']) ? variable_get('entityconnect_unload_add_default', 1) : $settings['entityconnect_unload_add'],
    '#description' => t('Choose "No" if you want to unload "add" button for the field'),
    '#options' => array(
      '0' => t('Yes'),
      '1' => t('No'),
    ),
    '#weight' => 1,
  );

  // Add choice for user to not load entity connect "edit" button
  // on the field.
  $form['entityconnect_unload_edit'] = array(
    '#type' => 'radios',
    '#title' => t('Display Entity Connect "edit" button.'),
    '#default_value' => !isset($settings['entityconnect_unload_edit']) ? variable_get('entityconnect_unload_edit_default', 1) : $settings['entityconnect_unload_edit'],
    '#description' => t('Choose "No" if you want to unload "edit" button for the field'),
    '#options' => array(
      '0' => t('Yes'),
      '1' => t('No'),
    ),
    '#weight' => 1,
  );

  $form['entityconnect_icon_add'] = array(
    '#required' => '1',
    '#key_type_toggled' => '1',
    '#default_value' => !isset($settings['entityconnect_show_add_icon']) ? variable_get('entityconnect_show_add_icon_default', 0) : $settings['entityconnect_show_add_icon'],
    '#description' => t('Default: "Icon only"<br />
                         Choose "Icon + Text" if you want to see the edit (pencil) icon + the text displayed by default.<br />
                         Choose "Text only" if you don\'t want to see the edit (pencil) icon displayed by default.<br />
                         Each field can override this value.'),
    '#weight' => '2',
    '#type' => 'radios',
    '#options' => array(
      '0' => t('Icon only'),
      '1' => t('Icon + Text'),
      '2' => t('Text only')
    ),
    '#title' => t('Default Entity Connect "add (+) icon" display'),
  );

  $form['entityconnect_icon_edit'] = array(
    '#required' => '1',
    '#key_type_toggled' => '1',
    '#default_value' => !isset($settings['entityconnect_show_edit_icon']) ? variable_get('entityconnect_show_edit_icon_default', 0) : $settings['entityconnect_show_edit_icon'],
    '#description' => t('Default: "Icon only"<br />
                         Choose "Icon + Text" if you want to see the edit (pencil) icon + the text displayed by default.<br />
                         Choose "Text only" if you don\'t want to see the edit (pencil) icon displayed by default.<br />
                         Each field can override this value.'),
    '#weight' => '3',
    '#type' => 'radios',
    '#options' => array(
      '0' => t('Icon only'),
      '1' => t('Icon + Text'),
      '2' => t('Text only')
    ),
    '#title' => t('Default Entity Connect "edit (pencil) icon" display'),
  );


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

  $modules_list = array(
    'entityreference',
    'node_reference',
    'user_reference'
  );

  // Use to add choice field.
  if (in_array($form['#field']['type'], $modules_list)) {
    $instance = $form['#instance'];
    $field = $form['#field'];
    $additions = module_invoke('entityconnect', 'field_instance_settings_form', $field, $instance);
    if (is_array($additions) && isset($form['instance'])) {
      $form['instance'] += $additions;
    }
  }
}

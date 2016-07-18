<?php
/**
 * @file
 * Handles all form alters and submit functions for entityconnect.
 */

/**
 * Defines the settings form.
 */
function _entityconnect_admin_form($form, &$form_state) {
  $form = array();

  $defaults = array(
    'buttons' => array(
      'button_add' => variable_get('entityconnect_unload_add_default', 1),
      'button_edit' => variable_get('entityconnect_unload_edit_default', 1),
    ),
    'icons' => array(
      'icon_add' => variable_get('entityconnect_show_add_icon_default', 0),
      'icon_edit' => variable_get('entityconnect_show_edit_icon_default', 0),
    ),
  );

  new Drupal\entityconnect\Form\AdministrationForm($form, $defaults);

  $form['entityconnect']['cache_lifetime'] = array(
    '#default_value' => variable_get('entityconnect_cache_lifetime', CACHE_PERMANENT),
    '#description' => t('Set cache lifetime in second.<br />
                        Set to 0 to CACHE_PERMANENT.'),
    '#element_validate' => array('element_validate_integer'),
    '#weight' => '0',
    '#type' => 'textfield',
    '#title' => t('Entity Connect cache lifetime'),
  );

  return $form;
}

/**
 * The settings form submit.
 */
function _entityconnect_admin_form_submit($form, &$form_state) {
  if ($form_state['clicked_button']['#id'] == 'edit-reset') {
    variable_set('entityconnect_cache_lifetime', CACHE_PERMANENT);
    variable_set('entityconnect_unload_add_default', 1);
    variable_set('entityconnect_unload_edit_default', 1);
    variable_set('entityconnect_show_add_icon_default', 0);
    variable_set('entityconnect_show_edit_icon_default', 0);
    drupal_set_message(t('The settings were reset to default.'));
  }
  else {
    dpm($form_state);
    variable_set('entityconnect_cache_lifetime', $form_state['values']['entityconnect']['cache_lifetime']);
    variable_set('entityconnect_unload_add_default', $form_state['values']['entityconnect']['buttons']['button_add']);
    variable_set('entityconnect_unload_edit_default', $form_state['values']['entityconnect']['buttons']['button_edit']);
    variable_set('entityconnect_show_add_icon_default', $form_state['values']['entityconnect']['icons']['icon_add']);
    variable_set('entityconnect_show_edit_icon_default', $form_state['values']['entityconnect']['icons']['icon_edit']);
    drupal_set_message(t('The settings were saved.'));
  }
}

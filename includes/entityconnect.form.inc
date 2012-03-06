<?php
/**
 * @file
 * Handles all form alters and submit functions for entityconnect.
 */

/**
 * Implements of hook_field_attach_form().
 *
 * Here we attach a submit button for each node ref field.
 */
function _entityconnect_add_field_attach_form($entity_type, $entity, &$form, &$form_state, $field_name, $field, $langcode = LANGUAGE_NONE) {
  $language = $form[$field_name]['#language'];
  foreach ($form[$field_name][$language] as $key => $field) {
    if (is_numeric($key)) {
      // We have to add some extra js for single value fields so we add a class
      // to let the js know.
      $extra = $form[$field_name][$language]['#cardinality'] == 1 ? 'single-value' : '';
      $form[$field_name][$language][$key]["add_entityconnect__{$field_name}_$key"] = array(
        '#type' => 'submit',
        '#limit_validation_errors' => array(array($field_name)),
        '#value' => t('New Content'),
        '#name' => "add_entityconnect__{$field_name}_$key",
        '#prefix' => "<div class = 'entityconnect-add $extra'>",
        '#suffix' => '</div>',
        '#key' => $key,
        '#field' => $field_name,
        '#add_child' => TRUE,
        '#language' => $language,
        '#submit' => array('entityconnect_include_form', 'entityconnect_add_edit_button_submit'),
        '#weight' => -2,
        '#attached' => array(
          'js' => array(
            drupal_get_path('module', 'entityconnect') . "/theme/js/entityconnect.js",
          ),
          'css' => array(
            drupal_get_path('module', 'entityconnect') . "/theme/css/entityconnect.css",
          ),
        ),
      );
    }// if()
  }// foreach()
}

/**
 * Implements of hook_field_attach_form().
 *
 * Here we attach a submit button for each node ref field.
 */
function _entityconnect_edit_field_attach_form($entity_type, $entity, &$form, &$form_state, $field_name, $field, $langcode = LANGUAGE_NONE) {
  $language = $form[$field_name]['#language'];
  foreach ($form[$field_name][$language] as $key => $field) {
    if (is_numeric($key)) {
      // We have to add some extra js for single value fields so we add a class
      // to let the js know.
      $extra = $form[$field_name][$language]['#cardinality'] == 1 ? 'single-value' : '';
      $form[$field_name][$language][$key]["edit_entityconnect__{$field_name}_$key"] = array(
        '#type' => 'submit',
        '#limit_validation_errors' => array(array($field_name)),
        '#value' => t('Edit Content'),
        '#name' => "edit_entityconnect__{$field_name}_$key",
        '#prefix' => "<div class = 'entityconnect-edit $extra'>",
        '#suffix' => '</div>',
        '#key' => $key,
        '#field' => $field_name,
        '#add_child' => FALSE,
        '#language' => 'und',
        '#submit' => array('entityconnect_include_form', 'entityconnect_add_edit_button_submit'),
        '#weight' => -2,
        '#attached' => array(
          'js' => array(
            drupal_get_path('module', 'entityconnect') . "/theme/js/entityconnect.js",
          ),
          'css' => array(
            drupal_get_path('module', 'entityconnect') . "/theme/css/entityconnect.css",
          ),
        ),
      );
    }// if()
  }// foreach()
}

/**
 * entityconnect_add_edit_button_submit() is called when a new node is to be
 * added.
 *
 * We cache the current state and form
 * and redirect to the entityconnect_add page with an append build_cached_id.
 */
function entityconnect_add_edit_button_submit(&$form, &$form_state) {
  $cache_id = "entityconnect-" . $form['#build_id'];
  $field = $form_state['clicked_button']['#field'];
  $key   = $form_state['clicked_button']['#key'];
  $language = $form[$field]['#language'];
  $data = array(
    'form'       => $form,
    'form_state' => $form_state,
    'dest'       => $_GET['q'],
    'field'      => $field,
    'key'        => $key,
    'add_child'  => $form_state['clicked_button']['#add_child'],
    'target_id'  => $form_state['values'][$field][$language][$key]['target_id'],
  );

  entityconnect_cache_set($cache_id, $data);

  unset($_GET['destination']);
  if ($data['add_child']) {
    $form_state['redirect'] = "admin/entityconnect/add/$cache_id";
  }
  else {
    if ($data['target_id']) {
      $form_state['redirect'] = "admin/entityconnect/edit/$cache_id";
    }// if()
  }// else()
}

/**
 * entityconnect_child_form_submit() is for submiting of are child create form
 * on submiting of a child form we set.
 *
 * Set the nid in the cache entry
 * the rediect to our redirect page.
 */
function entityconnect_child_form_submit(&$form, &$form_state) {
  $cache_id = $form_state['values']['parent_build_cache_id'];
  if ($cache_id && ($cache = cache_get($cache_id))) {
    $data = $cache->data;
    $field = field_info_field($cache->data['field']);

    $entity_reference_info = entityreference_get_selection_handler($field);
    $entity_type = $entity_reference_info->field['settings']['target_type'];

    switch ($entity_type) {
      case 'node':
        $data['target_id'] = $form_state['values']['nid'];
        break;

      case 'user':
        $data['target_id'] = $form_state['values']['name'];
        break;

      default:
        break;
    }// switch()
    entityconnect_cache_set($cache_id, $data);
    $form_state['redirect'] = "admin/entityconnect/return/$cache_id";
  }// if()
}

/**
 * entityconnect_child_form_cancel() simple
 * sets the redirect to a admin/entityconnect/redirect page.
 */
function entityconnect_child_form_cancel(&$form, &$form_state) {
  $cache_id = $form_state['clicked_button']['#parent_build_cache_id'];
  if ($cache_id && ($cache = cache_get($cache_id))) {
    $form_state['redirect'] = "admin/entityconnect/return/$cache_id";
  }// if()
}

/**
 * This is for when we return to the parent page
 * we find the cached form and form_state clean up the form_state a bit
 * and mark it to be rebuilt.
 *
 * If the cache as an nid we set that in the input.
 */
function entityconnect_return_form_alter(&$form, &$form_state, $form_id, $cache_id, $cache) {
  if (!isset($form_state['#entityconnect_processed'])) {
    $form_state = $cache->data['form_state'];
    $form = $cache->data['form'];
    $form_state['submitted'] = FALSE;
    $form_state['rebuild'] = TRUE;
    unset($form_state['clicked_button']);
    unset($form_state['submit_handlers']);

    $data = $cache->data;
    $language = $form[$data['field']]['#language'];

    if (isset($data['target_id']) && $data['target_id'] != 0 && $form_state['rebuild'] === TRUE) {
      if ($node = node_load($data['target_id'])) {

        $element['#default_value'] = $node->title;
        $element['#default_value'] .= ' (' . $node->nid . ')';

        $form_state['input'][$data['field']][$language][$data['key']]['target_id'] = $element['#default_value'];

      }
      elseif ($user = user_load_by_name($data['target_id'])) {

        $element['#default_value'] = $user->name;
        $element['#default_value'] .= ' (' . $user->uid . ')';

        $form_state['input'][$data['field']][$language][$data['key']]['target_id'] = $element['#default_value'];

      }// if()
    }// if()
    $form_state['#entityconnect_processed'] = TRUE;
    $form = drupal_rebuild_form($form_id, $form_state, $form);
  }// if()
}

/**
 * entityconnect_cache_set wraps cache set so we can
 * set the expire easily.
 */
function entityconnect_cache_set($cid, $data) {
  return cache_set($cid, $data, 'cache', CACHE_PERMANENT);
}

/**
 * entityconnect_child_form_alter() alters are child create form
 * we add a value field to hold the parent build_cache_id
 * and then we add a cancel button that run entityconnect_child_form_cancel
 * and we add a new submit nodeconect_child_form_submit.
 */
function entityconnect_child_form_alter(&$form, &$form_state, $form_id, $cache_id, $cache) {
  $form['parent_build_cache_id'] = array(
    '#type' => 'value',
    '#value' => $cache_id,
  );
  $form['actions']['cancel'] = array(
    '#type' => 'submit',
    '#value' => t('Cancel'),
    '#submit' => array('entityconnect_include_form', 'entityconnect_child_form_cancel'),
    '#parent_build_cache_id' => $cache_id,
    '#limit_validation_errors' => array(),
    '#weight' => 20,
  );
  $form['actions']['submit']['#submit'][] = 'entityconnect_include_form';
  $form['actions']['submit']['#submit'][] = 'entityconnect_child_form_submit';

  switch ($form_id) {
    case 'user_register_form':
      $form['actions']['submit']['#submit'][] = 'user_register_submit';
      break;

    case 'user_profile_form':
      $form['actions']['submit']['#submit'][] = 'user_profile_form_submit';
      break;

    default:
      break;
  }// switch()
}

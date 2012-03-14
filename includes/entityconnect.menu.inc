<?php
/**
 * @file
 * Handles all entityconnect menu item page callbacks.
 */

/**
 * Page callback: Redirects to the form page 
 * 
 * We redirect to the form page with the build_cache_id as a get param.
 */
function entityconnect_return($cache_id) {
  $cache = cache_get($cache_id);
  $css_id = "edit-" . str_replace('_', '-', $cache->data['field']);
  drupal_goto($cache->data['dest'], array(
      'query' => array(
        'build_cache_id' => $cache_id,
        'return' => TRUE),
          'fragment' => $css_id));
}

/**
 * Page callback: Load cached form info.
 *
 * This page will load the cached form info, and display links to each of the
 * entityreference types.
 * If there is only one it will redirect to that page.
 *
 * This is mostly a copy and hack up of the node add page.
 *
 * This page is directed to from the entityconnect_add_button_submit.
 */
function entityconnect_add($cache_id) {
  $content = array();
  $cache = cache_get($cache_id);
  $field = field_info_field($cache->data['field']);

  $entity_reference_info = entityreference_get_selection_handler($field);
  $entity_type = $entity_reference_info->field['settings']['target_type'];

  switch ($entity_type) {
    case 'node':
      // We get bundles referenced.
      $acceptable_types = $entity_reference_info->field['settings']['handler_settings']['target_bundles'];

      if (count($acceptable_types) > 0) {
        foreach (system_admin_menu_block(menu_get_item("node/add")) as $key => $item) {
          $type = str_replace("-", '_', str_replace("node/add/", "", $item['path']));
          if (isset($acceptable_types[$type]) && $acceptable_types[$type]) {
            $item['href'] = $item['href'] . "/$cache_id";
            $content[$key] = $item;
          }// if()
        }// foreach()
        // If we have only one content-type defined,
        // go directly to the node form.
        if (sizeof($content) == 1) {
          $item = array_pop($content);
          drupal_goto($item['href']);
        }// if()
      }
      else {
        foreach (system_admin_menu_block(menu_get_item("node/add")) as $key => $item) {
          $type = str_replace("-", '_', str_replace("node/add/", "", $item['path']));
          $item['href'] = $item['href'] . "/$cache_id";
          $content[$key] = $item;
        }// foreach()
      }// else()
      $output = theme('node_add_list', array('content' => $content));
      $output .= l(t('Cancel'), "admin/entityconnect/return/$cache_id");
      break;

    case 'user':
      $item['href'] = 'admin/people/create/' . $cache_id . '';
      drupal_goto($item['href']);
      break;

    default:
      break;
  }// switch()
  return $output;
}

/**
 * Page callback: Redirect to edit form. 
 *
 * We use this to redirect to a edit form but pass the build_cache_id.
 */
function entityconnect_edit($cache_id) {
  $cache = cache_get($cache_id);
  $field = field_info_field($cache->data['field']);

  $entity_reference_info = entityreference_get_selection_handler($field);
  $entity_type = $entity_reference_info->field['settings']['target_type'];

  switch ($entity_type) {
    case 'node':
      drupal_goto("node/" . $cache->data['target_id'] . "/edit", array('query' => array("build_cache_id" => $cache_id, "child" => TRUE)));
      break;

    case 'user':
      drupal_goto("user/" . $cache->data['target_id'] . "/edit", array('query' => array("build_cache_id" => $cache_id, "child" => TRUE)));
      break;
  }
}

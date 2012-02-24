<?php
/**
 * @file
 * Handles all entityconnect menu item page callbacks
 */

/*
 * node_connect_return is a hook_menu callback
 * redirects to the form page with the build_cache_id as a get param
 */
function entityconnect_return($cache_id) {
  $cache = cache_get($cache_id);
  $css_id = "edit-" . str_replace('_', '-', $cache->data['field']);
  drupal_goto($cache->data['dest'], array('query' => array("build_cache_id" => $cache_id, "return" => TRUE), 'fragment' => $css_id));

  return $cache_id;
}

/*
 * entityconnect_add is hook_menu callback 
 *
 * This page will load the cached form info, and display links to each of the referenceable types
 * If there is only one it will redirect to that page
 *
 * This is mostly a copy and hack up of the node add page
 *
 * This page is directed to from the entityconnect_add_button_submit
 */
function entityconnect_add($cache_id) {
  $content = array();
  $cache = cache_get($cache_id);
  $field = field_info_field($cache->data['field']);
  
  $entity_reference_info = entityreference_get_selection_handler($field);
  $entity_type = $entity_reference_info->field['settings']['target_type'];

  // We get bundles referenced
  $acceptable_types = $entity_reference_info->field['settings']['handler_settings']['target_bundles'];

  if ($entity_type == 'node') {
  
    if (count($acceptable_types)>0) {    
      foreach (system_admin_menu_block(menu_get_item("node/add")) as $key => $item) {
        $type = str_replace("-", '_', str_replace("node/add/", "", $item['path']));
        if (isset($acceptable_types[$type]) && $acceptable_types[$type]) {
          $item['href'] = $item['href'] . "/$cache_id";
          $content[$key] = $item;
        }//if()
      }//foreach()

      // if we have only one content-type defined, go directly to the node form. 
      if (sizeof($content) == 1) {
        $item = array_pop($content);
        drupal_goto($item['href']);
      }//if()
    } 
    else {
      foreach (system_admin_menu_block(menu_get_item("node/add")) as $key => $item) {
        $type = str_replace("-", '_', str_replace("node/add/", "", $item['path']));
        $item['href'] = $item['href'] . "/$cache_id";
        $content[$key] = $item;
      }
    } 
    
    $output = theme('node_add_list', array('content' => $content));
    $output .= l(t('Cancel'), "admin/entityconnect/return/$cache_id");
  } 
<<<<<<< HEAD
=======
  elseif ($entity_type == 'user') {
      $item['href'] = 'admin/people/create/' . $cache_id .'';
      drupal_goto($item['href']);
  }
>>>>>>> user_support
  
  return $output;
}

/*
 * entityconnect_edit is a hook_menu callback
 *
 * we use this to redirect to a edit form but pass the build_cache_id
 */
function entityconnect_edit($cache_id) {
  $cache = cache_get($cache_id);
  drupal_goto("node/" . $cache->data['target_id'] . "/edit", array('query' => array("build_cache_id" => $cache_id, "child" => TRUE)));
  return $cache_id;
}

<?php
/**
 * @file
 * Handles all entityconnect menu item page callbacks
 */

/**
 * node_connect_return is a hook_menu callback
 * redirects to the form page with the build_cache_id as a get param
 */
function entityconnect_return($cache_id) {
  $cache = cache_get($cache_id);
  $css_id = "edit-" . str_replace('_', '-', $cache->data['field']);
  drupal_goto($cache->data['dest'], array('query' => array("build_cache_id" => $cache_id, "return" => TRUE), 'fragment' => $css_id));

  return $cache_id;
}

/**
 * entityconnect_add is hook_menu callback
 *
 * This page will load the cached form info, and display links to each of the
 * referenceable types.
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

  //dpr($entity_type);
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
        }
      }

      $output = theme('node_add_list', array('content' => $content));
      $output .= l(t('Cancel'), "admin/entityconnect/return/$cache_id");
      break;

    case 'user':
      $item['href'] = 'admin/people/create/' . $cache_id . '';
      drupal_goto($item['href']);
      break;
    
    case 'comment':
      // TO DO.
      break;
    
    case 'file':
      // TO DO.
      break;
    
    /* case 'taxonomy_term':
      // We get bundles referenced.
      $acceptable_types = $entity_reference_info->field['settings']['handler_settings']['target_bundles'];

      if (count($acceptable_types) > 0) {
        $taxonomy_info = taxonomy_entity_info();
        foreach ($taxonomy_info as $key => $item) {
          foreach ($item['bundles'] as $key2 => $bundles) {
            $type = $key2;
            if (isset($acceptable_types[$type]) && $acceptable_types[$type]) {
              $bundles['href'] = $bundles['admin']['real path'];
              $bundles['href'] .= '/add/' . $cache_id . '';
              $content[$key2] = $bundles;
            }// if()
          }// foreach()
        }// foreach()
        if (sizeof($acceptable_types) == 1) {
          $item['href'] = 'admin/structure/taxonomy/' . array_pop($acceptable_types) . '/add/' . $cache_id . '';
          drupal_goto($item['href']);
        }// if()
      }
      else {
        $taxonomy_info = taxonomy_entity_info();
        foreach ($taxonomy_info as $key => $item) {
            $type = $key;
            $item['href'] = $bundles['admin']['real path'];
            $item['href'] .= '/add/' . $cache_id . '';
            $content[$key2] = $bundles;
       }// foreach()
      }// else()

      $output = theme('taxonomy_overview_vocabularies', array('taxonomy' => $content));
      $output .= l(t('Cancel'), "admin/entityconnect/return/$cache_id");
      break; */

    default:
      break;
  }
  return $output;
}

/**
 * entityconnect_edit is a hook_menu callback
 *
 * we use this to redirect to a edit form but pass the build_cache_id
 *
 * To Do: Fix it for user_support
 */
function entityconnect_edit($cache_id) {
  $cache = cache_get($cache_id);
  drupal_goto("node/" . $cache->data['target_id'] . "/edit", array('query' => array("build_cache_id" => $cache_id, "child" => TRUE)));
  return $cache_id;
}

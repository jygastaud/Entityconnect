<?php
/**
 * @file
 * Handles all entityconnect menu item page callbacks.
 */

/**
 * Page callback: Redirects to the form page.
 *
 * We redirect to the form page with the build_cache_id as a get param.
 */
function entityconnect_return($cache_id) {
  $cache = entityconnect_cache_get($cache_id);
  $css_id = "edit-" . str_replace('_', '-', $cache->data['field']);
  $options = array('query' => array(
              'build_cache_id' => $cache_id,
              'return' => TRUE),
            'fragment' => $css_id);
  //Collect additional request parameters, skip 'q', since this is the destination
  foreach ($cache->data['params'] as $key => $value) {
    if ('build_cache_id' == $key) {
      continue;
    }
    $options['query'][$key] = $value;
  }

  drupal_goto($cache->data['dest'], $options);
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
  $cache = entityconnect_cache_get($cache_id);
  $entity_type = $cache->data['target_entity_type'];
  $acceptable_types = $cache->data['acceptable_types'];

  $add_info = module_invoke_all('entityconnect_add_info', $cache_id, $entity_type, $acceptable_types);

  // Merge in default values.
  foreach ($add_info as $name => $data) {
    $add_info += array(
      'content' => array(
        'href' => '',
        'label' => '',
        'description' => ''
      ),
      'theme_callback' => 'entityconnect_entity_add_list',
    );
  }

  $context = array(
    'cache_id' => $cache_id,
    'entity_type' => $entity_type,
    'acceptable_tpes' => $acceptable_types
  );
  drupal_alter('entityconnect_add_info', $add_info, $context);

  if (isset($add_info)) {
    $content = $add_info['content'];
    $theme = $add_info['theme_callback'];
  }

  // If we have only one content-type defined,
  // go directly to the node form.
  // @todo: Need to be retest with taxonomy term.
  if (count($content) == 1) {
    $item = array_pop($content);
    drupal_goto($item['href']);
  }

  $output = theme($theme, array(
    'cache id' => $cache_id,
    'items' => $content,
  ));
  $output .= l(t('Cancel'), "admin/entityconnect/return/$cache_id");

  return $output;
}

/**
 * Implements hook_entityconnect_add_info().
 */
function entityconnect_entityconnect_add_info($cache_id, $entity_type, $acceptable_types) {
  if (!isset($entity_type)) {
    throw new \Exception(t('Entity type can not be empty'));
  }

  switch ($entity_type) {
    case 'node':
      if (count($acceptable_types) > 0) {
        foreach (node_type_get_types() as $key => $item) {
          if (isset($acceptable_types[$key]) && $acceptable_types[$key]) {
            $type = str_replace("_", '-', $key);
            $content[$key] = array(
              'href' => "node/add/$type/$cache_id",
              'label' => $item->name,
              'description' => $item->description
            );
          }
        }
      }
      else {
        foreach (node_type_get_types() as $key => $item) {
          $type = str_replace("_", '-', $key);
          $content[$key] = array(
            'href' => "node/add/$type/$cache_id",
            'label' => $item->name,
            'description' => $item->description
          );
        }
      }
      break;

    case 'user':
      $content[$entity_type]['href'] = "admin/people/create/$cache_id";
      break;

    case 'taxonomy_term':
      if (count($acceptable_types) > 0) {
        foreach (taxonomy_get_vocabularies() as $key => $item) {
          $type = $item->machine_name;
          if (isset($acceptable_types[$type]) && $acceptable_types[$type]) {
            $item->href = "admin/structure/taxonomy/$type/add/$cache_id";
            $content[$key] = $item;
          }
        }
      }
      else {
        foreach (taxonomy_get_vocabularies() as $key => $item) {
          !isset($item->href) ? $item->href = NULL : $item->href;
          $type = $item->machine_name;
          $item->href = "admin/structure/taxonomy/$type/add/$cache_id";
          $content[$key] = $item;
        }
      }
      $theme_callback = 'entityconnect_taxonomy_term_add_list';
      break;

    case 'taxonomy_vocabulary':
      $content[$entity_type]['href'] = "admin/structure/taxonomy/add/$cache_id";
      break;

    default:
      // @todo: to split
      // Entity construction kit support.
      if (module_exists('eck')) {
        $entity_info = entity_get_info($entity_type);

        if (!empty($entity_info['module']) && $entity_info['module'] == 'eck') {
          // Only one allowed, no need for the intermediate step.
          if (count($acceptable_types) > 0) {
            foreach ($entity_info['bundles'] as $key => $bundle) {
              $content[$key] = array(
                'href' => $bundle['crud']['add']['path'] . "/$cache_id",
                'label' => $bundle['label'],
                'description' => '',
              );
            }
          }
          else {
            foreach ($acceptable_types as $bundle_name) {
              $bundle = $entity_info['bundles'][$bundle_name];
              $content[$bundle_name] = array(
                'href' => $bundle['crud']['add']['path'] . "/$cache_id",
                'label' => $bundle['label'],
                'description' => ''
              );
            }
          }
        }
      }

      // Bean support.
      if (module_exists('bean_admin_ui')) {
        if (count($acceptable_types) > 0) {
          foreach (bean_admin_ui_get_types() as $key => $item) {
            if (isset($acceptable_types[$key]) && $acceptable_types[$key]) {
              $type = str_replace("_", '-', $key);
              $content[$key] = array(
                'href' => "block/add/$type/$cache_id",
                'label' => $item->label,
                'description' => $item->description
              );
            }
          }
        }
        else {
          foreach (bean_admin_ui_get_types() as $key => $item) {
            $type = str_replace("_", '-', $key);
            $content[$key] = array(
              'href' => "block/add/$type/$cache_id",
              'label' => $item->label,
              'description' => $item->description
            );
          }
        }
      }
      break;
  }

  if (isset($theme_callback)) {
    return array(
      'content' => $content,
      'theme_callback' => $theme_callback,
    );
  }
  else {
    return array(
      'content' => $content,
    );
  }
}

/**
 * Page callback: Redirect to edit form.
 *
 * We use this to redirect to a edit form but pass the build_cache_id.
 */
function entityconnect_edit($cache_id) {
  $cache = entityconnect_cache_get($cache_id);
  $entity_type = $cache->data['target_entity_type'];
  $target_id = $cache->data['target_id'];

  $edit_info = module_invoke_all('entityconnect_edit_info', $cache_id, $entity_type, $target_id);

  // Merge in default values.
  foreach ($edit_info as $name => $data) {
    $edit_info += array(
      'content' => array(
        'href' => '',
        'label' => '',
        'description' => ''
      ),
      'theme_callback' => 'entityconnect_entity_add_list',
    );
  }

  $context = array(
    'cache_id' => $cache_id,
    'entity_type' => $entity_type,
    'target_id' => $target_id
  );
  drupal_alter('entityconnect_edit_info', $edit_info, $context);

  if (isset($edit_info)) {
    $content = $edit_info['content'];
    $theme = $edit_info['theme_callback'];
  }

  $output = theme($theme, array(
    'cache id' => $cache_id,
    'items' => $content,
  ));
  $output .= l(t('Cancel'), "admin/entityconnect/return/$cache_id");

  return $output;
}

/**
 * Implements hook_entityconnect_edit_info().
 */
function entityconnect_entityconnect_edit_info($cache_id, $entity_type, $target_id) {

  if (!isset($entity_type)) {
    throw new \Exception(t('Entity type can not be empty'));
  }

  if (!isset($target_id)) {
    throw new \Exception(t('Target_id can not be empty'));
  }

  switch ($entity_type) {
    case 'node':
      if (is_array($target_id)) {
        $info = entity_load($entity_type, $target_id);
        foreach ($info as $key => $value) {
          $content[$key] = array(
            'label' => $value->title,
            'href' => 'node/' . $value->nid . '/edit',
            'description' =>  ''
          );
        }
      }
      else {
        drupal_goto("node/" . $target_id . "/edit", array(
          'query' => array("build_cache_id" => $cache_id, "child" => TRUE)));
      }
      break;

    case 'user':
      if (is_array($target_id)) {
        $info = entity_load($entity_type, $target_id);
        foreach ($info as $key => $value) {
          $content[$key] = array(
            'label' => $value->name,
            'href' => 'user/' . $value->uid . '/edit',
            'description' => ''
          );
        }
      }
      else {
        drupal_goto("user/" . $target_id . "/edit", array(
          'query' => array("build_cache_id" => $cache_id, "child" => TRUE)));
      }
      break;

    case 'taxonomy_term':
      if (is_array($target_id)) {
        $info = entity_load($entity_type, $target_id);
        foreach ($info as $key => $value) {
          $content[$key] = array(
            'label' => $value->name,
            'href' => 'taxonomy/term/' . $value->tid . '/edit',
            'description' => ''
          );
        }
      }
      else {
        drupal_goto("taxonomy/term/" . $target_id . "/edit", array(
          'query' => array("build_cache_id" => $cache_id, "child" => TRUE)));
      }
      break;

    case 'taxonomy_vocabulary':
      if (is_array($target_id)) {
        $info = entity_load($entity_type, $target_id);
        foreach ($info as $key => $value) {
          $content[$key] = array(
            'label' => $value->name,
            'href' => 'admin/structure/taxonomy/' . $value->name . '/edit',
            'description' => ''
          );
        }
      }
      else {
        $info = entity_load_single($entity_type, $target_id);
        drupal_goto("admin/structure/taxonomy/" . $info->name . "/edit", array(
          'query' => array("build_cache_id" => $cache_id, "child" => TRUE)));
      }
      break;

    default:
      // @todo: to split
      // Support for entity construction kit.
      if (module_exists('eck')) {
        $entity_info = entity_get_info($entity_type);
        if ($entity_info['module'] == 'eck') {

          if (is_array($target_id)) {
            $eck_entities = entity_load($entity_type, $target_id);
            foreach ($eck_entities as $key => $eck_entity) {
              $crud_edit_path = preg_replace('/\%/', $key, $entity_info['bundles'][$eck_entity->type]['crud']['edit']['path']);
              $content[$key] = array(
                'label' => $eck_entity->title,
                'href' => $crud_edit_path,
                'description' => ''
              );
            }
          }
          else {
            $eck_entity = entity_load_single($entity_type, $target_id);
            $crud_edit_path = preg_replace('/\%/', $target_id, $entity_info['bundles'][$eck_entity->type]['crud']['edit']['path']);
            drupal_goto($crud_edit_path, array(
              'query' => array("build_cache_id" => $cache_id, "child" => TRUE)));
          }
        }
      }

      // Support for Bean module..
      if (module_exists('bean_admin_ui')) {
        $entity_info = entity_get_info($entity_type);
        if ($entity_info['module'] == 'bean') {
          if (is_array($target_id)) {
            $info = entity_load($entity_type, $target_id);
            foreach ($info as $key => $value) {
              $content[$key] = array(
                'label' => $value->label,
                'href' => 'block/' . $value->delta . '/edit',
                'description' =>  ''
              );
            }
          }
          else {
            $bean_entity = entity_load_single($entity_type, $target_id);
            drupal_goto("block/" . $bean_entity->delta . "/edit", array(
              'query' => array("build_cache_id" => $cache_id, "child" => TRUE)));
          }
        }
      }
      break;
  }


  if (isset($theme_callback)) {
    return array(
      'content' => $content,
      'theme_callback' => $theme_callback,
    );
  }
  else {
    return array(
      'content' => $content,
    );
  }
}

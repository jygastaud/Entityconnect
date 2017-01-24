<?php

namespace Drupal\entityconnect\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteProvider;
use Drupal\Core\Url;
use Drupal\entityconnect\EntityconnectCache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for Entityconnect module routes.
 */
class EntityconnectController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Temporary session storage for entityconnect.
   *
   * @var \Drupal\entityconnect\EntityconnectCache
   */
  protected $entityconnectCache;

  /**
   * @var RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new EntityconnectController.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   * @param \Drupal\entityconnect\EntityconnectCache $entityconnectCache
    */
  function __construct(RendererInterface $renderer, EntityconnectCache $entityconnectCache) {
    $this->renderer = $renderer;
    $this->entityconnectCache = $entityconnectCache;
  }

  /**
   * Uses Symfony's ContainerInterface to declare dependency to be passed to constructor.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('entityconnect.cache')
    );
  }


  /**
   * We redirect to the form page with the build_cache_id as a get param.
   *
   * @param $cache_id
   *    build cache id.
   * @param bool $cancel
   *    whether or not the request was cancelled.
   * @return RedirectResponse
   */
  public function return_to($cache_id, $cancel = FALSE) {
    $cache_data = $this->entityconnectCache->get($cache_id);
    $cache_data['cancel'] = $cancel;
    $this->entityconnectCache->set($cache_id, $cache_data);
    $css_id = "edit-" . str_replace('_', '-', $cache_data['field']);
    $options = array('query' => array(
      'build_cache_id' => $cache_id,
      'return' => TRUE),
      'fragment' => $css_id);
    //Collect additional request parameters, skip 'q', since this is the destination
    foreach ($cache_data['params'] as $key => $value) {
      if ('build_cache_id' == $key) {
        continue;
      }
      $options['query'][$key] = $value;
    }
    $options['absolute'] = TRUE;
    /** @var RouteMatchInterface $routeMatch */
    $routeMatch = $cache_data['dest'];
    $url = Url::fromRouteMatch($routeMatch);
    $url->setOptions($options);
    return new RedirectResponse($url->toString());
  }

  /**
   * Page callback: Redirect to edit form.
   *
   * @param $cache_id
   * @return array|RedirectResponse
   */
  public function edit($cache_id) {
    $data = $this->entityconnectCache->get($cache_id);

    $entity_type = $data['target_entity_type'];
    $target_id = $this->_fixTargetId($data['target_id']);

    $edit_info = \Drupal::moduleHandler()->invokeAll('entityconnect_edit_info', array($cache_id, $entity_type, $target_id));

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
    \Drupal::moduleHandler()->alter('entityconnect_edit_info', $edit_info, $context);

    if (isset($edit_info)) {
      $content = $edit_info['content'];
      $theme = $edit_info['theme_callback'];

      if (count($content) == 1) {
        $item = array_pop($content);
        if (is_array($item['href'])) {
          $href= array_shift($item['href']);
        }
        else {
          $href = $item['href'];
        }
        $url = Url::fromUri('internal:' . $href);
        $options = array(
          'query' => array('build_cache_id' => $cache_id, 'child' => TRUE),
          'absolute' => TRUE,
        );
        $url = $url->setOptions($options)->toString();
        if (!$url) {
          $this->_returnWithMessage($this->t('Invalid url: %url', array('%url' => $url)), 'warning', $cache_id);
        }
        return new RedirectResponse($url);

      }

      return [
        '#theme' => $theme,
        '#items' => $content,
        '#cache_id' => $cache_id,
        '#cancel_link' => Link::createFromRoute($this->t('Cancel'), 'entityconnect.return', array('cache_id' => $cache_id, 'cancel' => TRUE))
      ];

    }

    $this->_returnWithMessage($this->t('Nothing to edit.'), 'warning', $cache_id);
  }

  /**
   * Callback for creating the build array of entities to edit.
   *
   * @param $cache_id
   * @param $entity_type
   * @param $target_id
   * @return array
   * @throws \Exception
   */
  public static function edit_info($cache_id, $entity_type, $target_id) {

    if (!isset($entity_type)) {
      throw new \Exception(t('Entity type can not be empty'));
    }

    if (!isset($target_id)) {
      throw new \Exception(t('Target_id can not be empty'));
    }

    $content = array();

    if (is_array($target_id)) {
      $info = \Drupal::entityTypeManager()->getStorage($entity_type)->loadMultiple($target_id);
      foreach ($info as $key => $value) {
        $content[$key] = array(
          'label' => $value->getTitle(),
          'href' => Url::fromRoute('entity.' . $entity_type . '.edit_form', array($entity_type => $key))->toString(),
          'description' =>  ''
        );
      }
    }
    else {
      $content[$entity_type]['href'] = Url::fromRoute('entity.' . $entity_type . '.edit_form', array($entity_type => $target_id))->toString();
    }

    return array(
      'content' => $content,
    );
  }

  /**
   * Add a new connecting entity.
   *
   * @param $cache_id
   * @return array|RedirectResponse
   */
  public function add($cache_id) {
    $data = $this->entityconnectCache->get($cache_id);
    $entity_type = $data['target_entity_type'];
    $acceptable_types = $data['acceptable_types'];

    $add_info = \Drupal::moduleHandler()->invokeAll('entityconnect_add_info', array($cache_id, $entity_type, $acceptable_types));

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
    \Drupal::moduleHandler()->alter('entityconnect_add_info', $add_info, $context);

    if (isset($add_info)) {
      $content = $add_info['content'];
      $theme = $add_info['theme_callback'];

      if (count($content) == 1) {
        $item = array_pop($content);
        $url = Url::fromUri('internal:' . $item['href']);
        $options = array(
          'query' => array('build_cache_id' => $cache_id, 'child' => TRUE),
          'absolute' => TRUE,
        );
        $url = $url->setOptions($options)->toString();
        if (!$url) {
          $this->_returnWithMessage($this->t('Invalid url: %url', array('%url' => $url)), 'warning', $cache_id);
        }
        return new RedirectResponse($url);
      }

      return [
        '#theme' => $theme,
        '#items' => $content,
        '#cache_id' => $cache_id,
        '#cancel_link' => Link::createFromRoute($this->t('Cancel'), 'entityconnect.return', array('cache_id' => $cache_id, 'cancel' => TRUE))
      ];
    }

    $this->_returnWithMessage($this->t('Nothing to add.'), 'warning', $cache_id);

  }

  /**
   * Callback for creating the build array of entities to add.
   *
   * @param $cache_id
   * @param $entity_type
   * @param $acceptable_types
   * @return array
   * @throws \Exception
   */
  public static function add_info($cache_id, $entity_type, $acceptable_types) {
    if (!isset($entity_type)) {
      throw new \Exception(t('Entity type can not be empty'));
    }

    $content = array();

    $routes = static::_getAddRoute($entity_type);

    if (!empty($routes)) {
      $route_name = key($routes);
      /** @var \Symfony\Component\Routing\Route $route */
      $route = current($routes);
      // If no parameters just try to get the url from route name.
      if (empty($params = $route->getOption('parameters'))) {
        $content[$entity_type]['href'] = Url::fromRoute($route_name)->toString();
      }
      // Otherwise, get the url from route name and parameters.
      else {
        // should only be one parameter.
        $route_param_key = key($params);
        foreach ($acceptable_types as $acceptable_type) {
          $type = \Drupal::entityTypeManager()->getStorage($route_param_key)->load($acceptable_type);
          if ($type) {
            $route_params = array($route_param_key => $acceptable_type);
            $href = Url::fromRoute($route_name, $route_params);
            $content[$type->id()] = array(
              'href' => $href->toString(),
              'label' => $type->label(),
              'description' =>  method_exists($type, 'getDescription') ? $type->getDescription() : '',
            );
          }
        }
      }
    }
/*
    switch ($entity_type) {
      case 'node':
        foreach($acceptable_types as $acceptable_type) {
          $type = \Drupal::entityTypeManager()->getStorage('node_type')->load($acceptable_type);
          if ($type) {
            $route_params['node_type'] = $type->id();
            $href = Url::fromRoute('node.add', $route_params);
            $content[$type->id()] = array(
              'href' => $href->toString(),
              'label' => $type->label(),
              'description' => $type->getDescription(),
            );
          }
        }
        break;

      case 'user':
        $content[$entity_type]['href'] = Url::fromRoute('user.admin_create')->toString();
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
        break;
    }
*/
    if (isset($content)) {
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
  }

  private function _returnWithMessage($msg, $status, $cache_id) {
    drupal_set_message($msg, $status);
    return $this->redirect('entityconnect.return', array('cache_id' => $cache_id, 'cancel' => TRUE));
  }

  /**
   * Makes sure our target id's are numeric.
   *
   * @param $target_id
   * @return array|int
   */
  private function _fixTargetId($target_id) {
    $array_target_id = is_array($target_id) ? $target_id : array($target_id);
    foreach ($array_target_id as &$value) {
      if (!is_numeric($value) && is_string($value)) {
        $value = EntityAutocomplete::extractEntityIdFromAutocompleteInput($value);
      }
    }

    return count($array_target_id) == 1 ? $array_target_id[0] : $array_target_id;
  }

  /**
   * Returns the Symfony routes of the given entity's add form.
   *
   * @param $entity_type
   * @return array
   */
  public static function _getAddRoute($entity_type) {
    /** @var RouteProvider $route_provider */
    $route_provider = \Drupal::getContainer()->get('router.route_provider');

    $route_name = array();

    switch ($entity_type) {
      case 'node':
        $route_name[] = 'node.add';
        break;
      case 'user':
        $route_name[] = 'user.admin_create';
        break;
      case 'shortcut':
        $route_name[] = 'shortcut.link_add';
        break;
      default:
        // Some default add form route names.
        $route_name = [
          $entity_type . '.add_form',
          'entity.' . $entity_type . '.add_form'
        ];
    }

    return $route_provider->getRoutesByNames($route_name);

  }

}


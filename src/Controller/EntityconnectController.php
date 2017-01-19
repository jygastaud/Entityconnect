<?php

namespace Drupal\entityconnect\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
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
   * Edit.
   *
   * @return string
   *   Return markup.
   */
  public function edit($cache_id) {
    return [
        '#type' => 'markup',
        '#markup' => $this->t('Implement method: return with parameter(s): @cache_id', array('@cache_id' => $cache_id))
    ];
  }
  /**
   * Add a new connecting entity.
   *
   * @return string
   *   Return markup.
   */
  public function add($cache_id) {
    $data = $this->entityconnectCache->get($cache_id);
    $entity_type = $data['target_entity_type'];
    $acceptable_types = $data['acceptable_types'];

    $content = array();

    foreach($acceptable_types as $acceptable_type) {
      $type = $this->entityTypeManager()->getStorage('node_type')->load($acceptable_type);
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
    $add_info = array(
      'content' => $content,
      'theme_callback' => 'entityconnect_add_list', //$theme_callback,
      'cache_id' => $cache_id,
    );

    $theme = $add_info['theme_callback'];

    if (count($content) == 1) {
      $options = [
        'absolute' => TRUE,
        'query' => [
          'build_cache_id' => $cache_id,
          'child' => TRUE,
        ],
      ];
      $route_params['node_type'] = key($content);
      return $this->redirect('node.add', $route_params, $options);
    }

    return [
        '#theme' => $theme,
        '#items' => $content,
        '#cache_id' => $cache_id,
        '#cancel_link' => Link::createFromRoute($this->t('Cancel'), 'entityconnect.return', array('cache_id' => $cache_id, 'cancel' => TRUE))
    ];
  }
}

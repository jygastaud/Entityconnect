<?php
/**
 * @author Agnes Chisholm <amaria@66428.no-reply.drupal.org>
 */

namespace Drupal\entityconnect;


use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\SessionManager;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityconnectCache
{

  /**
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  private $store;

  /**
   * @var \Drupal\Core\Session\SessionManager
   */

  private $session_manager;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $account;

  public function __construct(PrivateTempStoreFactory $store, SessionManager $session_manager, AccountInterface $account)
  {
    $this->store = $store->get('entityconnect');
    $this->session_manager = $session_manager;
    $this->account = $account;
    // Start a manual session for anonymous users.
    if ($account->isAnonymous() && !isset($_SESSION['entityconnect_session'])) {
      $_SESSION['entityconnect_session'] = true;
      $session_manager->start();
    }
  }

  /**
   * Uses Symfony's ContainerInterface to declare dependency to be passed to constructor.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.private_tempstore'),
      $container->get('session_manager'),
      $container->get('current_user')
    );
  }

  /**
   * Gets the data from our PrivateTempStore for the given key.
   *
   * @param $key
   * @return mixed
   */
  public function get($key) {
    return $this->store->get($key);
  }

  /**
   * Stores the key/data pair in our PrivateTempStore.
   *
   * @param $key
   * @param $data
   * @throws \Drupal\user\TempStoreException
   */
  public function set($key, $data) {
    $this->store->set($key, $data);
  }
}

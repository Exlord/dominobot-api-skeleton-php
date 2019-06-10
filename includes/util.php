<?php
/**
 * Created by PhpStorm.
 * User: Exlord
 * Date: 5/15/2019
 * Time: 4:35 PM
 */

use Zend\Cache\PatternFactory;
use Zend\Cache\StorageFactory;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\View\Renderer\PhpRenderer;

class Util {

  private static $instance = null;
  public         $cache    = null;
  public         $renderer;

  /**
   * @return Util
   */
  public static function GetInstance() {
    if (!isset(self::$instance)) {
      self::$instance = new Util();
    }

    return self::$instance;
  }

  private function __construct() {
    $this->cache    = StorageFactory::factory([
      'adapter' => [
        'name'    => 'filesystem',
        'options' => [
          'ttl'       => 1800,
          'cache_dir' => ROOT . '/cache',
          'namespace' => 'dominobot'
        ],
      ],
      'plugins' => [
        'exception_handler'                    => ['throw_exceptions' => false],
        'Zend\Cache\Storage\Plugin\Serializer' => []
      ],
    ]);
    $this->renderer = new PhpRenderer();
    $resolver       = new \Zend\View\Resolver\AggregateResolver();
    $this->renderer->setResolver($resolver);
    $map = new \Zend\View\Resolver\TemplateMapResolver([
      'payment' => ROOT . '/view/payment.phtml',
    ]);
    $resolver->attach($map);
  }
}



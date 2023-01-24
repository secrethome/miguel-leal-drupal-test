<?php

namespace Drupal\calibrate_base\Controller;

use Drupal\calibrate_base\Helper\BasicPagesHelper;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BasicPagesController.
 *
 * Provides an endpoint for making a redirect to a given referenced basic page.
 */
class BasicPagesController extends ControllerBase {

  /**
   * The basic pages helper service.
   *
   * @var \Drupal\calibrate_base\Helper\BasicPagesHelper
   */
  protected BasicPagesHelper $basicPagesHelper;

  /**
   * BasicPagesController constructor.
   *
   * @param \Drupal\calibrate_base\Helper\BasicPagesHelper $basic_pages_helper
   *   The basic pages helper service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(BasicPagesHelper $basic_pages_helper) {
    $this->basicPagesHelper = $basic_pages_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): BasicPagesController {
    return new static(
      $container->get('basic_pages.helper')
    );
  }

  /**
   * Redirect the user for the correct referenced basic page.
   *
   * @param string $id
   *   The basic page ID.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect object.
   */
  public function redirectTo($id, Request $request): RedirectResponse {
    $node = $this->basicPagesHelper->getBasicPage($id);

    if (!$node) {
      return $this->redirect('/');
    }

    return $this->redirect('entity.node.canonical', ['node' => $node->id()]);
  }

}

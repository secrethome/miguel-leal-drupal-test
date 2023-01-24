<?php

namespace Drupal\calibrate_base\Plugin\Condition;

use Drupal\calibrate_base\Helper\BasicPagesHelper;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Basic page ID' condition.
 *
 * @Condition(
 *   id = "basic_page_id",
 *   label = @Translation("Basic page ID"),
 * )
 */
class BasicPageId extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected TermStorageInterface $termStorage;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected RouteMatchInterface $routeMatch;

  /**
   * The basic pages helper service.
   *
   * @var \Drupal\calibrate_base\Helper\BasicPagesHelper
   */
  protected BasicPagesHelper $basicPagesHelper;

  /**
   * Creates a new BasicPageId instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\taxonomy\TermStorageInterface $term_storage
   *   The taxonomy storage.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\calibrate_base\Helper\BasicPagesHelper $basic_pages_helper
   *   The basic pages helper service.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    $plugin_definition,
    TermStorageInterface $term_storage,
    RouteMatchInterface $route_match,
    BasicPagesHelper $basic_pages_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->termStorage = $term_storage;
    $this->routeMatch = $route_match;
    $this->basicPagesHelper = $basic_pages_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('taxonomy_term'),
      $container->get('current_route_match'),
      $container->get('basic_pages.helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $options = [];
    $basic_pages = $this->termStorage->loadByProperties([
      'vid' => 'basic_pages',
    ]);

    /** @var \Drupal\taxonomy\TermInterface $basic_page */
    foreach ($basic_pages as $basic_page) {
      if (!$basic_page->hasField('field_machine_name') || $basic_page->get('field_machine_name')->isEmpty()) {
        continue;
      }

      $options[$basic_page->get('field_machine_name')->getString()] = $basic_page->label();
    }

    $form['basic_page_ids'] = [
      '#title' => $this->t('Basic pages'),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $this->configuration['basic_page_ids'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['basic_page_ids'] = array_filter($form_state->getValue('basic_page_ids'));
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if (count($this->configuration['basic_page_ids']) > 1) {
      $basic_pages = $this->configuration['basic_page_ids'];
      $last = array_pop($basic_pages);
      $basic_pages = implode(', ', $basic_pages);

      return $this->t('The basic page is @basic_page_ids or @last', [
        '@basic_page_ids' => $basic_pages,
        '@last' => $last,
      ]);
    }

    $basic_page = reset($this->configuration['basic_page_ids']);

    return $this->t('The basic page is @basic_page_id', ['@basic_page_id' => $basic_page]);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->routeMatch->getParameter('node');
    $route_name = $this->routeMatch->getRouteName();

    if ($route_name === 'entity.node.canonical' && $node instanceof NodeInterface) {
      $basic_page_id = $this->basicPagesHelper->getId($node);

      if (!$basic_page_id && empty($this->configuration['basic_page_ids'])) {
        return !$this->isNegated();
      }

      if (empty($this->configuration['basic_page_ids']) && $this->isNegated()) {
        return FALSE;
      }

      // Checks if no custom pages are set or is coupled
      // with an active custom page.
      if (empty($this->configuration['basic_page_ids']) && !$this->isNegated()) {
        return TRUE;
      }

      // If a node has an active custom page.
      if (in_array($basic_page_id, $this->configuration['basic_page_ids'])) {
        return TRUE;
      }

      return FALSE;
    }

    if (!empty($this->configuration['basic_page_ids']) || (empty($this->configuration['basic_page_ids']) && $this->isNegated())) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['basic_page_ids' => []] + parent::defaultConfiguration();
  }

}

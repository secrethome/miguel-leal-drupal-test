<?php

namespace Drupal\calibrate_base\Plugin\Block;

use Drupal\calibrate_base\Helper\BasicPagesHelper;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\node\NodeViewBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns the related articles block.
 *
 * @Block(
 *   id = "related_articles_block",
 *   admin_label = @Translation("Related articles block"),
 * )
 */
class RelatedArticlesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected NodeStorageInterface $nodeStorage;

  /**
   * The node view builder.
   *
   * @var \Drupal\node\NodeViewBuilder
   */
  protected NodeViewBuilder $nodeViewBuilder;

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
      $container->get('basic_pages.helper')
    );
  }

  /**
   * RelatedArticlesBlock constructor.
   *
   * @param array $configuration
   *   The container to pull out services used in the plugin.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\calibrate_base\Helper\BasicPagesHelper $basic_pages_helper
   *   The basic pages helper service.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    RouteMatchInterface $route_match,
    BasicPagesHelper $basic_pages_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->nodeViewBuilder = $entity_type_manager->getViewBuilder('node');
    $this->routeMatch = $route_match;
    $this->basicPagesHelper = $basic_pages_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->routeMatch->getParameter('node');
    $items = [];

    if (!$node instanceof NodeInterface
      || !$node->hasField('field_tags')
      || $node->get('field_tags')->isEmpty()
    ) {
      return [];
    }

    $tags = array_map(static fn (array $value) => $value['target_id'], $node->get('field_tags')->getValue());
    $articles = $this->nodeStorage
      ->getQuery()
      ->condition('field_tags', $tags, 'IN')
      ->condition('nid', $node->id(), '!=')
      ->sort('created', 'DESC')
      ->accessCheck()
      ->execute();

    if (empty($articles)) {
      return [];
    }

    foreach ($this->nodeStorage->loadMultiple($articles) as $article) {
      $items[] = $this->nodeViewBuilder->build($this->nodeViewBuilder->view($article, 'related'));
    }

    return [
      '#type' => 'container',
      'container' => [
        '#type' => 'container',
        'label' => [
          '#type' => 'html_tag',
          '#tag' => 'h2',
          '#value' => $this->t('Related Articles'),
        ],
        'more-link' => [
          '#type' => 'link',
          '#url' => $this->basicPagesHelper->getUrl('articles_overview'),
          '#title' => $this->t('more articles'),
        ],
      ],
      'items' => [
        '#theme' => 'item_list',
        '#items' => $items,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags(): array {
    return Cache::mergeTags(parent::getCacheTags(), [
      'node_list:article',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts(): array {
    return Cache::mergeContexts(parent::getCacheContexts(), [
      'route',
    ]);
  }

}

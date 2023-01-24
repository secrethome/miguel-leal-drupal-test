<?php

namespace Drupal\calibrate_base\Helper;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorageInterface;

/**
 * Class BasicPagesHelper.
 *
 * Provides a set of helper methods for the basic pages.
 */
class BasicPagesHelper {

  /**
   * The taxonomy storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected TermStorageInterface $termStorage;

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected NodeStorageInterface $nodeStorage;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected RouteMatchInterface $routeMatch;

  /**
   * BasicPagesHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RouteMatchInterface $route_match) {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->routeMatch = $route_match;
  }

  /**
   * Gets the node of the basic page by its ID.
   *
   * @param string $id
   *   The ID of the basic page.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The node entity if found, NULL otherwise.
   */
  public function getBasicPage(string $id): ?NodeInterface {
    $basic_page = $this->nodeStorage->loadByProperties([
      'field_page_id.entity.field_machine_name' => $id,
    ]);

    if (empty($basic_page) || !current($basic_page) instanceof NodeInterface) {
      return NULL;
    }

    return current($basic_page);
  }

  /**
   * Gets the path of a basic page by its ID.
   *
   * @param string $id
   *   The ID of the basic page.
   *
   * @return string|null
   *   The path of the basic page or NULL if no path could be found.
   */
  public function getPath(string $id): ?string {
    $basic_page = $this->getBasicPage($id);

    if (!$basic_page) {
      return NULL;
    }

    return $basic_page->toUrl()->toString();
  }

  /**
   * Gets the url of a basic page by its ID.
   *
   * @param string $id
   *   The ID of the basic page.
   *
   * @return \Drupal\Core\Url|null
   *   The url of the basic page or NULL if no url could be found.
   */
  public function getUrl(string $id): ?Url {
    $basic_page = $this->getBasicPage($id);

    if (!$basic_page) {
      return NULL;
    }

    return $basic_page->toUrl();
  }

  /**
   * Gets the basic page ID.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   *
   * @return string
   *   The ID of the basic page or NULL if nothing referenced.
   */
  public function getId(NodeInterface $node): ?string {
    if (!$node->hasField('field_page_id') || $node->get('field_page_id')->isEmpty()) {
      return NULL;
    }

    [$term_page_id] = $node->get('field_page_id')->referencedEntities();

    if (!$term_page_id instanceof TermInterface
      || !$term_page_id->hasField('field_machine_name')
      || $term_page_id->get('field_machine_name')->isEmpty()
    ) {
      return NULL;
    }

    return $term_page_id->get('field_machine_name')->getString();
  }

  /**
   * Checks if a node entity is referencing a basic page.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   * @param string $id
   *   The ID of the basic page.
   *
   * @return bool
   *   TRUE if the node is referencing the basic page, FALSE otherwise.
   */
  public function isReferencingBasicPage(NodeInterface $node, string $id): bool {
    if (!$node->hasField('field_page_id') || $node->get('field_page_id')->isEmpty()) {
      return FALSE;
    }

    [$term_page_id] = $node->get('field_page_id')->referencedEntities();

    if (!$term_page_id instanceof TermInterface
      || !$term_page_id->hasField('field_machine_name')
      || $term_page_id->get('field_machine_name')->isEmpty()
    ) {
      return FALSE;
    }

    return $term_page_id->get('field_machine_name')->getString() === $id;
  }

}

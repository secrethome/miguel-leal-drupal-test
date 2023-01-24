<?php

namespace Drupal\calibrate_base\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * A drush command file.
 */
class BasicPagesTermsDrushCommands extends DrushCommands {

  /**
   * Entity type service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entityTypeManager;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private ?LoggerChannelFactoryInterface $loggerChannelFactory;

  /**
   * Constructs a new BasicPagesTermsDrushCommands object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   Logger service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_channel_factory
  ) {
    parent::__construct();
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerChannelFactory = $logger_channel_factory;
  }

  /**
   * Drush command that will created the needed taxonomy terms for basic pages.
   *
   * @command calibrate:create_basic_pages_terms
   * @usage calibrate:create_basic_pages_terms
   */
  public function createBasicPagesTerms() {
    $this->loggerChannelFactory->get('calibrate_drush_commands')->info('Creating taxonomy terms for basic pages...');

    try {
      $vocabulary_storage = $this->entityTypeManager->getStorage('taxonomy_vocabulary');
      $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');

      $vocabulary_terms = [
        'basic_pages' => [
          'label' => 'Basic pages',
          'terms' => [
            'homepage' => 'Homepage',
            'news_overview' => 'News overview',
            'articles_overview' => 'Articles overview',
            'offices_overview' => 'Offices overview',
          ],
        ],
      ];

      foreach ($vocabulary_terms as $vid => $vocabulary) {
        if (empty($vocabulary_storage->load($vid))) {
          $vocabulary_storage->create([
            'name' => $vocabulary['label'],
            'vid' => $vid,
            'langcode' => 'en',
          ])->save();
        }

        foreach ($vocabulary['terms'] as $page_id => $term_name) {
          $params = [
            'name' => $term_name,
            'vid' => $vid,
            'langcode' => 'en',
            'field_machine_name' => $page_id,
          ];

          if ($term_storage->loadByProperties([
            'name' => $term_name,
          ])) {
            continue;
          }

          $term_storage->create($params)->save();
        }
      }
    }
    catch (\Exception $e) {
      $this->output()->writeln($e);
      $this->loggerChannelFactory->get('calibrate_drush_commands')->warning('Error found @e', ['@e' => $e]);
    }
  }

}

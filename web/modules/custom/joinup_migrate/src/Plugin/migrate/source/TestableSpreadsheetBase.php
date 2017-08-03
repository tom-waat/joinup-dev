<?php

namespace Drupal\joinup_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Database;
use Drupal\Core\Site\Settings;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_spreadsheet\Plugin\migrate\source\Spreadsheet;

/**
 * Provides a wrapper around Spreadsheet migrate source plugin.
 *
 * Wrap the original Spreadsheet migrate source in order to allow switching
 * the mode between 'production' and 'test'. The switch between the two modes
 * is made by setting the setting 'joinup_migrate.mode' either to 'production'
 * or to 'test'. This is done by editing the 'build.properties.local', setting
 * the property Set the 'migration.mode' either to 'production' or to test' and
 * then running `phing setup-migration`.
 *
 * @see \Drupal\migrate_spreadsheet\Plugin\migrate\source\Spreadsheet
 */
abstract class TestableSpreadsheetBase extends Spreadsheet {

  /**
   * Connection to source database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    $this->db = Database::getConnection('default', 'migrate');

    // Allow switching between 'production' and 'test' mode.
    $mode = Settings::get('joinup_migrate.mode');
    if (!$mode || !in_array($mode, ['production', 'test'])) {
      throw new MigrateException("The settings.php setting 'joinup_migrate.mode' is not configured or is invalid (should be 'production' or 'test'). Please run `phing setup-migration`.");
    }

    $configuration['file'] = "../resources/migrate/mapping-$mode.xlsx";

    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    /** @var \Drupal\migrate_spreadsheet\SpreadsheetIteratorInterface $iterator */
    $iterator = parent::initializeIterator();

    $iterator->rewind();
    $rows = [];
    while ($iterator->valid()) {
      $row = $iterator->current();
      if ($this->rowIsValid($row)) {
        $rows[] = $row;
      }
      $iterator->next();
    }

    return new \ArrayIterator($rows);
  }

  /**
   * Checks if a row is valid and logs all inconsistencies.
   *
   * @param array $row
   *   The row to be checked. The $row array can be altered.
   *
   * @return bool
   *   If the row is valid.
   */
  abstract protected function rowIsValid(array &$row);

}

<?php

namespace Drupal\joinup_test_entity\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\entity_test\Entity\EntityTest;

/**
 * Defines a test entity class for testing default values.
 *
 * @ContentEntityType(
 *   id = "joinup_test_field_override",
 *   label = @Translation("Joinup test entity field overrides"),
 *   base_table = "joinup_test_field_override",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "bundle" = "type"
 *   }
 * )
 */
class JoinupTestFieldOverride extends EntityTest {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $fields */
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['name']->addConstraint('UniqueFieldInBundle', ['bundles' => ['joinup_test_field_override']]);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function bundleFieldDefinitions(EntityTypeInterface $entity_type, $bundle, array $base_field_definitions) {
    $fields = parent::bundleFieldDefinitions($entity_type, $bundle, $base_field_definitions);
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $fields */
    if ($bundle == 'joinup_dummy_bundle') {
      $fields['name'] = clone $base_field_definitions['name'];
      $fields['name']->addConstraint('UniqueFieldInBundle', ['bundles' => [$bundle]]);
    }
    return $fields;
  }

}

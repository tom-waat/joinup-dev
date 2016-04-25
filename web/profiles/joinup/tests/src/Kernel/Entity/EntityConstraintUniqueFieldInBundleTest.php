<?php

namespace Drupal\Tests\joinup\Kernel\Entity;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests entity reference selection plugins.
 *
 * @group joinup
 *
 * @requires profile joinup
 */
class EntityConstraintUniqueFieldInBundleTest extends EntityKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'user',
    'system',
    'field',
    'text',
    'filter',
    'entity_test',
    'joinup_extras',
  ];

  /**
   * The typed data manager to use.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedData;

  /**
   * The storage item.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * A random title used repeatedly.
   *
   * @var string
   */
  protected $randomTitle;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('entity_test_field_override');
    $this->typedData = $this->container->get('typed_data_manager');
    $this->storage = $this->entityManager->getStorage('entity_test_field_override');

  }

  /**
   * Tests the actual entity functionality.
   *
   * @todo: Fix description.
   */
  public function testFieldOverrideConstraint() {
    entity_test_create_bundle('some_test_bundle', 'Some test bundle', 'entity_test_field_override');
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $field_definitions */
    $field_definitions = $this->entityManager->getFieldDefinitions('entity_test_field_override', 'entity_test_field_override');

    // Set the constraints to the base field.
    /** @var \Drupal\field\FieldConfigInterface $base_config */
    $base_config = $field_definitions['name']->getConfig('entity_test_field_override');
    $base_config->addConstraint('UniqueFieldInBundle', ['bundles' => ['entity_test_field_override']]);
    $base_config->save();

    /** @var \Drupal\field\FieldConfigInterface $override_definition */
    $override_config = $field_definitions['name']->getConfig('some_test_bundle');
    $override_config->setConstraints(['UniqueFieldInBundle' => ['bundles' => ['some_test_bundle']]]);
    $override_config->save();

    $this->entityManager->clearCachedFieldDefinitions();
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $field_definitions */
    $field_definitions = $this->entityManager->getFieldDefinitions('entity_test_field_override', 'entity_test_field_override');

    // Verify overridden constraints.
    /** @var \Drupal\field\FieldConfigInterface $override_definition */
    $base_config = $field_definitions['name']->getConfig('entity_test_field_override');
    /** @var \Drupal\field\FieldConfigInterface $override_definition */
    $override_config = $field_definitions['name']->getConfig('some_test_bundle');

    // Get a random title for the test.
    $this->randomTitle = $this->randomMachineName();

    // Test for bundle entity_test_field_override.
    // $this->assertFieldOverrideConstraint('entity_test_field_override');
    // Test for bundle some_test_bundle.
    // $this->assertFieldOverrideConstraint('some_test_bundle');.
  }

  /**
   * The actual testing.
   *
   * @param string $bundle
   *   A bundle name.
   *
   * @todo: Fix the description.
   */
  public function assertFieldOverrideConstraint($bundle) {
    $user = $this->createUser();
    // Check that a random title can be applied in an entity.
    /** @var \Drupal\Core\Entity\EntityInterface $base_field_entity_1 */
    $base_field_entity_1 = $this->storage->create([
      'uid' => $user->id(),
      'type' => $bundle,
      'label' => $this->randomTitle,
    ]);
    /** @var \Drupal\Core\Field\FieldDefinitionInterface $dataDefinition */
    $dataDefinition = $this->entityManager->getFieldDefinitions('entity_test_field_override', $bundle)['name'];
    $typed_data = $this->typedData->create($dataDefinition->getItemDefinition(), $base_field_entity_1);
    $violations = $typed_data->validate();
    $this->assertEqual($violations->count(), 0, 'Validation passed for the first time the title is used.');
    // $base_field_entity_1->save();
  }

}

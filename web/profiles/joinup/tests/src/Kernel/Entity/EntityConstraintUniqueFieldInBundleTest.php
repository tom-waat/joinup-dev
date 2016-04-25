<?php

namespace Drupal\Tests\joinup\Kernel\Entity;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests entity reference selection plugins.
 *
 * @group joinup
 */
class EntityConstraintUniqueFieldInBundleTest extends EntityKernelTestBase {

  /**
   * The typed data manager to use.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedData;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
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
    entity_test_create_bundle('some_test_bundle', 'some_test_bundle', 'entity_test_field_override');

    // Set the constraints to the base and override field.
    /** @var \Drupal\Core\Field\FieldDefinitionInterface $base_definition */
    $base_definition = $this->entityManager->getFieldDefinitions('entity_test_field_override', 'entity_test_field_override')['name'];
    $base_definition->getItemDefinition()->addConstraint('UniqueFieldInBundle', ['bundles' => ['entity_test_field_override']]);
    /** @var \Drupal\Core\Field\FieldDefinitionInterface $override_definition */
    $override_definition = $this->entityManager->getFieldDefinitions('entity_test_field_override', 'some_test_bundle')['name'];
    $override_definition->getItemDefinition()->addConstraint('UniqueFieldInBundle', ['bundles' => ['some_test_bundle']]);

    $this->randomTitle = $this->randomMachineName();
  }

  function testFieldOverrideConstraint(){
    // Test for bundle entity_test_field_override.
    $this->assertFieldOverrideConstraint('entity_test_field_override');

    // Test for bundle some_test_bundle.
    $this->assertFieldOverrideConstraint('some_test_bundle');
  }

  /**
   * @param string $bundle
   *   A bundle name.
   */
  public function assertFieldOverrideConstraint($bundle) {
    $user = $this->createUser();
    // Check that a random title can be applied in an entity.
    /** @var \Drupal\Core\Entity\EntityInterface $base_field_entity_1 */
    $base_field_entity_1 = $this->storage->create([
      'type' => $bundle,
      'name' => $this->randomTitle,
      'user' => $user->id(),
    ]);
    /** @var \Drupal\Core\Field\FieldDefinitionInterface $dataDefinition */
    $dataDefinition = $this->entityManager->getFieldDefinitions('entity_test_field_override', $bundle)['name'];
    $typed_data = $this->typedData->create($dataDefinition->getItemDefinition(), $base_field_entity_1->getEntityTypeId(), null, $base_field_entity_1->getTypedData());
    $violations = $typed_data->validate();
    $this->assertEqual($violations->count(), 0, 'Validation passed for the first time the title is used.');
    $base_field_entity_1->save();

    // Check that a random title cannot be applied in the same entity.
    /** @var \Drupal\Core\Entity\EntityInterface $base_field_entity_1 */
    $base_field_entity_2 = $this->storage->create([
      'type' => $bundle,
      'name' => $this->randomTitle,
    ]);
    /** @var \Drupal\Core\Field\FieldDefinitionInterface $dataDefinition */
    $dataDefinition = $this->entityManager->getFieldDefinitions('entity_test_field_override', $bundle)['name'];
    $typed_data = $this->typedData->create($dataDefinition->getItemDefinition(), $base_field_entity_1->getEntityTypeId(), null, $base_field_entity_1->getTypedData());
    $violations = $typed_data->validate();
    $this->assertEqual($violations->count(), 1, 'Validation failed for the same entity.');
    $base_field_entity_2->save();

  }
}

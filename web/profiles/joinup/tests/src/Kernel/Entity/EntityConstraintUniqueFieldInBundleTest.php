<?php

namespace Drupal\Tests\joinup\Kernel\Entity;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests the UniqueFieldInBundle constraint.
 *
 * @group joinup
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
    'joinup_test_entity',
    'joinup_core',
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
    $this->installEntitySchema('joinup_test_field_override');
    $this->storage = $this->entityManager->getStorage('joinup_test_field_override');
    $this->typedData = $this->container->get('typed_data_manager');
  }

  /**
   * Tests the actual entity functionality.
   *
   * The method assertFieldOverrideConstraint is called twice because
   * the entity is actually saved to the database in order to check that
   * there are no validation errors when trying to save the field for the
   * second bundle.
   */
  public function testFieldOverrideConstraint() {
    // Get a random title for the test.
    $this->randomTitle = $this->randomMachineName();
    // Test for bundle entity_test_field_override.
    $this->assertFieldOverrideConstraint('joinup_test_field_override');
    // Test for bundle some_test_bundle.
    $this->assertFieldOverrideConstraint('joinup_dummy_bundle');
  }

  /**
   * Check if the validation constraints exist correctly in all bundles.
   */
  public function testConstraintAssignment() {
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $field_definitions */
    $field_definitions = $this->entityManager->getFieldDefinitions('joinup_test_field_override', 'joinup_test_field_override');
    $this->assertEqual($field_definitions['name']->getConstraints(), ['UniqueFieldInBundle' => ['bundles' => ['joinup_test_field_override']]], 'The constraints are stored correctly for the base field.');

    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $field_definitions */
    $field_definitions = $this->entityManager->getFieldDefinitions('joinup_test_field_override', 'joinup_dummy_bundle');
    $this->assertEqual($field_definitions['name']->getConstraints(), ['UniqueFieldInBundle' => ['bundles' => ['joinup_dummy_bundle']]], 'The constraints are stored correctly for the overriden field.');
  }

  /**
   * The actual testing.
   *
   * In this method we are checking that the first entity succeeds in
   * passing the validation and getting saved to the database and the second
   * fails due to duplicate field value.
   *
   * @param string $bundle
   *   A bundle name.
   */
  public function assertFieldOverrideConstraint($bundle) {
    $user = $this->createUser();

    // Check that a random title can be applied in an entity.
    /** @var \Drupal\Core\Entity\EntityInterface $base_field_entity_1 */
    $entity_1 = $this->storage->create([
      'uid' => $user->id(),
      'type' => $bundle,
      'name' => $this->randomTitle,
    ]);

    $violations = $entity_1->getTypedData()->validate();
    $this->assertFalse($violations->count(), "Validation passed for the first time the title is used in the bundle $bundle.");
    $entity_1->save();

    // Check that a random title cannot be applied at the same bundle.
    /** @var \Drupal\Core\Entity\EntityInterface $base_field_entity_1 */
    $entity_2 = $this->storage->create([
      'type' => $bundle,
      'name' => $this->randomTitle,
    ]);

    $violations = $entity_2->getTypedData()->validate();
    $this->assertTrue($violations->count(), "Validation failed for the second time the title is used in the bundle $bundle.");
    $violation = $violations[0];
    $this->assertEqual($violation->getMessage(), t('Content with @field_name %value already exists.', [
      '@field_name' => 'name',
      '%value' => $this->randomTitle,
    ]), 'The message for invalid value is correct.');

  }

}

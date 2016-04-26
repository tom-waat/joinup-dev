<?php

namespace Drupal\Tests\joinup\Kernel\Entity;

use Drupal\joinup_test_entity\Entity\JoinupTestFieldOverride;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests entity reference selection plugins.
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
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler */
    $moduleHandler = $this->container->get('module_handler');
    $moduleHandler->addProfile('joinup', 'profiles/joinup');
    $moduleHandler->loadInclude('joinup', 'php', 'src/Plugin/Validation/Constraint/UniqueFieldInBundleConstraint');
    $moduleHandler->buildModuleDependencies(['joinup' => $moduleHandler->getModule('joinup')]);
    /** @var ConstraintManager $validationManager */
    $validationManager = $this->container->get('validation.constraint');
    $validationManager->registerDefinitions();
    $validationManager->clearCachedDefinitions();

    $test = $validationManager->getDefinitionsByType('string');

    $this->installEntitySchema('joinup_test_field_override');

    JoinupTestFieldOverride::create(['type' => 'joinup_dummy_bundle'])->save();
    $this->entityManager->clearCachedBundles();
    $this->storage = $this->entityManager->getStorage('joinup_test_field_override');
    $this->typedData = $this->container->get('typed_data_manager');

    // Get a random title for the test.
    $this->randomTitle = $this->randomMachineName();
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
   * Tests the actual entity functionality.
   *
   * @todo: Fix description.
   */
  public function testFieldOverrideConstraint() {
    // Test for bundle entity_test_field_override.
    $this->assertFieldOverrideConstraint('joinup_test_field_override');
    // Test for bundle some_test_bundle.
    $this->assertFieldOverrideConstraint('joinup_dummy_bundle');
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
    // Check that a random title can be applied in an entity.
    /** @var \Drupal\Core\Entity\EntityInterface $base_field_entity_1 */
    $entity_1 = $this->storage->create([
      'type' => $bundle,
      'name' => $this->randomTitle,
    ]);

    $violations = $entity_1->getTypedData()->validate();
    $this->assertEqual($violations->count(), 0, "Validation passed for the first time the title is used in the bundle $bundle.");
    $entity_1->save();

    // Check that a random title cannot be applied at the same bundle.
    /** @var \Drupal\Core\Entity\EntityInterface $base_field_entity_1 */
    $entity_2 = $this->storage->create([
      'type' => $bundle,
      'name' => $this->randomTitle,
    ]);

    $violations = $entity_2->getTypedData()->validate();
    $this->assertEqual($violations->count(), 0, "Validation failed for the second time the title is used in the bundle $bundle.");
    $entity_2->save();
  }

}

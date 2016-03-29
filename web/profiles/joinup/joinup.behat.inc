<?php

/**
 * @file
 * Contains \UserSubContext.
 */

use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\DrupalSubContextInterface;
use \Behat\Gherkin\Node\TableNode;
use \Drupal\file\Entity\File;

/**
 * Behat step definitions for testing users.
 */
class JoinupSubContext extends DrupalSubContextBase implements DrupalSubContextInterface {
  /**
   * Test files.
   *
   * @var \Drupal\file\Entity\File[]
   */
  protected $files = [];

  /**
   * Test users.
   *
   * @var \Drupal\user\Entity\User[]
   */
  protected $registeredUsers = [];

  /**
   * Creates a user with data provided in a table.
   *
   * Table format:
   * | Username   | Mr Bond            |
   * | Password   | Bond007            |
   * | E-mail     | james.bond@mi5.org |
   *
   * @param TableNode $user_table
   *   The user data.
   *
   * @throws \Exception
   *   Thrown when a column name is incorrect.
   *
   * @Given (the following )user:
   */
  public function givenUser(TableNode $user_table) {
    $values = [];
    $aliases = self::userFieldAliases();
    // Replace the column aliases with the actual field names.
    foreach ($user_table->getRowsHash() as $key => $value) {
      if (array_key_exists($key, $aliases)) {
        $values[$aliases[$key]] = $value;
      }
      else {
        throw new \Exception("Unknown column '$key' in user table.");
      }
    }
    $this->handleFileFields($values, 'user', 'user');

    $user = (object) $values;
    $this->userCreate($user);
  }

  /**
   * Save files and change filename into a file id.
   */
  private function handleFileFields(&$values, $entity_type, $bundle) {
    $entity_manager = \Drupal::entityManager();
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $fields */
    $fields = $entity_manager->getFieldDefinitions($entity_type, $bundle);

    foreach ($values as $field_name => $value) {
      if (!isset($fields[$field_name])) {
        throw new \Exception("Field $field_name is not set on entity $entity_type  : $bundle");
      }
      if (empty($value)) {
        continue;
      }
      /** @var \Drupal\Core\Field\FieldDefinitionInterface $field_info */
      $field_info = $fields[$field_name];
      if (in_array($field_info->getType(), ['image', 'file'])) {
        $files_path = $this->getMinkParameter('files_path');
        $path = rtrim(realpath($files_path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $value;
        if (!is_file($path)) {
          throw new \Exception("File '$value' was not found in file path '$files_path'.");
        }
        // Copy the image into the public files folder and turn it into a File
        // entity before linking it.
        $uri = 'public://' . $value;
        file_unmanaged_copy($path, $uri);
        $file = File::create(['uri' => $uri]);
        $file->save();
        $values[$field_name] = $file->id();

        $this->files[$file->id()] = $file;
      }
    }
  }

  /**
   * Register a user.
   *
   * @Given the following user registration at :location:
   */
  public function submitRegistrationForm(TableNode $table, $location) {
    $this->visitPath($location);

    // Fill in fields.
    foreach ($table->getRowsHash() as $field => $value) {
      $this->getSession()->getPage()->fillField($field, $value);
    };
    // Submit form.
    $this->getSession()->getPage()->pressButton("Create new account");

    // Get the last registered user.
    $storage = \Drupal::entityManager()->getStorage('user');
    $query = $storage->getQuery();
    $result = $query->sort('uid', 'DESC')->range(0, 1)->execute();
    $uid = array_pop($result);
    /** @var \Drupal\user\UserInterface $user */
    $user = $storage->load($uid);

    // Check if latest registered user is created during this request.
    $user_creation_time = $user->getCreatedTime();
    $request_time = \Drupal::requestStack()->getCurrentRequest()->server->get('REQUEST_TIME');
    if (($request_time - $user_creation_time) < 0) {
      // Track user for auto delete in tear-down.
      $this->registeredUsers[$uid] = $user;
    }
    else {
      throw new Exception('User not registered.');
    }
  }

  /**
   * Remove any created registered users.
   *
   * @AfterScenario
   */
  public function cleanRegisteredUsers() {
    // Remove any users that were created.
    if (!empty($this->registeredUsers)) {
      /** @var \Drupal\user\Entity\User $user */
      foreach ($this->registeredUsers as $user) {
        $user->delete();
      }
    }
  }

  /**
   * Mapping of human readable names to machine names.
   *
   * @return array
   *    Field mapping.
   */
  private static function userFieldAliases() {
    return [
      'Username' => 'name',
      'Password' => 'pass',
      'E-mail' => 'mail',
      'Status' => 'status',
      'First name' => 'field_user_first_name',
      'Family name' => 'field_user_family_name',
      'Photo' => 'field_user_photo',
      'Professional profile' => 'field_user_professional_profile',
      'Organisation' => 'field_user_organisation',
      'Nationality' => 'field_user_nationality',
      'Professional domain' => 'field_user_professional_domain',
      // @todo Social network
    ];
  }

}
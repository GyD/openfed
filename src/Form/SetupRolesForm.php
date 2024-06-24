<?php
namespace Drupal\openfed\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;
use Drupal\openfed\Helper;
use Drupal\node\Entity\NodeType;
use Drupal\user\Entity\User;

/**
 * Defines a form for selecting which languages to install.
 */
class SetupRolesForm extends FormBase {
  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'openfed_install_role_form';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#title'] = $this->t('Set up roles');

    $form['role_list'] = [
      '#type' => 'checkboxes',
      '#title' => t('Roles'),
      '#options' => \Drupal::service('openfed.helper')
        ->_openfed_get_roles_list(),
      '#description' => t('By selecting the roles, the roles and their associated set of permissions will be automatically created.<br><br>
      They can also be created after the installation, but the set of permissions will have to be defined manually.'),
    ];

    $form['workflow_option'] = [
      '#type' => 'radios',
      '#title' => t('Workflow settings'),
      '#options' => \Drupal::service('openfed.helper')
        ->_openfed_get_workflow_list(),
      '#default_value' => Helper::WORKFLOW_BASIC_CONFIG,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $roles = array_filter($form_state->getValue('role_list'));
    $workflow_option = $form_state->getValue('workflow_option');

    $openfed_roles = \Drupal::service('openfed.helper')
      ->_openfed_get_roles_list_default();

    foreach ($openfed_roles as $role_id => $role) {
      if ($role['required'] || isset($roles[$role_id])) {
        $new_role = [
          'id' => $role_id,
          'label' => $openfed_roles[$role_id]['label'],
          'langcode' => 'en',
          'status' => 1,
          'weight' => $openfed_roles[$role_id]['weight'],
          'permissions' => [],
        ];

        // Create role.
        if (!Role::load($new_role['id'])) {
          Role::create($new_role)->save();
        }
      }
    }

    // Check selected workflow options to enable role and set configuration.
    if (!empty($workflow_option) && $workflow_option == Helper::WORKFLOW_ADVANCED_CONFIG) {
      // Remove the default openfed_workflow configuration to be re-created
      // during install process of openfed_workflow module.
      \Drupal::configFactory()->getEditable('workflows.workflow.openfed_workflow')->delete();
      // Enable Advanced Workflow configuration.
      \Drupal::service('module_installer')->install(['openfed_workflow']);
    }

    // For user managers, we just need to add a new permission to assign user
    // manager roles.
    $user_manager_role_conf = 'user.role.user_manager';
    $user_manager_new_permissions = array(
      'assign user_manager role',
    );
    $config = \Drupal::configFactory()->getEditable($user_manager_role_conf);
    $config->set(
      'permissions',
      array_merge($config->get('permissions'), $user_manager_new_permissions)
    );
    $config->save();

    // It's required that user 1 is set with Administratior role.
    $user_admin = User::load(1);
    $user_admin->addRole(Helper::ADMINISTRATOR_ROLE_ID);
    $user_admin->save();
  }
}

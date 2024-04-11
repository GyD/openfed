<?php

/**
 * @file
 * Post update functions for Openfed profile.
 */

/**
 * Disable allowed_formats module.
 */
function openfed_post_update_disable_allowed_formats(?array &$sandbox = null): void {
  $key_value = \Drupal::keyValue('post_update');
  $update_list = $key_value->get('existing_updates');

  // If the latest post_update from allowed_formats module has ran, we can
  // disable the module.
  // There's no way to define dependencies on post_updates but post_updates run
  // alphabetically so allowed_formats will run before openfed.
  // @see https://www.drupal.org/project/drupal/issues/3124766
  if (in_array('allowed_formats_post_update_formats2core', $update_list)) {
    \Drupal::service('module_installer')->uninstall(['allowed_formats']);
  }

}

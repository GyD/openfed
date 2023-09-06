<?php

namespace OpenfedProject\composer;

use Composer\Script\Event;
use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

/**
 * A class to do several validations before updating to Openfed 12.x.
 *
 * This will help maintainers to do an informed update.
 *
 * This file is present in Openfed 11.2+ so composer can early call it in
 * Openfed 12 and make a check before having to update Openfed. As such,
 * this file is not supposed to be used by Openfed 11.1 and bellow.
 */
class OpenfedValidations {

  /**
   * Validates if Openfed can be updated to version 12.
   */
  public static function validateUpdate12(Event $event) {

    // This check will assure that other checks will be performed only if this
    // is a Drupal site, otherwise they will be ignored (like for first time
    // installations).
    if (!self::isDrupalSite()) {
      return;
    }

    // We validate if Openfed version is 12 or above.
    if (!self::checkProjectVersion()) {
      return;
    }

    // Some modules were removed from Openfed 12, so they should be deleted
    // before updating to this version.
    self::checkDeprecatedModules($event->getArguments());

    // Some themes were removed from Openfed 12, so they should be deleted
    // before updating to this version.
    self::checkDeprecatedThemes($event->getArguments());

    // Twig Tweak module was updated so, if used, it should be checked for
    // compatibility issues.
    self::checkTwigTweak3Compatibility();

    shell_exec('drush cr');
  }

  /**
   * Check if current site is a Drupal site.
   *
   * @return bool
   *   TRUE if drupal is bootstrapped.
   */
  private static function isDrupalSite() {
    $output = trim(shell_exec('drush status --field="Drupal bootstrap"') ?? '');
    if (empty($output)) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Checks if the current version is Openfed 11.2.0 or above.
   *
   * @return bool
   *   Return true if current version is 11.2.0 or above, false otherwise.
   */
  private static function checkProjectVersion() {
    $composer_openfed = json_decode(file_get_contents('composer.openfed.json'), TRUE);
    $current_version = $composer_openfed['require']['openfed/openfed'];
    preg_match('/(?:[\d+\.?]+[a-zA-Z0-9-]*)/', $current_version, $matches);

    // If current version is dev, we should ignore version check.
    if (strpos($current_version, 'dev') !== FALSE) {
      return FALSE;
    }

    return version_compare(trim($matches[0], '.'), '11.2', '>=');
  }

  /**
   * Checks if deprecated modules are enabled and stops update if true.
   *
   * @throws \ErrorException
   *   Exception when deprecated modules are enabled.
   */
  private static function checkDeprecatedModules($arguments = []) {
    $recheck = false;
    $modules_to_check = [
      'ofed_switcher',
      'rdf'
    ];
    foreach ($modules_to_check as $module) {
      $output = trim(shell_exec('drush pml --field="status" --filter="name=' . $module . '"'));
      if ($output == 'Enabled') {
        if (in_array('fix', $arguments)) {
          shell_exec('drush pmu ' . $module);
          $recheck = true;
          // Re-run to make sure modules were disabled.
          self::checkDeprecatedModules();
        } else {
          throw new \ErrorException("You can't proceed with Openfed update until you uninstall $module. See Openfed 12 release notes.");
        }
      }
    }

    // Re-run to make sure modules were disabled.
    if ($recheck) {
      self::checkDeprecatedModules();
    }
  }

  /**
   * Checks if deprecated themes are enabled and stops update if true.
   *
   * @throws \ErrorException
   *   Exception when deprecated themes are enabled.
   */
  private static function checkDeprecatedThemes($arguments = []) {
    $recheck = false;
    $themes_to_check = [
      'openfed_admin',
      'adminimal_theme',
    ];

    // Check if one of these themes is set as the admin theme and set another.
    $admin_theme = current(unserialize(trim(shell_exec('drush cget --include-overridden system.theme admin --format=php'))));
    if (in_array('fix', $arguments)) {
      if (in_array($admin_theme, $themes_to_check)) {
        // Temporarly set kiso as admin theme so we can uninstall current theme.
        shell_exec('drush cset system.theme admin kiso -y');
      }
    }

    foreach ($themes_to_check as $theme) {
      $output = trim(shell_exec('drush pml --field="status" --filter="name=' . $theme . '"'));
      if ($output == 'Enabled') {
        if (in_array('fix', $arguments)) {
          shell_exec('drush thun ' . $theme);
          $recheck = true;
        } else {
          throw new \ErrorException("You can't proceed with Openfed update until you uninstall $theme. If you use $theme as the administration theme, you have to manually change it before you are able to uninstall the theme. See Openfed 12 release notes.");
        }
      }
    }

    // Re-run to make sure module was disabled.
    if ($recheck) {
      self::checkDeprecatedThemes();
    }
  }

  /**
   * Checks if Twig Tweak is enabled.
   *
   * @return bool
   *   TRUE if twig is enabled.
   */
  private static function isTwigTweakEnabled() {
    $module = 'twig_tweak';
    $output = trim(shell_exec('drush pml --field="status" --filter="' . $module . '"'));
    if ($output == 'Enabled') {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Initiates Drupal container.
   *
   * @throws \Exception
   */
  private static function initDrupalContainer() {
    $autoloader = require_once getcwd() . '/docroot/autoload.php';
    $request = Request::createFromGlobals();
    $kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
    $kernel->boot();
    $kernel->preHandle($request);
    if (PHP_SAPI !== 'cli') {
      $request->setSession($kernel->getContainer()->get('session'));
    }
  }

  /**
   * Check template for Twig Tweak 3.x compatibility issues.
   *
   * See https://git.drupalcode.org/project/twig_tweak/-/blob/3.x/docs/migration-to-3.x.md.
   *
   * @throws \ErrorException
   *   Exception when twig tweak 3 compatibility fails.
   */
  private static function checkTwigTweak3Compatibility() {
    if (self::isTwigTweakEnabled()) {
      // 1. Check for drupal_entity() and drupal_field() with second argument
      // as null or not present.
      $entityFieldSearch = shell_exec('find ./docroot/themes/ ./config/ -name "*.twig" | xargs grep -hiP "drupal_(entity|field)\([\'\"].*?[\'\"](,\s*null)?\)"');
      $entityFieldPattern = '/drupal_(entity|field)\([\'\"]([^,]*)[\'\"](,\s*null)?\)/';
      preg_match_all($entityFieldPattern, $entityFieldSearch, $entityFieldMatches);

      if (!empty($entityFieldMatches[0])) {
        throw new \ErrorException("In your theme, drupal_entity() or drupal_field() is used with the second argument as null or missing. Convert your theme to Twig Tweak 3.x before updating Openfed. See https://git.drupalcode.org/project/twig_tweak/-/blob/3.x/docs/migration-to-3.x.md.");
      }
    }
  }

}

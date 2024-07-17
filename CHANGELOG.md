CHANGELOG
=========

17 July 2024 - Version 12.2.2
------------------------------
- Add hook_update to install twig_real_content on existing projects, due to the new dependency

16 July 2024 - Version 12.2.1
------------------------------
Several fixes and updates:
- Issue #31: Default Workflow overriden when enabling Openfed Workflow module
- Issue #42: update page_manager patches to fix page title
- Issue #65: issue creating a new content type using default config
- Issue #70: fix Claro issues
- Enable drupal/twig_real_content to be used with Kiso (Kiso issue 64)
- Updates due to psa-2024-06-26
- Remove leftover config for display suite
- Update leaflet_maptiler module to version 2.0.0
- Update menu_link_weight module to version 2.0-alpha6
- Add translatable_menu_link_uri module
- Add drupal/twig_real_content

22 May 2024 - Version 12.2.0
------------------------------
First stable release of version 12.2

11 April 2024 - Version 12.2.0-beta1
------------------------------
- Update Drupal core to version 10.2.x
- Updated contrib modules
- Update default config install to use core allowed_formats
- Add update hook for allowed_formats
- Add post_update to disable allowed_formats module
- Add patch for alertbox D10 compatibility
- Add patch for leaflet_maptiler compatibility with latest leaflet
- Add validation script to check for deprecated twig functions
- Remove Openfed install dependency on several modules
- Add an installation option for Openfed federal header module
- Remove installation option for securelogin
- Cleanup code

29 November 2023 - Version 12.1.0
------------------------------

- Removed 2 ckeditor related hard-coded contrib modules
- Updated contrib modules

06 September 2023 - Version 12.1.0-beta4
------------------------------
  Update install hooks

26 July 2023 - Version 12.1.0-beta3
------------------------------
  Skip openfed_admin deprecated theme check during composer openfed validations process.


26 July 2023 - Version 12.1.0-beta2
------------------------------
  Move openfed_admin block configurations into openfed_admin theme folder.


26 July 2023 - Version 12.1.0-beta1
------------------------------
  Update core to version 10.1.1

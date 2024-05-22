CHANGELOG
=========

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

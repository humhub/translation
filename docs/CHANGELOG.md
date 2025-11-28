Changelog
=========

1.1.0 (unreleased)
-------------------------
- Enh: Allow translation of non-enabled modules
- Enh: Allow automatic translation with Google cloud translation API
- Enh: Provide specific error message in case message path could not be found instead of 404
- Fix #36: Fix empty translation text on activities
- Enh #40: Support attribute “target” for link tag
- Fix #44: Allow snake case for URI vars in href
- Fix #47: Fix tests
- Enh #58: Tests for `next` version
- Fix #59: Fix visibility of the method `Controller::getAccessRules()`
- Fix #63: Don't purify the char `&`
- Fix #65: Fix keeping of target in links of translated text
- Fix #41: Allow to translate module from folder name different than module id
- Enh #69: Display translation from parent language in placeholder
- Enh #74: Use PHP CS Fixer
- Enh #75: Improve command to rename category
- Enh #77: Reduce translation message categories
- Enh #82: Migration to Bootstrap 5 for HumHub 1.18
- Enh #83: Improved Module Test GitHub Actions
- Enh #84: Implemented `module-coding-standards`
- Fix #88: Hide the save button when user has no permission to manage translations

1.0.0 (June 10, 2020)
-----------------------
- Enh: 1.4 nonce support
- Enh: Added form acknowledge behavior
- Chg: Major refactoring
- Enh: Advanced translation validation
- Fix: Do not include modules without base message path
- Enh: Added translation log stream to language spaces
- Enh: Added translation history view
- Enh: Use of select2 dropdowns
- Chg: Moved build-archive from core into translation module
- Enh: Extended validation
- Chg: Updated HumHub min version to 1.5
- Fix: Invalid validation error when parsing plural or selection message pattern
- Enh #29: Added "Only show missing translation" filter toggle to translation editor

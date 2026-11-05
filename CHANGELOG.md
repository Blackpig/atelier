# Changelog

All notable changes to `blackpig-creatif/atelier` are documented in this file.

## v2.1.1 - 2026-11-05

### Fixed

- Adding `AtelierPlugin` to more than one panel threw "A Tome with id 'atelier' is already registered". The Grimoire documentation Tome is now registered once in `AtelierServiceProvider` instead of once per panel in the plugin.

### Changed

- Added a test suite (previously none) and applied Pint formatting.

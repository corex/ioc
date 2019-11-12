# Changelog

## 2.0.0

### Fixed
- Setting instance to shared when calling instance().
- Refactored detection of typehints in resolver. Setting default value if exist and not specified.

### Changed
- Required php 7.2+
- Renamed Exception class to IoCException and moved to proper location.
- Added strict coding standard in files.
- Method singleton() renamed to bindSingleton().

### Added
- Added changelog.
- Added method bindShared() with same functionality as bindSingleton().

## 1.0.2

### Changed
- 100% Code Coverage.
- Travis automated tests.

## 1.0.1

### Changed
- Change Container::getInstance() to use static instead of self.

## 1.0.0

### Added
- Initial release.

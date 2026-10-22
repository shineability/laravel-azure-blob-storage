# Changelog

## [Unreleased]

## [1.4.0] - 2026-10-22

### Added

- Support SAS connection strings from the Azure portal and the Azurite `UseDevelopmentStorage=true` shortcut ([**55eeb8c**](https://github.com/shineability/laravel-azure-blob-storage/commit/55eeb8c))

### Fixed

- Connection strings with a trailing `;` or empty segments no longer crash ([**4e9641f**](https://github.com/shineability/laravel-azure-blob-storage/commit/4e9641f))
- `temporaryUploadUrl()` now passes `$options` (e.g. `permissions`) through ([**f0ebca6**](https://github.com/shineability/laravel-azure-blob-storage/commit/f0ebca6))

## [1.3.0] - 2026-07-06

### Changed

- Require `azure-oss/storage-blob-flysystem` ^2.0 ([**23768d8**](https://github.com/shineability/laravel-azure-blob-storage/commit/23768d8)). The package's own API is unchanged, but apps that also require `azure-oss/storage*` directly or catch its removed exception classes should review the [azure-oss upgrade guide](https://github.com/Azure-OSS/azure-storage-blob-php/blob/main/UPGRADE.md). Temporary URLs now percent-encode the SAS expiry timestamp.

## [1.2.0] - 2026-05-22

### Added

- Laravel 13 support ([**2f8e915**](https://github.com/shineability/laravel-azure-blob-storage/commit/2f8e915))

### Changed

- Drop Laravel 11 support (reached end-of-life March 12, 2026) ([**2f8e915**](https://github.com/shineability/laravel-azure-blob-storage/commit/2f8e915))
- Update `README.md` installation section ([**a585070**](https://github.com/shineability/laravel-azure-blob-storage/commit/a585070))

## [1.1.0] - 2026-02-18

### Added

- Laravel package auto-discovery for service provider and facade ([**aa2d378**](https://github.com/shineability/laravel-azure-blob-storage/commit/aa2d378))
- Add status badges to `README.md`

## [1.0.0] - 2026-01-21

### Added

- Support for SAS token authentication ([**36a9a4c**](https://github.com/shineability/laravel-azure-blob-storage/commit/36a9a4c))
- Support for connection strings in named connections ([**eb45659**](https://github.com/shineability/laravel-azure-blob-storage/commit/eb45659))

### Fixed

- Pass options through in `temporaryUrl()` ([**51ff0ef**](https://github.com/shineability/laravel-azure-blob-storage/commit/51ff0ef))

### Changed

- Make value object classes `final readonly` ([**107e836**](https://github.com/shineability/laravel-azure-blob-storage/commit/107e836))
- Require `azure-oss/storage-blob-flysystem` ^1.4 ([**2351eae**](https://github.com/shineability/laravel-azure-blob-storage/commit/2351eae))

## [0.2.0] - 2025-04-20

### Added

- Support for generating temporary upload URLs

## [0.1.0] - 2025-04-17

- Initial release

[Unreleased]: https://github.com/shineability/laravel-azure-blob-storage/compare/1.4.0...HEAD
[1.4.0]: https://github.com/shineability/laravel-azure-blob-storage/compare/1.3.0...1.4.0
[1.3.0]: https://github.com/shineability/laravel-azure-blob-storage/compare/1.2.0...1.3.0
[1.2.0]: https://github.com/shineability/laravel-azure-blob-storage/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/shineability/laravel-azure-blob-storage/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/shineability/laravel-azure-blob-storage/compare/0.2.0...1.0.0
[0.2.0]: https://github.com/shineability/laravel-azure-blob-storage/compare/0.1.0...0.2.0
[0.1.0]: https://github.com/shineability/laravel-azure-blob-storage/releases/tag/0.1.0

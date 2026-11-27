# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 27 November 2026
### Added
- CLI command `cache:debug:list-by-tag <tag>` that lists all cache entry IDs carrying a given cache tag
- Option `--limit` (`-l`) to cap the number of listed entries

### Fixed
- Cache tags with invalid characters are now rejected with a clear error instead of being passed to the cache backend
- Cache entries are looked up via the low-level cache frontend, so tag lookups work reliably
- Cache backend errors are reported as a CLI error instead of crashing the command
- PHP 8.4 deprecation for implicitly nullable parameter
- Correct composer requirements (PHP ^8.1, ext-pcre)

### Changed
- Cache is lazy-loaded via a proxy so the command no longer slows down every `bin/magento` call
- CI and test files are excluded from package archives

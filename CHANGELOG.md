# Changelog

All notable changes to `laravel-attribute-routing` are documented here.
This project follows [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [0.1.1] - 2026-08-20

### Changed

- `attribute-routing:list` now renders in the same style as `php artisan route:list`:
  borderless layout with dot leaders, coloured HTTP verbs, `{parameters}` highlighted,
  all-verb routes collapsed to `ANY`, and controller names shortened against the
  application namespace. Middleware, excluded middleware, and `where` constraints moved
  behind `-v`, and long lines are truncated to the terminal width instead of wrapping.

## [0.1.0] - 2026-08-20

Initial release, extracted from a production Laravel application where it has
been registering 200+ routes since 2026.

### Added

- Support for Laravel 12 and 13 on PHP 8.2+.

- HTTP verb attributes: `#[Get]`, `#[Post]`, `#[Put]`, `#[Patch]`, `#[Delete]`,
  `#[Options]`, `#[Any]`, `#[MatchRoute]` — all repeatable.
- Class-level composition: `#[Prefix]`, `#[Version]`, `#[Middleware]`, `#[Name]`.
- `#[WithPermission]` mapping permission enums to middleware, via the
  `Permitted` contract, plain backed enums, or raw strings.
- `#[Throttle]` for rate limiting, `#[WithoutMiddleware]` for opting out of
  inherited middleware, `#[SkipDiscovery]` for excluding a class.
- Auto-discovery service provider that respects Laravel's route caching.
- `php artisan attribute-routing:list` for inspecting what was discovered.
- Publishable config for discovery paths and the permission middleware format.

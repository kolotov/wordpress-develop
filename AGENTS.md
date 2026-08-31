# Project Agent Policy

## Supported runtime

This repository is intentionally modern-only.

- Support PHP 8.5 only.
- Support PHPUnit 13 only.
- Support the latest WordPress 7.x line only.
- The `phpunit-13` branch is the only migration target for this work.

## Explicitly unsupported compatibility

Do not preserve, restore, add, or emulate backward compatibility for any of the following:

- PHP 8.4 or earlier.
- PHPUnit 12 or earlier.
- WordPress 6.x or earlier.
- `yoast/phpunit-polyfills` or any API surface inherited from it.
- `PHPUnit_Framework_*`, `PHPUnit_Util_*`, or other removed PHPUnit aliases/internals.
- Legacy PHPUnit DocBlock metadata when PHPUnit 13 attributes are available.
- Compatibility adapters spanning multiple PHPUnit generations.
- Historical callback shapes, helper methods, lifecycle behavior, signatures, aliases, or shims solely because older PHPUnit/PHP/WordPress versions exposed them.

Do not add a compatibility shim unless it is independently required by the current PHP 8.5 + PHPUnit 13 + latest WordPress 7.x contract.

## Regression review baseline

When reviewing regressions, weakening, or migration completeness:

1. Use PHP 8.5, PHPUnit 13, and the latest WordPress 7.x behavior as the compatibility baseline.
2. Do not treat older PHPUnit, Yoast PHPUnit Polyfills, older PHP versions, or older WordPress branches as compatibility authorities.
3. Prefer native PHPUnit 13 public APIs, attributes, and lifecycle methods.
4. Preserve WordPress test-harness behavior that is required by the current WordPress 7.x test suite.
5. Reject changes that weaken assertions, cleanup, isolation, coverage precision, or test discovery under the supported modern stack.
6. A green test run is not sufficient by itself; manually inspect lifecycle, global state, hook cleanup, factories, error/deprecation handling, and public harness contracts relevant to the supported stack.
7. Historical WordPress branches and old-version upgrade coverage are outside scope when they require unsupported PHP or PHPUnit versions.

## Repository relationship

This repository is paired with `kolotov/wp-phpunit` on branch `phpunit-13`.
Composer must consume `wp-phpunit/wp-phpunit` from `https://github.com/kolotov/wp-phpunit.git` using `dev-phpunit-13`.

## Local validation environment

- Run local PHPUnit profiles through `tools/phpunit13/run-local-podman.sh`; run migration auditing through its `audit` profile. Host PHP, Composer, Node, npm, PHPUnit, MariaDB, Memcached, Make targets, and direct host test commands are not valid validation.
- The paired `wp-phpunit` repository is the single owner of `containers/phpunit13/Containerfile` and `containers/phpunit13/runtime.env`. Do not duplicate those files in this repository.
- Published local and GitHub validation must require `composer.lock` and `tools/phpunit13/runtime-image.lock` to name the same provider commit and must consume the tested runtime, MariaDB, and Memcached by immutable digest.
- Unpublished paired validation may use only one `WP_PHPUNIT_SOURCE_HOST` checkout; it supplies both the runtime definition and Composer path repository so mixed provider states are impossible.
- Keep MariaDB and Memcached as disposable sibling containers so test state cannot leak between validation runs. Published service images are digest-pinned; unpublished candidate runs pull the exact versions declared by the provider runtime manifest.
- GitHub Actions and local validation must execute the same `tools/phpunit13/run-profile.sh` and `run-tests.sh` semantics; only the container engine differs (Docker in CI, Podman locally).
- The full WordPress PHPUnit matrix and migration anti-weakening audit are owned by this repository. Do not move or duplicate them into `wp-phpunit`.
- Do not suppress, hide, filter, or downgrade warnings, audit findings, notices, or failures in validation commands. Preserve diagnostics and fix root causes or classify them explicitly in separate gates.

## Git safety

- Do not push unless the user explicitly authorizes the push.
- Never force-push unless the user explicitly authorizes a force push.
- Preserve unrelated working-tree changes.
- Stage and commit only reviewed logical units.

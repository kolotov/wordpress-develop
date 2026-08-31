#!/usr/bin/env bash
set -euo pipefail

ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)
PROFILE=${1:-single-site}

export CONTAINER_ENGINE=podman
export WORDPRESS_PHPUNIT_COVERAGE=false

case "$PROFILE" in
    single-site)
        export WORDPRESS_PHPUNIT_MULTISITE=false
        export WORDPRESS_PHPUNIT_MEMCACHED=false
        export WORDPRESS_PHPUNIT_TESTS_DOMAIN=example.org
        export WORDPRESS_PHPUNIT_TEST_GROUPS=
        ;;
    multisite)
        export WORDPRESS_PHPUNIT_MULTISITE=true
        export WORDPRESS_PHPUNIT_MEMCACHED=false
        export WORDPRESS_PHPUNIT_TESTS_DOMAIN=example.org
        export WORDPRESS_PHPUNIT_TEST_GROUPS=
        ;;
    single-site-memcached)
        export WORDPRESS_PHPUNIT_MULTISITE=false
        export WORDPRESS_PHPUNIT_MEMCACHED=true
        export WORDPRESS_PHPUNIT_TESTS_DOMAIN=example.org
        export WORDPRESS_PHPUNIT_TEST_GROUPS=
        ;;
    multisite-memcached)
        export WORDPRESS_PHPUNIT_MULTISITE=true
        export WORDPRESS_PHPUNIT_MEMCACHED=true
        export WORDPRESS_PHPUNIT_TESTS_DOMAIN=example.org
        export WORDPRESS_PHPUNIT_TEST_GROUPS=
        ;;
    single-site-port)
        export WORDPRESS_PHPUNIT_MULTISITE=false
        export WORDPRESS_PHPUNIT_MEMCACHED=false
        export WORDPRESS_PHPUNIT_TESTS_DOMAIN=example.org:8889
        export WORDPRESS_PHPUNIT_TEST_GROUPS=
        ;;
    multisite-port)
        export WORDPRESS_PHPUNIT_MULTISITE=true
        export WORDPRESS_PHPUNIT_MEMCACHED=false
        export WORDPRESS_PHPUNIT_TESTS_DOMAIN=example.org:8889
        export WORDPRESS_PHPUNIT_TEST_GROUPS=
        ;;
    html-api)
        export WORDPRESS_PHPUNIT_MULTISITE=false
        export WORDPRESS_PHPUNIT_MEMCACHED=false
        export WORDPRESS_PHPUNIT_TESTS_DOMAIN=example.org
        export WORDPRESS_PHPUNIT_TEST_GROUPS=html-api-web-platform-tests
        ;;
    xdebug)
        export WORDPRESS_PHPUNIT_MULTISITE=false
        export WORDPRESS_PHPUNIT_MEMCACHED=false
        export WORDPRESS_PHPUNIT_TESTS_DOMAIN=example.org
        export WORDPRESS_PHPUNIT_TEST_GROUPS=xdebug
        ;;
    coverage-single-site)
        export WORDPRESS_PHPUNIT_MULTISITE=false
        export WORDPRESS_PHPUNIT_MEMCACHED=false
        export WORDPRESS_PHPUNIT_TESTS_DOMAIN=example.org
        export WORDPRESS_PHPUNIT_TEST_GROUPS=
        export WORDPRESS_PHPUNIT_COVERAGE=true
        ;;
    coverage-multisite)
        export WORDPRESS_PHPUNIT_MULTISITE=true
        export WORDPRESS_PHPUNIT_MEMCACHED=false
        export WORDPRESS_PHPUNIT_TESTS_DOMAIN=example.org
        export WORDPRESS_PHPUNIT_TEST_GROUPS=
        export WORDPRESS_PHPUNIT_COVERAGE=true
        ;;
    audit)
        exec "$ROOT/tools/phpunit13/run-audit.sh"
        ;;
    *)
        printf 'Unknown profile: %s\n' "$PROFILE" >&2
        printf '%s\n' 'Expected: single-site, multisite, single-site-memcached, multisite-memcached, single-site-port, multisite-port, html-api, xdebug, coverage-single-site, coverage-multisite, or audit.' >&2
        exit 2
        ;;
esac

exec "$ROOT/tools/phpunit13/run-profile.sh"

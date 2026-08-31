#!/usr/bin/env bash
set -euo pipefail

ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)
ENGINE=${CONTAINER_ENGINE:-podman}
MULTISITE=${WORDPRESS_PHPUNIT_MULTISITE:-false}
MEMCACHED=${WORDPRESS_PHPUNIT_MEMCACHED:-false}
TESTS_DOMAIN=${WORDPRESS_PHPUNIT_TESTS_DOMAIN:-example.org}
TEST_GROUPS=${WORDPRESS_PHPUNIT_TEST_GROUPS:-}
COVERAGE=${WORDPRESS_PHPUNIT_COVERAGE:-false}
REPORT=${WORDPRESS_PHPUNIT_REPORT:-false}
PHPUNIT_CONFIG=${WORDPRESS_PHPUNIT_CONFIG:-}
PARATEST_PROCESSES=${PARATEST_PROCESSES:-8}
ENV_FILE=$(mktemp)
RUN_ID="${RANDOM}-$$"
NETWORK="wordpress-phpunit13-${RUN_ID}"
DB_CONTAINER="wordpress-phpunit13-db-${RUN_ID}"
CACHE_CONTAINER="wordpress-phpunit13-cache-${RUN_ID}"
WORKSPACE_VOLUME="wordpress-phpunit13-workspace-${RUN_ID}"

cleanup() {
    "$ENGINE" rm -f "$DB_CONTAINER" "$CACHE_CONTAINER" >/dev/null 2>&1 || true
    "$ENGINE" volume rm -f "$WORKSPACE_VOLUME" >/dev/null 2>&1 || true
    "$ENGINE" network rm "$NETWORK" >/dev/null 2>&1 || true
    rm -f "$ENV_FILE"
}
trap cleanup EXIT HUP INT TERM

for value in "$MULTISITE" "$MEMCACHED" "$COVERAGE" "$REPORT"; do
    case "$value" in
        true|false) ;;
        *)
            printf 'Invalid boolean value: %s\n' "$value" >&2
            exit 2
            ;;
    esac
done
LOCAL_HARNESS=
if [[ -n "${WP_PHPUNIT_SOURCE_HOST:-}" ]]; then
    LOCAL_HARNESS=$(cd "$WP_PHPUNIT_SOURCE_HOST" && pwd)
    if [[ ! -f "$LOCAL_HARNESS/composer.json" || ! -f "$LOCAL_HARNESS/containers/phpunit13/Containerfile" ]]; then
        printf 'Invalid unpublished wp-phpunit source: %s\n' "$LOCAL_HARNESS" >&2
        exit 1
    fi
fi

"$ROOT/tools/phpunit13/prepare-runtime.sh" "$ENV_FILE"
# shellcheck disable=SC1090
source "$ENV_FILE"

if [[ "$REPORT" == true ]]; then
    REPORTER_SOURCE=${WORDPRESS_PHPUNIT_REPORTER_HOST:-$ROOT/test-runner}
    if [[ ! -f "$REPORTER_SOURCE/report.php" ]]; then
        printf 'WordPress Test Reporter is missing: %s/report.php\n' "$REPORTER_SOURCE" >&2
        exit 1
    fi
    if [[ -z "${WPT_REPORT_API_KEY:-}" ]]; then
        printf '%s\n' 'WPT_REPORT_API_KEY is required when test reporting is enabled.' >&2
        exit 1
    fi
fi

"$ENGINE" network create "$NETWORK" >/dev/null
"$ENGINE" volume create "$WORKSPACE_VOLUME" >/dev/null
"$ENGINE" run -d --name "$DB_CONTAINER" --network "$NETWORK" --network-alias mysql \
    -e MARIADB_ROOT_PASSWORD=password \
    "$LOCAL_DB_IMAGE" >/dev/null
if [[ "$MEMCACHED" == true ]]; then
    "$ENGINE" run -d --name "$CACHE_CONTAINER" --network "$NETWORK" --network-alias memcached \
        "$LOCAL_MEMCACHED_IMAGE" >/dev/null
fi

for attempt in $(seq 1 60); do
    if "$ENGINE" exec "$DB_CONTAINER" mariadb-admin ping -h127.0.0.1 -uroot -ppassword --silent >/dev/null 2>&1; then
        break
    fi
    if [[ "$attempt" -eq 60 ]]; then
        printf '%s\n' 'MariaDB did not become ready.' >&2
        "$ENGINE" logs "$DB_CONTAINER" >&2
        exit 1
    fi
    sleep 1
done

actual_db_version=$(
    "$ENGINE" exec "$DB_CONTAINER" mariadb -h127.0.0.1 -N -B -uroot -ppassword -e 'SELECT VERSION()'
)
case "$actual_db_version" in
    "$LOCAL_DB_VERSION"*) ;;
    *)
        printf 'Unexpected MariaDB version: %s; expected %s\n' "$actual_db_version" "$LOCAL_DB_VERSION" >&2
        exit 1
        ;;
esac
"$ENGINE" exec "$DB_CONTAINER" mariadb -h127.0.0.1 -uroot -ppassword -e \
    'CREATE DATABASE IF NOT EXISTS wordpress_develop_tests;'

printf '\n==> WordPress PHPUnit profile\n'
printf 'Engine: %s\n' "$ENGINE"
printf 'Runtime: %s\n' "$LOCAL_PHP_IMAGE"
printf 'MariaDB: %s\n' "$actual_db_version"
printf 'Memcached: %s\n' "$([[ "$MEMCACHED" == true ]] && printf '%s' "$LOCAL_MEMCACHED_IMAGE" || printf 'disabled')"
printf 'Multisite: %s\n' "$MULTISITE"
printf 'Domain: %s\n' "$TESTS_DOMAIN"
printf 'Groups: %s\n' "${TEST_GROUPS:-default matrix}"
printf 'Coverage: %s\n' "$COVERAGE"
printf 'PHPUnit config: %s\n' "${PHPUNIT_CONFIG:-derived from multisite}"
printf 'Reporting: %s\n' "$REPORT"

COMMON_ARGS=(
    --rm
    --network "$NETWORK"
    -v "$WORKSPACE_VOLUME:/var/www"
    -w /var/www
    -e HOME=/tmp/wordpress-home
    -e COMPOSER_CACHE_DIR=/tmp/composer-cache
    -e PUPPETEER_SKIP_DOWNLOAD=true
    -e "GUTENBERG_EXPECTED_SHA=${GUTENBERG_EXPECTED_SHA:-}"
    -e LOCAL_PHP_MEMCACHED="$MEMCACHED"
    -e LOCAL_PHP_XDEBUG=false
    -e LOCAL_PHP_PCOV="$COVERAGE"
)
if [[ "$ENGINE" == podman ]]; then
    COMMON_ARGS+=(--security-opt label=disable)
fi
if [[ -n "$LOCAL_HARNESS" ]]; then
    COMMON_ARGS+=( -v "$LOCAL_HARNESS:/wp-phpunit-source:ro" )
fi
if [[ "$REPORT" == true ]]; then
    COMMON_ARGS+=( -v "$REPORTER_SOURCE:/test-runner:ro" )
fi

COPY_ARGS=(--rm --network "$NETWORK" -v "$WORKSPACE_VOLUME:/var/www" -v "$ROOT:/source:ro")
if [[ "$ENGINE" == podman ]]; then
    COPY_ARGS+=(--security-opt label=disable)
fi
"$ENGINE" run "${COPY_ARGS[@]}" "$LOCAL_PHP_IMAGE" bash -lc \
    'rsync -a --delete --exclude=.git/ --exclude=.cache/ --exclude=node_modules/ --exclude=vendor/ --exclude=test-runner/ /source/ /var/www/'

"$ENGINE" run "${COMMON_ARGS[@]}" "$LOCAL_PHP_IMAGE" bash -lc '
    set -euo pipefail
    npm ci
    npm run build:dev
    if [[ -d /wp-phpunit-source ]]; then
        repository=$(php -r '\''echo json_encode(["type" => "path", "url" => "/wp-phpunit-source", "options" => ["versions" => ["wp-phpunit/wp-phpunit" => "dev-phpunit-13"]]], JSON_UNESCAPED_SLASHES);'\'')
        composer config --json repositories.wp-phpunit "$repository"
        composer update wp-phpunit/wp-phpunit --with-dependencies --no-interaction --prefer-dist
    else
        composer install --no-interaction --prefer-dist
    fi
'

"$ENGINE" run "${COMMON_ARGS[@]}" \
    -e "WP_TESTS_DOMAIN=$TESTS_DOMAIN" \
    "$LOCAL_PHP_IMAGE" bash -lc '
        set -euo pipefail
        php -r '\''
            $contents = file_get_contents("wp-tests-config-sample.php");
            if ($contents === false) {
                throw new RuntimeException("Cannot read wp-tests-config-sample.php");
            }
            $replace = [
                "youremptytestdbnamehere" => "wordpress_develop_tests",
                "yourusernamehere" => "root",
                "yourpasswordhere" => "password",
                "localhost" => "mysql",
                "\x27WP_TESTS_DOMAIN\x27, \x27example.org\x27" => "\x27WP_TESTS_DOMAIN\x27, \x27" . getenv("WP_TESTS_DOMAIN") . "\x27",
            ];
            $contents = str_replace(array_keys($replace), array_values($replace), $contents);
            $contents .= "\ndefine( \x27FS_METHOD\x27, \x27direct\x27 );\n";
            if (file_put_contents("wp-tests-config.php", $contents) === false) {
                throw new RuntimeException("Cannot write wp-tests-config.php");
            }
        '\''
    '

TEST_ARGS=("${COMMON_ARGS[@]}"
    -v "$ROOT:/artifacts"
    -e WORDPRESS_PHPUNIT_WORKSPACE=/var/www
    -e WORDPRESS_PHPUNIT_MULTISITE="$MULTISITE"
    -e WORDPRESS_PHPUNIT_MEMCACHED="$MEMCACHED"
    -e WORDPRESS_PHPUNIT_TEST_GROUPS="$TEST_GROUPS"
    -e WORDPRESS_PHPUNIT_COVERAGE="$COVERAGE"
    -e WORDPRESS_PHPUNIT_CONFIG="$PHPUNIT_CONFIG"
    -e PARATEST_PROCESSES="$PARATEST_PROCESSES"
    -e WP_TESTS_DB_NAME=wordpress_develop_tests
    -e WP_TESTS_DB_USER=root
    -e WP_TESTS_DB_PASSWORD=password
    -e WP_TESTS_DB_HOST=mysql
    -e GITHUB_SHA="${GITHUB_SHA:-local}"
)

set +e
"$ENGINE" run "${TEST_ARGS[@]}" "$LOCAL_PHP_IMAGE" \
    bash tools/phpunit13/run-tests.sh
test_status=$?
set -e

if [[ "$REPORT" == true && "$test_status" -eq 0 ]]; then
    "$ENGINE" run "${COMMON_ARGS[@]}" \
        -e WPT_REPORT_API_KEY \
        -e WPT_PREPARE_DIR=/var/www \
        -e WPT_TEST_DIR=/var/www \
        "$LOCAL_PHP_IMAGE" php /test-runner/report.php
fi

exit "$test_status"

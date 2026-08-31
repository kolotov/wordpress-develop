#!/usr/bin/env bash
set -euo pipefail

ROOT=${WORDPRESS_PHPUNIT_WORKSPACE:-/var/www}
MULTISITE=${WORDPRESS_PHPUNIT_MULTISITE:-false}
MEMCACHED=${WORDPRESS_PHPUNIT_MEMCACHED:-false}
TEST_GROUPS=${WORDPRESS_PHPUNIT_TEST_GROUPS:-}
COVERAGE=${WORDPRESS_PHPUNIT_COVERAGE:-false}
REQUESTED_CONFIG=${WORDPRESS_PHPUNIT_CONFIG:-}
PARATEST_PROCESSES=${PARATEST_PROCESSES:-8}
GITHUB_SHA=${GITHUB_SHA:-local}

cd "$ROOT"

for value in "$MULTISITE" "$MEMCACHED" "$COVERAGE"; do
    case "$value" in
        true|false) ;;
        *)
            printf 'Invalid boolean value: %s\n' "$value" >&2
            exit 2
            ;;
    esac
done
if ! [[ "$PARATEST_PROCESSES" =~ ^[1-9][0-9]*$ ]]; then
    printf 'Invalid PARATEST_PROCESSES: %s\n' "$PARATEST_PROCESSES" >&2
    exit 2
fi

phpunit_version=$(vendor/bin/phpunit --version | awk 'NR == 1 { print $2 }')
case "$phpunit_version" in
    13.*) ;;
    *)
        printf 'Expected PHPUnit 13, got %s.\n' "$phpunit_version" >&2
        exit 1
        ;;
esac
if [[ ! -x vendor/bin/paratest ]]; then
    printf '%s\n' 'Current WordPress lock did not install ParaTest.' >&2
    exit 1
fi
if composer show yoast/phpunit-polyfills >/dev/null 2>&1; then
    printf '%s\n' 'Current WordPress runtime unexpectedly installed yoast/phpunit-polyfills.' >&2
    exit 1
fi

run_top_level_suite() {
    local label=$1
    shift

    set +e
    (
        set -e
        "$@"
    )
    local status=$?
    set -e

    if [[ "$status" -ne 0 ]]; then
        printf 'Top-level PHPUnit suite failed: %s (exit %s)\n' "$label" "$status" >&2
        return "$status"
    fi
}

strict_flags=(
    --fail-on-warning
    --fail-on-risky
    --fail-on-deprecation
    --fail-on-phpunit-deprecation
    --fail-on-notice
    --fail-on-phpunit-notice
    --display-deprecations
    --display-phpunit-deprecations
    --display-notices
    --display-phpunit-notices
)

if [[ "$MULTISITE" == true ]]; then
    configuration=${REQUESTED_CONFIG:-tests/phpunit/multisite.xml}
    ms_tests=run_ms_tests
    site_label=multisite
else
    configuration=${REQUESTED_CONFIG:-phpunit.xml.dist}
    ms_tests=no_ms_tests
    site_label=single-site
fi
if [[ ! -f "$configuration" ]]; then
    printf 'PHPUnit configuration does not exist: %s\n' "$configuration" >&2
    exit 1
fi

reset_prefix() {
    php tools/phpunit13/reset-test-database.php --reset-prefix "$1"
}

install_database() {
    local token=${1:-}
    if [[ -n "$token" ]]; then
        TEST_TOKEN="$token" XDEBUG_MODE=off php tests/phpunit/includes/install.php \
            wp-tests-config.php "$ms_tests" run_core_tests
    else
        env -u TEST_TOKEN -u UNIQUE_TEST_TOKEN XDEBUG_MODE=off php \
            tests/phpunit/includes/install.php wp-tests-config.php "$ms_tests" run_core_tests
    fi
}

prepare_master_database() {
    reset_prefix wptests_
    install_database
}

prepare_parallel_databases() {
    prepare_master_database
    local worker
    for worker in $(seq 1 "$PARATEST_PROCESSES"); do
        printf '==> Preparing ParaTest worker database: %s/%s (%s)\n' "$worker" "$PARATEST_PROCESSES" "$site_label"
        reset_prefix "wptests_${worker}_"
        install_database "$worker"
    done
}

list_tests() {
    local config=$1
    shift
    WP_TESTS_SKIP_INSTALL=1 XDEBUG_MODE=off vendor/bin/phpunit \
        --configuration "$config" --list-tests "$@" \
        | sed -n 's/^ - //p' | sort
}

capture_pristine_discovery() {
    local manifest=".runtime-pristine-${site_label}.txt"

    printf '\n==> Preparing pristine WordPress discovery database: %s\n' "$site_label"
    prepare_master_database
    printf '\n==> Capturing pristine WordPress discovery: %s\n' "$site_label"
    list_tests "$configuration" > "$manifest"
    if [[ ! -s "$manifest" ]]; then
        printf 'Pristine WordPress discovery is empty: %s\n' "$site_label" >&2
        exit 1
    fi
}

apply_provider_overlay() {
    local provider_path
    provider_path=$(php -r '
        require "vendor/autoload.php";
        $path = Composer\InstalledVersions::getInstallPath("wp-phpunit/wp-phpunit");
        if (! is_string($path) || $path === "") {
            fwrite(STDERR, "Cannot resolve installed wp-phpunit package path.\\n");
            exit(1);
        }
        echo rtrim($path, DIRECTORY_SEPARATOR);
    ')

    if [[ ! -d "$provider_path/includes" || ! -d "$provider_path/data" || ! -f "$provider_path/tools/core/bootstrap-phpunit13.php" ]]; then
        printf 'Installed wp-phpunit package is missing distributable harness files: %s\n' "$provider_path" >&2
        exit 1
    fi

    printf '\n==> Overlaying installed wp-phpunit harness: %s\n' "$provider_path"
    rsync -a "$provider_path/includes/" tests/phpunit/includes/
    rsync -a "$provider_path/data/" tests/phpunit/data/
    cp "$provider_path/tools/core/bootstrap-phpunit13.php" tests/phpunit/includes/bootstrap-phpunit13.php
}

verify_runtime_discovery_parity() {
    local pristine_manifest=".runtime-pristine-${site_label}.txt"
    local overlay_manifest=".runtime-overlay-${site_label}.txt"
    local missing_manifest=".runtime-missing-${site_label}.txt"
    local extra_manifest=".runtime-extra-${site_label}.txt"

    printf '\n==> Runtime discovery parity: %s\n' "$site_label"
    prepare_master_database
    list_tests "$configuration" > "$overlay_manifest"
    if [[ ! -s "$overlay_manifest" ]]; then
        printf 'Overlay WordPress discovery is empty: %s\n' "$site_label" >&2
        exit 1
    fi

    comm -23 "$pristine_manifest" "$overlay_manifest" > "$missing_manifest"
    comm -13 "$pristine_manifest" "$overlay_manifest" > "$extra_manifest"
    if [[ -s "$missing_manifest" || -s "$extra_manifest" ]]; then
        printf 'WordPress discovery changed after wp-phpunit overlay: %s\n' "$site_label" >&2
        [[ ! -s "$missing_manifest" ]] || { printf '%s\n' '--- Missing after overlay ---' >&2; cat "$missing_manifest" >&2; }
        [[ ! -s "$extra_manifest" ]] || { printf '%s\n' '--- Added after overlay ---' >&2; cat "$extra_manifest" >&2; }
        exit 1
    fi

    printf 'Discovery preserved exactly: %s (%s tests)\n' "$site_label" "$(wc -l < "$overlay_manifest")"
    rm -f "$pristine_manifest" "$overlay_manifest" "$missing_manifest" "$extra_manifest"
}

run_serial_suite() {
    local label=$1
    local config=$2
    local install_mode=$3
    shift 3

    printf '\n==> WordPress PHPUnit serial suite: %s\n' "$label"
    prepare_master_database

    local manifest=".selected-serial-${label}.txt"
    local quiet_bootstrap=0
    if [[ "$label" == ajax ]]; then
        quiet_bootstrap=1
    fi

    WP_TESTS_QUIET_BOOTSTRAP="$quiet_bootstrap" list_tests "$config" "$@" > "$manifest"
    if [[ ! -s "$manifest" ]]; then
        printf 'Selected WordPress serial suite is empty: %s\n' "$label" >&2
        exit 1
    fi
    printf 'Selected serial suite: %s (%s tests)\n' "$label" "$(wc -l < "$manifest")"

    if [[ "$install_mode" == fresh ]]; then
        env -u WP_TESTS_SKIP_INSTALL WP_TESTS_QUIET_BOOTSTRAP="$quiet_bootstrap" XDEBUG_MODE=off \
            vendor/bin/phpunit --configuration "$config" "${strict_flags[@]}" "$@"
    else
        WP_TESTS_QUIET_BOOTSTRAP="$quiet_bootstrap" WP_TESTS_SKIP_INSTALL=1 XDEBUG_MODE=off \
            vendor/bin/phpunit --configuration "$config" "${strict_flags[@]}" "$@"
    fi
    rm -f "$manifest"
}

run_parallel_default() {
    local label=$1
    local config=$2

    printf '\n==> WordPress PHPUnit ParaTest suite: %s\n' "$label"
    prepare_parallel_databases

    local config_dir parallel_config isolated_config serial_manifest
    if [[ "$config" == */* ]]; then
        config_dir=${config%/*}
        parallel_config="${config_dir}/.paratest-${label}.xml"
        isolated_config="${config_dir}/.isolated-${label}.xml"
    else
        parallel_config=".paratest-${label}.xml"
        isolated_config=".isolated-${label}.xml"
    fi
    serial_manifest=".serial-only-${label}.txt"

    php tools/phpunit13/prepare-paratest-configuration.php \
        "$ROOT" "$config" "$parallel_config" "$isolated_config" "$serial_manifest"

    local full_manifest=".selected-full-${label}.txt"
    local parallel_manifest=".selected-parallel-${label}.txt"
    local isolated_manifest=".selected-isolated-${label}.txt"
    local union_manifest=".selected-union-${label}.txt"
    local duplicate_manifest=".selected-duplicates-${label}.txt"
    local missing_manifest=".selected-missing-${label}.txt"
    local extra_manifest=".selected-extra-${label}.txt"

    list_tests "$config" > "$full_manifest"
    list_tests "$parallel_config" > "$parallel_manifest"
    list_tests "$isolated_config" > "$isolated_manifest"
    cat "$parallel_manifest" "$isolated_manifest" | sort > "$union_manifest"
    uniq -d "$union_manifest" > "$duplicate_manifest"
    comm -23 "$full_manifest" "$union_manifest" > "$missing_manifest"
    comm -13 "$full_manifest" "$union_manifest" > "$extra_manifest"

    if [[ ! -s "$full_manifest" ]]; then
        printf 'Selected WordPress suite is empty: %s\n' "$label" >&2
        exit 1
    fi
    if [[ -s "$duplicate_manifest" || -s "$missing_manifest" || -s "$extra_manifest" ]]; then
        printf 'Parallel/isolated split changed selected WordPress tests: %s\n' "$label" >&2
        [[ ! -s "$duplicate_manifest" ]] || { printf '%s\n' '--- Duplicated tests ---' >&2; cat "$duplicate_manifest" >&2; }
        [[ ! -s "$missing_manifest" ]] || { printf '%s\n' '--- Missing tests ---' >&2; cat "$missing_manifest" >&2; }
        [[ ! -s "$extra_manifest" ]] || { printf '%s\n' '--- Extra tests ---' >&2; cat "$extra_manifest" >&2; }
        exit 1
    fi

    printf 'Selected test split preserved exactly: %s (%s parallel + %s isolated = %s tests)\n' \
        "$label" "$(wc -l < "$parallel_manifest")" "$(wc -l < "$isolated_manifest")" "$(wc -l < "$full_manifest")"

    rm -rf tests/phpunit/data-paratest-*
    XDEBUG_MODE=off vendor/bin/paratest \
        --configuration "$parallel_config" \
        --processes="$PARATEST_PROCESSES" \
        "${strict_flags[@]}"

    if [[ -s "$isolated_manifest" ]]; then
        printf '\n==> WordPress PHPUnit serial-only suite: %s\n' "$label"
        XDEBUG_MODE=off vendor/bin/phpunit \
            --configuration "$isolated_config" \
            "${strict_flags[@]}"
    fi

    rm -f "$parallel_config" "$isolated_config" "$serial_manifest" \
        "$full_manifest" "$parallel_manifest" "$isolated_manifest" "$union_manifest" \
        "$duplicate_manifest" "$missing_manifest" "$extra_manifest"
}

run_xdebug_suite() {
    printf '\n==> WordPress PHPUnit Xdebug suite\n'
    prepare_master_database

    local ini_dir
    ini_dir=$(mktemp -d)
    trap 'rm -rf "$ini_dir"' RETURN
    printf '%s\n' 'zend_extension=xdebug.so' 'opcache.enable_cli=0' > "$ini_dir/99-wordpress-xdebug.ini"
    local scan_dir="${PHP_INI_SCAN_DIR:-/usr/local/etc/php/conf.d}"
    scan_dir="${scan_dir}:${ini_dir}"

    PHP_INI_SCAN_DIR="$scan_dir" XDEBUG_MODE=develop,debug php -r \
        'if (! extension_loaded("xdebug")) { fwrite(STDERR, "Xdebug did not load.\n"); exit(1); }'
    PHP_INI_SCAN_DIR="$scan_dir" WP_TESTS_SKIP_INSTALL=1 XDEBUG_MODE=develop,debug \
        vendor/bin/phpunit --configuration phpunit.xml.dist \
        "${strict_flags[@]}" --group xdebug --exclude-group __fakegroup__

    rm -rf "$ini_dir"
    trap - RETURN
}

run_coverage_suite() {
    local flag=single
    if [[ "$MULTISITE" == true ]]; then
        flag=multisite
    fi
    printf '\n==> WordPress PHPUnit coverage: %s\n' "$flag"
    if ! php -r 'exit(extension_loaded("pcov") ? 0 : 1);'; then
        printf '%s\n' 'PCOV is not loaded for a coverage run.' >&2
        exit 1
    fi
    env -u WP_TESTS_SKIP_INSTALL XDEBUG_MODE=off php \
        -d pcov.enabled=1 \
        -d "pcov.directory=$ROOT/src" \
        -d pcov.initial.files=10000 \
        vendor/bin/phpunit --configuration "$configuration" \
        "${strict_flags[@]}" \
        --coverage-clover "/artifacts/wp-code-coverage-${flag}-${GITHUB_SHA}.xml"
}

printf 'PHPUnit: %s\n' "$phpunit_version"
printf 'ParaTest: %s\n' "$(vendor/bin/paratest --version | head -n 1)"
printf 'Multisite: %s\n' "$MULTISITE"
printf 'Memcached: %s\n' "$MEMCACHED"
printf 'Groups: %s\n' "${TEST_GROUPS:-default matrix}"
printf 'Coverage: %s\n' "$COVERAGE"

mkdir -p src/wp-content/mu-plugins src/wp-content/plugins

if [[ -z "$TEST_GROUPS" && "$COVERAGE" == false ]]; then
    capture_pristine_discovery
fi
apply_provider_overlay
if [[ -z "$TEST_GROUPS" && "$COVERAGE" == false ]]; then
    verify_runtime_discovery_parity
fi

if [[ "$MEMCACHED" == true ]]; then
    cp tests/phpunit/includes/object-cache.php src/wp-content/object-cache.php
else
    rm -f src/wp-content/object-cache.php
fi

if [[ -n "$TEST_GROUPS" ]]; then
    if [[ "$TEST_GROUPS" == xdebug ]]; then
        run_top_level_suite xdebug run_xdebug_suite
    else
        run_top_level_suite "group-${TEST_GROUPS//[^0-9A-Za-z_.-]/_}" \
            run_serial_suite "group-${TEST_GROUPS//[^0-9A-Za-z_.-]/_}" "$configuration" cached --group "$TEST_GROUPS"
    fi
    exit 0
fi

if [[ "$COVERAGE" == true ]]; then
    run_top_level_suite coverage run_coverage_suite
    exit 0
fi

if [[ "$MULTISITE" == false ]]; then
    run_top_level_suite external-http run_serial_suite external-http "$configuration" cached --group external-http
fi

if [[ "$MEMCACHED" == true ]]; then
    run_top_level_suite "${site_label}-memcached" run_serial_suite "${site_label}-memcached" "$configuration" fresh
else
    run_top_level_suite "$site_label" run_parallel_default "$site_label" "$configuration"
fi

run_top_level_suite ajax run_serial_suite ajax "$configuration" cached --group ajax
if [[ "$MULTISITE" == true ]]; then
    run_top_level_suite ms-files run_serial_suite ms-files "$configuration" cached --group ms-files
fi
run_top_level_suite xdebug run_xdebug_suite

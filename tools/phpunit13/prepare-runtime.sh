#!/usr/bin/env bash
set -euo pipefail

ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)
ENGINE=${CONTAINER_ENGINE:-podman}
ENV_OUTPUT=${1:-}
LOCK_FILE="$ROOT/tools/phpunit13/runtime-image.lock"
RUNTIME_REPOSITORY=${WP_PHPUNIT_RUNTIME_IMAGE_REPOSITORY:-ghcr.io/kolotov/wp-phpunit-runtime}
RUNTIME_ENV_FILE=$(mktemp)
trap 'rm -f "$RUNTIME_ENV_FILE"' EXIT HUP INT TERM

resolve_locked_provider_ref() {
    python3 - "$ROOT/composer.lock" <<'PY'
import json
import sys

with open(sys.argv[1], encoding='utf-8') as handle:
    lock = json.load(handle)

for package in lock.get('packages', []) + lock.get('packages-dev', []):
    if package.get('name') != 'wp-phpunit/wp-phpunit':
        continue
    source = package.get('source') or {}
    allowed_urls = {
        'https://github.com/kolotov/wp-phpunit.git',
        'https://github.com/kolotov/wp-phpunit',
        'git@github.com:kolotov/wp-phpunit.git',
    }
    if source.get('type') != 'git' or source.get('url') not in allowed_urls:
        raise SystemExit(f"Unexpected wp-phpunit VCS source: {source.get('url', 'missing')}")
    reference = source.get('reference') or ''
    if not reference:
        raise SystemExit('wp-phpunit lock entry has no VCS reference')
    print(reference)
    break
else:
    raise SystemExit('wp-phpunit/wp-phpunit is missing from composer.lock')
PY
}

read_runtime_lock() {
    python3 - "$LOCK_FILE" <<'PY'
import json
import sys

with open(sys.argv[1], encoding='utf-8') as handle:
    lock = json.load(handle)

required = (
    'provider_reference',
    'runtime_image',
    'mariadb_image',
    'memcached_image',
    'runtime_contract',
)
for key in required:
    value = lock.get(key)
    if value in (None, ''):
        raise SystemExit(f'runtime-image.lock is missing {key}')

print(lock['provider_reference'])
print(lock['runtime_image'])
print(lock['mariadb_image'])
print(lock['memcached_image'])
print(lock['runtime_contract'])
PY
}

emit() {
    local line=$1
    if [[ -n "$ENV_OUTPUT" ]]; then
        printf '%s\n' "$line" >> "$ENV_OUTPUT"
    else
        printf '%s\n' "$line"
    fi
}

if ! command -v "$ENGINE" >/dev/null 2>&1; then
    printf 'Container engine not found: %s\n' "$ENGINE" >&2
    exit 1
fi

if [[ -n "${WP_PHPUNIT_SOURCE_HOST:-}" ]]; then
    RUNTIME_SOURCE=$(cd "$WP_PHPUNIT_SOURCE_HOST" && pwd)
    RUNTIME_ENV_SOURCE="$RUNTIME_SOURCE/containers/phpunit13/runtime.env"
    RUNTIME_CONTAINERFILE="$RUNTIME_SOURCE/containers/phpunit13/Containerfile"
    if [[ ! -f "$RUNTIME_ENV_SOURCE" || ! -f "$RUNTIME_CONTAINERFILE" ]]; then
        printf 'Canonical runtime files are missing under %s.\n' "$RUNTIME_SOURCE" >&2
        exit 1
    fi

    cp "$RUNTIME_ENV_SOURCE" "$RUNTIME_ENV_FILE"
    # shellcheck disable=SC1090
    source "$RUNTIME_ENV_FILE"
    if [[ "${RUNTIME_CONTRACT_VERSION:-}" != '1' ]]; then
        printf 'Unsupported local runtime contract: %s.\n' "${RUNTIME_CONTRACT_VERSION:-missing}" >&2
        exit 1
    fi

    RUNTIME_REF=local-override
    runtime_fingerprint=$(
        cat \
            "$RUNTIME_CONTAINERFILE" \
            "$RUNTIME_ENV_SOURCE" \
            "$RUNTIME_SOURCE/containers/phpunit13/entrypoint.sh" \
            | sha256sum \
            | awk '{ print substr($1, 1, 16) }'
    )
    RUNTIME_IMAGE="wordpress-phpunit-runtime:local-${runtime_fingerprint}"
    DB_IMAGE="docker.io/library/mariadb:${MARIADB_VERSION}"
    MEMCACHED_IMAGE="docker.io/library/memcached:${MEMCACHED_VERSION}-alpine"

    printf 'Using explicit unpublished wp-phpunit source for runtime and Composer: %s\n' "$RUNTIME_SOURCE"
    "$ENGINE" build --pull \
        --build-arg RUNTIME_REVISION=local-override \
        --file "$RUNTIME_CONTAINERFILE" \
        --tag "$RUNTIME_IMAGE" \
        "$RUNTIME_SOURCE"
    "$ENGINE" pull "$DB_IMAGE"
    "$ENGINE" pull "$MEMCACHED_IMAGE"
else
    PROVIDER_REF=$(resolve_locked_provider_ref)
    if [[ ! -f "$LOCK_FILE" ]]; then
        printf 'Missing immutable runtime lock: %s\n' "$LOCK_FILE" >&2
        printf '%s\n' 'Publish the paired wp-phpunit runtime, refresh composer.lock, then generate runtime-image.lock.' >&2
        exit 1
    fi

    mapfile -t runtime_lock < <(read_runtime_lock)
    RUNTIME_REF=${runtime_lock[0]}
    RUNTIME_IMAGE=${runtime_lock[1]}
    DB_IMAGE=${runtime_lock[2]}
    MEMCACHED_IMAGE=${runtime_lock[3]}
    EXPECTED_CONTRACT=${runtime_lock[4]}

    if [[ "$RUNTIME_REF" != "$PROVIDER_REF" ]]; then
        printf 'Runtime/provider mismatch: composer.lock=%s runtime-image.lock=%s.\n' "$PROVIDER_REF" "$RUNTIME_REF" >&2
        exit 1
    fi
    case "$RUNTIME_IMAGE" in
        "$RUNTIME_REPOSITORY"@sha256:*) ;;
        *)
            printf 'Runtime lock contains an unexpected runtime repository: %s\n' "$RUNTIME_IMAGE" >&2
            exit 1
            ;;
    esac
    case "$DB_IMAGE" in
        docker.io/library/mariadb@sha256:*) ;;
        *)
            printf 'Runtime lock contains an unexpected MariaDB repository: %s\n' "$DB_IMAGE" >&2
            exit 1
            ;;
    esac
    case "$MEMCACHED_IMAGE" in
        docker.io/library/memcached@sha256:*) ;;
        *)
            printf 'Runtime lock contains an unexpected Memcached repository: %s\n' "$MEMCACHED_IMAGE" >&2
            exit 1
            ;;
    esac

    "$ENGINE" pull "$RUNTIME_IMAGE"
    "$ENGINE" pull "$DB_IMAGE"
    "$ENGINE" pull "$MEMCACHED_IMAGE"

    image_revision=$("$ENGINE" image inspect "$RUNTIME_IMAGE" --format '{{ index .Config.Labels "org.opencontainers.image.revision" }}')
    if [[ "$image_revision" != "$RUNTIME_REF" ]]; then
        printf 'Runtime image revision mismatch: expected %s, got %s.\n' "$RUNTIME_REF" "$image_revision" >&2
        exit 1
    fi
    image_source=$("$ENGINE" image inspect "$RUNTIME_IMAGE" --format '{{ index .Config.Labels "org.opencontainers.image.source" }}')
    if [[ "$image_source" != 'https://github.com/kolotov/wp-phpunit' ]]; then
        printf 'Runtime image source mismatch: %s\n' "$image_source" >&2
        exit 1
    fi

    "$ENGINE" run --rm --entrypoint cat "$RUNTIME_IMAGE" \
        /usr/local/share/wp-phpunit/runtime.env > "$RUNTIME_ENV_FILE"
    # shellcheck disable=SC1090
    source "$RUNTIME_ENV_FILE"
    if [[ "${RUNTIME_CONTRACT_VERSION:-}" != "$EXPECTED_CONTRACT" || "$EXPECTED_CONTRACT" != '1' ]]; then
        printf 'Runtime contract mismatch: image=%s lock=%s expected=1.\n' \
            "${RUNTIME_CONTRACT_VERSION:-missing}" "$EXPECTED_CONTRACT" >&2
        exit 1
    fi
fi

actual_memcached_version=$("$ENGINE" run --rm --entrypoint memcached "$MEMCACHED_IMAGE" --version)
case "$actual_memcached_version" in
    *"$MEMCACHED_VERSION"*) ;;
    *)
        printf 'Unexpected Memcached version: %s; expected %s.\n' "$actual_memcached_version" "$MEMCACHED_VERSION" >&2
        exit 1
        ;;
esac

printf '\n==> Shared PHPUnit runtime\n'
printf 'Provider ref: %s\n' "$RUNTIME_REF"
printf 'Runtime image: %s\n' "$RUNTIME_IMAGE"
printf 'MariaDB image: %s\n' "$DB_IMAGE"
printf 'Memcached image: %s\n' "$MEMCACHED_IMAGE"
"$ENGINE" run --rm "$RUNTIME_IMAGE" bash -lc '
    printf "Ubuntu: %s\n" "$(. /etc/os-release && printf "%s" "$VERSION_ID")"
    printf "PHP: %s\n" "$(php -r '\''echo PHP_VERSION;'\'')"
    printf "Imagick: %s\n" "$(php -r '\''echo phpversion("imagick");'\'')"
    printf "Ghostscript: %s\n" "$(gs --version)"
    printf "Timezone DB: %s\n" "$(php -r '\''echo timezone_version_get();'\'')"
    printf "Node: %s\n" "$(node --version)"
    printf "npm: %s\n" "$(npm --version)"
    printf "Composer: %s\n" "$(composer --version --no-ansi | awk '\''NR == 1 { print $3 }'\'')"
    printf "FPM: %s\n" "$(php-fpm -v | head -n 1)"
'

emit "LOCAL_PHP_IMAGE=$RUNTIME_IMAGE"
emit "LOCAL_PHP_VERSION=$PHP_VERSION"
emit "LOCAL_DB_IMAGE=$DB_IMAGE"
emit "LOCAL_MEMCACHED_IMAGE=$MEMCACHED_IMAGE"
emit "LOCAL_DB_VERSION=$MARIADB_VERSION"

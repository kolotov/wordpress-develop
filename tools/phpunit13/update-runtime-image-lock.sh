#!/usr/bin/env bash
set -euo pipefail

ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)
ENGINE=${CONTAINER_ENGINE:-podman}
LOCK_FILE="$ROOT/tools/phpunit13/runtime-image.lock"
RUNTIME_REPOSITORY=${WP_PHPUNIT_RUNTIME_IMAGE_REPOSITORY:-ghcr.io/kolotov/wp-phpunit-runtime}
TEMP_ENV=$(mktemp)
trap 'rm -f "$TEMP_ENV"' EXIT HUP INT TERM

resolve_repo_digest() {
    local image=$1
    local expected_repository=$2

    "$ENGINE" image inspect "$image" | python3 -c '
import json
import sys

repository = sys.argv[1]
data = json.load(sys.stdin)[0]
platform_digest = data.get("Digest") or ""
prefix = repository + "@sha256:"
matching = sorted(digest for digest in (data.get("RepoDigests") or []) if digest.startswith(prefix))
if not matching:
    raise SystemExit(f"No RepoDigest for {repository}")

index_candidates = [digest for digest in matching if not digest.endswith(platform_digest)] if platform_digest else []
if len(index_candidates) == 1:
    print(index_candidates[0])
    raise SystemExit(0)
if len(index_candidates) > 1:
    raise SystemExit(f"Ambiguous multi-platform RepoDigests for {repository}: {index_candidates}")
if len(matching) == 1:
    print(matching[0])
    raise SystemExit(0)
raise SystemExit(f"Ambiguous RepoDigests for {repository}: {matching}")
' "$expected_repository"
}

provider_ref=$(
    python3 - "$ROOT/composer.lock" <<'PY'
import json
import sys
with open(sys.argv[1], encoding='utf-8') as handle:
    lock = json.load(handle)
for package in lock.get('packages', []) + lock.get('packages-dev', []):
    if package.get('name') == 'wp-phpunit/wp-phpunit':
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
)

runtime_tag="${RUNTIME_REPOSITORY}:${provider_ref}"
"$ENGINE" pull "$runtime_tag"
revision=$("$ENGINE" image inspect "$runtime_tag" --format '{{ index .Config.Labels "org.opencontainers.image.revision" }}')
if [[ "$revision" != "$provider_ref" ]]; then
    printf 'Published runtime revision mismatch: expected %s, got %s.\n' "$provider_ref" "$revision" >&2
    exit 1
fi
source_label=$("$ENGINE" image inspect "$runtime_tag" --format '{{ index .Config.Labels "org.opencontainers.image.source" }}')
if [[ "$source_label" != 'https://github.com/kolotov/wp-phpunit' ]]; then
    printf 'Published runtime source mismatch: %s\n' "$source_label" >&2
    exit 1
fi
runtime_image=$(resolve_repo_digest "$runtime_tag" "$RUNTIME_REPOSITORY")

"$ENGINE" run --rm --entrypoint cat "$runtime_image" \
    /usr/local/share/wp-phpunit/runtime.env > "$TEMP_ENV"
# shellcheck disable=SC1090
source "$TEMP_ENV"
if [[ "${RUNTIME_CONTRACT_VERSION:-}" != '1' ]]; then
    printf 'Unsupported published runtime contract: %s.\n' "${RUNTIME_CONTRACT_VERSION:-missing}" >&2
    exit 1
fi

mariadb_tag="docker.io/library/mariadb:${MARIADB_VERSION}"
memcached_tag="docker.io/library/memcached:${MEMCACHED_VERSION}-alpine"
"$ENGINE" pull "$mariadb_tag"
"$ENGINE" pull "$memcached_tag"
mariadb_image=$(resolve_repo_digest "$mariadb_tag" 'docker.io/library/mariadb')
memcached_image=$(resolve_repo_digest "$memcached_tag" 'docker.io/library/memcached')

actual_mariadb_version=$("$ENGINE" run --rm --entrypoint mariadbd "$mariadb_image" --version)
case "$actual_mariadb_version" in
    *"$MARIADB_VERSION"*) ;;
    *)
        printf 'Unexpected MariaDB image version: %s; expected %s.\n' "$actual_mariadb_version" "$MARIADB_VERSION" >&2
        exit 1
        ;;
esac

actual_memcached_version=$("$ENGINE" run --rm --entrypoint memcached "$memcached_image" --version)
case "$actual_memcached_version" in
    *"$MEMCACHED_VERSION"*) ;;
    *)
        printf 'Unexpected Memcached image version: %s; expected %s.\n' "$actual_memcached_version" "$MEMCACHED_VERSION" >&2
        exit 1
        ;;
esac

python3 - "$LOCK_FILE" "$provider_ref" "$runtime_image" "$mariadb_image" "$memcached_image" "$RUNTIME_CONTRACT_VERSION" <<'PY'
import json
import sys

path, provider_ref, runtime_image, mariadb_image, memcached_image, contract = sys.argv[1:]
payload = {
    'provider_reference': provider_ref,
    'runtime_image': runtime_image,
    'mariadb_image': mariadb_image,
    'memcached_image': memcached_image,
    'runtime_contract': int(contract),
}
with open(path, 'w', encoding='utf-8') as handle:
    json.dump(payload, handle, indent=2)
    handle.write('\n')
PY

printf 'Updated %s for wp-phpunit %s.\n' "$LOCK_FILE" "$provider_ref"
printf 'Runtime: %s\nMariaDB: %s\nMemcached: %s\n' "$runtime_image" "$mariadb_image" "$memcached_image"

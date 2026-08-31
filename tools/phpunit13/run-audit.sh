#!/usr/bin/env bash
set -euo pipefail

ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)
ENGINE=${CONTAINER_ENGINE:-podman}
BASELINE=${WORDPRESS_DEVELOP_BASELINE_REF:-8b91cc16cc78b817386b406f50ced8df86fb466d}
ENV_FILE=$(mktemp)
trap 'rm -f "$ENV_FILE"' EXIT HUP INT TERM

if ! git -C "$ROOT" cat-file -e "${BASELINE}^{commit}" 2>/dev/null; then
    git -C "$ROOT" fetch --depth=1 origin "$BASELINE"
fi

"$ROOT/tools/phpunit13/prepare-runtime.sh" "$ENV_FILE"
# shellcheck disable=SC1090
source "$ENV_FILE"

args=(
    --rm
    -v "$ROOT:/var/www:ro"
    -w /var/www
    -e "WORDPRESS_DEVELOP_BASELINE_REF=$BASELINE"
    -e "WORDPRESS_DEVELOP_EXPECTED_HEAD=$(git -C "$ROOT" rev-parse HEAD)"
    -e GIT_CONFIG_COUNT=1
    -e GIT_CONFIG_KEY_0=safe.directory
    -e GIT_CONFIG_VALUE_0=/var/www
)
if [[ "$ENGINE" == "podman" ]]; then
    args+=(--security-opt label=disable --userns=keep-id)
fi

"$ENGINE" run "${args[@]}" "$LOCAL_PHP_IMAGE" \
    php tools/phpunit13/audit-migration-tests.php /var/www

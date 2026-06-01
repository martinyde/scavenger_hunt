#!/usr/bin/env bash
#
# preview-resolve.sh
#
# Resolve a GitHub issue number to a worktree path using the project's
# branch naming convention: issue-<N>-<short-slug>.
#
# Usage:
#   bin/preview-resolve.sh <issue-number>
#
# Exit codes:
#   0  – exactly one matching worktree found; prints absolute path on stdout
#   1  – usage error (missing/invalid issue number)
#   2  – no worktree found for the given issue number
#   3  – more than one worktree matches the given issue number

set -euo pipefail

usage() {
    cat >&2 <<EOF
Usage: $(basename "$0") <issue-number>

Resolves a GitHub issue number to a local git worktree path by matching
the branch naming convention 'issue-<N>-<slug>'.

Prints the absolute worktree path on stdout; errors go to stderr.
EOF
}

if [[ $# -ne 1 ]]; then
    usage
    exit 1
fi

issue="$1"

if ! [[ "$issue" =~ ^[1-9][0-9]*$ ]]; then
    echo "error: issue number must be a positive integer, got '$issue'" >&2
    exit 1
fi

# Walk porcelain output: every block has a 'worktree <path>' line and a
# 'branch refs/heads/<branch>' line (detached HEADs have neither). We
# collect worktrees whose branch matches issue-<N>-* exactly.
declare -a matches=()
current_worktree=""

while IFS= read -r line; do
    if [[ -z "$line" ]]; then
        current_worktree=""
        continue
    fi

    case "$line" in
        worktree\ *)
            current_worktree="${line#worktree }"
            ;;
        branch\ refs/heads/issue-${issue}-*)
            if [[ -n "$current_worktree" ]]; then
                matches+=("$current_worktree")
            fi
            ;;
    esac
done < <(git worktree list --porcelain)

case ${#matches[@]} in
    0)
        echo "error: no worktree found for issue #${issue}" >&2
        echo "       expected a branch matching 'issue-${issue}-<slug>' in 'git worktree list'" >&2
        exit 2
        ;;
    1)
        echo "${matches[0]}"
        ;;
    *)
        echo "error: multiple worktrees match issue #${issue}:" >&2
        for path in "${matches[@]}"; do
            echo "       - $path" >&2
        done
        exit 3
        ;;
esac

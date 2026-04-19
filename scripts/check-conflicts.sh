#!/usr/bin/env bash
set -euo pipefail

if rg -n "^(<<<<<<<|=======|>>>>>>>)" app database routes >/tmp/conflicts.txt; then
  echo "❌ Merge conflict markers found:"
  cat /tmp/conflicts.txt
  exit 1
fi

echo "✅ No merge conflict markers found in app/, database/, routes/."

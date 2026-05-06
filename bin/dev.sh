#!/usr/bin/env bash
# Restart the GreenAmal local stack: MySQL (Homebrew) + PHP built-in server.
# Usage:  ./bin/dev.sh          → starts both
#         ./bin/dev.sh stop     → stops MySQL + kills any running php -S
#
set -e

PROJECT_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PORT="${PORT:-8000}"

case "${1:-start}" in
  stop)
    echo "→ Stopping MySQL …"
    brew services stop mysql >/dev/null 2>&1 || true
    pkill -f "php -S localhost:${PORT}" 2>/dev/null || true
    echo "✓ Stopped."
    ;;

  status)
    echo "MySQL:" && brew services list | grep -E '^mysql\b' || echo "  not installed"
    echo
    echo "PHP server on :${PORT}:"
    if pgrep -fl "php -S localhost:${PORT}" >/dev/null; then
      pgrep -fl "php -S localhost:${PORT}"
    else
      echo "  (not running)"
    fi
    ;;

  start|*)
    echo "→ Starting MySQL …"
    brew services start mysql >/dev/null

    # Wait until MySQL is reachable (max 10 s)
    for i in {1..20}; do
      if mysqladmin -h 127.0.0.1 -u root ping --silent 2>/dev/null; then break; fi
      sleep 0.5
    done

    if ! mysqladmin -h 127.0.0.1 -u root ping --silent 2>/dev/null; then
      echo "✗ MySQL didn't come up. Check 'brew services list' and 'tail -n 50 /opt/homebrew/var/mysql/*.err'"
      exit 1
    fi
    echo "✓ MySQL ready."

    # Kill any stale PHP server on the port
    pkill -f "php -S localhost:${PORT}" 2>/dev/null || true

    cd "$PROJECT_ROOT"
    echo "→ Starting PHP server on http://localhost:${PORT}/"
    echo "  (Ctrl+C to stop)"
    php -S "localhost:${PORT}" -t .
    ;;
esac

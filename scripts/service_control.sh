#!/usr/bin/env bash
# ==============================================================================
# Service Control Script (On / Off / Restart / Status)
# Services: Apache2 (Web Server), MySQL (Database)
# ==============================================================================
set -euo pipefail

ACTION="${1:-}"
TARGET_SERVICE="${2:-all}"

function print_usage() {
    echo "Usage: $0 {start|stop|restart|reload|status} [apache2|mysql|all]"
    echo ""
    echo "Commands:"
    echo "  start    - Turn ON service(s)"
    echo "  stop     - Turn OFF service(s)"
    echo "  restart  - Restart service(s)"
    echo "  reload   - Gracefully reload service(s)"
    echo "  status   - View service status and port listeners"
    echo ""
    echo "Targets:"
    echo "  all      - Both Apache2 and MySQL (default)"
    echo "  apache2  - Apache HTTP Server only"
    echo "  mysql    - MySQL Database Server only"
    exit 1
}

if [[ -z "${ACTION}" || ! "${ACTION}" =~ ^(start|stop|restart|reload|status)$ ]]; then
    print_usage
fi

function run_sudo() {
    if [ "$EUID" -eq 0 ]; then
        "$@"
    else
        sudo "$@"
    fi
}

function check_service() {
    local svc="$1"
    if systemctl is-active --quiet "${svc}"; then
        echo -e "  - \e[32m● ${svc} is RUNNING (active)\e[0m"
    else
        echo -e "  - \e[31m○ ${svc} is STOPPED (inactive)\e[0m"
    fi
}

function manage_unit() {
    local cmd="$1"
    local svc="$2"
    echo "[INFO] Executing '${cmd}' on ${svc}..."
    run_sudo systemctl "${cmd}" "${svc}"
    check_service "${svc}"
}

SERVICES=()
case "${TARGET_SERVICE}" in
    apache2)
        SERVICES=("apache2")
        ;;
    mysql)
        SERVICES=("mysql")
        ;;
    all)
        SERVICES=("mysql" "apache2")
        ;;
    *)
        echo "[ERROR] Unknown service: '${TARGET_SERVICE}'" >&2
        print_usage
        ;;
esac

echo "========================================================"
echo " Service Control: ${ACTION^^} -> ${TARGET_SERVICE^^}"
echo "========================================================"

if [ "${ACTION}" == "status" ]; then
    for svc in "${SERVICES[@]}"; do
        check_service "${svc}"
    done
    echo ""
    echo "[Listening Ports Overview]"
    ss -tulpn | grep -E ':80|:443|:8080|:3306' || true
else
    # For start: start mysql first, then apache2
    # For stop: stop apache2 first, then mysql
    if [ "${ACTION}" == "stop" ]; then
        for ((i=${#SERVICES[@]}-1; i>=0; i--)); do
            manage_unit "${ACTION}" "${SERVICES[i]}"
        done
    else
        for svc in "${SERVICES[@]}"; do
            manage_unit "${ACTION}" "${svc}"
        done
    fi
fi

echo "========================================================"
echo "[SUCCESS] Action '${ACTION}' completed."

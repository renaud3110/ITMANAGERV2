#!/bin/bash
# Test rapide de l'API inventaire (à supprimer en prod)
API_URL="${1:-https://it.rgdsystems.be/api/inventory.php}"
API_KEY="${2:-itmanager-agent-2024-secure-key-change-me}"

curl -s -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -H "X-Api-Key: $API_KEY" \
  -d '{"hostname":"TEST-PC","processor":"Intel Test","os_name":"Windows","os_version":"11","ram_total_bytes":16777216000,"ram_used_bytes":8388608000}'

#!/bin/bash
# Compile l'agent inventaire pour Linux et Windows
cd "$(dirname "$0")"

echo "Build agent inventaire..."
go build -o itmanager-agent && echo "  ✓ Linux: itmanager-agent"
GOOS=windows GOARCH=amd64 go build -o itmanager-agent.exe && echo "  ✓ Windows: itmanager-agent.exe"

echo "Done."

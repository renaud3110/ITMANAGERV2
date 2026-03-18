#!/bin/bash
# Compile l'agent moniteur pour Windows (64-bit et 32-bit)
cd "$(dirname "$0")"

echo "Build agent monitor Windows 64-bit..."
GOOS=windows GOARCH=amd64 go build -o itmanager-monitor.exe
if [ $? -eq 0 ]; then
    echo "  ✓ itmanager-monitor.exe (64-bit)"
else
    echo "  ✗ Échec 64-bit"
fi

echo "Build agent monitor Windows 32-bit..."
GOOS=windows GOARCH=386 go build -o itmanager-monitor-32.exe
if [ $? -eq 0 ]; then
    echo "  ✓ itmanager-monitor-32.exe (32-bit)"
else
    echo "  ✗ Échec 32-bit"
fi

echo "Done."
echo ""
echo "Si l'erreur 'non compatible avec Windows' apparaît:"
echo "  - Utilisez itmanager-monitor-32.exe sur Windows 32-bit"
echo "  - Utilisez itmanager-monitor.exe sur Windows 64-bit"

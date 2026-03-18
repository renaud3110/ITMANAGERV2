<?php
/**
 * Parse la sortie de l'agent NAS (smartctl -i/-H, mdadm, partages, volumes)
 * @return array { 'shares' => [], 'volumes' => [], 'disks' => [], 'raid' => [] }
 */
function parse_syno_audit_output($text) {
    $shares = [];
    $volumes = [];
    $disks = [];
    $raidArrays = [];
    $raidRaw = '';

    $lines = preg_split('/\r\n|\r|\n/', $text);
    $section = '';
    $currentDisk = null;
    $currentRaid = null;

    foreach ($lines as $line) {
        $lineRaw = $line;
        $line = rtrim($line);

        if (preg_match('/^=====\s*(.+?)\s*=====$/', $line, $m)) {
            if ($currentDisk !== null) {
                $disks[] = $currentDisk;
            }
            if ($currentRaid !== null) {
                $raidArrays[] = $currentRaid;
            }
            $section = strtolower(trim($m[1]));
            $currentDisk = null;
            $currentRaid = null;
            continue;
        }

        if ($section === 'partages') {
            if (strpos($line, '|') !== false) {
                $p = explode('|', $line, 2);
                $shares[] = ['name' => trim($p[0]), 'path' => trim($p[1] ?? '')];
            } elseif (trim($line) !== '') {
                $shares[] = ['name' => trim($line), 'path' => ''];
            }
        }

        if ($section === 'disques detectes' || $section === 'disques détectes') {
            if (preg_match('/^----\s+(\/dev\/\S+)\s+----$/', $line, $m)) {
                if ($currentDisk !== null) {
                    $disks[] = $currentDisk;
                }
                $currentDisk = ['name' => $m[1], 'device' => $m[1], 'id' => $m[1], 'status' => 'unknown', 'smart_status' => '', 'model' => '', 'vendor' => '', 'serial' => '', 'size_total' => 0, 'details' => []];
            } elseif ($line === '--- SMART HEALTH ---') {
                continue;
            } elseif ($currentDisk !== null) {
                if (preg_match('/(?:Model (?:Family|Number|Device)|Product)[^:]*:\s*(.+)/i', $line, $mm)) {
                    $product = trim($mm[1]);
                    $currentDisk['model'] = (!empty($currentDisk['vendor']) ? $currentDisk['vendor'] . ' ' : '') . $product;
                } elseif (preg_match('/Vendor[^:]*:\s*(.+)/i', $line, $mm)) {
                    $currentDisk['vendor'] = trim($mm[1]);
                } elseif (preg_match('/Serial number[^:]*:\s*(.+)/i', $line, $mm)) {
                    $currentDisk['serial'] = trim($mm[1]);
                } elseif (preg_match('/User Capacity[^:]*:\s*([\d,\s]+)\s*bytes/i', $line, $mm)) {
                    $currentDisk['size_total'] = (int) preg_replace('/[\s,]/', '', $mm[1]);
                } elseif (preg_match('/User Capacity[^[]*\[([^\]]+)\]/i', $line, $mm)) {
                    $currentDisk['size_h'] = trim($mm[1]);
                } elseif (stripos($line, 'SMART overall') !== false || stripos($line, 'SMART Health') !== false) {
                    $currentDisk['smart_status'] = trim(preg_replace('/.*(?:SMART overall[- ]?health|SMART Health)[^:]*:\s*/i', '', $line));
                    $currentDisk['status'] = (stripos($line, 'PASSED') !== false || stripos($line, 'OK') !== false) ? 'normal' : 'warning';
                } elseif (preg_match('/^\s*(\d+)\s+(Reallocated|Pending|Offline|Temperature).*$/i', $line, $m)) {
                    $currentDisk['details'][] = $m[2] . ': ' . $m[1];
                }
            }
        }

        if ($section === 'raid mdadm') {
            if (preg_match('/^----\s+(\/dev\/\S+)\s+----$/', $line, $m)) {
                if ($currentRaid !== null) {
                    $raidArrays[] = $currentRaid;
                }
                $currentRaid = ['device' => $m[1], 'raid_level' => '', 'array_size' => 0, 'array_size_h' => '', 'state' => '', 'active_devices' => ''];
            } elseif ($currentRaid !== null) {
                if (preg_match('/Raid Level[^:]*:\s*(.+)/i', $line, $mm)) {
                    $currentRaid['raid_level'] = trim($mm[1]);
                } elseif (preg_match('/Array Size[^:]*:\s*(\d+)\s*\(([^)]+)\)/i', $line, $mm)) {
                    $currentRaid['array_size'] = (int) $mm[1] * 512;
                    $currentRaid['array_size_h'] = trim($mm[2]);
                } elseif (preg_match('/State[^:]*:\s*(.+)/i', $line, $mm)) {
                    $currentRaid['state'] = trim($mm[1]);
                } elseif (preg_match('/Active Devices[^:]*:\s*(.+)/i', $line, $mm)) {
                    $currentRaid['active_devices'] = trim($mm[1]);
                }
            }
        }

        if (strpos($section, 'raid') !== false && $section !== 'raid mdadm') {
            $raidRaw .= $lineRaw . "\n";
        }

        if ($section === 'volumes') {
            if (preg_match('/^\S+\s+(\d+\.?\d*[KMGTPE]?)\s+(\d+\.?\d*[KMGTPE]?)\s+(\d+\.?\d*[KMGTPE]?)\s+(\d+%)\s+(.+)$/', $line, $m)) {
                $mount = trim($m[5]);
                if ($mount !== 'Mounted' && $mount !== 'on' && $mount !== '') {
                    $sizeBytes = _parse_size_to_bytes(trim($m[1]));
                    $usedBytes = _parse_size_to_bytes(trim($m[2]));
                    $volumes[] = [
                        'name' => basename($mount) ?: $mount,
                        'mount' => $mount,
                        'size' => $sizeBytes,
                        'used' => $usedBytes,
                        'size_h' => trim($m[1]),
                        'used_h' => trim($m[2]),
                        'avail_h' => trim($m[3]),
                        'use_pct' => trim($m[4]),
                        'status' => 'normal',
                    ];
                }
            }
        }
    }

    if ($currentDisk !== null) {
        $disks[] = $currentDisk;
    }
    if ($currentRaid !== null) {
        $raidArrays[] = $currentRaid;
    }

    return [
        'shares' => $shares,
        'volumes' => $volumes,
        'disks' => $disks,
        'raid' => $raidArrays,
    ];
}

function _parse_size_to_bytes($s) {
    $s = trim($s);
    if ($s === '') return 0;
    $n = (float) preg_replace('/[^0-9.]/', '', $s);
    $u = strtoupper(preg_replace('/[0-9.]/', '', $s));
    $k = 1024;
    if ($u === 'T') $n *= $k * $k * $k * $k;
    elseif ($u === 'G') $n *= $k * $k * $k;
    elseif ($u === 'M') $n *= $k * $k * $k;
    elseif ($u === 'K') $n *= $k;
    return (int) round($n);
}

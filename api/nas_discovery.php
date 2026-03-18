<?php
/**
 * Découverte du contenu NAS (partages, volumes)
 * Supporte Synology DSM (FileStation + Storage), QNAP (basique), SMB générique (à venir)
 */

class NasDiscovery {

    /**
     * @param array $nas Données du NAS (host, port, type)
     * @param string $username
     * @param string $password
     * @return array { success: bool, shares?: array, volumes?: array, error?: string }
     */
    public function discover($nas, $username, $password) {
        $type = strtolower($nas['type'] ?? 'synology');
        $host = $nas['host'] ?? '';
        $port = (int)($nas['port'] ?? 5000);

        if (empty($host)) {
            return ['success' => false, 'error' => 'Hôte non défini'];
        }

        switch ($type) {
            case 'synology':
                return $this->discoverSynology($host, $port, $username, $password, $nas);
            case 'qnap':
                return $this->discoverQnap($host, $port, $username, $password, $nas);
            default:
                return ['success' => false, 'error' => 'Type NAS non supporté pour la découverte: ' . $type];
        }
    }

    /**
     * Synology DSM : login puis FileStation list_share + Storage volumes
     */
    private function discoverSynology($host, $port, $username, $password, $nas) {
        $useHttps = ($port === 5001 || $port === 443);
        $scheme = $useHttps ? 'https' : 'http';
        $baseUrl = $scheme . '://' . $host . ':' . $port;

        // 1. Login - obtenir le SID
        $loginUrl = $baseUrl . '/webapi/auth.cgi?api=SYNO.API.Auth&version=6&method=login&account=' . urlencode($username) . '&passwd=' . urlencode($password) . '&format=sid&session=FileStation';

        $ctx = stream_context_create([
            'http' => ['timeout' => 15],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);
        $loginResp = @file_get_contents($loginUrl, false, $ctx);
        if ($loginResp === false) {
            return ['success' => false, 'error' => 'Impossible de joindre le NAS (vérifier host/port, connexion réseau ou pare-feu)'];
        }
        $loginData = json_decode($loginResp, true);
        if (!$loginData || empty($loginData['data']['sid'])) {
            $err = $loginData['error']['code'] ?? 0;
            $msg = $loginData['error']['errors'] ?? 'Login échoué';
            if ($err === 400) $msg = 'Identifiants incorrects';
            if ($err === 401) $msg = 'Identifiants incorrects';
            return ['success' => false, 'error' => 'Authentification échouée: ' . (is_array($msg) ? json_encode($msg) : $msg)];
        }
        $sid = $loginData['data']['sid'];

        // 2. Liste des partages (FileStation)
        $shareUrl = $baseUrl . '/webapi/entry.cgi?api=SYNO.FileStation.List&version=2&method=list_share&additional=volume_status&_sid=' . urlencode($sid);
        $shareResp = @file_get_contents($shareUrl, false, $ctx);
        $shares = [];
        if ($shareResp !== false) {
            $shareData = json_decode($shareResp, true);
            if (!empty($shareData['data']['shares'])) {
                foreach ($shareData['data']['shares'] as $s) {
                    $shares[] = [
                        'name' => $s['name'] ?? '',
                        'path' => $s['vol_path'] ?? '',
                        'desc' => $s['desc'] ?? ''
                    ];
                }
            }
        }

        // 3. Volumes (Storage)
        $volUrl = $baseUrl . '/webapi/entry.cgi?api=SYNO.Storage.CGI.Storage&version=1&method=load&_sid=' . urlencode($sid);
        $volResp = @file_get_contents($volUrl, false, $ctx);
        $volumes = [];
        if ($volResp !== false) {
            $volData = json_decode($volResp, true);
            if (!empty($volData['data']['volumes'])) {
                foreach ($volData['data']['volumes'] as $v) {
                    $volumes[] = [
                        'name' => $v['display_name'] ?? $v['name'] ?? '',
                        'size' => $v['size'] ?? 0,
                        'used' => $v['used_size'] ?? 0,
                        'status' => $v['status'] ?? ''
                    ];
                }
            }
        }

        // Logout (optionnel)
        $logoutUrl = $baseUrl . '/webapi/auth.cgi?api=SYNO.API.Auth&version=1&method=logout&_sid=' . urlencode($sid);
        @file_get_contents($logoutUrl, false, $ctx);

        // Sauvegarder le résultat en BDD
        $this->saveDiscovery($nas['id'] ?? 0, $shares, $volumes);

        return [
            'success' => true,
            'shares' => $shares,
            'volumes' => $volumes
        ];
    }

    /**
     * QNAP QTS - API différente (basique)
     */
    private function discoverQnap($host, $port, $username, $password, $nas) {
        $baseUrl = 'https://' . $host . ':' . ($port ?: 443);
        $ctx = stream_context_create([
            'http' => ['timeout' => 15],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);
        // QNAP utilise une API REST différente - implémentation simplifiée
        $shares = [];
        $volumes = [];
        $this->saveDiscovery($nas['id'] ?? 0, $shares, $volumes, null, 'QNAP: découverte non implémentée');
        return [
            'success' => false,
            'error' => 'Découverte QNAP non encore implémentée. Seul Synology est supporté actuellement.',
            'shares' => [],
            'volumes' => []
        ];
    }

    private function saveDiscovery($nasId, $shares, $volumes, $raw = null, $error = null) {
        if (!$nasId) return;
        try {
            $db = new Database();
            $db->query(
                "INSERT INTO nas_discovery (nas_id, shares_json, volumes_json, raw_response, error_message) VALUES (?, ?, ?, ?, ?)",
                [$nasId, json_encode($shares), json_encode($volumes), $raw, $error]
            );
        } catch (Exception $e) {
            error_log('NasDiscovery save: ' . $e->getMessage());
        }
    }
}

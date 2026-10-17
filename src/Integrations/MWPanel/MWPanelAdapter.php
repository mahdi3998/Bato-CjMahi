<?php
namespace Bato\Integrations\MWPanel;

use Exception;

class MWPanelAdapter
{
    private MWPanelClient $client;

    public function __construct(MWPanelClient $client)
    {
        $this->client = $client;
    }

    public function health(): bool
    {
        try {
            // attempt to login if token invalid
            if (!$this->client) return false;
            // try a lightweight call: get locations for reseller if possible
            // We can't assume reseller_id here; call a generic endpoint
            $resp = $this->client->get('/api/xui-panel');
            return is_array($resp);
        } catch (Exception $e) {
            return false;
        }
    }

    public function createSubscription(array $data): array
    {
        // data: total_volume_bytes, duration_days, panel_ids[], customer_label
        $resp = $this->client->createPackage($data);
        if (empty($resp) || empty($resp['data'])) {
            throw new Exception('MWPanel createPackage failed');
        }
        $id = $resp['data']['id'] ?? null;
        $uuid = $resp['data']['uuid'] ?? null;

        // share status
        if ($id) {
            try {
                $this->client->sharePackageStatus($id, ['shared' => true]);
            } catch (Exception $e) {
                // ignore
            }
        }

        return [
            'remote_id' => $id,
            'remote_uuid' => $uuid,
            'raw' => $resp
        ];
    }

    public function getSubscription(string $remoteIdOrUuid): array
    {
        // Try as id
        try {
            if (is_numeric($remoteIdOrUuid)) {
                $resp = $this->client->getPackageShare((int)$remoteIdOrUuid);
                // normalize
                return $this->normalizePackageShare($resp);
            }
            // try uuid
            $resp = $this->client->getShareByUuid($remoteIdOrUuid);
            return $this->normalizeShare($resp);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateSubscription(string $remoteId, array $data): array
    {
        $resp = $this->client->put("/api/v2ray-package/{$remoteId}", $data);
        return $resp;
    }

    public function extendSubscription(string $remoteId, int $seconds): array
    {
        // Fetch current package
        $resp = $this->client->get("/api/v2ray-package/{$remoteId}");
        $current = $resp['data'] ?? [];
        $currentDuration = $current['duration_days'] ?? 0;
        $addDays = (int) floor($seconds / 86400);
        $newDuration = $currentDuration + $addDays;
        $payload = ['duration_days' => $newDuration];
        $updated = $this->client->put("/api/v2ray-package/{$remoteId}", $payload);
        return $updated;
    }

    public function revokeSubscription(string $remoteId): bool
    {
        $resp = $this->client->put("/api/v2ray-package/{$remoteId}", ['status' => 'suspended']);
        return isset($resp['data']);
    }

    public function deleteSubscription(string $remoteId): bool
    {
        $resp = $this->client->delete("/api/v2ray-package/{$remoteId}");
        return isset($resp['data']);
    }

    public function getUsage(string $remoteId): array
    {
        // Attempt to get share data
        try {
            $resp = $this->client->get("/api/v2ray-package/{$remoteId}/share");
            // try normalize
            return $this->normalizePackageShare($resp);
        } catch (Exception $e) {
            return [];
        }
    }

    private function normalizeShare(array $resp): array
    {
        // Accept both structures
        $data = $resp['data'] ?? $resp;
        $out = [];
        $out['remote_id'] = $data['id'] ?? ($data['package_id'] ?? null);
        $out['uuid'] = $data['uuid'] ?? ($data['share_uuid'] ?? null);
        $out['subscription_url'] = $data['subscription_url'] ?? null;
        $out['expires_at'] = $data['expires_at'] ?? null;
        $out['total_volume_bytes'] = $data['total_volume_bytes'] ?? null;
        $out['used_bytes'] = $data['used_bytes'] ?? ($data['usage'] ?? null);
        $out['locations'] = $data['locations'] ?? [];
        $out['configs'] = $data['configs'] ?? [];
        return $out;
    }

    private function normalizePackageShare(array $resp): array
    {
        $data = $resp['data'] ?? $resp;
        return $this->normalizeShare(['data' => $data]);
    }
}

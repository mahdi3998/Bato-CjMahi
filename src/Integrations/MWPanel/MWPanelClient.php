<?php
namespace Bato\Integrations\MWPanel;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MWPanelClient
{
    private string $baseUrl;
    private Client $http;
    private int $timeout;
    private int $connectTimeout;
    private bool $verify;
    private MWPanelTokenManager $tokenManager;

    public function __construct(array $config, MWPanelTokenManager $tokenManager)
    {
        $this->baseUrl = rtrim($config['base_url'], '/');
        $this->timeout = $config['timeout'] ?? 20;
        $this->connectTimeout = $config['connect_timeout'] ?? 10;
        $this->verify = $config['verify_ssl'] ?? true;
        $this->tokenManager = $tokenManager;

        $this->http = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'connect_timeout' => $this->connectTimeout,
            'verify' => $this->verify,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    private function authHeaders(): array
    {
        $token = $this->tokenManager->getValidAccessToken();
        if ($token) {
            return ['Authorization' => 'Bearer ' . $token];
        }
        return [];
    }

    private function request(string $method, string $uri, array $options = [])
    {
        $maxRetries = 2;
        $retries = 0;
        $retryStatus = [502,503,504,520,521,522,523,524];

        do {
            try {
                $headers = $options['headers'] ?? [];
                $options['headers'] = array_merge($headers, $this->authHeaders());
                $resp = $this->http->request($method, $uri, $options);
                $body = (string) $resp->getBody();
                return json_decode($body, true);
            } catch (RequestException $e) {
                $response = $e->getResponse();
                $code = $response ? $response->getStatusCode() : null;
                // If auth error, try login once
                if ($code === 401 && $this->tokenManager->isTokenValid() === false) {
                    $this->tokenManager->login();
                    $retries++;
                    continue;
                }
                if (in_array($code, $retryStatus) && $retries < $maxRetries) {
                    $retries++;
                    sleep(1);
                    continue;
                }
                throw $e;
            }
        } while ($retries <= $maxRetries);

        throw new \RuntimeException('MWPanel request failed after retries');
    }

    // Public helper methods
    public function get(string $uri, array $query = [])
    {
        $opts = [];
        if (!empty($query)) {
            $opts['query'] = $query;
        }
        return $this->request('GET', $uri, $opts);
    }

    public function post(string $uri, array $data = [])
    {
        $opts = [];
        if (!empty($data)) {
            $opts['json'] = $data;
        }
        return $this->request('POST', $uri, $opts);
    }

    public function put(string $uri, array $data = [])
    {
        $opts = [];
        if (!empty($data)) {
            $opts['json'] = $data;
        }
        return $this->request('PUT', $uri, $opts);
    }

    public function patch(string $uri, array $data = [])
    {
        $opts = [];
        if (!empty($data)) {
            $opts['json'] = $data;
        }
        return $this->request('PATCH', $uri, $opts);
    }

    public function delete(string $uri)
    {
        return $this->request('DELETE', $uri, []);
    }

    // MWPanel specific helpers
    public function login(string $username, string $password): array
    {
        return $this->post('/api/auth/login', [
            'username' => $username,
            'password' => $password
        ]);
    }

    public function getLocations(int $resellerId): array
    {
        return $this->get("/api/v2ray-package/reseller/{$resellerId}/panels/summary");
    }

    public function createPackage(array $payload): array
    {
        return $this->post('/api/v2ray-package', $payload);
    }

    public function sharePackageStatus(int $id, array $payload): array
    {
        return $this->patch("/api/v2ray-package/{$id}/share/status", $payload);
    }

    public function getPackageShare(int $id): array
    {
        return $this->get("/api/v2ray-package/{$id}/share");
    }

    public function getShareByUuid(string $uuid): array
    {
        return $this->get("/api/v2ray-share/{$uuid}");
    }
}

<?php
namespace Bato\Integrations\MWPanel;

use PDO;

class MWPanelTokenManager
{
    private PDO $pdo;
    private string $username;
    private string $password;
    private MWPanelClient $client; // circular: client may use this; construct carefully

    public function __construct(PDO $pdo, array $config)
    {
        $this->pdo = $pdo;
        $this->username = $config['username'];
        $this->password = $config['password'];
    }

    public function attachClient(MWPanelClient $client): void
    {
        $this->client = $client;
    }

    public function login(): bool
    {
        // perform login via temporary client (without token manager attached)
        $tmpClient = new MWPanelClient([
            'base_url' => getenv('MWPANEL_BASE_URL') ?: '',
            'timeout' => (int)getenv('MWPANEL_TIMEOUT') ?: 20,
            'connect_timeout' => (int)getenv('MWPANEL_CONNECT_TIMEOUT') ?: 10,
            'verify_ssl' => (bool)filter_var(getenv('MWPANEL_VERIFY_SSL') ?: 'true', FILTER_VALIDATE_BOOLEAN)
        ], $this);

        $resp = $tmpClient->post('/api/auth/login', [
            'username' => $this->username,
            'password' => $this->password
        ]);

        if (!is_array($resp) || empty($resp['data']['access_token'])) {
            return false;
        }

        $accessToken = $resp['data']['access_token'];
        $refreshToken = $resp['data']['refresh_token'] ?? null;
        $expiresIn = $resp['data']['expires_in'] ?? 3600;

        $expiresAt = (new \DateTime())->modify("+{$expiresIn} seconds")->format('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare("INSERT INTO mwpanel_tokens (access_token, refresh_token, expires_at) VALUES (:access, :refresh, :expires)");
        $stmt->execute([
            ':access' => $accessToken,
            ':refresh' => $refreshToken,
            ':expires' => $expiresAt
        ]);

        return true;
    }

    public function getValidAccessToken(): ?string
    {
        $stmt = $this->pdo->query("SELECT access_token, expires_at FROM mwpanel_tokens ORDER BY id DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $expires = $row['expires_at'];
        $expiresDT = new \DateTime($expires);
        $now = new \DateTime();
        $margin = new \DateInterval('PT60S'); // 60 seconds safety
        $expiresDT->sub($margin);

        if ($now >= $expiresDT) {
            return null;
        }
        return $row['access_token'];
    }

    public function isTokenValid(): bool
    {
        return $this->getValidAccessToken() !== null;
    }

    public function invalidateToken(): void
    {
        $this->pdo->exec("DELETE FROM mwpanel_tokens");
    }
}

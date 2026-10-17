<?php
// bin/worker.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Bootstrap/app.php';

use Bato\Queue\RedisQueue;
use Bato\Domain\OrderRepository;
use Bato\Integrations\MWPanel\MWPanelTokenManager;
use Bato\Integrations\MWPanel\MWPanelClient;
use Bato\Services\Provisioner;
use Bato\Services\TelegramApi;

$pdo = pdo();
$queue = new RedisQueue();
$orders = new OrderRepository($pdo);

$mwConfig = [
    'base_url' => getenv('MWPANEL_BASE_URL') ?: '',
    'timeout' => (int)getenv('MWPANEL_TIMEOUT') ?: 20,
    'connect_timeout' => (int)getenv('MWPANEL_CONNECT_TIMEOUT') ?: 10,
    'verify_ssl' => filter_var(getenv('MWPANEL_VERIFY_SSL') ?: 'true', FILTER_VALIDATE_BOOLEAN)
];
$tokenManager = new MWPanelTokenManager($pdo, ['username' => getenv('MWPANEL_USERNAME'), 'password' => getenv('MWPANEL_PASSWORD')]);
$mwClient = new MWPanelClient($mwConfig, $tokenManager);
$tokenManager->attachClient($mwClient);

$tg = new TelegramApi(getenv('BOT_TOKEN'));
$prov = new Provisioner($mwClient, $orders, $tg, $pdo);

echo "Worker started...\n";
while (true) {
    try {
        $job = $queue->pop(10);
        if (!$job) continue;
        echo "Got job: " . json_encode($job) . "\n";
        if (($job['type'] ?? '') === 'provision' && !empty($job['order_id'])) {
            $orderId = (int)$job['order_id'];
            try {
                $prov->provision($orderId);
                echo "Provisioned order {$orderId}\n";
            } catch (Exception $e) {
                echo "Provision error for {$orderId}: " . $e->getMessage() . "\n";
                // Optionally requeue with delay or record failure
            }
        }
    } catch (Exception $e) {
        echo "Worker loop error: " . $e->getMessage() . "\n";
        sleep(3);
    }
}

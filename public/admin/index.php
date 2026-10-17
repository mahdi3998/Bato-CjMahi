<?php
// public/admin/index.php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/Bootstrap/app.php';

$pdo = pdo();
$orders = $pdo->query('SELECT o.*, u.telegram_id FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);
$products = $pdo->query('SELECT * FROM products ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fa">
<head>
  <meta charset="utf-8">
  <title>Admin - Bato</title>
  <style>body{font-family:Vazir, Helvetica, Arial;direction:rtl;padding:20px}table{width:100%;border-collapse:collapse}td,th{border:1px solid #ddd;padding:8px}</style>
</head>
<body>
  <h1>Bato - پنل مدیریت</h1>
  <h2>محصولات</h2>
  <table>
    <tr><th>ID</th><th>نام</th><th>قیمت</th><th>مدت(روز)</th></tr>
    <?php foreach($products as $p): ?>
      <tr><td><?=htmlspecialchars($p['id'])?></td><td><?=htmlspecialchars($p['name'])?></td><td><?=htmlspecialchars($p['price'])?></td><td><?=htmlspecialchars($p['duration_days'])?></td></tr>
    <?php endforeach; ?>
  </table>

  <h2>سفارش‌ها</h2>
  <table>
    <tr><th>ID</th><th>کاربر (tg)</th><th>محصول</th><th>مبلغ</th><th>وضعیت</th><th>تاریخ</th></tr>
    <?php foreach($orders as $o): ?>
      <tr>
        <td><?=htmlspecialchars($o['id'])?></td>
        <td><?=htmlspecialchars($o['telegram_id'])?></td>
        <td><?=htmlspecialchars($o['product_id'])?></td>
        <td><?=htmlspecialchars($o['amount'])?></td>
        <td><?=htmlspecialchars($o['status'])?></td>
        <td><?=htmlspecialchars($o['created_at'])?></td>
      </tr>
    <?php endforeach; ?>
  </table>

</body>
</html>

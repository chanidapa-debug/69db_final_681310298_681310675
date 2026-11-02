<?php
require __DIR__ . '/db.php';
require __DIR__ . '/config.php';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$page = $_GET['page'] ?? 'dashboard';
if (!isset($modules[$page])) $page = 'dashboard';

$counts = [];
foreach (['products'=>'products','categories'=>'categories','customers'=>'customers','orders'=>'orders','order_items'=>'order_items','product_variants'=>'product_variants'] as $key=>$table) {
    $counts[$key] = (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}
$labels = [
  'products'=>'สินค้า',
  'categories'=>'หมวดหมู่',
  'customers'=>'ลูกค้า',
  'orders'=>'คำสั่งซื้อ',
  'order_items'=>'รายการออเดอร์',
  'product_variants'=>'ขนาดสินค้า'
];
$navIcons = [
  'dashboard'=>'🏠',
  'products'=>'🛍️',
  'categories'=>'🏷️',
  'customers'=>'👥',
  'orders'=>'📦',
  'order_items'=>'📋',
  'product_variants'=>'📏'
];
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Everyday Shop · E-commerce Back Office</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="brand">
      <span class="brand-mark">🌸</span>
      <div>
        <b>EVERYDAY</b>
        <small>SHOP ADMIN</small>
      </div>
    </div>
    <div class="nav-caption">เมนูหลัก</div>
    <a class="nav-link <?= $page==='dashboard'?'active':'' ?>" href="index.php">
      <span class="nav-icon"><?= $navIcons['dashboard'] ?></span> Dashboard
    </a>
    <?php foreach ($modules as $key=>$m): ?>
      <a class="nav-link <?= $page===$key?'active':'' ?>" href="index.php?page=<?= h($key) ?>">
        <span class="nav-icon"><?= $navIcons[$key] ?? '📁' ?></span>
        <?= h($m['title']) ?>
      </a>
    <?php endforeach; ?>
    <div class="sidebar-bottom">
      <span class="status-dot"></span> เชื่อมต่อฐานข้อมูลพร้อมใช้งาน
    </div>
  </aside>
  <main class="main">
    <header class="topbar">
      <div>
        <div class="eyebrow">BACK OFFICE · E-COMMERCE</div>
        <h1><?= $page==='dashboard' ? 'ภาพรวมร้านค้า' : h($modules[$page]['title']) ?></h1>
      </div>
      <div class="admin-chip">
        <span class="avatar">🌸</span>
        <div>
          <b>Administrator</b>
          <small>ผู้ดูแลระบบร้านค้า</small>
        </div>
      </div>
    </header>
    <?php if ($page==='dashboard'): ?>
      <section class="welcome">
        <div class="welcome-text-side">
          <div class="welcome-pill">🌸 ยินดีต้อนรับสู่ระบบจัดการร้านค้า</div>
          <h2>จัดการร้านค้าของคุณ<br>ได้ง่ายและน่ารักในที่เดียว</h2>
          <p>ดูแลสินค้า ลูกค้า และคำสั่งซื้อผ่านระบบ Back Office สไตล์พาสเทลสดใส</p>
        </div>
      </section>

      <section class="stat-grid">
        <?php foreach ($counts as $key=>$count): ?>
          <a class="stat-card stat-<?= h($key) ?>" href="index.php?page=<?= h($key) ?>">
            <div class="stat-header">
              <span class="stat-label"><?= h($labels[$key]) ?></span>
              <span class="stat-icon-bubble"><?= $navIcons[$key] ?? '📁' ?></span>
            </div>
            <strong><?= number_format($count) ?></strong>
            <small>รายการในฐานข้อมูล</small>
          </a>
        <?php endforeach; ?>
      </section>

      <section class="panel">
        <div class="panel-head">
          <div>
            <h3>เริ่มจัดการข้อมูลร้านค้า</h3>
            <p>เลือกเมนูเพื่อดู เพิ่ม แก้ไข หรือตรวจสอบข้อมูลสินค้าและลูกค้า</p>
          </div>
        </div>
        <div class="quick-links">
          <?php foreach($modules as $key=>$m): ?>
            <a href="index.php?page=<?= h($key) ?>" class="quick-link-card">
              <span class="ql-icon"><?= $navIcons[$key] ?? '📁' ?></span>
              <div class="ql-info">
                <b>จัดการ<?= h($m['title']) ?></b>
                <small><?= number_format($counts[$key] ?? 0) ?> รายการ</small>
              </div>
              <span class="ql-arrow">➜</span>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php else: ?>
      <?php
      $module = $modules[$page];
      require __DIR__ . '/crud.php';
      ?>
    <?php endif; ?>
    <footer>EVERYDAY SHOP · E-commerce Back Office · Cute Pastel Edition</footer>
  </main>
</div>

<!-- Floating Scroll To Top Button -->
<button type="button" id="scrollTopBtn" class="scroll-top-btn" title="เลื่อนขึ้นด้านบนสุด" aria-label="เลื่อนขึ้นด้านบนสุด">
  <span class="scroll-top-arrow">▲</span>
  <span class="scroll-top-label">บนสุด</span>
</button>

<script>
(function() {
  const scrollBtn = document.getElementById('scrollTopBtn');
  if (!scrollBtn) return;

  function checkScroll() {
    const scrollPos = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
    if (scrollPos > 250) {
      scrollBtn.classList.add('is-visible');
    } else {
      scrollBtn.classList.remove('is-visible');
    }
  }

  window.addEventListener('scroll', checkScroll, { passive: true });

  scrollBtn.addEventListener('click', function(e) {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });

  checkScroll();
})();
</script>
</body>
</html>

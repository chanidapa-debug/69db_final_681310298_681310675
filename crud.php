<?php
$table = $module['table'];
$pkFields = $module['pk'];
$fields = $module['fields'];
$listFields = $module['list'];
$message = '';
$error = '';

function fetchPkRow($pdo, $table, $pkFields, $encoded) {
    $vals = json_decode(base64_decode($encoded), true);
    if (!is_array($vals) || count($vals)!==count($pkFields)) return null;
    $where=[]; $params=[];
    foreach ($pkFields as $i=>$pk) { $where[]="`$pk` = ?"; $params[]=$vals[$i]; }
    $st=$pdo->prepare("SELECT * FROM `$table` WHERE ".implode(' AND ',$where));
    $st->execute($params);
    return $st->fetch() ?: null;
}
$editRow = null;
if (isset($_GET['edit'])) $editRow = fetchPkRow($pdo,$table,$pkFields,$_GET['edit']);

if ($_SERVER['REQUEST_METHOD']==='POST') {
  try {
    if (isset($_POST['delete_pk'])) {
      $vals=json_decode(base64_decode($_POST['delete_pk']),true);
      if (!is_array($vals) || count($vals)!==count($pkFields)) throw new Exception('รหัสรายการไม่ถูกต้อง');
      $where=[];$params=[];
      foreach($pkFields as $i=>$pk){$where[]="`$pk` = ?";$params[]=$vals[$i];}
      $st=$pdo->prepare("DELETE FROM `$table` WHERE ".implode(' AND ',$where));$st->execute($params);
      $message='ลบข้อมูลเรียบร้อยแล้ว ✨';
    } elseif (isset($_POST['save'])) {
      $data=[];
      foreach($fields as $name=>$def){
        $value=$_POST[$name] ?? '';
        if (($def['type']??'')==='datetime-local' && $value!=='') $value=str_replace('T',' ',$value).(strlen($value)===16?':00':'');
        if ($value==='') $value=null;
        if (($def['required']??false) && ($value===null || $value==='')) throw new Exception('กรุณากรอก '.$def['label']);
        $data[$name]=$value;
      }
      $oldEncoded=$_POST['old_pk'] ?? '';
      if ($oldEncoded!=='') {
        $old=json_decode(base64_decode($oldEncoded),true);
        if (!is_array($old) || count($old)!==count($pkFields)) throw new Exception('รหัสรายการเดิมไม่ถูกต้อง');
        $sets=[];$params=[];
        foreach($data as $k=>$v){$sets[]="`$k` = ?";$params[]=$v;}
        $where=[];
        foreach($pkFields as $i=>$pk){$where[]="`$pk` = ?";$params[]=$old[$i];}
        $st=$pdo->prepare("UPDATE `$table` SET ".implode(',',$sets)." WHERE ".implode(' AND ',$where));$st->execute($params);
        $message='บันทึกการแก้ไขข้อมูลเรียบร้อยแล้ว ✨';
      } else {
        $cols=array_keys($data);$marks=array_fill(0,count($cols),'?');
        $st=$pdo->prepare("INSERT INTO `$table` (`".implode('`,`',$cols)."`) VALUES (".implode(',',$marks).")");
        $st->execute(array_values($data));
        $message='เพิ่มข้อมูลใหม่เรียบร้อยแล้ว ✨';
      }
      $editRow=null;
    }
  } catch (Throwable $e) {
    $error = $e instanceof PDOException ? 'บันทึกไม่สำเร็จ อาจมีรหัสซ้ำหรือมีข้อมูลอื่นเชื่อมโยงอยู่' : $e->getMessage();
  }
}
if (isset($_GET['edit'])) $editRow = fetchPkRow($pdo,$table,$pkFields,$_GET['edit']);

// เตรียม lookup map สำหรับ foreign key เพื่อแสดงชื่อที่อ่านง่าย
$lookupMaps = [];
foreach ($fields as $fieldName => $def) {
    if (($def['type'] ?? '') === 'select' && isset($def['source'])) {
        try {
            $lookupSt = $pdo->query("SELECT `{$def['source_key']}` AS k, `{$def['source_label']}` AS v FROM `{$def['source']}`");
            $lookupMaps[$fieldName] = $lookupSt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Throwable $e) {}
    }
}

$rows=$pdo->query("SELECT * FROM `$table` ORDER BY 1 ASC LIMIT 300")->fetchAll();
function rowToken($row,$pkFields){$vals=[];foreach($pkFields as $pk)$vals[]=$row[$pk];return base64_encode(json_encode($vals));}
?>
<section class="panel">
  <div class="panel-head">
    <div>
      <div class="panel-title-wrap">
        <h3>จัดการ<?= h($module['title']) ?></h3>
        <span class="count-chip"><?= number_format(count($rows)) ?> รายการ</span>
      </div>
      <p class="panel-sub">รองรับการค้นดู เพิ่ม แก้ไข และลบข้อมูลในระบบ Back Office</p>
    </div>
    <a class="button button-primary" href="index.php?page=<?= h($page) ?>&new=1">
      <span class="btn-icon">＋</span> เพิ่มข้อมูลใหม่
    </a>
  </div>

  <?php if($message): ?><div class="alert success"><span class="alert-icon">🎀</span> <?= h($message) ?></div><?php endif; ?>
  <?php if($error): ?><div class="alert error"><span class="alert-icon">⚠️</span> <?= h($error) ?></div><?php endif; ?>

  <?php if($editRow || isset($_GET['new'])): ?>
    <form method="post" class="edit-form">
      <div class="form-title">
        <span><?= $editRow ? '✏️ แก้ไขข้อมูล' : '🌸 เพิ่มข้อมูลใหม่' ?></span>
        <small><?= $editRow ? 'ปรับปรุงรายละเอียดรายการ' : 'กรอกข้อมูลให้ครบถ้วนแล้วกดบันทึก' ?></small>
      </div>
      <input type="hidden" name="old_pk" value="<?= $editRow?h(rowToken($editRow,$pkFields)):'' ?>">
      <div class="form-grid">
      <?php foreach($fields as $name=>$def):
        $val=$editRow[$name]??'';
        if (($def['type']??'')==='datetime-local' && $val) $val=date('Y-m-d\TH:i',strtotime($val));
      ?>
        <label class="form-group <?= ($name==='pd_image_url'||($def['type']??'')==='textarea') ? 'full-width' : '' ?>">
          <span class="label-text">
            <?= h($def['label']) ?>
            <?php if(($def['required']??false)): ?><em class="required-star">*</em><?php endif; ?>
          </span>

          <?php if($name === 'pd_image_url'): ?>
            <div class="image-input-container">
              <input type="url" name="<?= h($name) ?>" id="input_<?= h($name) ?>" value="<?= h($val) ?>" placeholder="https://example.com/product-image.jpg" oninput="updateImagePreview(this.value)" onchange="updateImagePreview(this.value)">
              <div class="url-preview-single">
                <span class="preview-mini-title">👁️ ตัวอย่างภาพสินค้าตาม URL</span>
                <div class="preview-box-frame">
                  <img id="live-img-preview" src="<?= !empty($val) ? h($val) : '' ?>" alt="ตัวอย่างภาพสินค้า" class="<?= !empty($val) ? '' : 'is-hidden' ?>" onerror="onPreviewError(this)">
                  <div id="live-img-empty" class="no-img-pill <?= !empty($val) ? 'is-hidden' : '' ?>">
                    <span>🛍️</span>
                    <small>ใส่ URL เพื่อดูรูปตัวอย่าง</small>
                  </div>
                </div>
              </div>
            </div>
          <?php elseif(($def['type']??'')==='select'): 
            $opts=$pdo->query("SELECT `{$def['source_key']}` AS k, `{$def['source_label']}` AS v FROM `{$def['source']}` ORDER BY 2")->fetchAll(); ?>
            <select name="<?= h($name) ?>" <?= ($def['required']??false)?'required':'' ?>>
              <option value="">-- เลือก<?= h($def['label']) ?> --</option>
              <?php foreach($opts as $o): ?>
                <option value="<?= h($o['k']) ?>" <?= (string)$val===(string)$o['k']?'selected':'' ?>><?= h($o['k'].' - '.$o['v']) ?></option>
              <?php endforeach; ?>
            </select>
          <?php elseif(($def['type']??'')==='select-static'): ?>
            <select name="<?= h($name) ?>" <?= ($def['required']??false)?'required':'' ?>>
              <option value="">-- เลือก<?= h($def['label']) ?> --</option>
              <?php foreach($def['options'] as $k=>$v): ?>
                <option value="<?= h($k) ?>" <?= (string)$val===(string)$k?'selected':'' ?>><?= h($v) ?></option>
              <?php endforeach; ?>
            </select>
          <?php elseif(($def['type']??'')==='textarea'): ?>
            <textarea name="<?= h($name) ?>" rows="3" placeholder="กรอก<?= h($def['label']) ?>"><?= h($val) ?></textarea>
          <?php else: ?>
            <input type="<?= h($def['type']??'text') ?>" name="<?= h($name) ?>" value="<?= h($val) ?>" <?= isset($def['step'])?'step="'.h($def['step']).'"':'' ?> <?= ($def['required']??false)?'required':'' ?> placeholder="กรอก<?= h($def['label']) ?>">
          <?php endif; ?>
        </label>
      <?php endforeach; ?>
      </div>
      <div class="form-actions">
        <button class="button button-primary" name="save" value="1">
          <span>💾 บันทึกข้อมูล</span>
        </button>
        <a class="button button-secondary" href="index.php?page=<?= h($page) ?>">ยกเลิก</a>
      </div>
    </form>
    <script>
    function updateImagePreview(url) {
      const img = document.getElementById('live-img-preview');
      const empty = document.getElementById('live-img-empty');
      if (!img || !empty) return;
      url = (url || '').trim();
      if (url) {
        img.src = url;
        img.classList.remove('is-hidden');
        empty.classList.add('is-hidden');
      } else {
        img.src = '';
        img.classList.add('is-hidden');
        empty.classList.remove('is-hidden');
        const emptySmall = empty.querySelector('small');
        if (emptySmall) emptySmall.textContent = 'ใส่ URL เพื่อดูรูปตัวอย่าง';
      }
    }
    function onPreviewError(img) {
      img.classList.add('is-hidden');
      const empty = document.getElementById('live-img-empty');
      if (empty) {
        empty.classList.remove('is-hidden');
        const emptySmall = empty.querySelector('small');
        if (emptySmall) emptySmall.textContent = '❌ โหลดรูปภาพไม่ได้';
      }
    }
    </script>
  <?php endif; ?>

  <div class="table-wrap">
    <table class="cute-table">
      <thead>
        <tr>
          <?php foreach($listFields as $col): ?>
            <th class="<?= $col==='pd_image_url'?'th-thumb':'' ?>"><?= $col === '_row_number' ? 'ลำดับ' : h($fields[$col]['label'] ?? $col) ?></th>
          <?php endforeach; ?>
          <th class="th-actions">จัดการ</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($rows as $index => $r): ?>
        <tr>
          <?php foreach($listFields as $col): ?>
            <td class="<?= $col==='pd_image_url'?'td-thumb':'' ?>">
             <?php if ($col === '_row_number'): ?>
              <?= $index + 1 ?>
             <?php elseif ($col === 'pd_image_url'): ?>
                <div class="thumb-cell">
                  <?php if (!empty($r[$col])): ?>
                    <a href="<?= h($r[$col]) ?>" target="_blank" rel="noopener noreferrer" class="thumb-link" title="คลิกเพื่อดูรูปภาพเต็ม">
                      <img src="<?= h($r[$col]) ?>" class="table-img-thumb" alt="<?= h($r['pd_name'] ?? 'Product') ?>" loading="lazy" onerror="this.onerror=null;this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'130\' height=\'120\' viewBox=\'0 0 130 120\'><rect width=\'130\' height=\'120\' fill=\'%23fffcfd\' stroke=\'%23f0b9c7\' stroke-width=\'2\' rx=\'12\'/><text x=\'50%25\' y=\'45%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' font-size=\'26\'>🛍️</text><text x=\'50%25\' y=\'72%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' font-size=\'11\' fill=\'%23534173\' font-family=\'sans-serif\'>โหลดรูปไม่ได้</text></svg>';">
                    </a>
                  <?php else: ?>
                    <div class="no-img-pill">
                      <span>🛍️</span>
                      <small>ไม่มีรูปภาพ</small>
                    </div>
                  <?php endif; ?>
                </div>
              <?php elseif ($col === 'address'): ?>
                <div class="address-cell" title="<?= h($r[$col] ?? '') ?>">
                  <span class="addr-pin">📍</span>
                  <span class="addr-text"><?= !empty($r[$col]) ? h($r[$col]) : '<span class="addr-empty">ไม่ระบุที่อยู่</span>' ?></span>
                </div>
              <?php elseif (in_array($col, ['price', 'total_amount', 'subtotal'])): ?>
                <span class="price-chip"><?= ($r[$col] !== null && $r[$col] !== '') ? '฿' . number_format((float)$r[$col], 2) : '-' ?></span>
              <?php elseif ($col === 'status'): ?>
                <?php
                  $stVal = strtolower((string)$r[$col]);
                  $badgeCls = match($stVal) {
                    'completed' => 'st-completed',
                    'processing' => 'st-processing',
                    'cancelled' => 'st-cancelled',
                    default => 'st-pending'
                  };
                ?>
                <span class="status-chip <?= $badgeCls ?>"><?= h($r[$col]) ?></span>
              <?php elseif (isset($lookupMaps[$col]) && isset($lookupMaps[$col][$r[$col]])): ?>
                <span class="lookup-pill"><?= h($lookupMaps[$col][$r[$col]]) ?></span>
              <?php elseif ($col === 'email'): ?>
                <span class="email-text">✉️ <?= h($r[$col]) ?></span>
              <?php elseif ($col === 'phone'): ?>
                <span class="phone-text"><?= !empty($r[$col]) ? '📞 '.h($r[$col]) : '-' ?></span>
              <?php else: ?>
                <?= h($r[$col] ?? '') ?>
              <?php endif; ?>
            </td>
          <?php endforeach; ?>
          <td class="td-actions">
            <div class="actions-wrap">
              <a class="small-action edit-btn" href="index.php?page=<?= h($page) ?>&edit=<?= h(rowToken($r,$pkFields)) ?>">
                <span>✏️</span> แก้ไข
              </a>
              <form method="post" onsubmit="return confirm('ยืนยันการลบข้อมูลนี้หรือไม่?')" class="delete-form">
                <input type="hidden" name="delete_pk" value="<?= h(rowToken($r,$pkFields)) ?>">
                <button class="small-action danger-btn" type="submit">
                  <span>🗑️</span> ลบ
                </button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if(!$rows): ?>
        <tr><td colspan="<?= count($listFields)+1 ?>" class="empty">🌸 ยังไม่มีข้อมูลในหมวดนี้</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="table-note">
    <span>💡 แสดงรายการข้อมูลเรียงลำดับรหัสจากน้อยไปมาก (1, 2, 3...) · ข้อมูลเชื่อมโยงจะได้รับการปกป้องเพื่อความปลอดภัยของฐานข้อมูล</span>
  </div>
</section>

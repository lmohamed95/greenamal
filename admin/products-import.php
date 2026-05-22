<?php
/**
 * Import an edited products CSV (the file produced by products-export.php).
 *
 * Only Prix, Prix barré, and Stock columns are applied. Match is done by ID
 * (first column), so the owner can reorder rows or delete rows they didn't
 * touch — only rows whose values actually changed get an UPDATE.
 *
 * Flow:
 *   1. GET                       → upload form
 *   2. POST with file            → parse, compute diff, render confirmation
 *   3. POST with confirm=1       → apply changes (transactional)
 */

require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

$page_title = 'Importer les prix';
$current = 'products';

$errors = [];
$preview = null;        // array of pending changes when in preview mode
$applied = null;        // count when changes were just applied

/**
 * Parse the uploaded/posted CSV and return a list of pending changes.
 * Returns ['changes' => [...], 'errors' => [...], 'unchanged' => N, 'missing' => N].
 *
 * Each change: ['id', 'name', 'old_price', 'new_price', 'old_compare', 'new_compare', 'old_stock', 'new_stock']
 */
function parse_price_csv(string $path): array {
    $errors = [];
    $changes = [];
    $unchanged = 0;
    $missing = 0;

    $fh = @fopen($path, 'r');
    if (!$fh) {
        return ['changes' => [], 'errors' => ['Impossible de lire le fichier.'], 'unchanged' => 0, 'missing' => 0];
    }

    // Detect & strip UTF-8 BOM.
    $bom = fread($fh, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($fh);
    }

    // Detect separator from the first line (; comma or tab).
    $first = fgets($fh);
    if ($first === false) {
        fclose($fh);
        return ['changes' => [], 'errors' => ['Le fichier est vide.'], 'unchanged' => 0, 'missing' => 0];
    }
    $sep = ';';
    if (substr_count($first, ';') === 0 && substr_count($first, ',') > 0) $sep = ',';
    if (substr_count($first, "\t") > substr_count($first, $sep)) $sep = "\t";

    // Parse header row.
    $header = str_getcsv($first, $sep);
    $header = array_map(fn($h) => strtolower(trim((string) $h)), $header);

    // Locate the columns we care about by header name (tolerant matching).
    $col_id      = null;
    $col_price   = null;
    $col_compare = null;
    $col_stock   = null;
    foreach ($header as $i => $h) {
        if ($h === 'id')                                       $col_id      = $i;
        elseif (str_starts_with($h, 'prix barr') || $h === 'compare_at_price') $col_compare = $i;
        elseif (str_starts_with($h, 'prix') || $h === 'price') $col_price   = $i;
        elseif ($h === 'stock')                                $col_stock   = $i;
    }
    if ($col_id === null) {
        fclose($fh);
        return ['changes' => [], 'errors' => ['Colonne « ID » introuvable dans l\'en-tête.'], 'unchanged' => 0, 'missing' => 0];
    }
    if ($col_price === null && $col_compare === null && $col_stock === null) {
        fclose($fh);
        return ['changes' => [], 'errors' => ['Aucune colonne modifiable trouvée (Prix, Prix barré, Stock).'], 'unchanged' => 0, 'missing' => 0];
    }

    // Pull every id we'll need in one query.
    $rows = [];
    $line_no = 1;
    while (($row = fgetcsv($fh, 0, $sep)) !== false) {
        $line_no++;
        if (count($row) === 1 && trim((string) $row[0]) === '') continue; // blank line
        $rows[] = ['line' => $line_no, 'cells' => $row];
    }
    fclose($fh);

    if (!$rows) {
        return ['changes' => [], 'errors' => ['Aucune ligne de données.'], 'unchanged' => 0, 'missing' => 0];
    }

    $ids = [];
    foreach ($rows as $r) {
        $id = (int) ($r['cells'][$col_id] ?? 0);
        if ($id > 0) $ids[] = $id;
    }
    $ids = array_values(array_unique($ids));
    $current = [];
    if ($ids) {
        $ph = implode(',', array_fill(0, count($ids), '?'));
        foreach (db_all("SELECT id, name, price, compare_at_price, stock FROM products WHERE id IN ($ph)", $ids) as $p) {
            $current[(int) $p['id']] = $p;
        }
    }

    // Parses "12,50" or "12.50" or "12 50" into float; '' → null.
    $parse_num = function ($raw): array {
        $s = trim((string) $raw);
        if ($s === '') return [null, true];
        $s = str_replace([' ', ' '], '', $s); // strip thin/normal spaces
        $s = str_replace(',', '.', $s);
        if (!is_numeric($s)) return [null, false];
        return [(float) $s, true];
    };

    foreach ($rows as $r) {
        $cells = $r['cells'];
        $line = $r['line'];

        $id = (int) ($cells[$col_id] ?? 0);
        if ($id <= 0) {
            $errors[] = "Ligne {$line} : ID manquant ou invalide.";
            continue;
        }
        if (!isset($current[$id])) {
            $missing++;
            $errors[] = "Ligne {$line} : produit ID {$id} introuvable.";
            continue;
        }

        $cur = $current[$id];
        $change = ['id' => $id, 'name' => $cur['name']];
        $has_change = false;
        $row_ok = true;

        if ($col_price !== null) {
            [$new_price, $ok] = $parse_num($cells[$col_price] ?? '');
            if (!$ok) {
                $errors[] = "Ligne {$line} ({$cur['name']}) : prix invalide « " . trim((string) ($cells[$col_price] ?? '')) . " ».";
                $row_ok = false;
            } elseif ($new_price === null) {
                $errors[] = "Ligne {$line} ({$cur['name']}) : prix vide — ignoré.";
                $row_ok = false;
            } elseif ($new_price < 0) {
                $errors[] = "Ligne {$line} ({$cur['name']}) : prix négatif.";
                $row_ok = false;
            } else {
                $change['old_price'] = (float) $cur['price'];
                $change['new_price'] = $new_price;
                if (abs($change['new_price'] - $change['old_price']) > 0.001) $has_change = true;
            }
        }

        if ($col_compare !== null) {
            [$new_compare, $ok] = $parse_num($cells[$col_compare] ?? '');
            if (!$ok) {
                $errors[] = "Ligne {$line} ({$cur['name']}) : prix barré invalide.";
                $row_ok = false;
            } else {
                $old_compare = $cur['compare_at_price'] !== null ? (float) $cur['compare_at_price'] : null;
                $change['old_compare'] = $old_compare;
                $change['new_compare'] = $new_compare; // may be null (cleared)
                $same = ($old_compare === null && $new_compare === null)
                     || ($old_compare !== null && $new_compare !== null && abs($old_compare - $new_compare) < 0.001);
                if (!$same) $has_change = true;
            }
        }

        if ($col_stock !== null) {
            $raw = trim((string) ($cells[$col_stock] ?? ''));
            if ($raw === '') {
                // empty stock cell → skip (don't blow away the value)
            } elseif (!ctype_digit(str_replace([' ', '-'], '', $raw)) && !is_numeric($raw)) {
                $errors[] = "Ligne {$line} ({$cur['name']}) : stock invalide « {$raw} ».";
                $row_ok = false;
            } else {
                $new_stock = (int) $raw;
                if ($new_stock < 0) {
                    $errors[] = "Ligne {$line} ({$cur['name']}) : stock négatif.";
                    $row_ok = false;
                } else {
                    $change['old_stock'] = (int) $cur['stock'];
                    $change['new_stock'] = $new_stock;
                    if ($change['new_stock'] !== $change['old_stock']) $has_change = true;
                }
            }
        }

        if (!$row_ok) continue;
        if ($has_change) $changes[] = $change;
        else $unchanged++;
    }

    return ['changes' => $changes, 'errors' => $errors, 'unchanged' => $unchanged, 'missing' => $missing];
}

// ---------------------------------------------------------------------------
// POST: confirm & apply
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'apply') {
    csrf_verify();

    $changes = json_decode((string) ($_POST['changes_json'] ?? ''), true);
    if (!is_array($changes) || !$changes) {
        $errors[] = 'Aucune modification à appliquer.';
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $n = 0;
            foreach ($changes as $c) {
                $id = (int) ($c['id'] ?? 0);
                if ($id <= 0) continue;

                $sets = [];
                $params = [];
                if (array_key_exists('new_price', $c) && $c['new_price'] !== null) {
                    $sets[] = 'price = ?';
                    $params[] = (float) $c['new_price'];
                }
                if (array_key_exists('new_compare', $c)) {
                    $sets[] = 'compare_at_price = ?';
                    $params[] = $c['new_compare'] !== null && $c['new_compare'] !== '' ? (float) $c['new_compare'] : null;
                }
                if (array_key_exists('new_stock', $c)) {
                    $sets[] = 'stock = ?';
                    $params[] = (int) $c['new_stock'];
                }
                if (!$sets) continue;

                $params[] = $id;
                db_query('UPDATE products SET ' . implode(', ', $sets) . ' WHERE id = ?', $params);
                $n++;
            }
            $pdo->commit();
            $applied = $n;
            $_SESSION['flash'] = ['type' => 'success', 'msg' => $n . ' produit(s) mis à jour.'];
            redirect('products.php');
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Erreur lors de la mise à jour : ' . $e->getMessage();
        }
    }
}

// ---------------------------------------------------------------------------
// POST: upload file → preview
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'apply') {
    csrf_verify();

    if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
        $code = $_FILES['csv']['error'] ?? -1;
        $errors[] = 'Échec du téléversement (code ' . (int) $code . ').';
    } else {
        $tmp = $_FILES['csv']['tmp_name'];
        $size = (int) ($_FILES['csv']['size'] ?? 0);
        if ($size <= 0 || $size > 5 * 1024 * 1024) {
            $errors[] = 'Fichier vide ou trop volumineux (max 5 Mo).';
        } else {
            $result = parse_price_csv($tmp);
            $errors = array_merge($errors, $result['errors']);
            $preview = [
                'changes'   => $result['changes'],
                'unchanged' => $result['unchanged'],
                'missing'   => $result['missing'],
            ];
        }
    }
}

require __DIR__ . '/_includes/header.php';
?>

<style>
  .import-card { background: var(--surface); border-radius: var(--radius); padding: 24px; margin-top: 16px; }
  .import-card h2 { margin: 0 0 8px; font-size: 18px; }
  .import-card p { margin: 0 0 16px; color: var(--ink-mute); font-size: 14px; }
  .import-drop { border: 2px dashed var(--border); border-radius: var(--radius-sm); padding: 32px; text-align: center; }
  .import-drop input[type=file] { display: block; margin: 12px auto; }
  .import-errors { background: #fde6e2; color: #a82e10; padding: 12px 16px; border-radius: var(--radius-sm); margin: 16px 0; }
  .import-errors ul { margin: 8px 0 0 18px; padding: 0; font-size: 13px; }
  .change-table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 14px; }
  .change-table th, .change-table td { padding: 8px 12px; border-bottom: 1px solid var(--border); text-align: left; }
  .change-table th { background: var(--surface-2); font-weight: 600; }
  .change-table .num { text-align: right; font-variant-numeric: tabular-nums; }
  .change-table .old { color: var(--ink-mute); text-decoration: line-through; }
  .change-table .new { font-weight: 600; color: var(--brand, #1b6a3f); }
  .preview-stats { display: flex; gap: 24px; margin: 16px 0; font-size: 14px; }
  .preview-stats strong { font-size: 24px; display: block; }
</style>

<div class="page">
  <div class="breadcrumb-admin">
    <a href="index.php">Tableau de bord</a><span>/</span>
    <a href="products.php">Produits</a><span>/</span>
    <span>Importer les prix</span>
  </div>

  <div class="page-head">
    <div>
      <h1>Importer les prix</h1>
      <p>Téléversez le fichier CSV modifié par la coopérative. Seules les colonnes Prix, Prix barré et Stock sont appliquées.</p>
    </div>
  </div>

  <?php if ($errors): ?>
    <div class="import-errors">
      <strong>Problèmes détectés :</strong>
      <ul>
        <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if ($preview === null): ?>
    <div class="import-card">
      <h2>1. Téléverser le fichier</h2>
      <p>Le fichier doit être au format CSV (export depuis cette interface ou réenregistré depuis Excel en CSV UTF-8).</p>
      <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="import-drop">
          <input type="file" name="csv" accept=".csv,text/csv" required>
          <button type="submit" class="btn btn-primary">Analyser le fichier</button>
        </div>
      </form>
    </div>
  <?php else: ?>
    <div class="import-card">
      <h2>2. Vérifier les changements</h2>
      <div class="preview-stats">
        <div><strong><?= count($preview['changes']) ?></strong>à modifier</div>
        <div><strong><?= (int) $preview['unchanged'] ?></strong>inchangés</div>
        <?php if ($preview['missing'] > 0): ?>
          <div><strong><?= (int) $preview['missing'] ?></strong>introuvables</div>
        <?php endif; ?>
      </div>

      <?php if (!$preview['changes']): ?>
        <p>Aucun changement détecté. Le fichier correspond aux prix actuels.</p>
        <a href="products-import.php" class="btn btn-ghost">Recommencer</a>
      <?php else: ?>
        <table class="change-table">
          <thead>
            <tr>
              <th>Produit</th>
              <th class="num">Prix avant</th>
              <th class="num">Prix après</th>
              <th class="num">Prix barré</th>
              <th class="num">Stock</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($preview['changes'] as $c): ?>
              <tr>
                <td><?= e($c['name']) ?> <span class="cell-mute">#<?= (int) $c['id'] ?></span></td>
                <td class="num">
                  <?php if (isset($c['old_price'])): ?><span class="old"><?= number_format($c['old_price'], 2, ',', ' ') ?></span><?php endif; ?>
                </td>
                <td class="num">
                  <?php if (isset($c['new_price'])): ?><span class="new"><?= number_format($c['new_price'], 2, ',', ' ') ?></span><?php endif; ?>
                </td>
                <td class="num">
                  <?php if (array_key_exists('new_compare', $c)):
                    $oc = $c['old_compare']; $nc = $c['new_compare'];
                    $changed = ($oc === null) !== ($nc === null) || ($oc !== null && $nc !== null && abs($oc - $nc) > 0.001);
                    if ($changed): ?>
                      <span class="old"><?= $oc === null ? '—' : number_format($oc, 2, ',', ' ') ?></span>
                      → <span class="new"><?= $nc === null ? '—' : number_format($nc, 2, ',', ' ') ?></span>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
                <td class="num">
                  <?php if (array_key_exists('new_stock', $c) && $c['old_stock'] !== $c['new_stock']): ?>
                    <span class="old"><?= (int) $c['old_stock'] ?></span> → <span class="new"><?= (int) $c['new_stock'] ?></span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <form method="post" style="margin-top: 20px; display: flex; gap: 12px;">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="apply">
          <input type="hidden" name="changes_json" value='<?= e(json_encode($preview['changes'], JSON_UNESCAPED_UNICODE)) ?>'>
          <button type="submit" class="btn btn-primary" data-confirm="Appliquer ces changements ?">Appliquer les <?= count($preview['changes']) ?> changement(s)</button>
          <a href="products-import.php" class="btn btn-ghost">Annuler</a>
        </form>
      <?php endif; ?>
    </div>
  <?php endif; ?>

</div>

<script>
  document.querySelectorAll('button[data-confirm]').forEach(b => {
    b.addEventListener('click', e => { if (!confirm(b.dataset.confirm)) e.preventDefault(); });
  });
</script>

<?php require __DIR__ . '/_includes/footer.php'; ?>

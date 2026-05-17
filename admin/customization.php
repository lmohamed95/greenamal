<?php
require_once __DIR__ . '/../includes/auth.php';
admin_require_login();

$page_title = 'Personnalisation';
$current    = 'customization';

// Save on POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    foreach ($_POST as $k => $v) {
        if ($k === '_csrf' || $k === '_action') continue;
        if (!is_string($v)) continue;
        db_query(
            "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
            [$k, $v]
        );
    }
    redirect('customization.php?saved=1');
}

// Load all settings into a flat array
$s = [];
foreach (db_all("SELECT setting_key, setting_value FROM settings") as $row) {
    $s[$row['setting_key']] = (string) $row['setting_value'];
}

/** Read a setting with default for the form (admin side — plain raw value). */
function v(array $s, string $k, string $d = ''): string {
    return (isset($s[$k]) && $s[$k] !== '') ? $s[$k] : $d;
}

require __DIR__ . '/_includes/header.php';
?>

<div class="page">
  <div class="breadcrumb-admin"><a href="index.php">Tableau de bord</a><span>/</span><span>Personnalisation</span></div>
  <div class="page-head">
    <div>
      <h1>Personnalisation du site</h1>
      <p>Modifiez les textes, images et CTAs de la page d'accueil sans toucher au code.</p>
    </div>
    <a href="../" target="_blank" class="btn btn-outline" style="align-self:flex-start;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      Voir la page d'accueil
    </a>
  </div>

  <?php if (!empty($_GET['saved'])): ?>
    <div style="background: var(--success-bg); color: var(--success); padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 18px; font-size: 0.88rem;">
      ✓ Personnalisation enregistrée. Rafraîchissez la page d'accueil pour voir le résultat.
    </div>
  <?php endif; ?>

  <div class="card" style="background: var(--surface-tint, #FAF1DE); border-left: 3px solid #D08F2A; margin-bottom: 18px;">
    <div class="card-body" style="font-size: 13px; line-height: 1.55;">
      <strong>Astuce · accents typographiques :</strong> dans les <em>titres</em>, utilisez ces marqueurs pour styliser un mot :
      <code style="background:rgba(0,0,0,0.06);padding:1px 6px;border-radius:4px;">{accent}mot{/accent}</code> = italique terracotta ·
      <code style="background:rgba(0,0,0,0.06);padding:1px 6px;border-radius:4px;">{ochre}mot{/ochre}</code> = italique ocre ·
      <code style="background:rgba(0,0,0,0.06);padding:1px 6px;border-radius:4px;">{br}</code> = retour à la ligne ·
      <code style="background:rgba(0,0,0,0.06);padding:1px 6px;border-radius:4px;">{code}first25{/code}</code> = badge code promo.
      Tout autre HTML est échappé pour la sécurité.
    </div>
  </div>

  <form method="post">
    <?= csrf_field() ?>

    <!-- ═════════════════════ HERO ═════════════════════ -->
    <div class="card" style="margin-bottom: 16px;">
      <div class="card-head">
        <h3>Hero (haut de page)</h3>
        <span class="head-meta">Le bloc principal en haut de la page d'accueil</span>
      </div>
      <div class="card-body">
        <div style="display:grid; grid-template-columns: 1.2fr 1fr; gap:24px;" class="cust-hero-grid">
          <div>
            <div class="form-grid">
              <div class="field full">
                <label>Eyebrow (petite ligne au-dessus du titre)</label>
                <input type="text" name="home_hero_eyebrow" value="<?= e(v($s, 'home_hero_eyebrow', 'Coopérative féminine · Azrou, Maroc')) ?>">
              </div>
              <div class="field full">
                <label>Titre principal (H1)</label>
                <input type="text" name="home_hero_title" value="<?= e(v($s, 'home_hero_title', 'Le Maroc {accent}authentique{/accent},{br}en bouteille.')) ?>">
                <span class="help">Ex. <code>Le Maroc {accent}authentique{/accent},{br}en bouteille.</code></span>
              </div>
              <div class="field full">
                <label>Sous-titre (paragraphe d'intro)</label>
                <textarea name="home_hero_lede" rows="3"><?= e(v($s, 'home_hero_lede', "Des huiles essentielles distillées à la main, des plantes cueillies dans l'Atlas, du couscous roulé selon la tradition. 100 % naturels, certifiés ONSSA, livrés chez vous.")) ?></textarea>
              </div>
              <div class="field">
                <label>CTA principal · texte</label>
                <input type="text" name="home_hero_cta_primary_label" value="<?= e(v($s, 'home_hero_cta_primary_label', 'Découvrir la boutique')) ?>">
              </div>
              <div class="field">
                <label>CTA principal · lien</label>
                <input type="text" name="home_hero_cta_primary_url" value="<?= e(v($s, 'home_hero_cta_primary_url', '/boutique')) ?>">
              </div>
              <div class="field">
                <label>CTA secondaire · texte</label>
                <input type="text" name="home_hero_cta_secondary_label" value="<?= e(v($s, 'home_hero_cta_secondary_label', 'Notre histoire')) ?>">
              </div>
              <div class="field">
                <label>CTA secondaire · lien</label>
                <input type="text" name="home_hero_cta_secondary_url" value="<?= e(v($s, 'home_hero_cta_secondary_url', '/notre-histoire')) ?>">
              </div>
              <div class="field">
                <label>Pastille de confiance 1</label>
                <input type="text" name="home_hero_trust_1" value="<?= e(v($s, 'home_hero_trust_1', 'Certifié ONSSA')) ?>">
              </div>
              <div class="field">
                <label>Pastille de confiance 2</label>
                <input type="text" name="home_hero_trust_2" value="<?= e(v($s, 'home_hero_trust_2', 'Livraison 24-48h')) ?>">
              </div>
              <div class="field full">
                <label>Pastille de confiance 3</label>
                <input type="text" name="home_hero_trust_3" value="<?= e(v($s, 'home_hero_trust_3', 'Paiement à la livraison')) ?>">
              </div>
            </div>

            <div style="margin-top:18px; padding-top:18px; border-top:1px solid var(--line);">
              <strong style="display:block; margin-bottom:10px; font-size:0.78rem; letter-spacing:0.08em; text-transform:uppercase; color:var(--ink-3);">Cartes flottantes sur l'image</strong>
              <div class="form-grid">
                <div class="field">
                  <label>Note (gros chiffre)</label>
                  <input type="text" name="home_hero_card_rating_num" value="<?= e(v($s, 'home_hero_card_rating_num', '4.9')) ?>">
                </div>
                <div class="field">
                  <label>Note (sur)</label>
                  <input type="text" name="home_hero_card_rating_frac" value="<?= e(v($s, 'home_hero_card_rating_frac', '/ 5')) ?>">
                </div>
                <div class="field full">
                  <label>Sous-texte note</label>
                  <input type="text" name="home_hero_card_rating_sub" value="<?= e(v($s, 'home_hero_card_rating_sub', '+1 200 AVIS CLIENTS')) ?>">
                </div>
                <div class="field">
                  <label>Carte 2 · chiffre</label>
                  <input type="text" name="home_hero_card_years_num" value="<?= e(v($s, 'home_hero_card_years_num', '+15 ans')) ?>">
                </div>
                <div class="field">
                  <label>Carte 2 · sous-texte</label>
                  <input type="text" name="home_hero_card_years_sub" value="<?= e(v($s, 'home_hero_card_years_sub', 'de savoir-faire')) ?>">
                </div>
              </div>
            </div>
          </div>

          <div>
            <label style="display:block; font-size:0.78rem; font-weight:600; margin-bottom:8px; color:var(--ink-2);">Image du hero</label>
            <div class="upload-widget"
                 data-target="hero"
                 data-name="hero_image_url"
                 data-current="<?= e(v($s, 'hero_image_url', '/assets/img/categories/huiles-essentielles.jpg')) ?>"></div>
            <p class="help" style="margin-top: 8px;">Format conseillé : JPG/WebP, ratio 4:5, ≥ 1200 × 1500 px. Maximum 5 Mo.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ═════════════════════ TRUST STRIP ═════════════════════ -->
    <div class="card" style="margin-bottom: 16px;">
      <div class="card-head">
        <h3>Bandeau de confiance</h3>
        <span class="head-meta">4 items en bandeau vert sous le hero</span>
      </div>
      <div class="card-body">
        <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:14px;" class="settings-grid">
          <?php foreach ([1,2,3,4] as $i):
            $defaults_t = [
              1 => ['Origine Atlas', 'Cueillette artisanale'],
              2 => ['Livraison 24-48h', 'Partout au Maroc'],
              3 => ['COD disponible', 'Paiement à la livraison'],
              4 => ['Satisfait ou remboursé', "14 jours pour changer d'avis"],
            ];
          ?>
            <div style="padding:14px; border:1px solid var(--line); border-radius:8px;">
              <div style="font-size:11px; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:var(--ink-3); margin-bottom:10px;">Item <?= $i ?></div>
              <div class="form-grid">
                <div class="field full">
                  <label>Titre</label>
                  <input type="text" name="home_trust_<?= $i ?>_title" value="<?= e(v($s, "home_trust_{$i}_title", $defaults_t[$i][0])) ?>">
                </div>
                <div class="field full">
                  <label>Sous-titre</label>
                  <input type="text" name="home_trust_<?= $i ?>_subtitle" value="<?= e(v($s, "home_trust_{$i}_subtitle", $defaults_t[$i][1])) ?>">
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- ═════════════════════ BEST-SELLERS ═════════════════════ -->
    <div class="card" style="margin-bottom: 16px;">
      <div class="card-head">
        <h3>Best-sellers (en-tête de section)</h3>
        <span class="head-meta">Les produits affichés sont sélectionnés via « Produit en vedette »</span>
      </div>
      <div class="card-body">
        <div class="form-grid">
          <div class="field"><label>Eyebrow</label><input type="text" name="home_best_eyebrow" value="<?= e(v($s, 'home_best_eyebrow', '★ Best-sellers')) ?>"></div>
          <div class="field"><label>Lien (texte)</label><input type="text" name="home_best_link_label" value="<?= e(v($s, 'home_best_link_label', 'Voir tous les best-sellers')) ?>"></div>
          <div class="field full"><label>Titre</label><input type="text" name="home_best_title" value="<?= e(v($s, 'home_best_title', 'Les {accent}coups de cœur{/accent} de la tribu.')) ?>"></div>
          <div class="field full"><label>Sous-titre</label><input type="text" name="home_best_subtitle" value="<?= e(v($s, 'home_best_subtitle', "Les produits que nos client·e·s commandent encore et encore.")) ?>"></div>
        </div>
      </div>
    </div>

    <!-- ═════════════════════ CATEGORIES ═════════════════════ -->
    <div class="card" style="margin-bottom: 16px;">
      <div class="card-head"><h3>Catégories (en-tête de section)</h3></div>
      <div class="card-body">
        <div class="form-grid">
          <div class="field"><label>Eyebrow</label><input type="text" name="home_cat_eyebrow" value="<?= e(v($s, 'home_cat_eyebrow', 'Nos univers')) ?>"></div>
          <div class="field"><label>Lien (texte)</label><input type="text" name="home_cat_link_label" value="<?= e(v($s, 'home_cat_link_label', 'Tout voir')) ?>"></div>
          <div class="field full"><label>Titre</label><input type="text" name="home_cat_title" value="<?= e(v($s, 'home_cat_title', 'Explorez nos {accent}catégories{/accent}.')) ?>"></div>
          <div class="field full"><label>Sous-titre</label><input type="text" name="home_cat_subtitle" value="<?= e(v($s, 'home_cat_subtitle', 'Dix familles de produits, une seule promesse : la nature à l\'état pur.')) ?>"></div>
        </div>
      </div>
    </div>

    <!-- ═════════════════════ NOUVEAUTÉS ═════════════════════ -->
    <div class="card" style="margin-bottom: 16px;">
      <div class="card-head"><h3>Nouveautés (en-tête de section)</h3></div>
      <div class="card-body">
        <div class="form-grid">
          <div class="field"><label>Eyebrow</label><input type="text" name="home_new_eyebrow" value="<?= e(v($s, 'home_new_eyebrow', 'Nouveautés')) ?>"></div>
          <div class="field"><label>Lien (texte)</label><input type="text" name="home_new_link_label" value="<?= e(v($s, 'home_new_link_label', 'Tout voir')) ?>"></div>
          <div class="field full"><label>Titre</label><input type="text" name="home_new_title" value="<?= e(v($s, 'home_new_title', 'Tout juste {ochre}arrivé{/ochre}.')) ?>"></div>
        </div>
      </div>
    </div>

    <!-- ═════════════════════ STORY ═════════════════════ -->
    <div class="card" style="margin-bottom: 16px;">
      <div class="card-head"><h3>Notre histoire (bloc avec image)</h3></div>
      <div class="card-body">
        <div style="display:grid; grid-template-columns: 1.2fr 1fr; gap:24px;" class="cust-hero-grid">
          <div>
            <div class="form-grid">
              <div class="field"><label>Eyebrow</label><input type="text" name="home_story_eyebrow" value="<?= e(v($s, 'home_story_eyebrow', 'Notre histoire')) ?>"></div>
              <div class="field"><label>Stat · chiffre</label><input type="text" name="home_story_stat_num" value="<?= e(v($s, 'home_story_stat_num', '+45')) ?>"></div>
              <div class="field full"><label>Titre</label><input type="text" name="home_story_title" value="<?= e(v($s, 'home_story_title', 'Une coopérative, des mains, un {accent}héritage{/accent}.')) ?>"></div>
              <div class="field full"><label>Paragraphe 1</label><textarea name="home_story_p1" rows="3"><?= e(v($s, 'home_story_p1', "À Azrou, au cœur du Moyen Atlas, des femmes amazighes perpétuent un savoir-faire transmis de mères en filles. Cueillette à l'aube, distillation au feu de bois, mouture à la pierre.")) ?></textarea></div>
              <div class="field full"><label>Paragraphe 2</label><textarea name="home_story_p2" rows="3"><?= e(v($s, 'home_story_p2', "Chaque produit GreenAmal raconte cette histoire, celle d'un Maroc rural, debout, fier, et résolument tourné vers l'avenir.")) ?></textarea></div>
              <div class="field"><label>Stat · libellé</label><input type="text" name="home_story_stat_label" value="<?= e(v($s, 'home_story_stat_label', 'femmes accompagnées')) ?>"></div>
              <div class="field"><label>CTA · texte</label><input type="text" name="home_story_cta_label" value="<?= e(v($s, 'home_story_cta_label', 'En savoir plus')) ?>"></div>
              <div class="field full"><label>CTA · lien</label><input type="text" name="home_story_cta_url" value="<?= e(v($s, 'home_story_cta_url', '/notre-histoire')) ?>"></div>
            </div>

            <div style="margin-top:18px; padding-top:18px; border-top:1px solid var(--line);">
              <strong style="display:block; margin-bottom:10px; font-size:0.78rem; letter-spacing:0.08em; text-transform:uppercase; color:var(--ink-3);">Les 4 valeurs (sous le paragraphe)</strong>
              <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:12px;">
                <?php $val_def = [
                  1 => ['100% local', 'Cultivé et transformé au Maroc.'],
                  2 => ['Certifié ONSSA', 'Qualité contrôlée à chaque lot.'],
                  3 => ['Commerce équitable', 'Rémunération juste des productrices.'],
                  4 => ['Sans intermédiaire', 'Du cultivateur à votre porte.'],
                ]; foreach ([1,2,3,4] as $i): ?>
                  <div style="padding:10px; border:1px solid var(--line); border-radius:8px;">
                    <div class="form-grid">
                      <div class="field full"><label>Valeur <?= $i ?> · titre</label><input type="text" name="home_story_value_<?= $i ?>_title" value="<?= e(v($s, "home_story_value_{$i}_title", $val_def[$i][0])) ?>"></div>
                      <div class="field full"><label>Valeur <?= $i ?> · texte</label><input type="text" name="home_story_value_<?= $i ?>_text" value="<?= e(v($s, "home_story_value_{$i}_text", $val_def[$i][1])) ?>"></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <div>
            <label style="display:block; font-size:0.78rem; font-weight:600; margin-bottom:8px; color:var(--ink-2);">Image de la coopérative</label>
            <div class="upload-widget"
                 data-target="hero"
                 data-name="home_story_image"
                 data-current="<?= e(v($s, 'home_story_image', '')) ?>"></div>
            <p class="help" style="margin-top: 8px;">Ratio portrait 4:5 recommandé. Laissez vide pour utiliser l'image par défaut.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ═════════════════════ REVIEWS ═════════════════════ -->
    <div class="card" style="margin-bottom: 16px;">
      <div class="card-head"><h3>Avis clients (témoignages)</h3></div>
      <div class="card-body">
        <div class="form-grid">
          <div class="field"><label>Eyebrow (note globale)</label><input type="text" name="home_review_eyebrow" value="<?= e(v($s, 'home_review_eyebrow', '★★★★★ · 1 247 avis')) ?>"></div>
          <div class="field"><label>Lien (texte)</label><input type="text" name="home_review_link_label" value="<?= e(v($s, 'home_review_link_label', 'Découvrir les produits')) ?>"></div>
          <div class="field full"><label>Titre</label><input type="text" name="home_review_title" value="<?= e(v($s, 'home_review_title', "Ce qu'on en pense.")) ?>"></div>
        </div>

        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:14px; margin-top:16px;" class="review-edit-grid">
          <?php $rev_def = [
            1 => ["L'huile d'argan est juste extraordinaire. Goût intense, parfum incomparable. On sent qu'elle a été faite avec soin. Je ne reviendrai jamais en arrière.", 'Salma K.', 'Casablanca · vérifié'],
            2 => ["Le couscous d'orge me rappelle celui de ma grand-mère. Texture parfaite, goût authentique. Et la livraison était au top, 36h pour Rabat.", 'Younes A.', 'Rabat · vérifié'],
            3 => ["Les huiles essentielles sont d'une pureté rare. Mon kiné en utilise et m'a recommandé GreenAmal. Service WhatsApp ultra-réactif aussi.", 'Fatima B.', 'Marrakech · vérifié'],
          ]; foreach ([1,2,3] as $i): ?>
            <div style="padding:12px; border:1px solid var(--line); border-radius:8px;">
              <div style="font-size:11px; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:var(--ink-3); margin-bottom:10px;">Avis <?= $i ?></div>
              <div class="form-grid">
                <div class="field full"><label>Texte</label><textarea name="home_review_<?= $i ?>_text" rows="4"><?= e(v($s, "home_review_{$i}_text", $rev_def[$i][0])) ?></textarea></div>
                <div class="field"><label>Nom</label><input type="text" name="home_review_<?= $i ?>_name" value="<?= e(v($s, "home_review_{$i}_name", $rev_def[$i][1])) ?>"></div>
                <div class="field"><label>Lieu / méta</label><input type="text" name="home_review_<?= $i ?>_meta" value="<?= e(v($s, "home_review_{$i}_meta", $rev_def[$i][2])) ?>"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- ═════════════════════ NEWSLETTER ═════════════════════ -->
    <div class="card" style="margin-bottom: 16px;">
      <div class="card-head"><h3>Newsletter (bandeau vert foncé)</h3></div>
      <div class="card-body">
        <div class="form-grid">
          <div class="field"><label>Eyebrow</label><input type="text" name="home_nl_eyebrow" value="<?= e(v($s, 'home_nl_eyebrow', '−25% offerts')) ?>"></div>
          <div class="field"><label>Texte du bouton</label><input type="text" name="home_nl_btn" value="<?= e(v($s, 'home_nl_btn', "S'inscrire")) ?>"></div>
          <div class="field full"><label>Titre</label><input type="text" name="home_nl_title" value="<?= e(v($s, 'home_nl_title', 'Rejoignez la tribu {ochre}GreenAmal{/ochre}.')) ?>"></div>
          <div class="field full"><label>Paragraphe</label><textarea name="home_nl_text" rows="2"><?= e(v($s, 'home_nl_text', 'Recevez le code {code}first25{/code}, des recettes amazighes inédites et nos offres en avant-première.')) ?></textarea></div>
        </div>
      </div>
    </div>

    <div style="position:sticky; bottom:0; padding:16px 0; background:var(--bg); margin-top:24px; border-top:1px solid var(--line);">
      <button type="submit" class="btn btn-primary btn-lg">Enregistrer la personnalisation</button>
      <a href="../" target="_blank" class="btn btn-outline btn-lg" style="margin-left:8px;">Prévisualiser</a>
    </div>
  </form>
</div>

<style>
  @media (max-width: 960px) {
    .cust-hero-grid { grid-template-columns: 1fr !important; }
    .review-edit-grid { grid-template-columns: 1fr !important; }
  }
</style>

<?php require __DIR__ . '/_includes/footer.php'; ?>

<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail du bien — Key Immobilier</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include_once('portions de page/entete.html'); ?>

<?php
if (isset($_GET['code'])) {
    $connect = new PDO("mysql:host=localhost;dbname=agence_immobiliere", "immobilier", "motdepasse");
    if ($connect) {
        $code   = (int)$_GET['code'];
        $result = $connect->query("SELECT * FROM bien WHERE id_bien=$code")->fetch();
        $dim       = $result['dim'];
        $bien      = $result['type'];
        $details   = $result['details'];
        $tabimg    = explode(',', $result['image']);
        $price     = number_format($result['prix'], 0, ',', ' ');
        $ville     = $result['ville'];
        $nom_bien  = $result['nom_bien'];
        $adresse   = $result['adresse'];
        $idprop    = $result['id_prop'];

        $idpropR = $connect->query("SELECT idpers FROM proprietaire WHERE idprop=$idprop")->fetch();
        $idpropR = $idpropR['idpers'];
        $prop    = $connect->query("SELECT * FROM personne WHERE idpers=$idpropR")->fetch();
        $nom     = $prop['nom'];
        $prenom  = $prop['prenom'];
        $tel     = $prop['tel'];
        $mail    = $prop['mail'];
    }
}
?>

<a href="javascript:history.back()" class="back-link">← Retour</a>

<div class="detail-layout">
    <!-- Galerie -->
    <section class="detail-gallery">
        <img src="<?= htmlspecialchars($tabimg[1] ?? '') ?>" id="p_img" alt="Photo principale" onerror="this.src='images/image/villa2.jpg'">
        <div id="s_img">
            <?php foreach ($tabimg as $value): if (!trim($value)) continue; ?>
                <img src="<?= htmlspecialchars(trim($value)) ?>" class="s_img" alt="Miniature">
            <?php endforeach; ?>
        </div>

        <!-- Description -->
        <div style="background:white;border-radius:var(--radius);padding:1.5rem;margin-top:1.5rem;box-shadow:var(--shadow);">
            <h3 style="margin-bottom:.75rem;">Description</h3>
            <p style="color:var(--gris);line-height:1.7;"><?= htmlspecialchars($details) ?></p>
        </div>
    </section>

    <!-- Fiche info -->
    <aside class="detail-info" style="background:white;border-radius:var(--radius);padding:2rem;box-shadow:var(--shadow);position:sticky;top:1rem;">
        <span class="badge-type" style="margin-bottom:1rem;"><?= htmlspecialchars($bien) ?></span>
        <h2 style="font-size:1.5rem;margin-bottom:1.5rem;"><?= htmlspecialchars($nom_bien) ?></h2>

        <ul>
            <li>
                <h1>Prix</h1>
                <span style="color:var(--or);font-size:1.3rem;font-weight:700;"><?= $price ?> €<span style="font-size:.85rem;font-weight:400;color:var(--gris);">/mois</span></span>
            </li>
            <li><h1>Dimension</h1><span><?= htmlspecialchars($dim) ?></span></li>
            <li><h1>Ville</h1><span><?= htmlspecialchars($ville) ?></span></li>
            <li><h1>Adresse</h1><span><?= htmlspecialchars($adresse) ?></span></li>
        </ul>

        <div style="border-top:1px solid var(--gris-clair);margin-top:1.2rem;padding-top:1.2rem;">
            <p style="font-size:.82rem;text-transform:uppercase;letter-spacing:.06em;color:var(--gris);font-weight:600;margin-bottom:.75rem;">Propriétaire</p>
            <p style="font-size:.92rem;color:var(--bleu);">
                <strong><?= htmlspecialchars($prenom . ' ' . $nom) ?></strong><br>
                📞 <?= htmlspecialchars($tel) ?><br>
                ✉️ <?= htmlspecialchars($mail) ?>
            </p>
        </div>

        <?php if (!isset($_GET['idpers']) && isset($_GET['code'])): ?>
            <a href="visiteur.php?afficher=true" style="display:block;text-align:center;margin-top:1.5rem;padding:1rem;border-radius:var(--radius-sm);border:1.5px solid var(--bleu);color:var(--bleu);font-weight:500;font-size:.95rem;">
                Connectez-vous pour louer ce bien
            </a>
        <?php elseif (isset($_GET['code']) && isset($_GET['idpers'])): ?>
            <form action="traiter_location.php" method="post">
                <input type="hidden" name="idpers" value="<?= (int)$_GET['idpers'] ?>">
                <input type="hidden" name="label_bien" value="<?= htmlspecialchars($bien) ?>">
                <input type="hidden" name="id_bien" value="<?= $code ?>">
                <input type="submit" value="🔑 Louer ce bien" id="louer">
            </form>
        <?php endif; ?>
    </aside>
</div>

<?php include_once('portions de page/footer.html'); $connect = null; ?>

<script>
const thumbs = document.querySelectorAll('.s_img');
const main   = document.getElementById('p_img');
thumbs.forEach(img => {
    img.addEventListener('click', function() {
        main.src = this.src;
        thumbs.forEach(t => t.style.opacity = '.6');
        this.style.opacity = '1';
    });
});
</script>
</body>
</html>

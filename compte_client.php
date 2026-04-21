<?php
session_start();
if (!(isset($_GET['code']) && isset($_SESSION['connect'.$_GET['code']]) && $_SESSION['connect'.$_GET['code']]==$_GET['code'])) {
    die("<div style='font-family:sans-serif;padding:3rem;text-align:center;'><p style='color:#c0392b;font-size:1.2rem;margin-bottom:1rem;'>Veuillez vous identifier.</p><a href='authentification_commerciaux.php'>← Retour</a></div>");
}
$code = $_GET['code'];

try {
    $connect = new PDO("mysql:host=localhost;dbname=agence_immobiliere;charset=utf8", "immobilier", "motdepasse", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Erreur BDD : " . htmlspecialchars($e->getMessage()));
}

$message = '';

// Suppression
if (isset($_POST['supprimer'])) {
    $idpers = (int)$_POST['supprimer'];
    $connect->exec("DELETE FROM personne WHERE idpers=$idpers");
    $message = "Le compte client a été supprimé.";
}

// Recherche
$search = $_POST['search'] ?? '';
if ($search) {
    $stmt = $connect->prepare("SELECT p.*, cc.password FROM personne p JOIN compte_client cc ON p.mail=cc.mail WHERE p.nom LIKE ? OR p.prenom LIKE ? OR p.mail LIKE ?");
    $s = "%$search%";
    $stmt->execute([$s, $s, $s]);
} else {
    $stmt = $connect->query("SELECT p.*, cc.password FROM personne p JOIN compte_client cc ON p.mail=cc.mail");
}
$clients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comptes clients — Key Immobilier</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .clients-page { max-width: 1100px; margin: 0 auto; padding: 0 2rem 3rem; }
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .table-section {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th {
            background: var(--bleu);
            color: var(--or-clair);
            font-size: .78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: .85rem 1rem;
            text-align: left;
        }
        td { padding: .85rem 1rem; border-bottom: 1px solid var(--gris-clair); font-size: .9rem; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: var(--blanc); }
        .action-group { display: flex; gap: .5rem; }
        .empty-state { text-align:center; padding:2.5rem; color:var(--gris); }
        .alert-success { background:#dcfce7;color:#16a34a;border-radius:var(--radius-sm);padding:.85rem 1.25rem;margin-bottom:1.25rem;font-size:.9rem; }
        .avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: var(--or-clair);
            color: var(--bleu);
            font-weight: 700;
            font-size: .85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .client-name { display: flex; align-items: center; gap: .75rem; }
    </style>
</head>
<body>
<?php include_once("portions de page/entete.html"); ?>

<a href="commerciaux.php?code=<?= $code ?>" class="back-link">← Retour au tableau de bord</a>

<div class="clients-page">
    <div class="section-header" style="padding:0 0 1.5rem;">
        <h2>Gestion des comptes clients</h2>
        <p><?= count($clients) ?> client<?= count($clients) > 1 ? 's' : '' ?> enregistré<?= count($clients) > 1 ? 's' : '' ?></p>
    </div>

    <?php if ($message): ?>
        <div class="alert-success">✅ <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="top-bar">
        <form method="post" action="" style="display:contents;">
            <div class="search-bar" style="max-width:360px;">
                <input type="search" name="search" placeholder="Rechercher par nom, email…" value="<?= htmlspecialchars($search) ?>">
                <input type="submit" value="Rechercher">
            </div>
        </form>
    </div>

    <div class="table-section">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>CIN</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th>Naissance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($clients)): ?>
                    <tr><td colspan="6"><div class="empty-state">Aucun client trouvé.</div></td></tr>
                <?php else: foreach ($clients as $c): ?>
                    <tr>
                        <td>
                            <div class="client-name">
                                <div class="avatar"><?= strtoupper(substr($c['prenom'],0,1) . substr($c['nom'],0,1)) ?></div>
                                <div>
                                    <strong><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></strong>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($c['cin']) ?></td>
                        <td><?= htmlspecialchars($c['tel']) ?></td>
                        <td><?= htmlspecialchars($c['mail']) ?></td>
                        <td><?= htmlspecialchars($c['dateNaissance']) ?></td>
                        <td>
                            <div class="action-group">
                                <form method="post" action="modifier_compte_client.php">
                                    <input type="hidden" name="code" value="<?= $c['idpers'] ?>">
                                    <button type="submit" class="btn btn-outline btn-sm">✏️ Modifier</button>
                                </form>
                                <form method="post" action="" onsubmit="return confirm('Supprimer ce client ?')">
                                    <input type="hidden" name="supprimer" value="<?= $c['idpers'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">🗑 Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once("portions de page/footer.html"); ?>
</body>
</html>

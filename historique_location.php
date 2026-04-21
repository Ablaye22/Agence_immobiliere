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

// Récupérer tous les biens loués
$loues = $connect->query("
    SELECT b.*, p.nom, p.prenom, p.tel, p.mail, l.idLoc
    FROM bien b
    JOIN locataire l ON b.idLoc = l.idLoc
    JOIN personne p ON l.idpers = p.idpers
")->fetchAll();

// Récupérer toutes les demandes
$demandes = $connect->query("
    SELECT dl.*, p.nom, p.prenom, p.mail, p.tel
    FROM demand_location dl
    JOIN personne p ON dl.idpers = p.idpers
    ORDER BY dl.No_demand DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des locations — Key Immobilier</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .hist-page { max-width: 1100px; margin: 0 auto; padding: 0 2rem 3rem; }
        .tabs {
            display: flex;
            gap: .5rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--gris-clair);
            padding-bottom: 0;
        }
        .tab-btn {
            padding: .65rem 1.5rem;
            border: none;
            background: none;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            font-weight: 500;
            color: var(--gris);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all .2s;
        }
        .tab-btn.active { color: var(--bleu); border-bottom-color: var(--or); font-weight: 600; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
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
        .empty-state { text-align:center; padding:2.5rem; color:var(--gris); font-size:.95rem; }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .stat-icon {
            width: 44px; height: 44px;
            border-radius: 10px;
            background: var(--or-clair);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        .stat-num { font-size: 1.6rem; font-weight: 700; color: var(--bleu); line-height: 1; }
        .stat-label { font-size: .82rem; color: var(--gris); margin-top: .2rem; }
        @media(max-width:600px) { .stats-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php include_once("portions de page/entete.html"); ?>

<a href="commerciaux.php?code=<?= $code ?>" class="back-link">← Retour au tableau de bord</a>

<div class="hist-page">
    <div class="section-header" style="padding:0 0 1.5rem;">
        <h2>Historique des locations</h2>
        <p>Vue d'ensemble des biens loués et des demandes en cours.</p>
    </div>

    <!-- Statistiques -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon">🏠</div>
            <div>
                <div class="stat-num"><?= count($loues) ?></div>
                <div class="stat-label">Biens loués actuellement</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div>
                <div class="stat-num"><?= count($demandes) ?></div>
                <div class="stat-label">Total des demandes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div>
                <?php $attente = array_filter($demandes, fn($d) => $d['reponse'] === null || (!in_array($d['reponse'], [0,1,2]))); ?>
                <div class="stat-num"><?= count($attente) ?></div>
                <div class="stat-label">Demandes en attente</div>
            </div>
        </div>
    </div>

    <!-- Onglets -->
    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('loues', this)">🏠 Biens loués (<?= count($loues) ?>)</button>
        <button class="tab-btn" onclick="switchTab('demandes', this)">📋 Toutes les demandes (<?= count($demandes) ?>)</button>
    </div>

    <!-- Tab : Biens loués -->
    <div class="tab-content active" id="tab-loues">
        <div class="table-section">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Bien</th>
                            <th>Type</th>
                            <th>Ville</th>
                            <th>Prix/mois</th>
                            <th>Locataire</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($loues)): ?>
                        <tr><td colspan="7"><div class="empty-state">Aucun bien loué actuellement.</div></td></tr>
                    <?php else: foreach ($loues as $r): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['nom_bien']) ?></strong></td>
                            <td><span class="badge-type"><?= htmlspecialchars($r['type']) ?></span></td>
                            <td><?= htmlspecialchars($r['ville']) ?></td>
                            <td style="color:var(--or);font-weight:600;"><?= number_format($r['prix'],0,',',' ') ?> €</td>
                            <td><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></td>
                            <td><?= htmlspecialchars($r['tel']) ?></td>
                            <td><?= htmlspecialchars($r['mail']) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab : Toutes les demandes -->
    <div class="tab-content" id="tab-demandes">
        <div class="table-section">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Client</th>
                            <th>Email</th>
                            <th>Type de bien</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($demandes)): ?>
                        <tr><td colspan="5"><div class="empty-state">Aucune demande enregistrée.</div></td></tr>
                    <?php else: foreach ($demandes as $d):
                        $reponse = $d['reponse'];
                        if ($reponse == 1) { $badge = '<span class="badge badge-success">Accepté</span>'; }
                        elseif ($reponse == 0) { $badge = '<span class="badge badge-danger">Refusé</span>'; }
                        elseif ($reponse == 2) { $badge = '<span class="badge badge-warning">Confirmé client</span>'; }
                        else { $badge = '<span class="badge" style="background:#f3f4f6;color:var(--gris);">En attente</span>'; }
                    ?>
                        <tr>
                            <td><strong>#<?= $d['No_demand'] ?></strong></td>
                            <td><?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?></td>
                            <td><?= htmlspecialchars($d['mail']) ?></td>
                            <td><span class="badge-type"><?= htmlspecialchars($d['label_bien']) ?></span></td>
                            <td><?= $badge ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once("portions de page/footer.html"); ?>

<script>
function switchTab(id, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + id).classList.add('active');
    btn.classList.add('active');
}
</script>
</body>
</html>

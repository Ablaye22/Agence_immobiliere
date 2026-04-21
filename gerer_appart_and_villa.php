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

if (isset($_POST['a_no_demand'])) {
    $no = (int)$_POST['a_no_demand'];
    $connect->exec("UPDATE demand_location SET reponse=1 WHERE No_demand=$no");
    $message = "✅ Demande #$no acceptée.";
}
if (isset($_POST['r_no_demand'])) {
    $no = (int)$_POST['r_no_demand'];
    $connect->exec("UPDATE demand_location SET reponse=0 WHERE No_demand=$no");
    $message = "❌ Demande #$no refusée.";
}
if (isset($_POST['v_no_demand'])) {
    $no = (int)$_POST['v_no_demand'];
    $row = $connect->query("SELECT * FROM demand_location WHERE No_demand=$no")->fetch();
    if ($row) {
        $idpers  = $row['idpers'];
        $id_bien = $row['id_bien'];
        $connect->exec("INSERT INTO locataire(idpers) VALUES($idpers)");
        $idLoc = $connect->query("SELECT idLoc FROM locataire WHERE idpers=$idpers ORDER BY idLoc DESC LIMIT 1")->fetch()['idLoc'];
        $connect->exec("UPDATE bien SET idLoc=$idLoc WHERE id_bien=$id_bien");
        $connect->exec("DELETE FROM demand_location WHERE No_demand=$no");
        $message = "✅ Location validée pour la demande #$no.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des demandes — Key Immobilier</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .manage-page { max-width: 1100px; margin: 0 auto; padding: 0 2rem 3rem; }
        .table-section {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        .table-section-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gris-clair);
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .table-section-header h3 { font-size: 1.1rem; margin: 0; }
        .table-section-header .count {
            background: var(--or-clair);
            color: var(--bleu);
            font-size: .75rem;
            font-weight: 700;
            padding: .2rem .6rem;
            border-radius: 999px;
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
        .empty-state {
            text-align: center;
            padding: 2.5rem;
            color: var(--gris);
            font-size: .95rem;
        }
        .alert {
            padding: .85rem 1.25rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.25rem;
            font-size: .9rem;
            background: #dcfce7;
            color: #16a34a;
        }
    </style>
</head>
<body>
<?php include_once("portions de page/entete.html"); ?>

<a href="commerciaux.php?code=<?= $code ?>" class="back-link">← Retour au tableau de bord</a>

<div class="manage-page">
    <div class="section-header" style="padding:0 0 1.5rem;">
        <h2>Gestion des demandes de location</h2>
        <p>Acceptez, refusez ou finalisez les demandes des clients.</p>
    </div>

    <?php if ($message): ?>
        <div class="alert"><?= $message ?></div>
    <?php endif; ?>

    <!-- Demandes en attente -->
    <div class="table-section">
        <div class="table-section-header">
            <span style="font-size:1.3rem;">📋</span>
            <h3>Demandes en attente</h3>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th>Type de bien</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $res = $connect->query("SELECT DISTINCT dl.*, p.nom, p.prenom, p.tel, p.mail FROM demand_location dl JOIN personne p ON dl.idpers=p.idpers WHERE dl.reponse IS NULL OR dl.reponse NOT IN (0,1,2)");
                $rows = $res->fetchAll();
                if (empty($rows)): ?>
                    <tr><td colspan="7"><div class="empty-state">Aucune demande en attente.</div></td></tr>
                <?php else: foreach ($rows as $row): ?>
                    <tr>
                        <td><strong>#<?= $row['No_demand'] ?></strong></td>
                        <td><?= htmlspecialchars($row['nom']) ?></td>
                        <td><?= htmlspecialchars($row['prenom']) ?></td>
                        <td><?= htmlspecialchars($row['tel']) ?></td>
                        <td><?= htmlspecialchars($row['mail']) ?></td>
                        <td><span class="badge-type"><?= htmlspecialchars($row['label_bien']) ?></span></td>
                        <td>
                            <div class="action-group">
                                <form method="post" action="">
                                    <input type="hidden" name="a_no_demand" value="<?= $row['No_demand'] ?>">
                                    <input type="hidden" name="code" value="<?= $code ?>">
                                    <button type="submit" class="btn btn-success btn-sm">✓ Accepter</button>
                                </form>
                                <form method="post" action="">
                                    <input type="hidden" name="r_no_demand" value="<?= $row['No_demand'] ?>">
                                    <input type="hidden" name="code" value="<?= $code ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">✕ Refuser</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Demandes acceptées à finaliser -->
    <div class="table-section">
        <div class="table-section-header">
            <span style="font-size:1.3rem;">✅</span>
            <h3>Demandes acceptées — à finaliser</h3>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th>Type de bien</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $res2 = $connect->query("SELECT DISTINCT dl.*, p.nom, p.prenom, p.tel, p.mail FROM demand_location dl JOIN personne p ON dl.idpers=p.idpers WHERE dl.reponse=2");
                $rows2 = $res2->fetchAll();
                if (empty($rows2)): ?>
                    <tr><td colspan="7"><div class="empty-state">Aucune demande à finaliser.</div></td></tr>
                <?php else: foreach ($rows2 as $row): ?>
                    <tr>
                        <td><strong>#<?= $row['No_demand'] ?></strong></td>
                        <td><?= htmlspecialchars($row['nom']) ?></td>
                        <td><?= htmlspecialchars($row['prenom']) ?></td>
                        <td><?= htmlspecialchars($row['tel']) ?></td>
                        <td><?= htmlspecialchars($row['mail']) ?></td>
                        <td><span class="badge-type"><?= htmlspecialchars($row['label_bien']) ?></span></td>
                        <td>
                            <form method="post" action="">
                                <input type="hidden" name="v_no_demand" value="<?= $row['No_demand'] ?>">
                                <input type="hidden" name="code" value="<?= $code ?>">
                                <button type="submit" class="btn btn-gold btn-sm">🔑 Valider la location</button>
                            </form>
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

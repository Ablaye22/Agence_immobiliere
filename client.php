<?php
try {
    $connection = new PDO("mysql:host=localhost;dbname=agence_immobiliere;charset=utf8", "immobilier", "motdepasse", [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("<div style='font-family:sans-serif;padding:3rem;text-align:center;'><p style='color:#c0392b;'>Erreur de connexion : " . htmlspecialchars($e->getMessage()) . "</p></div>");
}

if (!isset($_POST['mail']) || !isset($_POST['pwd'])) {
    die("<div style='font-family:sans-serif;padding:3rem;text-align:center;'><p style='color:#c0392b;font-size:1.2rem;margin-bottom:1rem;'>Accès non autorisé.</p><a href='visiteur.php'>← Retour</a></div>");
}

$mail = $_POST['mail'];
$pwd  = $_POST['pwd'];

$stmt = $connection->prepare("SELECT * FROM compte_client WHERE mail=?");
$stmt->execute([$mail]);
$compte = $stmt->fetch();

if (!$compte || $compte['mail'] !== $mail || $compte['password'] !== $pwd) {
    die("<div style='font-family:sans-serif;padding:3rem;text-align:center;'><p style='color:#c0392b;font-size:1.2rem;margin-bottom:1rem;'>Identifiants incorrects.</p><a href='visiteur.php' style='color:#1a3a5c;'>← Retour</a></div>");
}

$stmt2 = $connection->prepare("SELECT * FROM personne WHERE mail=?");
$stmt2->execute([$mail]);
$pers   = $stmt2->fetch();
$nom    = $pers['nom'];
$prenom = $pers['prenom'];
$idpers = $pers['idpers'];
$mail   = $pers['mail'];

// Récupération des demandes
$d_acc = $connection->prepare("SELECT * FROM demand_location WHERE idpers=? AND reponse=1");
$d_acc->execute([$idpers]);
$rows_acc = $d_acc->fetchAll();

$d_ref = $connection->prepare("SELECT * FROM demand_location WHERE idpers=? AND reponse=0");
$d_ref->execute([$idpers]);
$rows_ref = $d_ref->fetchAll();

$d_att = $connection->prepare("SELECT * FROM demand_location WHERE idpers=? AND reponse IS NULL");
$d_att->execute([$idpers]);
$rows_att = $d_att->fetchAll();

$total_demandes = count($rows_acc) + count($rows_ref) + count($rows_att);

// Vue active
$vue = $_GET['vue'] ?? 'biens';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace client — Key Immobilier</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .client-nav {
            background: white;
            border-bottom: 1px solid var(--gris-clair);
            padding: 0 2.5rem;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 8px rgba(15,39,68,.06);
        }
        .client-nav a {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: 1rem 1.5rem;
            font-size: .9rem;
            font-weight: 500;
            color: var(--gris);
            border-bottom: 3px solid transparent;
            text-decoration: none;
            transition: all .2s;
        }
        .client-nav a:hover { color: var(--bleu); }
        .client-nav a.active { color: var(--bleu); border-bottom-color: var(--or); font-weight: 600; }
        .badge-count {
            background: var(--or-clair);
            color: var(--bleu);
            font-size: .7rem;
            font-weight: 700;
            padding: .15rem .5rem;
            border-radius: 999px;
        }
        .badge-count.urgent { background: #fee2e2; color: #dc2626; }

        .client-layout { max-width: 1300px; margin: 0 auto; padding: 2rem 2.5rem; }

        /* Cartes demandes */
        .demandes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.25rem;
            margin-top: 1rem;
        }
        .demande-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            border-left: 4px solid transparent;
            transition: transform .2s, box-shadow .2s;
        }
        .demande-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
        .demande-card.acceptee { border-left-color: var(--vert); }
        .demande-card.refusee  { border-left-color: var(--rouge); }
        .demande-card.attente  { border-left-color: var(--or); }
        .demande-card-header { display: flex; justify-content: space-between; align-items: center; }
        .demande-card-header strong { font-size: 1rem; color: var(--bleu); }
        .demande-card-body { font-size: .9rem; color: var(--gris); line-height: 1.7; }
        .demande-card-body span { color: var(--bleu); font-weight: 600; }
        .demande-card-actions { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: auto; }

        /* Stats pills */
        .stats-bar { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .stat-pill {
            display: flex; align-items: center; gap: .6rem;
            background: white; border-radius: 999px;
            padding: .5rem 1.25rem; box-shadow: var(--shadow);
            font-size: .88rem; font-weight: 500; color: var(--bleu);
        }
        .dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .dot-green  { background: var(--vert); }
        .dot-red    { background: var(--rouge); }
        .dot-yellow { background: var(--or); }

        /* Barre recherche */
        .search-section {
            background: linear-gradient(135deg, var(--bleu) 0%, var(--bleu-med) 100%);
            padding: 1.25rem 2.5rem;
            display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
        }
        .search-section p { color: var(--or-clair); font-size: .9rem; white-space: nowrap; }

        .empty-demandes { text-align: center; padding: 3rem 2rem; color: var(--gris); }
        .empty-demandes .icon { font-size: 3rem; margin-bottom: 1rem; }

        @media(max-width:768px) {
            .client-layout { padding: 1.5rem 1rem; }
            .client-nav { padding: 0 1rem; overflow-x: auto; }
            .search-section { padding: 1rem; }
        }
    </style>
</head>
<body>

<header>
    <img src="images/image/logo3.png" alt="Logo" id="logo_header">
    <h1>Espace <mark>client</mark></h1>
    <div style="display:flex;gap:1rem;align-items:center;">
        <span id="user"><?= htmlspecialchars(strtoupper($prenom) . ' ' . strtoupper($nom)) ?></span>
        <form action="deconnexion.php" method="post" style="margin:0;">
            <input type="hidden" name="deconnect" value="deconnexion">
            <input type="submit" value="Déconnexion" id="deconnexion">
        </form>
    </div>
</header>

<!-- Navigation par onglets -->
<nav class="client-nav">
    <a href="?vue=biens&mail=<?= urlencode($mail) ?>&pwd=<?= urlencode($pwd) ?>"
       class="<?= $vue === 'biens' ? 'active' : '' ?>">
        🏠 Biens disponibles
    </a>
    <a href="?vue=demandes&mail=<?= urlencode($mail) ?>&pwd=<?= urlencode($pwd) ?>"
       class="<?= $vue === 'demandes' ? 'active' : '' ?>">
        📋 Mes demandes
        <?php if ($total_demandes > 0): ?>
            <span class="badge-count <?= count($rows_acc) > 0 ? 'urgent' : '' ?>">
                <?= $total_demandes ?>
            </span>
        <?php endif; ?>
    </a>
</nav>

<?php if ($vue === 'biens'): ?>

<!-- Recherche -->
<form action="" method="post">
    <input type="hidden" name="mail" value="<?= htmlspecialchars($mail) ?>">
    <input type="hidden" name="pwd"  value="<?= htmlspecialchars($pwd) ?>">
    <div class="search-section">
        <p>Rechercher un bien :</p>
        <div class="search-bar" style="max-width:480px;flex:1;">
            <input type="search" name="search" id="i_rech"
                   placeholder="Rechercher par ville…"
                   value="<?= htmlspecialchars($_POST['search'] ?? '') ?>">
            <input type="submit" id="s_rech" value="Rechercher">
        </div>
        <?php if (!empty($_POST['search'])): ?>
            <a href="?vue=biens&mail=<?= urlencode($mail) ?>&pwd=<?= urlencode($pwd) ?>"
               style="color:var(--or-clair);font-size:.85rem;">✕ Effacer</a>
        <?php endif; ?>
    </div>
</form>

<main class="page-content">
    <div class="client-layout">
        <div class="section-header" style="padding:0 0 1rem;">
            <h2>Biens disponibles</h2>
            <p>Cliquez sur un bien pour en voir les détails et faire une demande de location.</p>
        </div>
        <div class="conteneur" style="padding:0;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));">
            <?php
            if (!empty($_POST['search'])) {
                $req_biens = $connection->prepare("SELECT * FROM bien WHERE ville=?");
                $req_biens->execute([$_POST['search']]);
            } else {
                $req_biens = $connection->query("SELECT * FROM bien");
            }
            $found = false;
            while ($row = $req_biens->fetch()) {
                if ($row['idLoc']) continue;
                $found = true;
                $src   = explode(',', $row['image']);
                $src   = htmlspecialchars($src[1] ?? '');
                $type  = htmlspecialchars($row['type']);
                $dim   = htmlspecialchars($row['dim']);
                $det   = htmlspecialchars(mb_substr($row['details'], 0, 70)) . '…';
                $prix  = number_format($row['prix'], 0, ',', ' ');
                $ville = htmlspecialchars($row['ville']);
                $id    = (int)$row['id_bien'];
                $nom_b = htmlspecialchars($row['nom_bien']);
                echo "
                <a href='detail.php?code={$id}&idpers={$idpers}'>
                  <article class='objet'>
                    <img src='{$src}' alt='Photo' class='objet-image' onerror=\"this.src='images/image/villa2.jpg'\">
                    <div style='padding:.85rem;'>
                      <span class='badge-type'>{$type}</span>
                      <h3 style='font-size:.95rem;margin:.35rem 0;'>{$nom_b}</h3>
                      <p style='background:none;height:auto;padding:0;'>
                        <span style='color:var(--or);font-size:1.05rem;font-weight:700;'>{$prix} €/mois</span><br>
                        <span style='color:var(--gris);font-size:.82rem;'>📐 {$dim} · {$ville}</span>
                        <span style='color:var(--gris);font-size:.8rem;display:block;margin-top:.2rem;'>{$det}</span>
                      </p>
                    </div>
                  </article>
                </a>";
            }
            if (!$found) {
                echo "<div style='grid-column:1/-1;text-align:center;padding:3rem;color:var(--gris);'>
                    <p style='font-size:2rem;margin-bottom:.5rem;'>🏠</p>
                    <p>Aucun bien disponible" . (!empty($_POST['search']) ? " pour « " . htmlspecialchars($_POST['search']) . " »" : "") . ".</p>
                </div>";
            }
            ?>
        </div>
    </div>
</main>

<?php else: ?>

<!-- MES DEMANDES -->
<main class="page-content">
    <div class="client-layout">
        <div class="section-header" style="padding:0 0 1.5rem;">
            <h2>Mes demandes de location</h2>
            <p>Suivez l'état de toutes vos demandes en temps réel.</p>
        </div>

        <!-- Stats -->
        <div class="stats-bar">
            <div class="stat-pill"><span class="dot dot-green"></span><?= count($rows_acc) ?> acceptée<?= count($rows_acc) > 1 ? 's' : '' ?></div>
            <div class="stat-pill"><span class="dot dot-yellow"></span><?= count($rows_att) ?> en attente</div>
            <div class="stat-pill"><span class="dot dot-red"></span><?= count($rows_ref) ?> refusée<?= count($rows_ref) > 1 ? 's' : '' ?></div>
        </div>

        <?php if ($total_demandes === 0): ?>
            <div class="empty-demandes">
                <div class="icon">📭</div>
                <p>Vous n'avez aucune demande de location pour le moment.</p>
                <a href="?vue=biens&mail=<?= urlencode($mail) ?>&pwd=<?= urlencode($pwd) ?>"
                   class="btn btn-gold" style="margin-top:1.25rem;display:inline-flex;">
                    Parcourir les biens disponibles
                </a>
            </div>
        <?php else: ?>
            <div class="demandes-grid">

                <?php foreach ($rows_acc as $row): ?>
                <div class="demande-card acceptee">
                    <div class="demande-card-header">
                        <strong>Demande #<?= $row['No_demand'] ?></strong>
                        <span class="badge badge-success">✓ Acceptée</span>
                    </div>
                    <div class="demande-card-body">
                        Type : <span><?= htmlspecialchars($row['label_bien']) ?></span><br>
                        <small style="color:var(--vert);margin-top:.4rem;display:block;">
                            🎉 Acceptée ! Confirmez ou annulez ci-dessous.
                        </small>
                    </div>
                    <div class="demande-card-actions">
                        <form action="reponse_client.php" method="post">
                            <input type="hidden" name="accepter" value="<?= $row['No_demand'] ?>">
                            <button type="submit" class="btn btn-success btn-sm">✓ Confirmer</button>
                        </form>
                        <form action="reponse_client.php" method="post">
                            <input type="hidden" name="annuler" value="<?= $row['No_demand'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">✕ Annuler</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php foreach ($rows_att as $row): ?>
                <div class="demande-card attente">
                    <div class="demande-card-header">
                        <strong>Demande #<?= $row['No_demand'] ?></strong>
                        <span class="badge badge-warning">⏳ En attente</span>
                    </div>
                    <div class="demande-card-body">
                        Type : <span><?= htmlspecialchars($row['label_bien']) ?></span><br>
                        <small style="color:var(--gris);margin-top:.4rem;display:block;">
                            Votre demande est en cours de traitement.
                        </small>
                    </div>
                    <div class="demande-card-actions">
                        <form action="reponse_client.php" method="post">
                            <input type="hidden" name="annuler" value="<?= $row['No_demand'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">✕ Annuler</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php foreach ($rows_ref as $row): ?>
                <div class="demande-card refusee">
                    <div class="demande-card-header">
                        <strong>Demande #<?= $row['No_demand'] ?></strong>
                        <span class="badge badge-danger">✕ Refusée</span>
                    </div>
                    <div class="demande-card-body">
                        Type : <span><?= htmlspecialchars($row['label_bien']) ?></span><br>
                        <small style="color:var(--rouge);margin-top:.4rem;display:block;">
                            Cette demande a été refusée.
                        </small>
                    </div>
                    <div class="demande-card-actions">
                        <form action="reponse_client.php" method="post">
                            <input type="hidden" name="annuler" value="<?= $row['No_demand'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">🗑 Supprimer</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
        <?php endif; ?>
    </div>
</main>

<?php endif; ?>

<?php include_once("portions de page/footer.html"); $connection = null; ?>
</body>
</html>

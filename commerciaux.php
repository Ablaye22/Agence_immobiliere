<?php
session_start();
$connect = new PDO("mysql:host=localhost;dbname=agence_immobiliere", "immobilier", "motdepasse");
$erreur  = "";

if (isset($_POST['mail']) && isset($_POST['pwd'])) {
    $mail = $_POST['mail'];
    $pwd  = $_POST['pwd'];
}
if (isset($_GET['code']) && isset($_SESSION['connect_mail'.$_GET['code']]) && isset($_SESSION['connect_pwd'.$_GET['code']])) {
    $mail = $_SESSION['connect_mail'.$_GET['code']];
    $pwd  = $_SESSION['connect_pwd'.$_GET['code']];
}

if ($connect) {
    echo ("bonjout");
    $req  = "SELECT * FROM compte_commerciaux WHERE mail=?";
    $stmt = $connect->prepare($req);
    $stmt->execute([$mail ?? '']);
    $row  = $stmt->fetch();
    if (!isset($mail) || $mail !== $row['mail'] || $pwd !== $row['password']) {
        die("<div style='font-family:sans-serif;padding:3rem;text-align:center;'><p style='color:#c0392b;font-size:1.2rem;margin-bottom:1rem;'>Identifiants incorrects.</p><a href='authentification_commerciaux.php'>← Retour</a></div>");
    }
}
if ($connect) {
    $stmt = $connect->prepare("SELECT * FROM personne WHERE mail=?");
    $stmt->execute([$mail]);
    $stmt   = $stmt->fetch();
    $nom    = $stmt['nom'];
    $prenom = $stmt['prenom'];
    $tel    = $stmt['tel'];
    $idpers = $stmt['idpers'];
    $_SESSION['connect'.$idpers]       = $stmt['idpers'];
    $_SESSION['connect_pwd'.$idpers]   = $pwd;
    $_SESSION['connect_mail'.$idpers]  = $mail;
}
$code = $_SESSION['connect'.$idpers];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace commerciaux — Key Immobilier</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <?php include_once("portions de page/entete.html"); ?>
</header>

<header style="background:var(--bleu);padding:1.2rem 2.5rem;display:flex;align-items:center;justify-content:space-between;">
    <div>
        <p style="color:var(--or-clair);font-size:.85rem;margin-bottom:.2rem;">Connecté en tant que commercial</p>
        <p style="color:white;font-weight:600;font-size:1rem;"><?= htmlspecialchars($prenom . ' ' . $nom) ?> &nbsp;·&nbsp; <span style="color:var(--or-clair);font-weight:400;font-size:.9rem;"><?= htmlspecialchars($mail) ?></span></p>
    </div>
    <form method="post" action="deconnexion.php">
        <input type="hidden" name="deconnec_comm" value="true">
        <input type="hidden" name="idpers" value="<?= $idpers ?>">
        <input type="submit" value="Déconnexion" id="deconnexion">
    </form>
</header>

<main class="page-content" style="padding:2rem 2.5rem;">
    <div class="section-header" style="padding:0 0 1.5rem;">
        <h2>Tableau de bord</h2>
        <p>Gérez les locations, clients et biens immobiliers.</p>
    </div>

    <ul class="dashboard-menu">
        <li>
            <a href="gerer_appart_and_villa.php?code=<?= $code ?>">
                <div class="menu-icon">🏠</div>
                <div>
                    <strong>Appartements & Villas</strong>
                    <p style="font-size:.82rem;color:var(--gris);margin-top:.2rem;">Gérer les demandes de location</p>
                </div>
            </a>
        </li>
        <li>
            <a href="compte_client.php?code=<?= $code ?>">
                <div class="menu-icon">👤</div>
                <div>
                    <strong>Comptes clients</strong>
                    <p style="font-size:.82rem;color:var(--gris);margin-top:.2rem;">Voir et modifier les comptes</p>
                </div>
            </a>
        </li>
        <li>
            <a href="historique_location.php?code=<?= $code ?>">
                <div class="menu-icon">📋</div>
                <div>
                    <strong>Historique des locations</strong>
                    <p style="font-size:.82rem;color:var(--gris);margin-top:.2rem;">Locations en cours et passées</p>
                </div>
            </a>
        </li>
        <li>
            <a href="ajouter.php?code=<?= $code ?>">
                <div class="menu-icon">➕</div>
                <div>
                    <strong>Ajouter un bien</strong>
                    <p style="font-size:.82rem;color:var(--gris);margin-top:.2rem;">Enregistrer un appartement ou villa</p>
                </div>
            </a>
        </li>
        <li>
            <a href="ajouter_commerciaux.php?code=<?= $code ?>">
                <div class="menu-icon">🏢</div>
                <div>
                    <strong>Ajouter une agence</strong>
                    <p style="font-size:.82rem;color:var(--gris);margin-top:.2rem;">Créer un nouveau commercial</p>
                </div>
            </a>
        </li>
    </ul>
</main>

<?php include_once("portions de page/footer.html"); ?>
</body>
</html>

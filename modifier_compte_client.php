<?php
session_start();
$message = '';
$type_msg = '';

try {
    $connect = new PDO("mysql:host=localhost;dbname=agence_immobiliere;charset=utf8", "immobilier", "motdepasse", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Erreur BDD : " . htmlspecialchars($e->getMessage()));
}

// Chargement initial des données
$code = $_POST['code'] ?? $_GET['code'] ?? null;
$nom = $prenom = $cin = $tel = $naissance = $mail = $password = '';

if ($code) {
    $stmt = $connect->prepare("SELECT * FROM personne WHERE idpers=?");
    $stmt->execute([$code]);
    $pers = $stmt->fetch();
    if ($pers) {
        $nom       = $pers['nom'];
        $prenom    = $pers['prenom'];
        $cin       = $pers['cin'];
        $tel       = $pers['tel'];
        $naissance = $pers['dateNaissance'];
        $mail      = $pers['mail'];
        $stmt2 = $connect->prepare("SELECT password FROM compte_client WHERE mail=?");
        $stmt2->execute([$mail]);
        $cc = $stmt2->fetch();
        $password = $cc['password'] ?? '';
    }
}

// Mise à jour
if (isset($_POST['valider']) && $code) {
    $nom       = $_POST['nom']      ?? $nom;
    $prenom    = $_POST['prenom']   ?? $prenom;
    $tel       = $_POST['tel']      ?? $tel;
    $cin       = $_POST['cin']      ?? $cin;
    $mail_new  = $_POST['mail']     ?? $mail;
    $password  = $_POST['password'] ?? $password;
    $naissance = $_POST['naissance'] ?? $naissance;

    $connect->prepare("UPDATE personne SET nom=?,prenom=?,cin=?,tel=?,dateNaissance=?,mail=? WHERE idpers=?")
        ->execute([$nom, $prenom, $cin, $tel, $naissance, $mail_new, $code]);
    $connect->prepare("UPDATE compte_client SET password=? WHERE mail=?")
        ->execute([$password, $mail]);
    $mail = $mail_new;
    $message  = "Les informations du client ont été mises à jour.";
    $type_msg = 'success';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un compte client — Key Immobilier</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-page { max-width: 700px; margin: 2rem auto; padding: 0 2rem 3rem; }
        .form-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2rem 2.5rem;
        }
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-grid-2 .full { grid-column: 1 / -1; }
        .alert-success { background:#dcfce7;color:#16a34a;border-radius:var(--radius-sm);padding:.85rem 1.25rem;margin-bottom:1.25rem;font-size:.9rem; }
        .alert-error   { background:#fee2e2;color:#dc2626;border-radius:var(--radius-sm);padding:.85rem 1.25rem;margin-bottom:1.25rem;font-size:.9rem; }
        @media(max-width:600px) { .form-grid-2 { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php include_once("portions de page/entete.html"); ?>

<a href="compte_client.php" class="back-link">← Retour aux comptes clients</a>

<div class="form-page">
    <div class="section-header" style="padding:0 0 1.5rem;">
        <h2>Modifier un compte client</h2>
        <p>Mettez à jour les informations personnelles et d'accès du client.</p>
    </div>

    <?php if ($message): ?>
        <div class="alert-<?= $type_msg ?>"><?= $type_msg === 'success' ? '✅' : '❌' ?> <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="post" action="">
            <input type="hidden" name="code" value="<?= htmlspecialchars($code) ?>">
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" value="<?= htmlspecialchars($prenom) ?>" required>
                </div>
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($nom) ?>" required>
                </div>
                <div class="form-group">
                    <label>CIN</label>
                    <input type="text" name="cin" value="<?= htmlspecialchars($cin) ?>" required>
                </div>
                <div class="form-group">
                    <label>Date de naissance</label>
                    <input type="date" name="naissance" value="<?= htmlspecialchars($naissance) ?>" required>
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="tel" value="<?= htmlspecialchars($tel) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="mail" value="<?= htmlspecialchars($mail) ?>" required>
                </div>
                <div class="form-group full">
                    <label>Mot de passe</label>
                    <input type="password" name="password" value="<?= htmlspecialchars($password) ?>">
                </div>
            </div>
            <button type="submit" name="valider" class="btn btn-gold" style="width:100%;padding:.9rem;margin-top:.5rem;font-size:1rem;">
                Enregistrer les modifications
            </button>
        </form>
    </div>
</div>

<?php include_once("portions de page/footer.html"); ?>
</body>
</html>

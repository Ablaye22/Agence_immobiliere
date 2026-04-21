<?php
$afficher_login    = isset($_GET['afficher']);
$afficher_register = isset($_GET['afficher2']);

// Connexion PDO avec gestion d'erreurs
$connection = null;
$db_error   = null;
try {
    // Modifiez "root" et "" selon votre config MySQL Ubuntu
    // Sous Ubuntu, MySQL peut nécessiter : 'unix_socket=/var/run/mysqld/mysqld.sock'
    $connection = new PDO(
        "mysql:host=localhost;dbname=agence_immobiliere;charset=utf8",
        "immobilier",
        "motdepasse", // <-- Remplacez par votre mot de passe MySQL si nécessaire
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Key Immobilier — Trouvez votre bien</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <img src="images/image/logo3.png" alt="Logo" id="logo_header">
    <h1>Welcome to <mark>Key Immobilier</mark></h1>
    <div style="display:flex;gap:.75rem;align-items:center;">
        <button class="btn btn-outline" id="btn-login" style="border-color:rgba(255,255,255,.4);color:white;">Se connecter</button>
        <button class="btn btn-gold" id="btn-register">S'inscrire</button>
    </div>
</header>

<section class="hero">
    <h2>Trouvez le bien de vos rêves</h2>
    <p>Appartements et villas disponibles à la location partout dans le monde.</p>
    <form action="" method="post" style="display:flex;justify-content:center;max-width:560px;margin:0 auto;position:relative;">
        <div class="search-bar" style="width:100%;">
            <input type="search" name="search" placeholder="Rechercher par ville, type de bien…">
            <input type="submit" value="Rechercher" class="btn btn-gold">
        </div>
    </form>
</section>

<main class="page-content">
    <div class="section-header">
        <h2>Biens disponibles</h2>
        <p>Sélectionnez un bien pour en savoir plus</p>
    </div>
    <div class="conteneur">
        <?php if ($db_error): ?>
            <div style="color:red;padding:1rem;background:#fff3f3;border-radius:8px;margin:1rem;">
                <strong>Erreur de connexion à la base de données :</strong><br>
                <?= htmlspecialchars($db_error) ?><br><br>
                <em>Conseil Ubuntu : vérifiez que MySQL est démarré (<code>sudo systemctl start mysql</code>)
                et que l'utilisateur root a accès (<code>sudo mysql -u root</code>).</em>
            </div>
        <?php elseif ($connection): ?>
            <?php
            $req    = "SELECT * FROM bien";
            $result = $connection->query($req);
            $found  = false;
            while ($row = $result->fetch()) {
                if ($row['idLoc']) continue;
                $found = true;
                // Correction : l'image peut être une URL directe ou base64
                $src = $row['image'];
                if (strpos($src, ',') !== false) {
                    $parts = explode(',', $src);
                    $src   = $parts[1] ?? '';
                }
                $src  = htmlspecialchars($src);
                $type = htmlspecialchars($row['type']);
                $dim  = htmlspecialchars($row['dim']);
                $det  = htmlspecialchars(mb_substr($row['details'], 0, 80)) . '…';
                $prix = number_format($row['prix'], 0, ',', ' ');
                $nom  = htmlspecialchars($row['nom_bien']);
                $ville = htmlspecialchars($row['ville']);
                $id   = (int)$row['id_bien'];
                echo "
                <a href='detail.php?code={$id}'>
                  <article class='objet'>
                    <img src='{$src}' alt='Photo' class='objet-image' onerror=\"this.src='images/image/villa2.jpg'\">
                    <div style='padding:1rem;'>
                      <span class='badge-type'>{$type}</span>
                      <h3 style='margin-bottom:.35rem;font-size:1rem;'>{$nom}</h3>
                      <p style='background:none;height:auto;padding:0;'>
                        <span style='color:var(--or);font-size:1.1rem;font-weight:700;'>{$prix} €<span style='font-size:.8rem;font-weight:400;color:var(--gris);'>/mois</span></span><br>
                        <span style='color:var(--gris);font-size:.85rem;'>📐 {$dim} &nbsp;·&nbsp; {$ville}</span>
                        <span style='color:var(--gris);font-size:.82rem;margin-top:.25rem;display:block;'>{$det}</span>
                      </p>
                    </div>
                  </article>
                </a>";
            }
            if (!$found) {
                echo "<p style='padding:1rem;'>Aucun bien disponible pour le moment.</p>";
            }
            ?>
        <?php endif; ?>
    </div>
</main>

<!-- MODAL CONNEXION -->
<div class="modal-overlay <?= $afficher_login ? 'visible' : '' ?>" id="modal-login">
    <div class="modal-box">
        <button class="modal-close" id="close-login">✕</button>
        <h2>Se connecter</h2>
        <form action="client.php" method="post">
            <div class="form-group"><label>E-mail</label><input type="email" name="mail" placeholder="votre@email.com" required></div>
            <div class="form-group"><label>Mot de passe</label><input type="password" name="pwd" placeholder="••••••••" required></div>
            <input type="submit" value="S'authentifier" style="width:100%;padding:.75rem;" class="btn">
            <span class="form-link">Pas de compte ? <a href="#" id="switch-to-register">S'inscrire</a></span>
        </form>
    </div>
</div>

<!-- MODAL INSCRIPTION -->
<div class="modal-overlay <?= $afficher_register ? 'visible' : '' ?>" id="modal-register">
    <div class="modal-box" style="max-width:520px;">
        <button class="modal-close" id="close-register">✕</button>
        <h2>Créer un compte</h2>
        <form action="traiter.php" method="post">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group"><label>Prénom</label><input type="text" name="prenom" placeholder="Jean" required></div>
                <div class="form-group"><label>Nom</label><input type="text" name="nom" placeholder="Dupont" required></div>
            </div>
            <div class="form-group"><label>CIN</label><input type="text" name="cin" placeholder="Numéro d'identification" required></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group"><label>Téléphone</label><input type="number" name="tel" required></div>
                <div class="form-group"><label>Date de naissance</label><input type="date" name="naissance" required></div>
            </div>
            <div class="form-group"><label>E-mail</label><input type="email" name="mail" placeholder="votre@email.com" required></div>
            <div class="form-group"><label>Mot de passe</label><input type="password" name="pwd" placeholder="••••••••" required></div>
            <input type="submit" value="Créer mon compte" class="btn btn-gold" style="width:100%;padding:.75rem;">
            <span class="form-link">Déjà inscrit ? <a href="#" id="switch-to-login">Se connecter</a></span>
        </form>
    </div>
</div>

<?php include_once("portions de page/footer.html"); ?>

<script>
const loginModal    = document.getElementById('modal-login');
const registerModal = document.getElementById('modal-register');
document.getElementById('btn-login').onclick           = () => loginModal.classList.add('visible');
document.getElementById('btn-register').onclick        = () => registerModal.classList.add('visible');
document.getElementById('close-login').onclick         = () => loginModal.classList.remove('visible');
document.getElementById('close-register').onclick      = () => registerModal.classList.remove('visible');
document.getElementById('switch-to-register').onclick  = e => { e.preventDefault(); loginModal.classList.remove('visible'); registerModal.classList.add('visible'); };
document.getElementById('switch-to-login').onclick     = e => { e.preventDefault(); registerModal.classList.remove('visible'); loginModal.classList.add('visible'); };
[loginModal, registerModal].forEach(m => m.addEventListener('click', e => { if (e.target === m) m.classList.remove('visible'); }));
</script>
</body>
</html>


CREATE USER 'immobilier'@'localhost' IDENTIFIED BY 'motdepasse';
GRANT ALL PRIVILEGES ON *.* TO 'immobilier'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EXIT;
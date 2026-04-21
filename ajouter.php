<?php
session_start();
if (((isset($_GET['code'])) && isset($_SESSION['connect'.$_GET['code']]) && $_SESSION['connect'.$_GET['code']]==$_GET['code']) || isset($_POST['code'])) {
    $code = isset($_GET['code']) ? $_GET['code'] : $_POST['code'];
} else {
    die("<div style='font-family:sans-serif;padding:3rem;text-align:center;'><p style='color:#c0392b;font-size:1.2rem;margin-bottom:1rem;'>Veuillez vous identifier.</p><a href='authentification_commerciaux.php'>← Retour</a></div>");
}

$connect = null;
$success = null;
$db_error = null;

try {
    $connect = new PDO("mysql:host=localhost;dbname=agence_immobiliere;charset=utf8", "immobilier", "motdepasse", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    $db_error = $e->getMessage();
}

// Traitement du formulaire
if ($connect && isset($_POST['type_bien'])) {
    $prenom    = $_POST['prenom']   ?? '';
    $nom       = $_POST['nom']      ?? '';
    $naissance = $_POST['birthday'] ?? '';
    $tel       = $_POST['tel']      ?? '';
    $mail      = $_POST['mail']     ?? '';
    $cin       = $_POST['cin']      ?? '';
    $bien      = $_POST['type_bien'] ?? '';
    $adresse   = $_POST['adresse']  ?? '';
    $dim       = $_POST['dim']      ?? '';
    $ville     = $_POST['ville']    ?? '';
    $prix      = $_POST['prix']     ?? 0;
    $nom_bien  = $_POST['nom_bien'] ?? '';
    $details   = $_POST['details']  ?? '';

    // Insérer propriétaire si champs renseignés
    if ($nom && $prenom && $cin && $tel && $naissance && $mail) {
        $stmt = $connect->prepare("INSERT IGNORE INTO personne(nom,prenom,cin,tel,dateNaissance,mail) VALUES(:nom,:prenom,:cin,:tel,:naissance,:mail)");
        $stmt->execute([':nom'=>$nom,':prenom'=>$prenom,':cin'=>$cin,':tel'=>$tel,':naissance'=>$naissance,':mail'=>$mail]);
    }

    // Upload images
    $tab_img = '';
    if (!empty($_FILES['photo']['name'][0])) {
        $indice = 0;
        if (file_exists('fichier_bien.txt')) {
            $indice = (int)file_get_contents('fichier_bien.txt');
        }
        $extend_ok = ['jpg','png','jpeg'];
        foreach ($_FILES['photo']['name'] as $index => $value) {
            $extend = strtolower(substr(strrchr($value, '.'), 1));
            if (in_array($extend, $extend_ok)) {
                $dest = "images/Appartement/image{$indice}{$index}.{$extend}";
                move_uploaded_file($_FILES['photo']['tmp_name'][$index], $dest);
                $tab_img .= ',' . $dest;
            }
        }
        $tab_img = ltrim($tab_img, ',');
        file_put_contents('fichier_bien.txt', $indice + 1);
    }

    // Insérer le bien
    if ($bien && $adresse && $dim && $nom_bien && $tab_img) {
        $row = $connect->prepare("SELECT idpers FROM personne WHERE mail=?")->execute([$mail]);
        $stmt2 = $connect->prepare("SELECT idpers FROM personne WHERE mail=?");
        $stmt2->execute([$mail]);
        $prop = $stmt2->fetch();
        $idpers = $prop['idpers'] ?? null;

        if ($idpers) {
            $connect->prepare("INSERT IGNORE INTO proprietaire(idpers) VALUES(?)")->execute([$idpers]);
            $stmt3 = $connect->prepare("SELECT idprop FROM proprietaire WHERE idpers=?");
            $stmt3->execute([$idpers]);
            $idprop = $stmt3->fetch()['idprop'];

            $connect->prepare("INSERT INTO bien(id_prop,nom_bien,dim,prix,adresse,ville,details,image,type) VALUES(?,?,?,?,?,?,?,?,?)")
                ->execute([$idprop,$nom_bien,$dim,$prix,$adresse,$ville,$details,$tab_img,$bien]);
            $success = "Le bien <strong>" . htmlspecialchars($nom_bien) . "</strong> a été ajouté avec succès.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un bien — Key Immobilier</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-page { max-width: 860px; margin: 2rem auto; padding: 0 2rem 3rem; }
        .form-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2rem 2.5rem;
            margin-bottom: 1.5rem;
        }
        .form-card h2 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            padding-bottom: .75rem;
            border-bottom: 2px solid var(--or-clair);
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-grid-2 .full { grid-column: 1 / -1; }
        .radio-group { display: flex; gap: 1rem; flex-wrap: wrap; margin-top: .5rem; }
        .radio-group label {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem 1.25rem;
            border: 1.5px solid var(--gris-clair);
            border-radius: 999px;
            cursor: pointer;
            font-size: .9rem;
            font-weight: 500;
            transition: all .2s;
            color: var(--bleu);
        }
        .radio-group input[type="radio"] { display: none; }
        .radio-group input[type="radio"]:checked + label {
            border-color: var(--or);
            background: var(--or-clair);
            color: var(--bleu);
        }
        .file-upload {
            border: 2px dashed var(--gris-clair);
            border-radius: var(--radius-sm);
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s;
            color: var(--gris);
            font-size: .9rem;
        }
        .file-upload:hover { border-color: var(--or); }
        .alert-success {
            background: #dcfce7;
            color: #16a34a;
            border-radius: var(--radius-sm);
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            font-size: .95rem;
        }
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border-radius: var(--radius-sm);
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            font-size: .95rem;
        }
        @media(max-width:600px) { .form-grid-2 { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php include_once("portions de page/entete.html"); ?>

<a href="commerciaux.php?code=<?= $code ?>" class="back-link">← Retour au tableau de bord</a>

<div class="form-page">
    <div class="section-header" style="padding:0 0 1.5rem;">
        <h2>Ajouter un bien immobilier</h2>
        <p>Renseignez les informations du propriétaire et du bien à mettre en location.</p>
    </div>

    <?php if ($success): ?>
        <div class="alert-success">✅ <?= $success ?> <a href="ajouter.php?code=<?= $code ?>">Ajouter un autre bien</a></div>
    <?php endif; ?>
    <?php if ($db_error): ?>
        <div class="alert-error">❌ Erreur base de données : <?= htmlspecialchars($db_error) ?></div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="code" value="<?= htmlspecialchars($code) ?>">

        <!-- Propriétaire -->
        <div class="form-card">
            <h2>🏠 Informations sur le propriétaire</h2>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" placeholder="Jean" required>
                </div>
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" placeholder="Dupont" required>
                </div>
                <div class="form-group">
                    <label>Date de naissance</label>
                    <input type="date" name="birthday" required>
                </div>
                <div class="form-group">
                    <label>CIN</label>
                    <input type="number" name="cin" placeholder="Numéro d'identification" required>
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="number" name="tel" placeholder="0612345678" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="mail" placeholder="proprietaire@email.com" required>
                </div>
            </div>
        </div>

        <!-- Bien -->
        <div class="form-card">
            <h2>🔑 Informations sur le bien</h2>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Type du bien</label>
                    <select name="type_bien" class="form-group">
                        <option value="Appartement">Appartement</option>
                        <option value="Villa">Villa</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ville</label>
                    <input type="text" name="ville" placeholder="Paris" required>
                </div>
                <div class="form-group full">
                    <label>Nom du bien</label>
                    <input type="text" name="nom_bien" placeholder="Résidence Les Pins, Villa Azur…" required>
                </div>
                <div class="form-group">
                    <label>Prix / mois (€)</label>
                    <input type="number" name="prix" placeholder="1200" required>
                </div>
                <div class="form-group">
                    <label>Adresse</label>
                    <input type="text" name="adresse" placeholder="12 rue des Fleurs" required>
                </div>
                <div class="form-group full">
                    <label>Dimension</label>
                    <div class="radio-group">
                        <input type="radio" name="dim" value="F1" id="f1"><label for="f1">F1</label>
                        <input type="radio" name="dim" value="F2" id="f2"><label for="f2">F2</label>
                        <input type="radio" name="dim" value="F3" id="f3"><label for="f3">F3</label>
                        <input type="radio" name="dim" value="F4" id="f4"><label for="f4">F4</label>
                    </div>
                </div>
                <div class="form-group full">
                    <label>Photos du bien</label>
                    <div class="file-upload">
                        <input type="file" name="photo[]" multiple accept="image/*" style="width:100%;cursor:pointer;">
                        <p style="margin-top:.5rem;font-size:.82rem;">JPG, PNG, JPEG acceptés — plusieurs fichiers possibles</p>
                    </div>
                </div>
                <div class="form-group full">
                    <label>Description / Commentaire</label>
                    <textarea name="details" rows="5" placeholder="Décrivez le bien : équipements, état, points forts…" style="width:100%;padding:.7rem 1rem;border:1.5px solid var(--gris-clair);border-radius:var(--radius-sm);font-family:inherit;font-size:.95rem;resize:vertical;"></textarea>
                </div>
            </div>
        </div>

        <input type="submit" value="Enregistrer le bien" class="btn btn-gold" style="width:100%;padding:1rem;font-size:1rem;">
    </form>
</div>

<?php include_once("portions de page/footer.html"); ?>
</body>
</html>

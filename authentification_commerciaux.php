<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Key Immobilier — Espace Commerciaux</title>
    <!-- Même feuille de style que visiteur.php -->
    <link rel="stylesheet" href="style.css">
    <style>
        /* Styles spécifiques à la page d'authentification commerciaux */
        .auth-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 200px);
            padding: 2rem;
        }

        .auth-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0,0,0,.12);
            padding: 2.5rem;
            width: 100%;
            max-width: 460px;
        }

        .auth-card h1 {
            font-size: 1.6rem;
            margin-bottom: .25rem;
            color: var(--noir, #1a1a2e);
        }

        .auth-card .subtitle {
            color: var(--gris, #666);
            font-size: .9rem;
            margin-bottom: 2rem;
        }

        .auth-card .badge-role {
            display: inline-block;
            background: var(--or, #c8a951);
            color: white;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
            padding: .25rem .75rem;
            border-radius: 999px;
            margin-bottom: 1.25rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: .85rem;
            font-weight: 600;
            color: var(--noir, #1a1a2e);
            margin-bottom: .4rem;
        }

        .form-group input {
            width: 100%;
            padding: .7rem 1rem;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: .95rem;
            transition: border-color .2s;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--or, #c8a951);
        }

        .auth-card .btn {
            width: 100%;
            padding: .8rem;
            margin-top: .5rem;
            font-size: 1rem;
        }

        #erreur {
            color: #d9534f;
            font-size: .875rem;
            background: #fff3f3;
            border: 1px solid #f5c6c6;
            border-radius: 8px;
            padding: .6rem 1rem;
            margin-bottom: 1rem;
            display: none;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.25rem;
            font-size: .875rem;
            color: var(--gris, #666);
        }

        .back-link a {
            color: var(--or, #c8a951);
            text-decoration: none;
            font-weight: 600;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        /* Optionnel : bandeau décoratif côté droit sur grand écran */
        @media (min-width: 900px) {
            .auth-wrapper {
                gap: 4rem;
            }

            .auth-aside {
                display: flex;
                flex-direction: column;
                gap: 1rem;
                max-width: 320px;
            }

            .auth-aside-card {
                background: rgb(138, 228, 228);
                border-radius: 12px;
                padding: 1.25rem 1.5rem;
                font-size: .95rem;
                color: var(--noir, #1a1a2e);
            }

            .auth-aside-card strong {
                display: block;
                margin-bottom: .4rem;
                color: var(--or, #c8a951);
            }
        }

        @media (max-width: 899px) {
            .auth-aside { display: none; }
        }
    </style>
</head>
<body>

<?php include_once("portions de page/entete.html"); ?>

<div class="auth-wrapper">

    <!-- Formulaire principal -->
    <div class="auth-card">
        <span class="badge-role">🔑 Espace Commerciaux</span>
        <h1>Veuillez vous identifier</h1>
        <p class="subtitle">Accès réservé aux commerciaux de Key Immobilier.</p>

        <div id="erreur"></div>

        <form action="commerciaux.php" method="post" id="form-auth">
            <div class="form-group">
                <label for="mail">Adresse e-mail</label>
                <input type="email" name="mail" id="mail" placeholder="votre@email.com" required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="pwd">Mot de passe</label>
                <input type="password" name="pwd" id="pwd" placeholder="••••••••" required autocomplete="current-password">
            </div>
            <input type="submit" value="Se connecter" class="btn btn-gold">
        </form>

        <span class="back-link">
            <a href="visiteur.php">← Retour à l'accueil</a>
        </span>
    </div>

    <!-- Panneau informatif (visible uniquement sur grand écran) -->
    <aside class="auth-aside">
        <div class="auth-aside-card">
            <strong>📋 Gestion des biens</strong>
            Ajoutez, modifiez et archivez les biens disponibles à la location.
        </div>
        <div class="auth-aside-card">
            <strong>👥 Suivi des clients</strong>
            Consultez les demandes en cours et gérez les dossiers locataires.
        </div>
        <div class="auth-aside-card">
            <strong>📊 Tableau de bord</strong>
            Accédez aux statistiques et rapports de performance en temps réel.
        </div>
    </aside>

</div>

<?php include_once("portions de page/footer.html"); ?>

</body>
</html>

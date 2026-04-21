  <?php
   session_start();
   if((isset($code=$_GET['code']) or isset($code=$_POST['code'])) and isset($_SESSION['connect'.$code['code']]) and $_SESSION['connect'.$code]==$code['code'])
   {
    echo ("bonjour");
   }
   else
   {
   die("veuillez vous identifier dans la page de connection!!!<br/><a href='authentification_commerciaux.php'>aller à la page d'identification<a/>");
   exit; 
}
  ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="portions de page/portion.css">
    <style>
       
        #add_bien
        {
            width:900px;
           
        }
        .select_input
        {
            width:600px;
            height:40px;
            margin:40px;

        }
        select 
            {
            width:600px;
            height:40px;
            margin:40px;
            border-radius:15px;

        }
        legend 
        {
            font-size:40px;
            color:blue;
        }
        fieldset
        {
            border-radius:20px;
            background-color:aqua;
        }
        label 
        {
            font-size:40px;
        }
        .dim
        {
            width:90px;
            height:30px;
        }
        input
        {
            border-radius:15px;
            margin-left:70px;
        } 
       a 
       {
        font-size:40px;
        font-style:italic;
       }
       .conteneur
       {
        display:flex;
       }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une agence</title>
</head>
<body>
        <?php
            require_once("portions de page/entete.html");
        ?>
        <a href="commerciaux.php?code=<?php echo $_GET['code'] ?>">Retour</a>
        <div class="conteneur">
                <?php
                  include_once("portions de page/aside.html");
                ?>
            <div>
                <form action="" method="post" id="add_bien" enctype="multipart/form-data">
                            <legend>Informations sur l'agence</legend>
                            <label for="prenom">Prenom:</label><br><input type="text" value="tapez le prenom du propriétaire" onclick="this.value=''" class="select_input" name="prenom"><br>
                            <label for="nom">Nom:</label><br><input type="text" class="select_input" name="nom"><br>
                            <label for="birthdate">Date de naissance:</label><br><input type="date" class="select_input" name="birthday" ><br>
                            <label for="cin">CIN:</label><br><input type="number" name="cin" class="select_input"><br>
                            <label for="tel">Telephone:  </label><br><input type="number" class="select_input" name="tel"><br>
                            <label for="mail">Email</label><br><input type="email" name="mail" onclick='this.value=""' class="select_input" value="tapez ici votre adresse email"><br>
                            <label for="password">Mot de passe:</label> <br><input type="password" name="password" class="select_input" >
                            <input type="hidden" name="code" value=<?php echo $_get['code'] ?>>
                            <input type="submit" value="Enregistrer" class="select_input">
                </form>
            </div>
        </div>
        <?php
            include_once("portions de page/footer.html");
        ?>
        <!-- Enregistrement des informations du formulaire -->
        <?php
            if(isset($_POST['prenom']))
            {
                $prenom=$_POST['prenom'];
            }
            if(isset($_POST['nom']))
            {
                $nom=$_POST['nom'];
            }
            if(isset($_POST['birthday']))
            {
                $date_de_naissance=$_POST['birthday'];
            }
            if(isset($_POST['tel']))
            {
                $tel=$_POST['tel'];
            }
            if(isset($_POST['mail']))
            {
                $mail=$_POST['mail'];
            }
         
            
            if(isset($_POST['cin']))
            {
                $cin=$_POST['cin'];
            }
           if(isset($_POST['password']))
           {
            $password=$_POST['password'];
           }
            
            if(isset($prenom) and isset($nom) and isset($date_de_naissance) and isset($tel) and isset($mail) and isset($cin))
            {
                $connect=new PDO("mysql:host=localhost;dbname=agence_immobiliere","immobilier","motdepasse");
                if($connect)
                {
                        $req="insert into personne (nom,prenom,cin,tel,dateNaissance,mail) values (:nom,:prenom,:cin,:tel,:date_de_naissance,:mail)";
                        $stmt=$connect->prepare($req);
                        $stmt->execute(array(':nom'=>$nom,':prenom'=>$prenom,':cin'=>$cin,':tel'=>$tel,':date_de_naissance'=>$date_de_naissance,':mail'=>$mail));
                        $req="insert into compte_commerciaux (mail,password) values (:mail,:password)";
                        $stmt=$connect->prepare($req);
                        $stmt->execute(array(':mail'=> $mail,':password'=>$password));
                        if($stmt)
                        die("vos informations ont été ajouter avec succés <br>  <a href=>Retour <a/>");
                }
            }
            else
                die("Erreur lors de la connecxion au niveau de la base de donnée veuillez réessayer s'il vous plait");

        ?>
</body>
</html>
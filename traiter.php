<?php
    if(isset($_POST['prenom']))
    {
        $prenom=$_POST['prenom'];
    }
    if(isset($_POST['nom']))
    {
        $nom=$_POST['nom'];
    }
    if(isset($_POST['cin']))
    {
        $cin=$_POST['cin'];
    }
    if(isset($_POST['tel']))
    {
        $tel=$_POST['tel'];
    }
    if(isset($_POST['naissance']))
    {
        $naissance=$_POST['naissance'];
    }
    if(isset($_POST['mail']))
    {
        $mail=$_POST['mail'];
    }
    if(isset($_POST['pwd']))
    {
        $pwd=$_POST['pwd'];
    }
    if(isset($prenom)and isset($nom) and isset($cin) and isset($tel) and isset($naissance) and isset($pwd) and isset($mail))
    {
        $connect=new PDO("mysql:host=localhost;dbname=agence_immobiliere","immobilier","motdepasse");
        if($connect)
        {
            $req="insert into personne(nom,prenom,cin,tel,dateNaissance,mail) 
            values(:nom,:prenom,:cin,:tel,:naissance,:mail)
            ";
            $stmt=$connect->prepare($req);
            $stmt->execute(array(':nom'=>$nom,':prenom'=>$prenom,':cin'=>$cin,':tel'=>$tel,':naissance'=>$naissance,':mail'=>$mail));
            if($stmt)
            {
                echo "Vos informations ont été ajouter avec succes";
                echo "<br/><a href='visiteur.php'>Retour</a>";
            }
            else
            {
                echo "une Erreur est survenue lors de l'enregistrement veuillez reessayer plutard";
                echo "<br/><a href='visiteur.php'>Retour</a>";
            }
            $req="insert into compte_client values(:mail,:pwd)";
            $stmt=$connect->prepare($req);
            $stmt=$stmt->execute(array(':mail'=>$mail,':pwd'=>$pwd));
        }
    }
?>
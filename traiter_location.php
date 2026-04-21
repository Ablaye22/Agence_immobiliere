<?php
    $idpers=$_POST['idpers'];
    $id_bien=$_POST['id_bien'];
    $label_bien=$_POST['label_bien'];
    $connect=new PDO("mysql:host=localhost;dbname=agence_immobiliere","immobilier","motdepasse");
    if($connect)
    {
        $req="insert into demand_location(idpers,id_bien,label_bien) values(:idpers,:id_bien,:label_bien)";
        $stmt=$connect->prepare($req);
        $stmt->execute(array(':idpers'=>$idpers,':id_bien'=>$id_bien,':label_bien'=>$label_bien));
        echo "Vos informations ont été ajouter avec succes";
        $req="select * from personne where idpers=?";
        $stmt=$connect->prepare($req);
        $stmt->execute(array($idpers));
        $stmt=$stmt->fetch();
        $mail=$stmt['mail'];
        $req="select * from compte_client where mail='$mail'";
        $stmt=$connect->query($req);
        $stmt=$stmt->fetch();
        $pwd=$stmt['password'];
        echo "
           <form method='post' action='client.php' >
              <input type='hidden' name='mail' value=$mail >
              <input type='hidden' name='pwd' value=$pwd >
              <input type='submit'value='Retour'>
            </form>
        ";
    }
    $connect=null;
?>
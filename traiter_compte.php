<?php 
            session_start();
            // connection dans la base de donnée
            $connection=new PDO("mysql:host=localhost;dbname=agence_immobiliere", "immobilier","motdepasse");
            if($connection)
            {
                if(isset($_POST['mail'])and isset($_POST['pwd'])):
                    $mail=$_POST['mail'];
                    $pwd=$_POST['pwd']; 
                    $req="select * from compte_client where mail=?";    
                    $result=$connection->prepare($req);
                    if($result)
                    {
                        $result->execute(array($mail));
                        $result=$result->fetch();
                        if($result['mail']==$mail and $result['password']==$pwd)
                        {
                            header('Location:client.php?');
                            $req="select * from personne where mail=?";
                            $result=$connection->prepare($req);
                            $result->execute(array($mail));
                            $result=$result->fetch();
                            $nom=$result['nom'];
                            $prenom=$result['prenom'];
                            $idpers=$result['idpers'];
                            $_SESSION['idpers']=$idpers;
                            $_SESSION['nom']=$nom;
                            $_SESSION['prenom']=$prenom;
                        } 
                        else
                        {
                            die("<p id='erreur'>informations saisies incorrect!!!</p>");
                        }
                    }
                    else
                     die("problem de connexion");
                endif;
            }
            
        ?>
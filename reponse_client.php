<?php
    $connect=new PDO("mysql:host=localhost;dbname=agence_immobiliere","immobilier","motdepasse");
    if(isset($_POST['accepter']))
    {
        $no_demand=$_POST['accepter'];
        if($connect)
        { 
            $req="update demand_location set reponse=2 where No_demand=$no_demand";
            $connect->exec($req);
            echo "vos informations ont été prise en charge avec succès ";
            echo "<br/> <a href='client.php'>returner</a>";
           
        }

    }
    if(isset($_POST['annuler']))
    {
        $no_demand=$_POST['annuler'];
        if($connect)
        {
            $req="delete from demand_location where No_demand=$no_demand";
            $rep=$connect->exec($req);
            if($rep)
            {
                echo "vos informations ont été prise en charge avec succès ";
                echo "<br/> <a href='client.php'>returner</a>";
            }
        }
    }
?>
<?php
     session_start();
    if(isset($_POST['deconnect']))
    {
        header('location:visiteur.php');
        exit;
    }
    if(isset($_POST['deconnec_comm']) and isset($_POST['idpers']))
    {
        unset($_SESSION['connect'.$_POST['idpers']]);
        unset($_SESSION['connect_mail'.$_POST['idpers']]);
        unset($_SESSION['connect_pwd'.$_POST['idpers']]);
        header("location:authentification_commerciaux.php");
        exit;
    }
?>
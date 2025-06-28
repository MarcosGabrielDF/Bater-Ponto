<?php
session_start();

include '../../conexao.php';

if($_SERVER['REQUEST_METHOD' === 'POST']) {

    $tipo = $_POST['tipoUsuario'];
    $codigo = $_POST["codigo"];
    $email = $_POST['email'];
    $senha = $_POST['senha'];


    if($tipo == 'empresa'){
        
    } else($tipo == 'funcionario'){

    }

}else{
    echo "Requisição inválida.";
}

?>
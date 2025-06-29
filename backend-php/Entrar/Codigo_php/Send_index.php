<?php
session_start();

include '../../conexao.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tipo = $_POST['tipoUsuario'];
    $codigo = $_POST["codigo"];
    $email = $_POST['email'];
    $senha = $_POST['senha'];


    if($tipo == 'empresa'){
        
        // Verificar senha (verify password)
        $stmt = $pdo->prepare("SELECT senha FROM empresas WHERE email =:email");
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if($usuario && password_verify($senha, $usuario['senha'])){
            echo "Olá!";
        }else{
            echo "#ERRO";
        }


    } elseif($tipo == 'funcionario'){
        echo 'é um funcionario';
    }else{
        echo 'fim';
    }

}else{
    echo "Requisição inválida.";
}

?>
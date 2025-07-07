<?php
# Realiza o login dos usuários, redirecionando o funcionário para a tela de funcionário e a empresa para a tela correspondente.
# Performs user login, redirecting employees to the employee screen and companies to their respective screen.

session_start();

include '../../conexao.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tipo = $_POST['tipoUsuario'];
    $codigo = $_POST["codigo"];
    $email = $_POST['email'];
    $senha = $_POST['senha'];


    if($tipo == 'empresa'){
        
        // Busca os dados do usuário no banco de dados com base no e-mail fornecido
        $stmt = $pdo->prepare("SELECT email, senha, codigo_empresa FROM empresas WHERE email =:email");
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if($usuario != false){ //Verifica se o e-mail retornou algum resultado no banco de dados
            if($codigo == $usuario['codigo_empresa']){ // Compara o valor da variável com o valor armazenado no banco de dados
                if($usuario['email'] && password_verify($senha, $usuario['senha'])){ //Verifica se a senha informada está correta
                    echo "Olá!";
                }else{
                    echo "ERRO [SENHA INCORRETA]";
                }
            }else{
                echo "#ERRO [CÓDIGO INCORRETO]";
            }
        }else{
            echo "#ERRO [EMAIL NÃO ENCONTRADO]";
        }


    } elseif($tipo == 'funcionario'){

        // Busca os dados do usuário no banco de dados com base no e-mail fornecido
        $stmt = $pdo->prepare("SELECT email, senha, codigo_empresa FROM empresas WHERE email =:email");
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);


        if($usuario != false){ //Verifica se o e-mail retornou algum resultado no banco de dados
            if($codigo == $usuario['codigo_empresa']){ // Compara o valor da variável com o valor armazenado no banco de dados
                if($usuario['email'] && password_verify($senha, $usuario['senha'])){ //Verifica se a senha informada está correta
                    echo "Olá!";
                }else{
                    echo "ERRO [SENHA INCORRETA]";
                }
            }else{
                echo "#ERRO [CÓDIGO INCORRETO]";
            }
        }else{
            echo "#ERRO [EMAIL NÃO ENCONTRADO]";
        }

    }else{

        echo 'fim';
    
    }

}else{
    echo "Requisição inválida.";
}

?>
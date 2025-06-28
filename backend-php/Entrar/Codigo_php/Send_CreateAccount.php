<?php
# Arquivo para enviar as informações de criação de conta. 
# File to send account creation information.

session_Start();

include '../../conexao.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipoUsuario'];
    $codigo = $_POST['codigo'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    //Criptografando a senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    
    //Prepara a query cin base no tipo de usuário
    /* 
    $stmt = $conn->prepare ("INSERT INTO usuario (tipo, codigo, email, senha) VALUES (?, ?, ?, ?)");
    $stmt -> bind_param ("ssss", $tipo, $codigo, $email, $senhaHash);
    */
    
    if ($tipo == 'empresa') {
        $stmt = $conn->prepare("INSERT INTO empresas (nome, email, senha, codigo_empresa) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nome, $email, $senhaHash, $codigo);
    } else {
        // Buscar ID da empresa pelo código
        $stmtEmpresa = $conn->prepare("SELECT id FROM empresas WHERE codigo_empresa = ?");
        $stmtEmpresa->bind_param("s", $codigo);
        $stmtEmpresa->execute();
        $resultado = $stmtEmpresa->get_result();

        if ($resultado->num_rows === 1) {
            $empresa = $resultado->fetch_assoc();
            $empresa_id = $empresa['id'];

            $stmt = $conn->prepare("INSERT INTO funcionarios (nome, empresa_id, email, senha) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siss", $nome, $empresa_id, $email, $senhaHash);
        } else {
            die("Código da empresa inválido.");
        }

        $stmtEmpresa->close();
    }


    if ($stmt->execute()) {
        echo "Usuário cadastrado com sucesso!";
    } else {
        echo "Erro ao cadastrar: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Requisição inválida.";
}
?>
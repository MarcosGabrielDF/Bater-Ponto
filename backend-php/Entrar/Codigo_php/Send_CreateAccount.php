<?php
session_start(); // Garanta que session_start() está no topo

include '../../conexao.php'; // Certifique-se que $conn é inicializado aqui

// Redireciona para a página de cadastro se a requisição não for POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Requisição inválida.";
    header("Location: ../Estrutura_html/Create_Account.html"); // Altere para o seu formulário de cadastro
    exit();
}

$tipo = $_POST['TipoUsuario'] ?? '';
$codigo = $_POST['codigo'] ?? '';
$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

// --- 1. Validação de Entrada ---
if (empty($tipo) || empty($nome) || empty($email) || empty($senha)) {
    $_SESSION['error_message'] = "Todos os campos (exceto código para empresas) são obrigatórios.";
    header("Location: ../Estrutura_html/Create_Account.html");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_message'] = "Formato de e-mail inválido.";
    header("Location: ../Estrutura_html/Create_Account.html");
    exit();
}

if (strlen($senha) < 8) {
    $_SESSION['error_message'] = "A senha deve ter no mínimo 8 caracteres.";
    header("Location: /caminho/para/pagina_de_cadastro.php");
    exit();
}

// Criptografando a senha
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = null; // Inicializa $stmt para garantir que está definido

try {
    // --- 2. Verificação de E-mail Existente (para ambas as tabelas) ---
    $checkEmailCompanyStmt = $conn->prepare("SELECT id FROM empresas WHERE email = ?");
    $checkEmailCompanyStmt->bind_param("s", $email);
    $checkEmailCompanyStmt->execute();
    $checkEmailCompanyStmt->store_result();

    $checkEmailEmployeeStmt = $conn->prepare("SELECT id FROM funcionarios WHERE email = ?");
    $checkEmailEmployeeStmt->bind_param("s", $email);
    $checkEmailEmployeeStmt->execute();
    $checkEmailEmployeeStmt->store_result();

    if ($checkEmailCompanyStmt->num_rows > 0 || $checkEmailEmployeeStmt->num_rows > 0) {
        $_SESSION['error_message'] = "Este e-mail já está cadastrado.";
        header("Location: ../Estrutura_html/Create_Account.html");
        exit();
    }
    $checkEmailCompanyStmt->close();
    $checkEmailEmployeeStmt->close();

    // --- Lógica de Inserção Baseada no Tipo de Usuário ---
    if ($tipo === 'empresa') {
        if (empty($codigo)) {
            $_SESSION['error_message'] = "O código da empresa é obrigatório para o tipo 'empresa'.";
            header("Location: ../Estrutura_html/Create_Account.html");
            exit();
        }
        $stmt = $conn->prepare("INSERT INTO empresas (nome, email, senha, codigo_empresa) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nome, $email, $senhaHash, $codigo);
    } elseif ($tipo === 'funcionario') {
        if (empty($codigo)) {
            $_SESSION['error_message'] = "O código da empresa é obrigatório para funcionários.";
            header("Location: ../Estrutura_html/Create_Account.html");
            exit();
        }

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
            $_SESSION['error_message'] = "Código da empresa inválido.";
            header("Location: ../Estrutura_html/Create_Account.html");
            exit();
        }
        $stmtEmpresa->close();
    } else {
        $_SESSION['error_message'] = "Tipo de usuário inválido.";
        header("Location: ../Estrutura_html/Create_Account.html");
        exit();
    }

    // --- Execução da Query ---
    if ($stmt && $stmt->execute()) {
        $_SESSION['success_message'] = "Usuário cadastrado com sucesso!";
        header("Location: ../Estrutura_html/Create_Account.html"); // Redireciona para uma página de sucesso
        exit();
    } else {
        $_SESSION['error_message'] = "Erro ao cadastrar: " . ($stmt ? $stmt->error : "Erro na preparação da consulta.");
        header("Location: ../Estrutura_html/Create_Account.html");
        exit();
    }

} catch (mysqli_sql_exception $e) {
    // Para depuração (não use em produção diretamente ao usuário)
    error_log("Erro de SQL: " . $e->getMessage());
    $_SESSION['error_message'] = "Ocorreu um erro no servidor. Por favor, tente novamente.";
    header("Location: ../Estrutura_html/Create_Account.html");
    exit();
} finally {
    // Garante que o statement e a conexão sejam fechados, se estiverem abertos
    if ($stmt) {
        $stmt->close();
    }
    if ($conn) {
        $conn->close();
    }
}
?>
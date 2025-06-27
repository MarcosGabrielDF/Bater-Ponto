<?php
// Inicia a sessão. É crucial para manter o usuário logado entre as páginas.
session_start();

// Inclui o arquivo de conexão com o banco de dados.
// Certifique-se de que este arquivo inicializa a variável $conn.
include '../../conexao.php'; // Ajuste o caminho se necessário

// Redireciona se a requisição não for POST (ou seja, se alguém tentar acessar o arquivo diretamente).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Requisição inválida. Por favor, use o formulário de login.";
    header("Location: /caminho/para/sua/pagina_de_login.php"); // Altere para o caminho real da sua página de login
    exit();
}

// Obtém os dados do formulário, usando o operador ?? para evitar warnings caso algum campo esteja ausente.
$tipoUsuario = $_POST['tipoUsuario'] ?? '';
$codigo = $_POST['codigo'] ?? '';
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

// --- 1. Validação Básica de Entrada ---
// Verifica se todos os campos obrigatórios estão preenchidos.
if (empty($tipoUsuario) || empty($email) || empty($senha)) {
    $_SESSION['error_message'] = "Por favor, preencha todos os campos.";
    header("Location: /caminho/para/sua/pagina_de_login.php");
    exit();
}

// Valida o formato do e-mail.
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_message'] = "Formato de e-mail inválido.";
    header("Location: /caminho/para/sua/pagina_de_login.php");
    exit();
}

$stmt = null; // Inicializa a variável para o prepared statement

try {
    // --- 2. Lógica de Verificação de Login Baseada no Tipo de Usuário ---
    if ($tipoUsuario === 'empresa') {
        // Para empresas, o código da empresa é obrigatório.
        if (empty($codigo)) {
            $_SESSION['error_message'] = "Por favor, digite o código da empresa.";
            header("Location: /caminho/para/sua/pagina_de_login.php");
            exit();
        }

        // Prepara a consulta para buscar a empresa pelo e-mail e código.
        $stmt = $conn->prepare("SELECT id, nome, email, senha, codigo_empresa FROM empresas WHERE email = ? AND codigo_empresa = ?");
        $stmt->bind_param("ss", $email, $codigo);

    } elseif ($tipoUsuario === 'funcionario') {
        // Para funcionários, o código da empresa é obrigatório para associar ao ID da empresa.
        if (empty($codigo)) {
            $_SESSION['error_message'] = "Por favor, digite o código da empresa.";
            header("Location: /caminho/para/sua/pagina_de_login.php");
            exit();
        }

        // Primeiro, encontra o ID da empresa pelo código da empresa.
        $stmtEmpresa = $conn->prepare("SELECT id FROM empresas WHERE codigo_empresa = ?");
        $stmtEmpresa->bind_param("s", $codigo);
        $stmtEmpresa->execute();
        $resultadoEmpresa = $stmtEmpresa->get_result();

        if ($resultadoEmpresa->num_rows === 0) {
            $_SESSION['error_message'] = "Código da empresa inválido.";
            header("Location: /caminho/para/sua/pagina_de_login.php");
            exit();
        }
        $empresa = $resultadoEmpresa->fetch_assoc();
        $empresa_id = $empresa['id'];
        $stmtEmpresa->close(); // Fecha o statement da empresa

        // Em seguida, prepara a consulta para buscar o funcionário pelo e-mail e ID da empresa.
        $stmt = $conn->prepare("SELECT id, nome, email, senha, empresa_id FROM funcionarios WHERE email = ? AND empresa_id = ?");
        $stmt->bind_param("si", $email, $empresa_id);

    } else {
        // Se o tipo de usuário não for 'empresa' nem 'funcionario'.
        $_SESSION['error_message'] = "Tipo de usuário inválido.";
        header("Location: /caminho/para/sua/pagina_de_login.php");
        exit();
    }

    // Executa a consulta preparada.
    $stmt->execute();
    // Obtém o resultado da consulta.
    $resultado = $stmt->get_result();

    // --- 3. Verificação de Credenciais e Autenticação ---
    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc(); // Pega a linha do usuário encontrado como um array associativo.

        // Verifica se a senha fornecida corresponde à senha hasheada no banco de dados.
        if (password_verify($senha, $usuario['senha'])) {
            // Senha correta! Autenticação bem-sucedida.

            // Cria variáveis de sessão para manter o usuário logado e acessível em outras páginas.
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_name'] = $usuario['nome'];
            $_SESSION['user_email'] = $usuario['email'];
            $_SESSION['user_type'] = $tipoUsuario;

            // Se for funcionário, armazena também o ID da empresa.
            if ($tipoUsuario === 'funcionario') {
                $_SESSION['empresa_id'] = $usuario['empresa_id'];
            } else { // Se for empresa, armazena o código da empresa
                $_SESSION['codigo_empresa'] = $usuario['codigo_empresa'];
            }


            // Redireciona o usuário para uma página segura (ex: painel de controle).
            $_SESSION['success_message'] = "Login realizado com sucesso!";
            header("Location: /caminho/para/seu/dashboard.php"); // Altere para a página pós-login
            exit();

        } else {
            // Senha incorreta.
            $_SESSION['error_message'] = "E-mail ou senha incorretos.";
            header("Location: /caminho/para/sua/pagina_de_login.php");
            exit();
        }
    } else {
        // Usuário não encontrado (e-mail ou código da empresa incorretos).
        $_SESSION['error_message'] = "E-mail ou senha incorretos.";
        header("Location: /caminho/para/sua/pagina_de_login.php");
        exit();
    }

} catch (mysqli_sql_exception $e) {
    // Captura e trata erros de banco de dados.
    error_log("Erro de SQL no Login: " . $e->getMessage()); // Registra o erro para depuração.
    $_SESSION['error_message'] = "Ocorreu um erro no servidor. Por favor, tente novamente mais tarde.";
    header("Location: /caminho/para/sua/pagina_de_login.php");
    exit();
} finally {
    // Garante que o statement e a conexão sejam fechados, mesmo que ocorra um erro.
    if ($stmt) {
        $stmt->close();
    }
    if ($conn) {
        $conn->close();
    }
}
?>
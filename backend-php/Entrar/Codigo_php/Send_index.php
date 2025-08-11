<?php
# Realiza o login dos usuários.

session_start();

include '../../conexao.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tipo = trim($_POST['tipoUsuario'] ?? '');
    $codigo = trim($_POST["codigo"] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // Array para armazenar mensagens de erro.
    $mensagensDeErro = [];
    $usuario = null;
    $tabela = '';

    // 1. Verificação inicial
    if (empty($email) || empty($senha) || empty($tipo)) {
        $mensagensDeErro[] = 'Preencha todos os campos obrigatórios.';
    }

    // Se já existem erros básicos, não continua as verificações de banco de dados
    if (!empty($mensagensDeErro)) {
        echo json_encode([
            'sucesso' => false, 
            'mensagem' => implode('<br>', $mensagensDeErro)
        ]);
        exit;
    }

    // 2. Lógica de busca no banco de dados, baseada no tipo de usuário
    if ($tipo == 'empresa') {
        $tabela = 'empresas';
        $redirect = 'caminho/para/tela_empresa.html';
        $mensagem_sucesso = 'Login de empresa realizado com sucesso!';

        $stmt = $pdo->prepare("SELECT id, senha, codigo_empresa FROM {$tabela} WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    } elseif ($tipo == 'funcionario') {
        $tabela = 'funcionarios';
        $redirect = 'caminho/para/tela_funcionario.html';
        $mensagem_sucesso = 'Login de funcionário realizado com sucesso!';

        $stmt = $pdo->prepare("SELECT f.id, f.senha, e.codigo_empresa 
                               FROM funcionarios f 
                               JOIN empresas e ON f.empresa_id = e.id
                               WHERE f.email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    } else {
        $mensagensDeErro[] = 'Tipo de usuário inválido.';
    }

    // 3. Verificações de credenciais após buscar os dados
    if (!empty($mensagensDeErro)) {
        // Se o tipo de usuário já era inválido, encerra
    } elseif (!$usuario) {
        $mensagensDeErro[] = 'E-mail não encontrado.';
    } elseif ($codigo != $usuario['codigo_empresa']) {
        $mensagensDeErro[] = 'Código da empresa incorreto.';
    } elseif (!password_verify($senha, $usuario['senha'])) {
        $mensagensDeErro[] = 'Senha incorreta.';
    }
    
    // 4. Retorna a resposta final com todos os erros ou o sucesso
    if (!empty($mensagensDeErro)) {
        echo json_encode([
            'sucesso' => false, 
            'mensagem' => implode('<br>', $mensagensDeErro)
        ]);
        exit;
    }
    
    // Se chegou aqui, o login é válido. Cria a sessão e retorna sucesso.
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['tipo_usuario'] = $tipo;

    echo json_encode([
        'sucesso' => true,
        'mensagem' => $mensagem_sucesso,
        'redirecionarPara' => $redirect
    ]);

} else {
    echo json_encode([
        'sucesso' => false, 
        'mensagem' => 'Requisição inválida.'
    ]);
}
?>
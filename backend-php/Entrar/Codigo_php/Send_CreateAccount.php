<?php
# Arquivo para enviar as informações de criação de conta.

session_start();

include '../../conexao.php'; // cria $pdo (PDO)

// Define o cabeçalho para que o navegador entenda que a resposta é JSON
header('Content-Type: application/json');

// Só permite requisições POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo   = trim($_POST['tipoUsuario'] ?? '');
    $codigo = trim($_POST['codigo'] ?? '');
    $nome   = trim($_POST['nome'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $senha  = $_POST['senha'] ?? '';

    // Array para armazenar as mensagens de erro
    $mensagensDeErro = [];

    // Tratamento de variáveis
    $email = strtolower($email);
    $nome = strtolower($nome); // Corrigido, estava como $email

    // Verifica se todos os campos necessários estão preenchidos
    if (!$tipo || !$nome || !$email || !$senha) {
        $mensagensDeErro[] = 'Preencha todos os campos obrigatórios.';
    }

    // Verifica se a senha é muito curta (exemplo)
    if (strlen($senha) < 6) {
        $mensagensDeErro[] = 'A senha deve ter no mínimo 6 caracteres.';
    }

    //Empresa 
    if ($tipo === 'empresa') {
        // Validação do campo código para empresas (não pode ser vazio)
        if (empty($codigo)) {
            $mensagensDeErro[] = 'O código da empresa não pode ser vazio.';
        } else {
            // Verifica duplicidade de empresa
            $stmt = $pdo->prepare("SELECT codigo_empresa, nome, email FROM empresas WHERE codigo_empresa = :codigo OR nome = :nome OR email = :email LIMIT 1");
            $stmt->execute([
                ':codigo' => $codigo,
                ':nome'   => $nome,
                ':email'  => $email
            ]);
            $empresaExistente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($empresaExistente) {
                if ($empresaExistente['codigo_empresa'] === $codigo) {
                    $mensagensDeErro[] = "Código de empresa: '{$codigo}' já existe.";
                }
                if ($empresaExistente['nome'] === $nome) {
                    $mensagensDeErro[] = "Nome de empresa: '{$nome}' já existe.";
                }
                if ($empresaExistente['email'] === $email) {
                    $mensagensDeErro[] = "E-mail: '{$email}' já existe.";
                }
            }
        }

        // Se houver erros, retorna o JSON e encerra
        if (!empty($mensagensDeErro)) {
            echo json_encode(['sucesso' => false, 'mensagem' => $mensagensDeErro]);
            exit;
        }

        // Se passou nas verificações, insere nova empresa
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "INSERT INTO empresas (nome, email, senha, codigo_empresa)
             VALUES (:nome, :email, :senha, :codigo)"
        );
        $stmt->execute([
            ':nome'   => $nome,
            ':email'  => $email,
            ':senha'  => $senhaHash,
            ':codigo' => $codigo
        ]);

        echo json_encode(['sucesso' => true, 'mensagem' => 'Empresa cadastrada com sucesso!']);
    

    // Funcionario. 
    } elseif ($tipo === 'funcionario') {
        // Validação do campo código para funcionário
        if (empty($codigo)) {
            $mensagensDeErro[] = 'O código da empresa é obrigatório para funcionários.';
        } else {
            // 1. Verifica se o código da empresa é válido.
            $stmtEmpresa = $pdo->prepare("SELECT id FROM empresas WHERE codigo_empresa = :codigo LIMIT 1");
            $stmtEmpresa->execute([':codigo' => $codigo]);
            $empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);

            if (!$empresa) {
                $mensagensDeErro[] = "Código da empresa inválido: '{$codigo}'.";
            } else {
                $empresa_id = $empresa['id'];
            }
        }

        // 2. Verifica se o e-mail do funcionário já existe.
        $checkEmail = $pdo->prepare("SELECT 1 FROM funcionarios WHERE email = :email LIMIT 1");
        $checkEmail->execute([':email' => $email]);
        if ($checkEmail->fetch()) {
            $mensagensDeErro[] = "E-mail já cadastrado: '{$email}'.";
        }

        // Se houver erros, retorna o JSON e encerra
        if (!empty($mensagensDeErro)) {
            echo json_encode(['sucesso' => false, 'mensagem' => $mensagensDeErro]);
            exit;
        }

        // Insere funcionário
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "INSERT INTO funcionarios (nome, empresa_id, email, senha)
             VALUES (:nome, :empresa_id, :email, :senha)"
        );
        $stmt->execute([
            ':nome'       => $nome,
            ':empresa_id' => $empresa_id,
            ':email'      => $email,
            ':senha'      => $senhaHash
        ]);

        echo json_encode(['sucesso' => true, 'mensagem' => 'Funcionário cadastrado com sucesso!']);

    } else {
        $mensagensDeErro[] = "Tipo de usuário inválido.";
        echo json_encode(['sucesso' => false, 'mensagem' => $mensagensDeErro]);
    }
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => ['Requisição inválida.']]);
}
?>
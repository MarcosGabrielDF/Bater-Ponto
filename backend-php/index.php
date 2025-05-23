<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <link rel="stylesheet" href="css/entrar.css" />
</head>
<body>
  <main class="login-container">
    <h1>Entrar</h1>
    <form>
      <label for="codigo">Código da empresa</label>
      <input type="text" id="codigo" name="codigo" placeholder="Digite o código da empresa" required />

      <label for="email">Email</label>
      <input type="email" id="email" name="email" placeholder="Digite seu email" required />

      <label for="senha">Senha</label>
      <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required />

      <div class="row">
        <a href="#" class="esqueceu">Esqueceu a senha?</a> <br>
        <a href="Entrar/cadastrar/cadastro.php" class="esqueceu">Não tem uma conta?</a>
        <button type="submit">Entrar</button>
      </div>
    </form>
  </main>
</body>
</html>

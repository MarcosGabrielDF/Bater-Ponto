<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cadastro</title>
  <link rel="stylesheet" href="../../css/cadastro.css" />
</head>
<body>
  <main class="login-container">
    <h1>Cadastrar-se</h1>

    <form>
      <label for="tipoUsuario">Você é</label>
      <select id="tipoUsuario" name="tipoUsuario" required>
        <option value="" disabled selected>Selecione</option>
        <option value="empresa">Empresa</option>
        <option value="funcionario">Funcionário</option>
      </select>

      <div id="campoCodigo" class="codigo-field" style="display:none;">
        <label for="codigo" id="labelCodigo"></label>
        <input type="text" id="codigo" name="codigo" />
      </div>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" placeholder="Digite seu email" required />

      <label for="senha">Senha</label>
      <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required />

      <div class="row">
        <a href="../../index.php" class="esqueceu">Já tem uma conta?</a>
        <button type="submit">Cadastrar</button>
      </div>
    </form>
  </main>

  <script>
    const tipoUsuario = document.getElementById('tipoUsuario');
    const campoCodigo = document.getElementById('campoCodigo');
    const labelCodigo = document.getElementById('labelCodigo');

    tipoUsuario.addEventListener('change', () => {
      if (tipoUsuario.value === 'empresa') {
        campoCodigo.style.display = 'block';
        labelCodigo.textContent = 'Crie um código';
      } else if (tipoUsuario.value === 'funcionario') {
        campoCodigo.style.display = 'block';
        labelCodigo.textContent = 'Colocar código';
      } else {
        campoCodigo.style.display = 'none';
        labelCodigo.textContent = '';
      }
    });
  </script>
</body>
</html>

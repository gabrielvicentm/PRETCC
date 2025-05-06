<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Home</title>
  <link rel="stylesheet" href="../css/home.css">
</head>
<body>
  <div class="sidebar">
    <div class="logo" style="color: orange;">LOGO LINDA</div>
    <a href="home.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/home.png"/><span>Página Inicial</span></a>
    <a href="diario.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/dumbbell.png"/><span>Diário de Treino</span></a>
    <a href="pesquisa.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/search.png"/><span>Pesquisa</span></a>
    <a href="reels.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/film-reel.png"/><span>Vídeos Curtos</span></a>
    <a href="postar.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/plus-math.png"/><span>Postar</span></a>
    <a href="perfil.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/user.png"/><span>
      <?php echo "" . $_SESSION['user_name']; ?> 
    </span></a>
    <a href="../php/logout.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/logout-rounded-up.png" /><span>Sair</span></a>
  </div>










  <div class="content"> 
    <h1>Bem-vindo ao seu diário de treino</h1>
    <p>Aqui você terá seu diário de treino</p>
    
    <form action="processar_formulario.php" method="POST" id="formulario" style="border: 2px #1a1a1a; padding: 50px; border-radius: 10px; width: 50%; background-color: #1a1a1a">
  
  
    <p style="color: green; font-weight: bold;">Insira aqui a informação do seu treino Ex: Data e grupamento muscular</p>


  


  <!-- Inputs adicionais antes do formulário -->
<div style="margin-bottom: 20px; width: 50%; background-color: #1a1a1a; padding: 20px; border-radius: 10px;">
  <label for="data_treino" style="color: white;">Data do Treino:</label><br>
  <input type="date" name="data_treino" id="data_treino" required><br><br>

  <label for="grupamento" style="color: white;">Grupamento Muscular:</label>
  <input type="text" name="grupamento" id="grupamento" placeholder="Ex: Peito e tríceps" required><br>
</div>


  <div id="form-sections">



    <!-- Primeiro exercício padrão -->
    <div class="form-section" data-index="1">
      <label>Exercício:</label><br>
      <input type="text" name="exercicio_1"><br><br>

      <label>Número de Séries:</label><br>
      <input type="number" class="series-input" name="series_1" max="10"><br><br>

      <div class="series-inputs"></div>
    </div>
  </div>

  <br>
  <button style="color: green;" type="submit">Salvar Treino</button>

  <button type="button" onclick="adicionarExercicio()">Adicionar Exercício</button>
  
</form>

<script>
let exercicioIndex = 1;

///////////////////////////////////////////////////////// Função para gerar os campos de séries/peso/repetição
function atualizarSeries(section) {
  const seriesInput = section.querySelector('.series-input');
  const container = section.querySelector('.series-inputs');
  const index = section.dataset.index;

  seriesInput.addEventListener('input', () => {
    const valor = parseInt(seriesInput.value);
    container.innerHTML = '';

    if (!isNaN(valor) && valor > 0) {
      for (let i = 1; i <= valor; i++) {
        const wrapper = document.createElement('div');
        wrapper.style.marginBottom = "10px";

        const inputPeso = document.createElement('input');
        inputPeso.type = 'text';
        inputPeso.name = `exercicio_${index}_serie_${i}_peso`;
        inputPeso.placeholder = `Peso da série ${i}`;

        const inputRep = document.createElement('input');
        inputRep.type = 'text';
        inputRep.name = `exercicio_${index}_serie_${i}_repeticoes`;
        inputRep.placeholder = `Repetições da série ${i}`;

        wrapper.appendChild(inputPeso);
        wrapper.appendChild(document.createTextNode(' '));
        wrapper.appendChild(inputRep);

        container.appendChild(wrapper);
      }
    }
  });
}

///////////////////////////////////////////////////////// Função para adicionar novo exercício
function adicionarExercicio() {
  exercicioIndex++;
  const formSections = document.getElementById('form-sections');

  const novaSecao = document.createElement('div');
  novaSecao.classList.add('form-section');
  novaSecao.dataset.index = exercicioIndex;

  novaSecao.style.marginTop = '40px'; ///////////////////////// Aumenta o espaço entre blocos

  novaSecao.innerHTML = `
  <hr><br>
  <label>Exercício:</label><br>
  <input type="text" name="exercicio_${exercicioIndex}"><br><br>

  <label>Número de Séries:</label><br>
  <input type="number" class="series-input" name="series_${exercicioIndex}" min="1"><br><br>

  <div class="series-inputs"></div>

  <button type="button" class="remover-btn" onclick="removerExercicio(this)" style="margin-top: 10px; color: red;">Remover Exercício</button>
`;


  formSections.appendChild(novaSecao);
  atualizarSeries(novaSecao); ///////////////////////////////// ativa o script da nova seção
}


///////////////////////////////////////////////////////// Ativa o primeiro bloco ao carregar
document.addEventListener('DOMContentLoaded', () => {
  const primeiraSecao = document.querySelector('.form-section');
  atualizarSeries(primeiraSecao);
});

///////////////////////////////////////////////////////// Função do botão de remover exercício
function removerExercicio(botao) {
  const secao = botao.closest('.form-section');
  secao.remove();
}
</script>
</body>
</html>

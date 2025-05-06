<!DOCTYPE html>
<html lang="pt-br">
<head>
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #121212;
    margin: 0;
    padding: 20px;
    color: #e0e0e0;
  }

  .container {
    max-width: 800px;
    margin: auto;
    background-color: #1e1e1e;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.5);
  }

  h1, h2 {
    color: #ffffff;
  }

  input[type="text"],
  textarea,
  select {
    width: 100%;
    padding: 10px;
    margin-top: 8px;
    margin-bottom: 16px;
    border: 1px solid #444;
    border-radius: 6px;
    background-color: #2c2c2c;
    color: #fff;
    box-sizing: border-box;
  }

  .file-wrapper {
    position: relative;
    width: 150px;
    height: 150px;
    margin-bottom: 20px;
  }

  .file-preview {
    width: 150px;
    height: 150px;
    border: 2px dashed #555;
    border-radius: 12px;
    background-color: #2c2c2c;
    background-size: cover;
    background-position: center;
    color: #aaa;
    text-align: center;
    line-height: 150px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    overflow: hidden;
  }

  .file-preview:hover {
    background-color: #333;
  }

  .file-preview img {
    display: none;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
  }

  .file-wrapper input[type="file"] {
    opacity: 0;
    position: absolute;
    top: 0; left: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
  }

  input[type="submit"],
  button {
    background-color: #4CAF50;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
  }

  input[type="submit"]:hover,
  button:hover {
    background-color: #45a049;
  }

  label {
    font-weight: bold;
    display: block;
    margin-bottom: 6px;
  }
</style>

<script>
  function previewFile(input) {
    const preview = document.getElementById('preview-img');
    const container = document.getElementById('preview-container');
    const file = input.files[0];
    const reader = new FileReader();

    reader.addEventListener("load", function () {
      preview.src = reader.result;
      preview.style.display = 'block';
      container.style.lineHeight = 0;
    }, false);

    if (file) {
      reader.readAsDataURL(file);
    }
  }
</script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postar Mídia</title>
</head>
<body>
    <h1>Enviar Post</h1>
    
    <form action="salvar_post.php" method="POST" enctype="multipart/form-data">
    <label for="descricao">Descrição:</label><br>
    <textarea name="descricao" id="descricao" rows="4" cols="50" required></textarea><br><br>

    <label for="midia">Escolha uma Mídia (imagem ou vídeo):</label><br>
    
<div class="file-wrapper">
  <div class="file-preview" id="preview-container">
    <img id="preview-img" src="" alt="preview">
    Selecionar arquivo
  </div>
  <input type="file" name="midia" id="midia" required onchange="previewFile(this)">
</div>
<br><br>

    <button type="submit">Postar</button>
</form>


</body>
</html>

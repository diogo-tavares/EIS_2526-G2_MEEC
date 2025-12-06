<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hub de Coleções</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Barra superior -->
    <header class="top-bar-home">
        <div class="logo">
            <a href="homepage.html">
                <img src="images/logo.png" alt="Logo do Sistema">
            </a>
        </div>
        <div class="search-bar">
            <input type="text" placeholder="Pesquisar por coleções, eventos ou tags">
            <button>🔍</button>
        </div>
        <div class="user-icon">
            <a href="perfil.html">
                <img src="images/profile.png" alt="Perfil" height="90">
            </a>
        </div>
    </header>

    <!-- Conteúdo principal -->
    <main class="edit-profile-content">
        <h1>Editar perfil:</h1>

        <section class="edit-profile-container">
            <!-- Imagem do perfil -->
            <div class="edit-profile-img">
                <img src="images/profile.png" alt="Foto de Perfil" width="180">
    
                <!-- Input escondido -->
                <input type="file" id="profile-upload" accept="image/*" class="hidden-element">
    
                <!-- Botão que dispara o input -->
                <button class="btn-secondary" id="upload-btn">Carregar nova imagem</button>
            </div>


            <!-- Campos de edição -->
            <div class="edit-profile-form">
                <label for="birthdate"><strong>Nova data de nascimento:</strong></label>
                <input type="date" id="birthdate" name="birthdate">
                
                <div class="edit-profile-buttons">
                    <button class="btn-primary" id="confirm-btn">Confirmar</button>
                    <button class="btn-primary" id="cancel-btn">Desfazer alterações e voltar atrás</button>
                </div>
            </div>
        </section>
    </main>

    <!-- Barra inferior -->
    <footer class="bottom-bar">
        <a href="desenvolvedores.html">DESENVOLVEDORES</a>
    </footer>
    
    <script src="js/profile_update.js"></script>


</body>
</html>

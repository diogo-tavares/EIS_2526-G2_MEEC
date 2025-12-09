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
    <main class="add-collection-content">
        <h1>Adicionar coleção:</h1>

        <section class="add-collection-container">
            <form id="add-collection-form" class="add-collection-form">

                <label for="collection-name"><strong>Nome:</strong></label>
                <input type="text" id="collection-name" name="collection-name" placeholder="Digite o nome da coleção" required>

                <label for="collection-date"><strong>Data de criação:</strong></label>
                <input type="date" id="collection-date" name="collection-date" required>

                <label><strong>Tags (Preencha até 5, em minúsculas, sem espaços, acentos ou cedilhas):</strong></label>
                <div class="tags-grid">
                    <input type="text" id="tag-1" placeholder="Tag 1 (Obrigatório)" required>
                    <input type="text" id="tag-2" placeholder="Tag 2">
                    <input type="text" id="tag-3" placeholder="Tag 3">
                    <input type="text" id="tag-4" placeholder="Tag 4">
                    <input type="text" id="tag-5" placeholder="Tag 5">
                </div>
                
                <label for="collection-description"><strong>Descrição:</strong></label>
                <textarea id="collection-description" name="collection-description" placeholder="Descreva brevemente a coleção..." rows="5" required></textarea>

                <div class="add-collection-buttons">
                    <button type="submit" class="btn-primary">Confirmar</button>
                    <button type="button" id="cancel-btn" class="btn-primary">Desfazer alterações e voltar atrás</button>
                </div>
            </form>
        </section>
    </main>




    <!-- Barra inferior -->
    <footer class="bottom-bar">
        <a href="desenvolvedores.html">DESENVOLVEDORES</a>
    </footer>
    
    <script src="js/add_edit_colection.js" defer></script>

</body>
</html>

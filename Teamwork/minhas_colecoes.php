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
    <main class="main-content">
        <!-- Secção de coleções -->
        <section class="latest-collections">
            <h2>Minhas coleções:</h2>

            <div class="collection">
                <a href="colecao.html">1. Coleção 1</a>
                <p>Descrição da coleção 1</p>
            </div>

            <div class="collection">
                <a href="colecao.html">2. Coleção 2</a>
                <p>Descrição da coleção 2</p>
            </div>

            <div class="collection">
                <a href="colecao.html">3. Coleção 3</a>
                <p>Descrição da coleção 3</p>
            </div>

            <div class="collection">
                <a href="colecao.html">4. Coleção 4</a>
                <p>Descrição da coleção 4</p>
            </div>
        </section>

        <!-- Seção lateral direita -->
        <aside class="sidebar">
            <button class="btn-primary" onclick="window.location.href='add_colecao.html'">Adicionar coleção</button>
            <button class="btn-secondary" onclick="window.location.href='eventos.html'">Ver todos os eventos</button>
        </aside>
    </main>

    <!-- Barra inferior -->
    <footer class="bottom-bar">
        <a href="desenvolvedores.html">DESENVOLVEDORES</a>
    </footer>

</body>
</html>

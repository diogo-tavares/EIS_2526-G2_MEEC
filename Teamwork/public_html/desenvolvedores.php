<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php';

// 2. Obter ID do utilizador logado
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM developers";
$result = $conn->query($sql);

?>


<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hub de Coleções</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/pesquisa.js" defer></script>
</head>
<body>

    <!-- Barra superior -->
    <header class="top-bar-home">
        <div class="logo">
            <a href="homepage.php">
                <img src="images/logo.png" alt="Logo do Sistema">
            </a>
        </div>
        <div class="search-bar">
    
    <div class="search-input-wrapper">
        <input type="text" id="live-search-input" placeholder="🔍 Pesquisar..." autocomplete="off">
        <div id="search-results" class="search-results-list"></div>
    </div>

    <a href="social.php" class="social-hub-btn">
        <span class="social-hub-icon">🌍</span>
        <span class="social-hub-text">Social Hub</span>
    </a>

</div>
        <div class="user-icon">
            <a href="perfil.php">
                <img src="<?php echo htmlspecialchars($user_photo); ?>" alt="Perfil" height="90" style="border-radius: 50%; object-fit: cover; width: 90px;">
            </a>
        </div>
    </header>

    <!-- Conteúdo principal -->
    <main class="devs-content">
    <h1>Desenvolvedores:</h1>
    
    <?php if ($result && $result->num_rows > 0): ?>
            <?php while($dev = $result->fetch_assoc()): ?>
                <section class="dev-container">
                    <div class="dev-img">
                        <?php 
                            $dev_img = !empty($dev['photo_path']) ? $dev['photo_path'] : 'images/profile.png'; 
                        ?>
                        <img src="<?php echo htmlspecialchars($dev_img); ?>" alt="Foto de <?php echo htmlspecialchars($dev['name']); ?>" width="180" style="border-radius: 50%; object-fit: cover; width: 180px; height: 180px;">
                    </div>
                    <div class="dev-info">
                        <h2><?php echo htmlspecialchars($dev['name']); ?></h2>
                        <p><strong>E-mail: </strong><?php echo htmlspecialchars($dev['email']); ?></p>
                        <p><strong>Faculdade: </strong><?php echo htmlspecialchars($dev['faculty']); ?></p>
                        <p><strong>Curso: </strong><?php echo htmlspecialchars($dev['course']); ?></p>
                    </div>
                </section>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; padding: 20px;">Não foram encontrados desenvolvedores registados.</p>
        <?php endif; ?>
</main>


    <!-- Barra inferior -->
    <footer class="bottom-bar">
        <a href="desenvolvedores.php">DESENVOLVEDORES</a>
    </footer>

</body>
</html>

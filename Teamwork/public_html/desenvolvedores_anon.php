<?php
require_once 'php/db.php';

// Buscar todos os developers à base de dados
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
</head>
<body>

    <!-- Barra superior (igual à de login/registo) -->
    <header class="top-bar">
        <div class="logo">
            <a href="login.php">
                <img src="images/logo.png" alt="Logo do Sistema">
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
        <a href="desenvolvedores_anon.php">DESENVOLVEDORES</a>
    </footer>

</body>
</html>

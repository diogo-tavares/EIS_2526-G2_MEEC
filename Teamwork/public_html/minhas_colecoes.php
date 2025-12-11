<?php
session_start();
require_once 'php/db.php';
require_once 'php/auth.php';
require_once 'php/get_profile_pic.php'; // Para a foto de perfil no canto superior

// Obter ID do utilizador logado
$user_id = $_SESSION['user_id'];

// Buscar as coleções deste utilizador
// Ordenado pela data de criação (mais recente primeiro)

$sql = "SELECT * FROM collections WHERE user_id = ? ORDER BY created_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>




<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Coleções - Hub de Coleções</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/pesquisa.js" defer></script>
</head>
<body>

    <header class="top-bar-home">
        <div class="logo">
            <a href="homepage.php">
                <img src="images/logo.png" alt="Logo do Sistema">
            </a>
        </div>
        
        <div class="search-bar">
            <input type="text" id="live-search-input" placeholder="🔍 Pesquisar..." autocomplete="off">
            <div id="search-results" class="search-results-list"></div>
        </div>

        <div class="user-icon">
            <a href="perfil.php">
                <img src="<?php echo htmlspecialchars($user_photo ?? 'images/profile.png'); ?>" alt="Perfil" height="90" style="border-radius: 50%; object-fit: cover; width: 90px;">
            </a>
        </div>
    </header>

    <main class="main-content">
        
        <section class="latest-collections">
            <h2>Minhas coleções:</h2>

            <?php if ($result->num_rows > 0): ?>
                
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="collection">
                        <a href="colecao.php?id=<?php echo $row['id']; ?>">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </a>
                        
                        <p>
                            <?php 
                            $desc = htmlspecialchars($row['description']);
                            echo (strlen($desc) > 150) ? substr($desc, 0, 150) . '...' : $desc; 
                            ?>
                        </p>
                        
                        <p style="font-size: 0.8em; color: #666; margin-top: 5px;">
                            Criado em: <?php echo date('d/m/Y', strtotime($row['created_date'])); ?>
                        </p>
                    </div>
                <?php endwhile; ?>

            <?php else: ?>
                <div style="padding: 20px; background-color: #f8f9fa; border-radius: 8px; text-align: center;">
                    <p>Ainda não tens nenhuma coleção criada.</p>
                    <p>Clica no botão ao lado para começares!</p>
                </div>
            <?php endif; ?>

        </section>

        <aside class="sidebar">
            <button class="btn-primary" onclick="window.location.href='add_colecao.php'">Adicionar coleção</button>
            <button class="btn-primary" style="margin-top: 3px;" onclick="window.location.href='eventos.php'">Ver todos os eventos</button>
            
            <div style="margin-top: 5px;">
            <button class="btn-secondary" onclick="window.location.href='php/export_collections.php'" style="width:100%; margin-bottom: 5px;"">
                Exportar CSV
            </button>
            
            <form action="php/import_collections.php" method="POST" enctype="multipart/form-data" id="import-form"">
                <input type="file" name="csv_file" id="csv-input" accept=".csv" style="display: none;" onchange="document.getElementById('import-form').submit()">
                <button type="button" class="btn-secondary" onclick="document.getElementById('csv-input').click()" style="width: 100%;">
                    Importar CSV
                </button>
            </form>
            </div>        
        </aside>
    </main>

    <footer class="bottom-bar">
        <a href="desenvolvedores.php">DESENVOLVEDORES</a>
    </footer>

</body>
</html>

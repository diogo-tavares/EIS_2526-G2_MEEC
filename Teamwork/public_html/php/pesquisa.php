<?php
session_start();
require_once 'db.php';
require_once 'auth.php';

// Limpar buffers para garantir JSON limpo
ob_clean(); 

// Receber o termo de pesquisa
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($q) < 2) {
    echo json_encode([]); 
    exit;
}

$term = "%" . $q . "%";
$results = [];

// Query Otimizada:
// 1. Procura Eventos
// 2. Procura Coleções (por nome/descrição)
// 3. Procura Itens
// 4. Procura Coleções (por TAG) -> JOIN para obter os dados da coleção
// O 'UNION' remove automaticamente as coleções duplicadas se aparecerem em mais que uma pesquisa.

$sql = "
    (SELECT id, name as titulo, 'evento' as tipo, description as descr FROM events 
     WHERE name LIKE ? OR description LIKE ?)
    UNION
    (SELECT id, title as titulo, 'colecao' as tipo, description as descr FROM collections 
     WHERE title LIKE ? OR description LIKE ?)
    UNION
    (SELECT id, name as titulo, 'item' as tipo, '' as descr FROM items 
     WHERE name LIKE ?)
    UNION
    (SELECT c.id, c.title as titulo, 'colecao' as tipo, c.description as descr 
     FROM collections c
     INNER JOIN collection_tags ct ON c.id = ct.collection_id
     WHERE ct.tag_name LIKE ?)
    LIMIT 10
";

if ($stmt = $conn->prepare($sql)) {
    // Parâmetros: 
    // 1,2: Eventos (name, desc)
    // 3,4: Coleções (title, desc)
    // 5: Itens (name)
    // 6: Tags (tag_name)
    $stmt->bind_param("ssssss", $term, $term, $term, $term, $term, $term);
    
    if ($stmt->execute()) {
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            // Construir o link correto
            $link = "#";
            if ($row['tipo'] === 'evento') {
                $link = "evento.php?id=" . $row['id'];
            } elseif ($row['tipo'] === 'colecao') {
                $link = "colecao.php?id=" . $row['id']; 
            } elseif ($row['tipo'] === 'item') {
                $link = "item.php?id=" . $row['id'];
            }

            $results[] = [
                'id' => $row['id'],
                'titulo' => $row['titulo'],
                'tipo' => $row['tipo'], // Será sempre 'evento', 'colecao' ou 'item'
                'link' => $link
            ];
        }
    }
}

// Devolver JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($results);
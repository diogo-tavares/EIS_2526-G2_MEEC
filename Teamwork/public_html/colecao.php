<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hub de Coleções</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/colecao.js" defer></script>
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
        <!-- Secção de detalhes da coleção -->
        <section class="collection-details">
        <h2>Coleção</h2>

            <div class="collection-info">
                <p><strong>Nome: </strong>Nome da coleção</p>
                <p><strong>Data de criação: </strong>01/01/2025</p>
                <p><strong>Tags: </strong>cartas, raro</p>
                <p><strong>Número de itens: </strong>10</p>
                <p><strong>Descrição: </strong>Coleção dedicada às cartas raras de Pokémon da primeira geração.</p>
            </div>
            <!-- Secção para mostrar itens da coleção -->
            <div class="collection-items">
                <h3>Itens:</h3>
                <div class="item-gallery">
                    <a href="item.html"><img src="images/1.png" alt="Item 1"></a>
                    <a href="item.html"><img src="images/2.png" alt="Item 2"></a>
                    <a href="item.html"><img src="images/3.png" alt="Item 3"></a>
                    <a href="item.html"><img src="images/458italia.png" alt="Item 4"></a>
                    <a href="item.html"><img src="images/amg_f1_w13.jpg" alt="Item 5"></a>
                    <a href="item.html"><img src="images/amg_gt-r.png" alt="Item 6"></a>
                    <a href="item.html"><img src="images/amg_gt3.png" alt="Item 7"></a>
                </div>
            </div>
            
            <!-- Secção para mostrar eventos da coleção -->
            <div class="collection-events">
                
                <div class="events-group">
                    <!-- Começando pelo evento mais próximo até ao mais longe -->
                    <h3>Eventos Futuros:</h3>
                    
                    <div class="mini-event-card">
                        <h4>Torneio Regional de Pokémon</h4>
                        <p>📅 20 Dez 2025 • 10:00</p>
                        <p>📍 Lisboa, Parque das Nações</p>
                        <a href="evento.html">Ver detalhes →</a>
                    </div>
                    
                    <div class="mini-event-card">
                        <h4>Encontro de Colecionadores</h4>
                        <p>📅 15 Jan 2026 • 14:30</p>
                        <p>📍 Porto, Casa da Música</p>
                        <a href="evento.html">Ver detalhes →</a>
                    </div>
                </div>

                <div>
                    <!-- Começando pelo evento que aconteceu há menos tempo até ao que aconteceu há mais -->
                    <h3>Eventos Passados:</h3>
                                        
                    <div class="mini-event-card past-event">
                        <h4>Lançamento de nova carta Pokemon</h4>
                        <p>📅 21 Nov 2025 • 12:00</p>
                        <p>📍 Coimbra, Estádio Cidade</p>
                        <p class="event-action">Registar presença e classificar evento</p>
                        <a href="evento.html">Ver detalhes →</a>
                    </div>
                    
                    <div class="mini-event-card past-event">
                        <h4>Lançamento da Edição Especial</h4>
                        <p>📅 01 Nov 2025 • 09:00</p>
                        <p>📍 Online</p>
                        <p class="event-meta-info"><strong>A tua presença:</strong> Sim | <strong>Classificação:</strong> ⭐⭐⭐⭐⭐</p>
                        <a href="evento.html">Ver detalhes →</a>
                    </div>
                    
                </div>
                
            </div>
        </section>

        <!-- Secção lateral direita (botões de ação) -->
        <aside class="sidebar">
            <button class="btn-primary" onclick="window.location.href='add_item.html'">Adicionar item à coleção</button>
            <button class="btn-primary" onclick="window.location.href='editar_colecao.html'">Editar coleção</button>
            <button class="btn-primary" id="delete-item-btn">Eliminar coleção</button>
        </aside>
    </main>

    <!-- Barra inferior -->
    <footer class="bottom-bar">
        <a href="desenvolvedores.html">DESENVOLVEDORES</a>
    </footer>
    
    <!-- Pop-up -->
    <div id="confirm-popup" class="popup-overlay">
        <div class="popup-box">
            <h3>Tem a certeza que deseja eliminar este item?</h3>
            <div class="popup-buttons">
                <button id="confirm-yes" class="btn-secondary">Sim</button>
                <button id="confirm-no" class="btn-secondary">Não</button>
            </div>
        </div>
    </div>
    
    
    <!-- Pop up -->
    <div id="event-modal" class="modal-bg">
    <div class="modal-box">
        <h3>Registar presença e classificação</h3>

        <label><strong>Presença:</strong></label>
        <select id="presence-select">
            <option value="">Selecione...</option>
            <option value="sim">Sim</option>
            <option value="nao">Não</option>
        </select>

        <label><strong>Classificação (1-5):</strong></label>
        <select id="rating-select">
            <option value="">Selecione...</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
        </select>

        <div class="modal-buttons">
            <button id="confirm-modal" class="btn-secondary">Confirmar</button>
            <button id="cancel-modal" class="btn-secondary">Cancelar</button>
        </div>
    </div>
    </div>

</body>
</html>

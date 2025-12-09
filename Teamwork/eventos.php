<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hub de Coleções</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/eventos.js" defer></script>
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
    <section class="latest-collections">
        
        <!-- Começando pelo evento mais próximo até ao mais longe -->
        <h2>Eventos futuros:</h2>

        <div class="mini-event-card">
            <h4>Evento 1</h4>
            <p>Um grande encontro de entusiastas.</p>
            <p>📅 01 Jan 2026 • 10:00</p>
            <p>📍 Lisboa, Altice Arena</p>
            <a href="evento.html">Ver detalhes →</a>
        </div>

        <div class="mini-event-card">
            <h4>Evento 2</h4>
            <p>Workshop de conservação de itens.</p>
            <p>📅 01 Fev 2026 • 15:30</p>
            <p>📍 Porto, Exponor</p>
            <a href="evento.html">Ver detalhes →</a>
        </div>

        <div class="mini-event-card">
            <h4>Evento 3</h4>
            <p>Troca de cartas raras.</p>
            <p>📅 21 Fev 2026 • 09:00</p>
            <p>📍 Coimbra, Estádio Cidade</p>
            <a href="evento.html">Ver detalhes →</a>
        </div>
        
        <!-- Começando pelo evento que aconteceu há menos tempo até ao que aconteceu há mais -->
        <h2 class="event-section-title">Eventos passados:</h2>

        <div class="mini-event-card past-event">
            <h4>Evento 4</h4>
            <p>Descrição do evento 5</p>
            <p>📅 12 Nov 2025 • 18:00</p>
            <p>📍 Braga, Altice Forum</p>
            <p class="event-action">Registar presença e classificar evento</p>       
            <a href="evento.html">Ver detalhes →</a>
        </div>
        
        <div class="mini-event-card past-event">
            <h4>Evento 5</h4>
            <p>Descrição do evento 4</p>
            <p>📅 21 Set 2025 • 14:00</p>
            <p>📍 Online</p>
            <p class="event-meta-info"><strong>Presença:</strong> Nao | <strong>Classificação:</strong> ---</p>
            <a href="evento.html">Ver detalhes →</a>
        </div>
       
    </section>

    <!-- Seção lateral direita -->
    <aside class="sidebar">
        <button class="btn-primary" onclick="window.location.href='add_evento.html'">Adicionar evento</button>
    </aside>
    </main>

    <!-- Barra inferior -->
    <footer class="bottom-bar">
        <a href="desenvolvedores.html">DESENVOLVEDORES</a>
    </footer>
    
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

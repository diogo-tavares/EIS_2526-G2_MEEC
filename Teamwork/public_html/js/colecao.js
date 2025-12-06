document.addEventListener("DOMContentLoaded", () => {

    const deleteBtn = document.getElementById("delete-item-btn");
    const popup = document.getElementById("confirm-popup");
    const yesBtn = document.getElementById("confirm-yes");
    const noBtn = document.getElementById("confirm-no");

    // Abre o popup quando clicas em "Eliminar item"
    deleteBtn.addEventListener("click", () => {
        popup.style.display = "flex";
    });

    // Clica em "Sim" → elimina e redireciona
    yesBtn.addEventListener("click", () => {
        window.location.href = "minhas_colecoes.html";
    });

    // Clica em "Não" → fecha o popup
    noBtn.addEventListener("click", () => {
        popup.style.display = "none";
    });
    
    
    const modal = document.getElementById("event-modal");
    const modalConfirm = document.getElementById("confirm-modal");
    const modalCancel = document.getElementById("cancel-modal");
    const presenceSelect = document.getElementById("presence-select");
    const ratingSelect = document.getElementById("rating-select");
    
    let currentActionP = null; // guarda qual <p> foi clicado
    
        // Abrir modal ao clicar no texto (seja o link azul .event-action OU o texto já classificado .event-meta-info)
    document.querySelectorAll(".event-action, .event-meta-info").forEach(p => {
        p.addEventListener("click", () => {
            currentActionP = p;
            modal.style.display = "flex";
            
            // Reset campos do modal (para permitir refazer a classificação de raiz)
            presenceSelect.value = "";
            ratingSelect.value = "";
            ratingSelect.disabled = true; 
        });
    });

    // Habilitar/desabilitar classificação com base na presença
    presenceSelect.addEventListener("change", () => {
        if (presenceSelect.value === "sim") {
            ratingSelect.disabled = false;
        } else {
            ratingSelect.disabled = true;
            ratingSelect.value = ""; 
        }
    });

    // Confirmar
    modalConfirm.addEventListener("click", () => {
        const presence = presenceSelect.value;
        const rating = ratingSelect.value; 

        // Validações
        if (!presence) {
            alert("Por favor, selecione a presença!");
            return;
        }

        if (presence === "sim" && !rating) {
            alert("Por favor, selecione a classificação!");
            return;
        }

        // Lógica de Exibição
        let ratingDisplay;

        if (presence === "nao") {
            ratingDisplay = "---";
        } else {
            // Converte número em estrelas
            ratingDisplay = "⭐".repeat(parseInt(rating));
        }

        const presenceFormatted = presence.charAt(0).toUpperCase() + presence.slice(1);

        // 1. Remove a classe antiga (caso seja a primeira vez a clicar)
        currentActionP.classList.remove("event-action"); 
        
        // 2. Garante que tem a classe de metadados
        currentActionP.classList.add("event-meta-info");

        // 3. Atualiza o HTML
        currentActionP.innerHTML = `<strong>Presença:</strong> ${presenceFormatted} | <strong>Classificação:</strong> ${ratingDisplay}`;

        modal.style.display = "none"; 
    });

    // Cancelar
    modalCancel.addEventListener("click", () => {
        modal.style.display = "none";
    });  
    
    
});

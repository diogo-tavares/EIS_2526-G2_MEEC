document.addEventListener("DOMContentLoaded", () => {

    // --- LÓGICA DE ELIMINAR COLEÇÃO ---
    const deleteBtn = document.getElementById("delete-item-btn");
    const popup = document.getElementById("confirm-popup");
    const noBtn = document.getElementById("confirm-no");

    // Abre o popup quando clicas em "Eliminar coleção"
    if (deleteBtn) {
        deleteBtn.addEventListener("click", () => {
            popup.style.display = "flex";
        });
    }

    // Clica em "Não" → fecha o popup
    if (noBtn) {
        noBtn.addEventListener("click", () => {
            popup.style.display = "none";
        });
    }
    
    // --- LÓGICA DE CLASSIFICAR EVENTOS (IGUAL À PÁGINA EVENTOS) ---
    const modal = document.getElementById("event-modal");
    const modalConfirm = document.getElementById("confirm-modal");
    const modalCancel = document.getElementById("cancel-modal");
    const presenceSelect = document.getElementById("presence-select");
    const ratingSelect = document.getElementById("rating-select");
    const hiddenIdInput = document.getElementById("modal-event-id"); // Input escondido para guardar o ID
    
    // 1. Abrir Modal ao clicar no texto
    document.querySelectorAll(".event-action, .event-meta-info").forEach(p => {
        p.addEventListener("click", () => {
            // Ler o ID do evento que está no HTML (data-id)
            const eventId = p.getAttribute("data-id");
            hiddenIdInput.value = eventId;
            
            modal.style.display = "flex";
            
            // Resetar campos para limpo
            presenceSelect.value = "";
            ratingSelect.value = "";
            ratingSelect.disabled = true; 
        });
    });

    // 2. Lógica visual (Habilitar estrelas apenas se "Sim")
    if (presenceSelect) {
        presenceSelect.addEventListener("change", () => {
            // Aceita "1" ou "sim" para compatibilidade
            if (presenceSelect.value === "1" || presenceSelect.value === "sim") {
                ratingSelect.disabled = false;
            } else {
                ratingSelect.disabled = true;
                ratingSelect.value = ""; 
            }
        });
    }

    // 3. Confirmar e Gravar na Base de Dados (AJAX)
    if (modalConfirm) {
        modalConfirm.addEventListener("click", () => {
            const presence = presenceSelect.value;
            const rating = ratingSelect.value;
            const eventId = hiddenIdInput.value;

            // Validações
            if (presence === "") {
                alert("Por favor, selecione a presença!");
                return;
            }
            if ((presence === "1" || presence === "sim") && rating === "") {
                alert("Por favor, selecione a classificação!");
                return;
            }

            // Converter presença para 0 ou 1 (caso o value seja "sim"/"nao")
            // Se o teu HTML usa value="1", isto mantém o 1. Se usa "sim", converte.
            const presenceValue = (presence === "1" || presence === "sim") ? 1 : 0;

            // Enviar pedido ao servidor
            fetch('php/update_rating.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    event_id: eventId,
                    presence: presenceValue,
                    rating: rating
                })
            })
            .then(response => {
                if (!response.ok) throw new Error("Erro HTTP: " + response.status);
                return response.json();
            })
            .then(data => {
                if (data.sucesso) {
                    // Recarregar a página para mostrar os dados atualizados vindos do PHP
                    window.location.reload();
                } else {
                    alert("Erro ao gravar: " + (data.erro || "Erro desconhecido"));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert("Erro de comunicação com o servidor.");
            });
        });
    }

    // 4. Cancelar Modal
    if (modalCancel) {
        modalCancel.addEventListener("click", () => {
            modal.style.display = "none";
        });
    }  
});
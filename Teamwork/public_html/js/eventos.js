document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("event-modal");
    const modalConfirm = document.getElementById("confirm-modal");
    const modalCancel = document.getElementById("cancel-modal");
    const presenceSelect = document.getElementById("presence-select");
    const ratingSelect = document.getElementById("rating-select");
    const hiddenIdInput = document.getElementById("modal-event-id"); // O tal input escondido

    // 1. ABRIR MODAL
    // Adiciona o evento de clique a todos os links "Registar presença..."
    document.querySelectorAll(".event-action, .event-meta-info").forEach(p => {
        p.addEventListener("click", () => {
            // Ler o ID que pusemos no HTML PHP (data-id)
            const eventId = p.getAttribute("data-id");
            
            // Guardar esse ID no input escondido do modal
            hiddenIdInput.value = eventId;
            
            // Mostrar o modal
            modal.style.display = "flex";
            
            // Resetar campos
            presenceSelect.value = "";
            ratingSelect.value = "";
            ratingSelect.disabled = true; 
        });
    });

    // 2. Lógica visual (Habilitar estrelas só se for "Sim")
    presenceSelect.addEventListener("change", () => {
        if (presenceSelect.value === "1") { // Nota: "1" é o valor que definimos no HTML do eventos.php
            ratingSelect.disabled = false;
        } else {
            ratingSelect.disabled = true;
            ratingSelect.value = ""; 
        }
    });

    // 3. CONFIRMAR E GRAVAR (A parte nova)
    modalConfirm.addEventListener("click", () => {
        const presence = presenceSelect.value;
        const rating = ratingSelect.value;
        const eventId = hiddenIdInput.value;

        if (presence === "") {
            alert("Por favor, selecione a presença!");
            return;
        }
        if (presence === "1" && rating === "") {
            alert("Por favor, selecione a classificação!");
            return;
        }

        // Enviar para o PHP via AJAX (Fetch)
        fetch('php/update_rating.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                event_id: eventId,
                presence: presence,
                rating: rating
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                // Se gravou com sucesso, recarregamos a página
                // Ao recarregar, o PHP do eventos.php vai ler os dados novos da BD e mostrar atualizado!
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

    // 4. CANCELAR
    modalCancel.addEventListener("click", () => {
        modal.style.display = "none";
    });
});
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
        window.location.href = `php/delete_event.php?id=${currentEventId}`;
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
    const hiddenIdInput = document.getElementById("modal-event-id");

    // 1. Abrir Modal
    // Seleciona tanto o link "Registar..." (.event-action) como o texto de info (.event-meta-info)
    document.querySelectorAll(".event-action, .event-meta-info").forEach(p => {
        p.addEventListener("click", () => {
            const eventId = p.getAttribute("data-id");
            hiddenIdInput.value = eventId;
            
            modal.style.display = "flex";
            
            // Resetar campos
            presenceSelect.value = "";
            ratingSelect.value = "";
            ratingSelect.disabled = true; 
        });
    });

    // 2. Lógica visual (Habilitar estrelas)
    presenceSelect.addEventListener("change", () => {
        // Aceita "1" ou "sim" para compatibilidade
        if (presenceSelect.value === "1" || presenceSelect.value === "sim") {
            ratingSelect.disabled = false;
        } else {
            ratingSelect.disabled = true;
            ratingSelect.value = ""; 
        }
    });

    // 3. Confirmar e Gravar
    if (modalConfirm) {
        modalConfirm.addEventListener("click", () => {
            const presence = presenceSelect.value;
            const rating = ratingSelect.value;
            const eventId = hiddenIdInput.value;

            if (presence === "") {
                alert("Por favor, selecione a presença!");
                return;
            }
            if ((presence === "1" || presence === "sim") && rating === "") {
                alert("Por favor, selecione a classificação!");
                return;
            }

            // Converter para 0 ou 1 antes de enviar, por segurança
            const presenceValue = (presence === "1" || presence === "sim") ? 1 : 0;

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
                    window.location.reload(); // Recarrega a página para ver as alterações
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
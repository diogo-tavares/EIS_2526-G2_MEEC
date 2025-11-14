document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("event-modal");
    const modalConfirm = document.getElementById("confirm-modal");
    const modalCancel = document.getElementById("cancel-modal");
    const presenceSelect = document.getElementById("presence-select");
    const ratingSelect = document.getElementById("rating-select");

    let currentActionP = null; // guarda qual <p> foi clicado

    // Abrir modal ao clicar no texto
    document.querySelectorAll(".event-action").forEach(p => {
        p.addEventListener("click", () => {
            currentActionP = p;
            modal.style.display = "flex";
            
            // Reset campos do modal
            presenceSelect.value = "";
            ratingSelect.value = "";
            ratingSelect.disabled = true; // inicialmente desabilitado
        });
    });

    // Habilitar/desabilitar classificação com base na presença
    presenceSelect.addEventListener("change", () => {
        if (presenceSelect.value === "sim") {
            ratingSelect.disabled = false;
        } else {
            ratingSelect.disabled = true;
            ratingSelect.value = ""; // limpa caso estava preenchido
        }
    });

    // Confirmar
    modalConfirm.addEventListener("click", () => {
        const presence = presenceSelect.value;
        let rating = ratingSelect.value;

        if (!presence) {
            alert("Por favor, selecione a presença!");
            return;
        }

        if (presence === "sim" && !rating) {
            alert("Por favor, selecione a classificação!");
            return;
        }

        if (presence === "nao") rating = "---"; // caso não tenha ido

        // Substitui o <p> pelo texto final
        currentActionP.classList.remove("event-action"); // remove clicável
        currentActionP.innerHTML = `
            Presença: ${presence} <br>
            Classificação: ${rating}
        `;

        modal.style.display = "none"; // fecha modal
    });

    // Cancelar
    modalCancel.addEventListener("click", () => {
        modal.style.display = "none";
    });
});

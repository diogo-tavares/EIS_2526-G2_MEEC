document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("edit-event-form");
    const cancelBtn = document.getElementById("cancel-btn");

    // Botão Confirmar
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const name = document.getElementById("event-name").value.trim();
        const location = document.getElementById("event-location").value.trim();
        const date = document.getElementById("event-date").value.trim();
        const time = document.getElementById("event-time").value.trim();
        const price = document.getElementById("event-price").value.trim();
        const description = document.getElementById("event-description").value.trim();

        // Verifica se todos os campos estão preenchidos
        if (!name || !location || !date || !time || !price || !description) {
            alert("Por favor, preencha todos os campos antes de confirmar!");
            return;
        }

        // Tudo preenchido → redireciona
        window.location.href = "eventos.html";
    });

    // Botão Cancelar
    cancelBtn.addEventListener("click", function () {
        window.location.href = "eventos.html";
    });
});

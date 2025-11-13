document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("add-collection-form");
    const cancelBtn = document.getElementById("cancel-btn");

    // Botão Confirmar
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const name = document.getElementById("collection-name").value.trim();
        const date = document.getElementById("collection-date").value.trim();
        const type = document.getElementById("collection-type").value.trim();
        const description = document.getElementById("collection-description").value.trim();

        // Verifica se todos os campos estão preenchidos
        if (!name || !date || !type || !description) {
            alert("Por favor, preencha todos os campos antes de confirmar!");
            return;
        }

        // Tudo preenchido → redireciona
        window.location.href = "minhas_colecoes.html";
    });

    // Botão Cancelar
    cancelBtn.addEventListener("click", function () {
        window.location.href = "minhas_colecoes.html";
    });
});

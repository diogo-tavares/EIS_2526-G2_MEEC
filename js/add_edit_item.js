document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("add-item-form");
    const cancelBtn = document.getElementById("cancel-btn");

    // Botão confirmar
    form.addEventListener("submit", function(e) {
        e.preventDefault();

        const fields = form.querySelectorAll("input, select, textarea");
        let allFilled = true;

        fields.forEach(f => {
            if (!f.value) allFilled = false;
        });

        if (!allFilled) {
            alert("Por favor, preencha todos os campos!");
            return;
        }

        // Redireciona para "minhas_colecoes.html"
        window.location.href = "minhas_colecoes.html";
    });

    // Botão cancelar
    cancelBtn.addEventListener("click", function() {
        window.location.href = "minhas_colecoes.html";
    });
});

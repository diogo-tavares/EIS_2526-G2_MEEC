document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("add-collection-form");
    const cancelBtn = document.getElementById("cancel-btn");

    // Botão Confirmar
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const name = document.getElementById("collection-name").value.trim();
        const date = document.getElementById("collection-date").value.trim();
        const description = document.getElementById("collection-description").value.trim();
        
        // Lê os 5 campos
        const t1 = document.getElementById("tag-1").value.trim();
        const t2 = document.getElementById("tag-2").value.trim();
        const t3 = document.getElementById("tag-3").value.trim();
        const t4 = document.getElementById("tag-4").value.trim();
        const t5 = document.getElementById("tag-5").value.trim();
        
        // Junta tudo e remove vazios
        const tags = [t1, t2, t3, t4, t5].filter(tag => tag !== "");
        
        // Verifica se todos os campos estão preenchidos
        if (!name || !date || !description || tags.length === 0) {
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

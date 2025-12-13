document.addEventListener("DOMContentLoaded", function () {
    
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


    const uploadBtn = document.getElementById("upload-btn");
    const imageInput = document.getElementById("item-upload");
    const previewImg = document.querySelector(".edit-item-img img");

    // Clicar no botão abre o input de imagem
    uploadBtn.addEventListener("click", (e) => {
        e.preventDefault();
        imageInput.click();
    });

    // Quando o utilizador escolhe uma imagem, substitui a pré-visualização
    imageInput.addEventListener("change", () => {
        const file = imageInput.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = (event) => {
                previewImg.src = event.target.result; // Atualiza a imagem
            };

            reader.readAsDataURL(file);
        }
    });

});

document.addEventListener("DOMContentLoaded", function() {
    // Procura os elementos pelos IDs (têm de ser iguais nos dois PHPs)
    const imageInput = document.getElementById("item-image-input");
    const previewImg = document.getElementById("item-preview-img");

    // Só corre se os elementos existirem na página
    if (imageInput && previewImg) {
        
        // Deteta quando escolhes um ficheiro
        imageInput.addEventListener("change", function () {
            const file = imageInput.files[0];

            if (file) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    previewImg.src = e.target.result; // Muda a imagem
                    previewImg.style.display = "block"; // Garante que se vê
                };

                reader.readAsDataURL(file);
            }
        });
    }
});

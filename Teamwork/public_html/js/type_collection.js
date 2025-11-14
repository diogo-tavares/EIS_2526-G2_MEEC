document.addEventListener("DOMContentLoaded", function () {
       
    // Lê o parâmetro "tipo" da URL
    const params = new URLSearchParams(window.location.search);
    const tipo = params.get("tipo");

    if (tipo) {
        // Seleciona todos os links da barra
        const links = document.querySelectorAll(".category-bar a");

        links.forEach(link => {
            // Verifica se o link contém o tipo na URL
            if (link.href.includes("tipo=" + tipo)) {
                link.classList.add("active");
            }
        });

        // Atualizar o título "Coleção do tipo X"
        const titulo = document.querySelector("h2");
        if (titulo) {
            titulo.textContent = "Coleção do tipo " + tipo.charAt(0).toUpperCase() + tipo.slice(1);
        }
    }
    
    
});



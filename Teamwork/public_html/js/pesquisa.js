document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("live-search-input");
    const resultsContainer = document.getElementById("search-results");

    if (!searchInput || !resultsContainer) return;

    // Escutar o que o utilizador escreve
    searchInput.addEventListener("input", function() {
        const query = this.value.trim();

        if (query.length < 2) {
            resultsContainer.style.display = "none";
            resultsContainer.innerHTML = "";
            return;
        }

        // Fazer pedido ao PHP
        fetch(`php/pesquisa.php?q=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error("Erro na rede");
                }
                return response.text(); // Primeiro lemos como texto para ver se é JSON válido
            })
            .then(text => {
                try {
                    const data = JSON.parse(text); // Tentamos converter para JSON
                    
                    resultsContainer.innerHTML = ""; // Limpar anteriores

                    if (data.length > 0) {
                        data.forEach(item => {
                            const link = document.createElement("a");
                            link.href = item.link;
                            link.className = "search-result-item";

                            // Definir a classe da cor da tag
                            let tagClass = "tag-evento";
                            if (item.tipo === "colecao") tagClass = "tag-colecao";
                            if (item.tipo === "item") tagClass = "tag-item";
                            if (item.tipo === "tag") tagClass = "tag-tag";

                            // Formatação bonita
                            link.innerHTML = `
                                <span class="search-tag ${tagClass}">${item.tipo}</span>
                                <span>${item.titulo}</span>
                            `;

                            resultsContainer.appendChild(link);
                        });
                        resultsContainer.style.display = "block";
                    } else {
                        resultsContainer.innerHTML = '<div class="search-result-item" style="cursor: default; color: #777;">Sem resultados</div>';
                        resultsContainer.style.display = "block";
                    }

                } catch (e) {
                    console.error("Erro ao processar JSON:", e);
                    console.log("Resposta do servidor:", text); // Vais ver o erro PHP aqui se houver
                }
            })
            .catch(err => console.error("Erro na pesquisa:", err));
    });

    // Fechar a lista se clicar fora
    document.addEventListener("click", (e) => {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.style.display = "none";
        }
    });
});
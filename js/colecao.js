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
        window.location.href = "minhas_colecoes.html";
    });

    // Clica em "Não" → fecha o popup
    noBtn.addEventListener("click", () => {
        popup.style.display = "none";
    });
});

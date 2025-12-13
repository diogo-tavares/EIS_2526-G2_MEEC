document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("change-email-form");
    const cancelBtn = document.getElementById("cancel-btn");

    // Confirmar alterações usando validação nativa do HTML
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const oldEmail = document.getElementById("old-email").value.trim();
        const newEmail = document.getElementById("new-email").value.trim();

        // Guarda o novo email no localStorage
        localStorage.setItem("userEmail", newEmail);

        // Redireciona para a página de perfil
        window.location.href = "perfil.php";
    });

    // Desfazer alterações
    cancelBtn.addEventListener("click", function () {
        window.location.href = "perfil.php";
    });
});

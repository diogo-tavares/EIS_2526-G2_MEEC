document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("change-pass-form");
    const cancelBtn = document.getElementById("cancel-btn");

    // Confirmar alterações usando validação nativa do HTML
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const oldPass = document.getElementById("old-pass").value.trim();
        const newPass = document.getElementById("new-pass").value.trim();

        // Guarda a nova palavra-passe no localStorage
        localStorage.setItem("userPassword", newPass);

        // Redireciona para a página de perfil
        window.location.href = "perfil.html";
    });

    // Desfazer alterações
    cancelBtn.addEventListener("click", function () {
        window.location.href = "perfil.html";
    });
});

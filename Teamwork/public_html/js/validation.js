document.addEventListener("DOMContentLoaded", function () {
    // Validação do formulário de login
    const loginForm = document.getElementById("login-form");
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value.trim();

            if (!email || !password) {
                e.preventDefault();
                alert("Por favor, preencha todos os campos obrigatórios!");
            }
        });
    }

    // Validação do formulário de registo
    const registerForm = document.getElementById("register-form");
    if (registerForm) {
        registerForm.addEventListener("submit", function (e) {
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value.trim();
            const confirmPassword = document.getElementById("confirm-password").value.trim();

            if (!email || !password || !confirmPassword) {
                e.preventDefault();
                alert("Por favor, preencha todos os campos obrigatórios!");
            } else if (password !== confirmPassword) {
                e.preventDefault();
                alert("As palavras-passe não coincidem!");
            }
        });
    }
});

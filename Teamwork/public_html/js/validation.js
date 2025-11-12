document.addEventListener("DOMContentLoaded", function () {
    // Validação do formulário de login
    const loginForm = document.getElementById("login-form");
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value.trim();

            if (!email || !password) {
                alert("Por favor, preencha todos os campos obrigatórios!");
            } else {
                // Login bem-sucedido → redireciona
                window.location.href = "homepage.html";
            }
        });
    }

    // Validação do formulário de registo
    const registerForm = document.getElementById("register-form");
    if (registerForm) {
        registerForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value.trim();
            const confirmPassword = document.getElementById("confirm-password").value.trim();

            if (!email || !password || !confirmPassword) {
                alert("Por favor, preencha todos os campos obrigatórios!");
            } else if (password !== confirmPassword) {
                alert("As palavras-passe não coincidem!");
            } else {
                // Registo bem-sucedido → redireciona
                window.location.href = "homepage.html";
            }
        });
    }
});

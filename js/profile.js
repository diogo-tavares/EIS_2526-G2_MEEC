document.addEventListener("DOMContentLoaded", function () {
    // Selecionar elementos da página
    const profileImg = document.getElementById("profile-img");
    const birthdateField = document.getElementById("birthdate-display");
    const emailField = document.getElementById("email-display");

    const editProfileBtn = document.getElementById("edit-profile-btn");
    const changeEmailBtn = document.getElementById("change-email-btn");
    const changePassBtn = document.getElementById("change-pass-btn");

    // Obter valores do localStorage, caso tenham sido definidos
    const storedImg = localStorage.getItem("userProfileImg");
    const storedBirthdate = localStorage.getItem("userBirthdate");
    const storedEmail = localStorage.getItem("userEmail");

    // Atualizar imagem do perfil apenas se houver um valor armazenado
    if (storedImg && profileImg) {
        profileImg.src = storedImg;
    }

    // Atualizar data de nascimento apenas se houver um valor armazenado
    if (storedBirthdate && birthdateField) {
        birthdateField.textContent = storedBirthdate;
    }

    // Atualizar email apenas se houver um valor armazenado
    if (storedEmail && emailField) {
        emailField.textContent = storedEmail;
    }

    // Limpar os valores do localStorage após a utilização
    localStorage.removeItem("userProfileImg");
    localStorage.removeItem("userBirthdate");
    localStorage.removeItem("userEmail");

    // Redirecionar para a página de edição de perfil
    if (editProfileBtn) {
        editProfileBtn.addEventListener("click", function () {
            window.location.href = "editar_perfil.html";
        });
    }

    // Redirecionar para a página de alterar e-mail
    if (changeEmailBtn) {
        changeEmailBtn.addEventListener("click", function () {
            window.location.href = "mudar_mail.html";
        });
    }

    // Redirecionar para a página de alterar palavra-passe
    if (changePassBtn) {
        changePassBtn.addEventListener("click", function () {
            window.location.href = "mudar_pass.html";
        });
    }
});

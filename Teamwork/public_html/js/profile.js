document.addEventListener("DOMContentLoaded", function () {
    // Selecionar elementos da página
    const profileImg = document.getElementById("profile-img");
    const birthdateField = document.getElementById("birthdate-display");
    const editProfileBtn = document.getElementById("edit-profile-btn");

    // Obter valores do localStorage, caso tenham sido definidos
    const storedImg = localStorage.getItem("userProfileImg");
    const storedBirthdate = localStorage.getItem("userBirthdate");

    // Atualizar imagem do perfil apenas se houver um valor armazenado
    if (storedImg && profileImg) {
        profileImg.src = storedImg;
    }

    // Atualizar data de nascimento apenas se houver um valor armazenado
    if (storedBirthdate && birthdateField) {
        birthdateField.textContent = storedBirthdate;
    }

    // Limpar os valores do localStorage após a utilização
    // para que não persistam na próxima visita
    localStorage.removeItem("userProfileImg");
    localStorage.removeItem("userBirthdate");

    // Redirecionar para a página de edição de perfil ao clicar no botão
    if (editProfileBtn) {
        editProfileBtn.addEventListener("click", function () {
            window.location.href = "editar_perfil.html";
        });
    }
});

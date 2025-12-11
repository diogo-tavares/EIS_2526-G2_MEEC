document.addEventListener("DOMContentLoaded", function () {
    // Elementos do DOM
    const fileInput = document.getElementById("profile-upload");
    const uploadBtn = document.getElementById("upload-btn");
    const birthdateInput = document.getElementById("birthdate");
    const confirmBtn = document.getElementById("confirm-btn");
    const cancelBtn = document.getElementById("cancel-btn");
    const profileImg = document.querySelector(".edit-profile-img img");

    // Guardar estado inicial
    const initialBirthdate = birthdateInput.value;
    const initialImgSrc = profileImg.src;

    // Função para abrir seletor de arquivos
    if (uploadBtn && fileInput) {
        uploadBtn.addEventListener("click", function () {
            fileInput.click();
        });

        fileInput.addEventListener("change", function () {
            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    profileImg.src = e.target.result;
                };
                reader.readAsDataURL(fileInput.files[0]);
            }
        });
    }

    // Confirmar alterações
    if (confirmBtn) {
        confirmBtn.addEventListener("click", function (e) {
            e.preventDefault();

            const birthdateChanged = birthdateInput.value !== initialBirthdate;
            const imgChanged = profileImg.src !== initialImgSrc;

            if (birthdateChanged || imgChanged) {
                // Guardar alterações no localStorage
                if (birthdateChanged) {
                    localStorage.setItem("userBirthdate", birthdateInput.value);
                }
                if (imgChanged) {
                    localStorage.setItem("userProfileImg", profileImg.src);
                }

                // Redireciona para perfil.html
                window.location.href = "perfil.php";
            } else {
                alert("Nenhuma alteração foi feita!");
            }
        });
    }

    // Desfazer alterações
    if (cancelBtn) {
        cancelBtn.addEventListener("click", function () {
            window.location.href = "perfil.php";
        });
    }
});

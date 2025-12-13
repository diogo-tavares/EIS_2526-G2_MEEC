document.addEventListener('DOMContentLoaded', () => {
  const go = (id, target) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('click', (e) => {
      e.preventDefault();
      window.location.assign(target);
    });
  };

  go('edit-profile-btn',  'editar_perfil.php');
  go('change-email-btn',  'mudar_mail.php');
  go('change-pass-btn',   'mudar_pass.php');
});
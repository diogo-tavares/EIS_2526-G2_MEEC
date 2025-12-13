<?php
declare(strict_types=1);

/* ====== INÍCIO: PROCESSAMENTO (só em POST) ====== */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth.php'; // garante $_SESSION['user_id']
require_once 'php/get_profile_pic.php';

function back_here(string $q = ''): never {
  header('Location: editar_perfil.php' . ($q ? "?$q" : ''));
  exit();
}
function pick_upload(): ?array {
  foreach (['profile_image','photo','avatar','image','picture','file'] as $k) {
    if (!empty($_FILES[$k]) && isset($_FILES[$k]['error']) && $_FILES[$k]['error'] !== UPLOAD_ERR_NO_FILE) {
      return $_FILES[$k];
    }
  }
  return null;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'POST') {
  $user_id = (int)($_SESSION['user_id'] ?? 0);
  if ($user_id <= 0) back_here('err=nologin');

  $changed = false;

  /* (1) Atualizar data de nascimento (YYYY-MM-DD) se o campo existir */
  if (array_key_exists('birthdate', $_POST)) {
    $birth = trim((string)($_POST['birthdate'] ?? ''));
    if ($birth === '') {
      if ($stmt = $conn->prepare('UPDATE users SET birthdate = NULL WHERE id = ?')) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute(); $stmt->close();
      }
      $changed = true;
    } else {
      $d  = DateTime::createFromFormat('Y-m-d', $birth);
      $ok = $d && $d->format('Y-m-d') === $birth;
      if (!$ok) back_here('err=birth_fmt');

      $stmt = $conn->prepare('UPDATE users SET birthdate = ? WHERE id = ?');
      $stmt->bind_param('si', $birth, $user_id);
      $stmt->execute(); $stmt->close();
      $changed = true;
    }
  }

  /* (2) Upload da foto (se veio ficheiro) */
  $file = pick_upload();
  if ($file !== null) {
    if ($file['error'] !== UPLOAD_ERR_OK) back_here('err=upload');
    if (($file['size'] ?? 0) <= 0 || $file['size'] > 5*1024*1024) back_here('err=toolarge');

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']) ?: 'application/octet-stream';
    $okExt = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
    if (!isset($okExt[$mime])) back_here('err=type');

    $dirAbs = __DIR__ . '/images/users';
    if (!is_dir($dirAbs)) { @mkdir($dirAbs, 0775, true); }

    $ext   = $okExt[$mime];
    $name  = sprintf('uid_%d_%s_%s.%s', $user_id, date('Ymd_His'), bin2hex(random_bytes(4)), $ext);
    $abs   = $dirAbs . '/' . $name;
    $rel   = 'images/users/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $abs)) back_here('err=move');

    // foto antiga (para limpar se era nossa)
    $old = null;
    if ($stmt = $conn->prepare('SELECT photo_path FROM users WHERE id = ? LIMIT 1')) {
      $stmt->bind_param('i', $user_id);
      $stmt->execute(); $stmt->bind_result($old); $stmt->fetch(); $stmt->close();
    }

    // gravar nova
    $stmt = $conn->prepare('UPDATE users SET photo_path = ? WHERE id = ?');
    $stmt->bind_param('si', $rel, $user_id);
    $stmt->execute(); $stmt->close();

    $_SESSION['user_photo'] = $rel;

    // apagar antiga se estava dentro de images/users
    if (!empty($old)) {
      $oldAbs = __DIR__ . '/' . ltrim($old, '/');
      $base   = realpath($dirAbs);
      $oldR   = realpath($oldAbs);
      if ($oldR && $base && strpos($oldR, $base) === 0 && is_file($oldR)) { @unlink($oldR); }
    }

    $changed = true;
  }

  // Redirect final
  if ($changed) {
    header('Location: perfil.php?changed=profile');
    exit();
  } else {
    back_here('err=nothing');
  }
}

/* ====== FIM: PROCESSAMENTO; INÍCIO: PREPARAÇÃO DE DADOS PARA O HTML ====== */

// Buscar dados atuais do utilizador para preencher a página
$user_id = (int)($_SESSION['user_id'] ?? 0);
$photoPath = 'images/profile.png'; // fallback
$birthValue = '';

$headerPhoto = !empty($photoPath) ? $photoPath : 'images/profile.png';


if ($user_id > 0) {
  if ($stmt = $conn->prepare('SELECT photo_path, birthdate FROM users WHERE id = ? LIMIT 1')) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($dbPhoto, $dbBirth);
    if ($stmt->fetch()) {
      if (!empty($dbPhoto)) $photoPath = $dbPhoto;
      if (!empty($dbBirth)) $birthValue = $dbBirth; // já vem YYYY-MM-DD
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Perfil — Hub de Coleções</title>

  <!-- Mantém as tuas folhas de estilo -->
  <link rel="stylesheet" href="css/style.css?v=2">
  <style>
    .hidden-element { display:none; }
    .error-msg { color:#d00; margin-top:6px; font-size:.95rem; }
  </style>
</head>
<body>
  <!-- Barra superior (mantém a tua estrutura) -->
  <header class="top-bar-home">
    <div class="logo">
      <a href="homepage.php"><img src="images/logo.png" alt="Logo do Sistema"></a>
    </div>
    <div class="search-bar">
      <input type="text" placeholder="Pesquisar...">
      <button>🔍</button>
    </div>
    <div class="user-icon">
      <a href="perfil.php"><img src="<?php echo htmlspecialchars($user_photo); ?>" alt="Perfil" height="90" style="border-radius: 50%; object-fit: cover; width: 90px;"></a>
    </div>
  </header>

  <!-- Conteúdo principal -->
<main class="edit-profile-content">
  <h1>Editar perfil:</h1>

  <section class="edit-profile-container">
    <!-- Um ÚNICO form, mas com display:contents para não quebrar o layout -->
    <form action="editar_perfil.php" method="POST" enctype="multipart/form-data" style="display: contents;">

      <!-- Coluna ESQUERDA: imagem -->
      <div class="edit-profile-img">
  <img id="profile-preview"
       src="<?php echo htmlspecialchars($photoPath, ENT_QUOTES); ?>"
       alt="Foto de Perfil" width="180">

  <!-- Input de ficheiro: escondido fora do ecrã, mas NÃO display:none -->
  <input
    type="file"
    id="profile-upload"
    name="profile_image"
    accept="image/*"
    style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;pointer-events:none;">

  <!-- Label com aparência de botão, associado ao input -->
  <label for="profile-upload" class="btn-secondary" id="upload-btn" role="button" tabindex="0">
    Carregar nova imagem
  </label>

  <?php if (isset($_GET['err']) && in_array($_GET['err'], ['upload','toolarge','type','move'], true)): ?>
    <p class="error-msg">
      <?php
        $map = [
          'upload'   => 'Ocorreu um erro no envio do ficheiro.',
          'toolarge' => 'Ficheiro demasiado grande (máx. 5 MB).',
          'type'     => 'Formato não suportado. Usa JPG, PNG, WEBP ou GIF.',
          'move'     => 'Não foi possível guardar a imagem no servidor.'
        ];
        echo $map[$_GET['err']] ?? 'Falha no upload.';
      ?>
    </p>
  <?php endif; ?>
</div>

      <!-- Coluna DIREITA: data -->
      <div class="edit-profile-form">
        <label for="birthdate"><strong>Nova data de nascimento:</strong></label>
        <input type="date" id="birthdate" name="birthdate"
               value="<?php echo htmlspecialchars($birthValue ?? '', ENT_QUOTES); ?>">

        <?php if (isset($_GET['err']) && $_GET['err'] === 'birth_fmt'): ?>
          <p class="error-msg">Formato inválido. Usa AAAA-MM-DD.</p>
        <?php endif; ?>

        <div class="edit-profile-buttons">
          <!-- Só grava quando clicas aqui -->
          <button type="submit" class="btn-primary" id="confirm-btn">Confirmar</button>

          <button type="button" class="btn-primary" id="cancel-btn"
                  onclick="window.location.href='perfil.php'">
            Desfazer alterações e voltar atrás
          </button>
        </div>
      </div>

    </form>
  </section>
</main>


  <!-- Barra inferior -->
  <footer class="bottom-bar">
    <a href="desenvolvedores.html">DESENVOLVEDORES</a>
  </footer>

  <!-- JS mínimo para acionar o upload -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const uploadBtn   = document.getElementById('upload-btn');
      const uploadInput = document.getElementById('profile-upload');
      const submitImg   = document.getElementById('submit-img');

      if (uploadBtn && uploadInput && submitImg) {
        uploadBtn.addEventListener('click', () => uploadInput.click());
        uploadInput.addEventListener('change', () => {
          if (uploadInput.files && uploadInput.files.length > 0) {
            submitImg.click();
          }
        });
      }
    });
  </script>
</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const uploadInput = document.getElementById('profile-upload');
  const previewImg  = document.getElementById('profile-preview');

  if (uploadInput && previewImg) {
    uploadInput.addEventListener('change', () => {
      const file = uploadInput.files && uploadInput.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => { previewImg.src = e.target.result; };
      reader.readAsDataURL(file);
    });
  }
});
</script>


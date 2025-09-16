<?php
// public/forgot_password.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar contraseña</title>
</head>
<body>
  <h2>Recuperar contraseña</h2>
  <form id="forgotForm">
    <input type="email" name="email" placeholder="Tu email" required>
    <button type="submit">Enviar código</button>
  </form>

  <script>
  document.getElementById("forgotForm").addEventListener("submit", async (e) => {
      e.preventDefault();
      const email = e.target.email.value.trim();

      if (!email) {
          alert("Debes ingresar tu correo electrónico.");
          return;
      }

      try {
          let res = await fetch("../api/auth.php?action=requestPasswordReset", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ email })
          });

          let data = await res.json();
          alert(data.message);

          if (data.status === "success") {
              // Redirigir a la página de verificación
              window.location.href = "verify_code.php?email=" + encodeURIComponent(email);
          }
      } catch (err) {
          console.error("Error en la petición:", err);
          alert("Error al conectar con el servidor. Intenta de nuevo.");
      }
  });
  </script>
</body>
</html>

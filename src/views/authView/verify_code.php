<h2>Verificar código</h2>
<form id="verifyForm">
  <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email']) ?>">
  <input type="text" name="code" placeholder="Código de 6 dígitos" required>
  <button type="submit">Verificar</button>
</form>

<script>
document.getElementById("verifyForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    const email = e.target.email.value;
    const code = e.target.code.value;

    let res = await fetch("../../api/auth.php?action=verifyResetCode", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, code })
    });

    let data = await res.json();
    alert(data.message);
    if (data.status === "success") {
        window.location.href = "reset_password.php?email=" + encodeURIComponent(email) + "&code=" + code;
    }
});
</script>

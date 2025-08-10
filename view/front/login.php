<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Connexion PulsePlay</title>
    <style>
        /* Style modale simple */
        .modal {
            display: block; /* tu peux mettre none pour cacher au départ */
            position: fixed;
            z-index: 2000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.6);
        }
        .modal-content {
            background: #1a1a2e;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 350px;
            color: white;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 5px;
            border: none;
            font-size: 1rem;
        }
        button {
            background: #00d4aa;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        #loginMessage {
            color: #ff6b6b;
            min-height: 1.2em;
        }
    </style>
</head>
<body>

<div class="modal" id="loginModal">
    <div class="modal-content">
        <h2>Connexion</h2>
        <form id="loginForm">
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="password" placeholder="Mot de passe" required />
            <button type="submit">Se connecter</button>
        </form>
        <p id="loginMessage"></p>
    </div>
</div>

<script>
// Soumission du formulaire en AJAX
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('/PulsePlay/controller/UtilisateurController.php?action=login', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            window.location.href = data.redirect; // redirection selon rôle
        } else {
            document.getElementById('loginMessage').textContent = data.message;
        }
    })
    .catch(() => {
        document.getElementById('loginMessage').textContent = "Erreur réseau.";
    });
});
</script>

</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PulsePlay - Inscription</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .signup-container {
            background: rgba(26, 26, 46, 0.8);
            border-radius: 10px;
            padding: 2rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        h1 {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: #b8b8b8;
        }

        input, select {
            width: 100%;
            padding: 0.8rem;
            border-radius: 5px;
            border: 1px solid #2a2a4a;
            background: rgba(15, 15, 35, 0.5);
            color: white;
            font-size: 1rem;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #00d4aa;
            box-shadow: 0 0 0 2px rgba(0, 212, 170, 0.2);
        }

        button {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 5px;
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            color: white;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 212, 170, 0.3);
        }

        .message {
            margin-top: 1rem;
            text-align: center;
            min-height: 1.5rem;
        }

        .login-link {
            margin-top: 1.5rem;
            text-align: center;
        }

        .login-link a {
            color: #00d4aa;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h1>Créer un compte</h1>
        <form id="signupForm">
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>
            <div class="form-group">
                <label for="confirm_mot_de_passe">Confirmer le mot de passe</label>
                <input type="password" id="confirm_mot_de_passe" name="confirm_mot_de_passe" required>
            </div>
            <div class="form-group">
               <label for="role">Rôle :</label>
               <select name="role" id="role" required>
                   <option value="">-- Sélectionnez un rôle --</option>
                   <option value="admin">Admin</option>
                   <option value="entraineur">Entraîneur</option>
                   <option value="adherent">Adhérent</option>
               </select>
            </div>
            <button type="submit">S'inscrire</button>
            <div class="message" id="signupMessage"></div>
            <div class="login-link">
                <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const message = document.getElementById('signupMessage');
            
            // Réinitialiser le message
            message.textContent = "";
            message.style.color = "";
            
            // Vérifier que les mots de passe correspondent
            const password = formData.get('mot_de_passe');
            const confirmPassword = formData.get('confirm_mot_de_passe');
            
            if (password !== confirmPassword) {
                message.textContent = "Les mots de passe ne correspondent pas.";
                message.style.color = "#ff6b6b";
                return;
            }
            
            // Envoyer les données au serveur
            fetch('/PulsePlay/controller/UtilisateurController.php?action=signup', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    message.textContent = data.message;
                    message.style.color = "#00d4aa";
                    // Réinitialiser le formulaire après succès
                    document.getElementById('signupForm').reset();
                    // Rediriger vers la page de connexion après 2 secondes
                    setTimeout(() => {
                        window.location.href = "login.php";
                    }, 2000);
                } else {
                    message.textContent = data.message;
                    message.style.color = "#ff6b6b";
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                message.textContent = "Erreur de connexion au serveur.";
                message.style.color = "#ff6b6b";
            });
        });
    </script>
</body>
</html>
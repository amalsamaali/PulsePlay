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
            position: relative;
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
            transition: all 0.3s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #00d4aa;
            box-shadow: 0 0 0 2px rgba(0, 212, 170, 0.2);
        }

        input.error, select.error {
            border-color: #ff6b6b;
            box-shadow: 0 0 0 2px rgba(255, 107, 107, 0.2);
        }

        input.valid, select.valid {
            border-color: #00d4aa;
            box-shadow: 0 0 0 2px rgba(0, 212, 170, 0.2);
        }

        .error-message {
            color: #ff6b6b;
            font-size: 0.8rem;
            margin-top: 0.3rem;
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .error-message.show {
            display: block;
        }

        .success-message {
            color: #00d4aa;
            font-size: 0.8rem;
            margin-top: 0.3rem;
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .success-message.show {
            display: block;
        }

        .password-strength {
            margin-top: 0.3rem;
            font-size: 0.8rem;
        }

        .strength-bar {
            height: 4px;
            background: #2a2a4a;
            border-radius: 2px;
            margin-top: 0.2rem;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background: #ff6b6b; width: 25%; }
        .strength-fair { background: #ffa726; width: 50%; }
        .strength-good { background: #ffeb3b; width: 75%; }
        .strength-strong { background: #00d4aa; width: 100%; }

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
            position: relative;
        }

        button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 212, 170, 0.3);
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        button.loading .loading-spinner {
            display: block;
        }

        .message {
            margin-top: 1rem;
            text-align: center;
            min-height: 1.5rem;
            padding: 0.5rem;
            border-radius: 5px;
            font-weight: 500;
        }

        .message.success {
            background: rgba(0, 212, 170, 0.1);
            border: 1px solid rgba(0, 212, 170, 0.3);
            color: #00d4aa;
        }

        .message.error {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
            color: #ff6b6b;
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

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h1>Créer un compte</h1>
        <form id="signupForm" novalidate>
            <div class="form-group">
                <label for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" required minlength="2" maxlength="50" pattern="[A-Za-zÀ-ÿ\s-]+">
                <div class="error-message" id="nom-error"></div>
                <div class="success-message" id="nom-success"></div>
            </div>
            <div class="form-group">
                <label for="prenom">Prénom *</label>
                <input type="text" id="prenom" name="prenom" required minlength="2" maxlength="50" pattern="[A-Za-zÀ-ÿ\s-]+">
                <div class="error-message" id="prenom-error"></div>
                <div class="success-message" id="prenom-success"></div>
            </div>
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required>
                <div class="error-message" id="email-error"></div>
                <div class="success-message" id="email-success"></div>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe *</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required minlength="6">
                <div class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strength-fill"></div>
                    </div>
                    <span id="strength-text">Force du mot de passe</span>
                </div>
                <div class="error-message" id="mot_de_passe-error"></div>
                <div class="success-message" id="mot_de_passe-success"></div>
            </div>
            <div class="form-group">
                <label for="confirm_mot_de_passe">Confirmer le mot de passe *</label>
                <input type="password" id="confirm_mot_de_passe" name="confirm_mot_de_passe" required>
                <div class="error-message" id="confirm_mot_de_passe-error"></div>
                <div class="success-message" id="confirm_mot_de_passe-success"></div>
            </div>
            <div class="form-group">
               <label for="role">Rôle *</label>
               <select name="role" id="role" required>
                   <option value="">-- Sélectionnez un rôle --</option>
                   <option value="admin">Admin</option>
                   <option value="entraineur">Entraîneur</option>
                   <option value="adherent">Adhérent</option>
               </select>
               <div class="error-message" id="role-error"></div>
               <div class="success-message" id="role-success"></div>
            </div>
            <button type="submit" id="submitBtn">
                <div class="loading-spinner"></div>
                <span>S'inscrire</span>
            </button>
            <div class="message" id="signupMessage"></div>
            <div class="login-link">
                <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
            </div>
        </form>
    </div>

    <script>
        // Éléments du formulaire
        const form = document.getElementById('signupForm');
        const submitBtn = document.getElementById('submitBtn');
        const message = document.getElementById('signupMessage');

        // Validation en temps réel
        const fields = {
            nom: {
                element: document.getElementById('nom'),
                error: document.getElementById('nom-error'),
                success: document.getElementById('nom-success'),
                validate: (value) => {
                    if (!value) return 'Le nom est obligatoire';
                    if (value.length < 2) return 'Le nom doit contenir au moins 2 caractères';
                    if (value.length > 50) return 'Le nom ne peut pas dépasser 50 caractères';
                    if (!/^[A-Za-zÀ-ÿ\s-]+$/.test(value)) return 'Le nom ne peut contenir que des lettres, espaces et tirets';
                    return null;
                }
            },
            prenom: {
                element: document.getElementById('prenom'),
                error: document.getElementById('prenom-error'),
                success: document.getElementById('prenom-success'),
                validate: (value) => {
                    if (!value) return 'Le prénom est obligatoire';
                    if (value.length < 2) return 'Le prénom doit contenir au moins 2 caractères';
                    if (value.length > 50) return 'Le prénom ne peut pas dépasser 50 caractères';
                    if (!/^[A-Za-zÀ-ÿ\s-]+$/.test(value)) return 'Le prénom ne peut contenir que des lettres, espaces et tirets';
                    return null;
                }
            },
            email: {
                element: document.getElementById('email'),
                error: document.getElementById('email-error'),
                success: document.getElementById('email-success'),
                validate: (value) => {
                    if (!value) return 'L\'email est obligatoire';
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Format d\'email invalide';
                    return null;
                }
            },
            mot_de_passe: {
                element: document.getElementById('mot_de_passe'),
                error: document.getElementById('mot_de_passe-error'),
                success: document.getElementById('mot_de_passe-success'),
                validate: (value) => {
                    if (!value) return 'Le mot de passe est obligatoire';
                    if (value.length < 6) return 'Le mot de passe doit contenir au moins 6 caractères';
                    return null;
                }
            },
            confirm_mot_de_passe: {
                element: document.getElementById('confirm_mot_de_passe'),
                error: document.getElementById('confirm_mot_de_passe-error'),
                success: document.getElementById('confirm_mot_de_passe-success'),
                validate: (value) => {
                    const password = fields.mot_de_passe.element.value;
                    if (!value) return 'La confirmation du mot de passe est obligatoire';
                    if (value !== password) return 'Les mots de passe ne correspondent pas';
                    return null;
                }
            },
            role: {
                element: document.getElementById('role'),
                error: document.getElementById('role-error'),
                success: document.getElementById('role-success'),
                validate: (value) => {
                    if (!value) return 'Le rôle est obligatoire';
                    if (!['admin', 'entraineur', 'adherent'].includes(value)) return 'Rôle invalide';
                    return null;
                }
            }
        };

        // Validation en temps réel pour chaque champ
        Object.keys(fields).forEach(fieldName => {
            const field = fields[fieldName];
            const element = field.element;
            
            element.addEventListener('input', () => validateField(fieldName));
            element.addEventListener('blur', () => validateField(fieldName));
            element.addEventListener('focus', () => clearFieldMessages(fieldName));
        });

        // Validation d'un champ spécifique
        function validateField(fieldName) {
            const field = fields[fieldName];
            const value = field.element.value.trim();
            const error = field.error;
            const success = field.success;
            const element = field.element;

            // Nettoyer les messages précédents
            clearFieldMessages(fieldName);

            // Validation spéciale pour la confirmation du mot de passe
            if (fieldName === 'confirm_mot_de_passe' && value && fields.mot_de_passe.element.value) {
                const password = fields.mot_de_passe.element.value;
                if (value !== password) {
                    showFieldError(fieldName, 'Les mots de passe ne correspondent pas');
                    return false;
                }
            }

            // Validation du champ
            const validationError = field.validate(value);
            
            if (validationError) {
                showFieldError(fieldName, validationError);
                return false;
            } else if (value) {
                showFieldSuccess(fieldName, 'Champ valide');
                return true;
            }
            
            return true;
        }

        // Afficher une erreur pour un champ
        function showFieldError(fieldName, message) {
            const field = fields[fieldName];
            field.element.classList.add('error');
            field.element.classList.remove('valid');
            field.error.textContent = message;
            field.error.classList.add('show');
        }

        // Afficher un succès pour un champ
        function showFieldSuccess(fieldName, message) {
            const field = fields[fieldName];
            field.element.classList.add('valid');
            field.element.classList.remove('error');
            field.success.textContent = message;
            field.success.classList.add('show');
        }

        // Nettoyer les messages d'un champ
        function clearFieldMessages(fieldName) {
            const field = fields[fieldName];
            field.error.classList.remove('show');
            field.success.classList.remove('show');
        }

        // Validation complète du formulaire
        function validateForm() {
            let isValid = true;
            
            Object.keys(fields).forEach(fieldName => {
                if (!validateField(fieldName)) {
                    isValid = false;
                }
            });
            
            return isValid;
        }

        // Évaluation de la force du mot de passe
        function evaluatePasswordStrength(password) {
            let strength = 0;
            const strengthFill = document.getElementById('strength-fill');
            const strengthText = document.getElementById('strength-text');
            
            if (password.length >= 6) strength += 1;
            if (password.length >= 8) strength += 1;
            if (/[a-z]/.test(password)) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            strengthFill.className = 'strength-fill';
            
            if (strength <= 2) {
                strengthFill.classList.add('strength-weak');
                strengthText.textContent = 'Faible';
            } else if (strength <= 3) {
                strengthFill.classList.add('strength-fair');
                strengthText.textContent = 'Moyen';
            } else if (strength <= 4) {
                strengthFill.classList.add('strength-good');
                strengthText.textContent = 'Bon';
            } else {
                strengthFill.classList.add('strength-strong');
                strengthText.textContent = 'Fort';
            }
        }

        // Écouter les changements du mot de passe
        fields.mot_de_passe.element.addEventListener('input', function() {
            evaluatePasswordStrength(this.value);
            // Re-valider la confirmation si elle existe
            if (fields.confirm_mot_de_passe.element.value) {
                validateField('confirm_mot_de_passe');
            }
        });

        // Soumission du formulaire
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Nettoyer le message principal
            message.textContent = "";
            message.className = "message";
            
            // Validation complète
            if (!validateForm()) {
                showMessage('Veuillez corriger les erreurs dans le formulaire.', 'error');
                // Focus sur le premier champ en erreur
                const firstError = document.querySelector('input.error, select.error');
                if (firstError) firstError.focus();
                return;
            }
            
            // Désactiver le bouton et afficher le loading
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            
            const formData = new FormData(this);
            
            // Envoyer les données au serveur
            fetch('/PulsePlay/controller/UtilisateurController.php?action=signup', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    // Réinitialiser le formulaire après succès
                    form.reset();
                    // Nettoyer tous les états
                    Object.keys(fields).forEach(fieldName => {
                        fields[fieldName].element.classList.remove('valid', 'error');
                        clearFieldMessages(fieldName);
                    });
                    // Réinitialiser la force du mot de passe
                    document.getElementById('strength-fill').className = 'strength-fill';
                    document.getElementById('strength-text').textContent = 'Force du mot de passe';
                    
                    // Rediriger vers la page de connexion après 2 secondes
                    setTimeout(() => {
                        window.location.href = "login.php";
                    }, 2000);
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showMessage("Erreur de connexion au serveur.", 'error');
            })
            .finally(() => {
                // Réactiver le bouton et masquer le loading
                submitBtn.disabled = false;
                submitBtn.classList.remove('loading');
            });
        });

        // Afficher un message
        function showMessage(text, type) {
            message.textContent = text;
            message.className = `message ${type}`;
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Focus sur le premier champ
            fields.nom.element.focus();
        });
    </script>
</body>
</html>
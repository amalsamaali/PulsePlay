<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PulsePlay - Gestion d'Activités Sportives</title>
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
            overflow-x: hidden;
        }

        /* Header */
        .header {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(15, 15, 35, 0.95);
            backdrop-filter: blur(10px);
            z-index: 1000;
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-menu a:hover {
            color: #00d4aa;
        }

        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #00d4aa;
            transition: width 0.3s ease;
        }

        .nav-menu a:hover::after {
            width: 100%;
        }

        .login-btn, .signup-btn {
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 25px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-left: 1rem;
        }

        .login-btn:hover, .signup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 212, 170, 0.3);
        }
        
        .signup-btn {
            background: transparent;
            border: 2px solid #00d4aa;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: radial-gradient(circle at 20% 50%, rgba(0, 212, 170, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(0, 180, 216, 0.1) 0%, transparent 50%);
        }

        .hero-content {
            text-align: center;
            max-width: 800px;
            padding: 2rem;
            animation: fadeInUp 1s ease-out;
        }

        .hero h1 {
            font-size: 4rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #00d4aa, #00b4d8, #ffffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            color: #b8b8b8;
            line-height: 1.6;
        }

        .cta-buttons {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary, .btn-secondary {
            padding: 1rem 3rem;
            border: none;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 212, 170, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid #00d4aa;
        }

        .btn-secondary:hover {
            background: #00d4aa;
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 212, 170, 0.3);
        }

        /* Features Section */
        .features {
            padding: 5rem 0;
            background: rgba(15, 15, 35, 0.5);
        }

        .features-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .features h2 {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 3rem;
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
        }

        .feature-card {
            background: linear-gradient(145deg, rgba(26, 26, 46, 0.8), rgba(22, 33, 62, 0.8));
            padding: 2.5rem;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 212, 170, 0.2);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 212, 170, 0.2);
            border-color: rgba(0, 212, 170, 0.5);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 2rem;
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #00d4aa;
        }

        .feature-card p {
            color: #b8b8b8;
            line-height: 1.6;
        }

        /* Stats Section */
        .stats {
            padding: 5rem 0;
            background: rgba(0, 0, 0, 0.3);
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 3rem;
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .stat-item p {
            font-size: 1.2rem;
            color: #b8b8b8;
        }

        /* Footer */
        .footer {
            background: rgba(15, 15, 35, 0.9);
            padding: 3rem 0;
            text-align: center;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .footer p {
            color: #b8b8b8;
            margin-bottom: 2rem;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .social-links a {
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, #00d4aa, #00b4d8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 212, 170, 0.3);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.2rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .nav-menu {
                display: none;
            }
        }

        /* Floating elements */
        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(45deg, rgba(0, 212, 170, 0.1), rgba(0, 180, 216, 0.1));
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 50%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }
        /* Modal styles */
.modal {
    position: fixed;
    z-index: 2000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.8);
    display: none;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { transform: translateY(-30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-content {
    background: linear-gradient(145deg, #1a1a2e, #16213e);
    padding: 30px;
    border-radius: 15px;
    width: 400px;
    color: white;
    position: relative;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(0, 212, 170, 0.2);
    animation: slideIn 0.4s ease-out;
}

.modal-content h2 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 1.8rem;
    background: linear-gradient(45deg, #00d4aa, #00b4d8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.modal-content button#closeLogin, .modal-content button#closeSignup {
    position: absolute;
    top: 15px;
    right: 20px;
    background: none;
    border: none;
    color: #b8b8b8;
    font-size: 1.8rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.modal-content button#closeLogin:hover, .modal-content button#closeSignup:hover {
    color: #00d4aa;
    transform: rotate(90deg);
}

.form-group {
    margin-bottom: 15px;
}

.modal-content input, .modal-content select {
    width: 100%;
    padding: 12px 15px;
    margin: 10px 0;
    border-radius: 8px;
    border: 1px solid #2a2a4a;
    background: rgba(15, 15, 35, 0.5);
    color: white;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.modal-content input:focus, .modal-content select:focus {
    outline: none;
    border-color: #00d4aa;
    box-shadow: 0 0 0 2px rgba(0, 212, 170, 0.2);
}

.modal-content input::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.modal-content button[type="submit"] {
    width: 100%;
    padding: 12px;
    margin: 15px 0 10px;
    border-radius: 8px;
    border: none;
    background: linear-gradient(45deg, #00d4aa, #00b4d8);
    color: white;
    font-weight: bold;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.modal-content button[type="submit"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0, 212, 170, 0.3);
}

.modal-content button[type="submit"]:active {
    transform: translateY(0);
}

#loginMessage, #signupMessage {
    color: #ff6b6b;
    min-height: 1.5em;
    margin-top: 10px;
    text-align: center;
    font-size: 0.9rem;
}

/* Style pour les messages d'erreur et de succès */
.error-message {
    color: #ff6b6b;
}

.success-message {
    color: #00d4aa;
}

    </style>
    
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="nav-container">
            <div class="logo">PulsePlay</div>
            <nav class="nav-menu">
                <a href="#accueil">Accueil</a>
                <a href="#activites">Activités</a>
                <a href="#planning">Planning</a>
                <a href="#reservations">Réservations</a>
                <a href="#contact">Contact</a>
            </nav>
            <div style="display:flex;gap:1rem;">
                <button id="openLogin" class="login-btn">Connexion</button>
                <button id="openSignup" class="signup-btn">S'inscrire</button>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="accueil">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
        <div class="hero-content">
            <h1>PulsePlay</h1>
            <p>Votre plateforme complète pour gérer et organiser toutes vos activités sportives. Réservations, planning, suivi des performances - tout en un seul endroit.</p>
            <div class="cta-buttons">
                <a href="#" class="btn-primary">Commencer Maintenant</a>
                <a href="#" class="btn-secondary">Découvrir Plus</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="activites">
        <div class="features-container">
            <h2 class="animate-on-scroll">Fonctionnalités Principales</h2>
            <div class="features-grid">
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">🏃</div>
                    <h3>Gestion d'Activités</h3>
                    <p>Créez et organisez facilement vos cours, entraînements et événements sportifs avec un système intuitif.</p>
                </div>
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">📅</div>
                    <h3>Planning Intelligent</h3>
                    <p>Planifiez automatiquement vos sessions avec notre système intelligent qui évite les conflits d'horaires.</p>
                </div>
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">📊</div>
                    <h3>Suivi Performance</h3>
                    <p>Analysez vos progrès et ceux de vos participants avec des statistiques détaillées et des rapports personnalisés.</p>
                </div>
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">💳</div>
                    <h3>Réservations Faciles</h3>
                    <p>Système de réservation simplifié avec paiement intégré et confirmation automatique par email.</p>
                </div>
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">🎯</div>
                    <h3>Objectifs Personnalisés</h3>
                    <p>Définissez et suivez des objectifs personnalisés pour chaque participant selon leurs besoins.</p>
                </div>
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">📱</div>
                    <h3>Application Mobile</h3>
                    <p>Accédez à toutes les fonctionnalités depuis votre smartphone avec notre app mobile optimisée.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="stats-container">
            <div class="stat-item animate-on-scroll">
                <h3>5000+</h3>
                <p>Utilisateurs Actifs</p>
            </div>
            <div class="stat-item animate-on-scroll">
                <h3>15000+</h3>
                <p>Sessions Organisées</p>
            </div>
            <div class="stat-item animate-on-scroll">
                <h3>98%</h3>
                <p>Satisfaction Client</p>
            </div>
            <div class="stat-item animate-on-scroll">
                <h3>24/7</h3>
                <p>Support Disponible</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <p>&copy; 2025 PulsePlay. Tous droits réservés.</p>
            <div class="social-links">
                <a href="#">📘</a>
                <a href="#">📷</a>
                <a href="#">🐦</a>
                <a href="#">💼</a>
            </div>
            <p>Créé avec passion pour les amateurs de sport</p>
        </div>
    </footer>

    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(15, 15, 35, 0.98)';
                header.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.3)';
            } else {
                header.style.background = 'rgba(15, 15, 35, 0.95)';
                header.style.boxShadow = 'none';
            }
        });

        // Animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach(element => {
            observer.observe(element);
        });

        // Button click effects
        document.querySelectorAll('button, .btn-primary, .btn-secondary').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.height, rect.width);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
    </script>
    <!-- Modale login -->
<div class="modal" id="loginModal" style="display: none;">
    <div class="modal-content">
        <button id="closeLogin" title="Fermer la fenêtre">×</button>
        <h2>Connexion</h2>
        <form id="loginForm" action="../../controller/login_controller.php" method="POST" autocomplete="off">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required />
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Mot de passe" required />
            </div>
            <button type="submit">Se connecter</button>
        </form>
        <p id="loginMessage"></p>
        <div style="text-align: center; margin-top: 15px;">
            <a href="#" id="switchToSignup" style="color: #00d4aa; text-decoration: none; font-size: 0.9rem;">Pas encore de compte ? S'inscrire</a>
        </div>
    </div>
</div>

<!-- Modale signup -->
<div class="modal" id="signupModal" style="display: none;">
    <div class="modal-content">
        <button id="closeSignup" title="Fermer la fenêtre">×</button>
        <h2>Inscription</h2>
        <form id="signupForm" autocomplete="off">
            <div class="form-group">
                <input type="text" name="nom" placeholder="Nom" required />
            </div>
            <div class="form-group">
                <input type="text" name="prenom" placeholder="Prénom" required />
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required />
            </div>
            <div class="form-group">
                <select name="role" required>
                    <option value="">-- Choisir un rôle --</option>
                    <option value="adherent">Adhérent</option>
                    <option value="entraineur">Entraîneur</option>
                   
                </select>
            </div>
            <div class="form-group">
                <input type="password" name="mot_de_passe" placeholder="Mot de passe" required />
            </div>
            <div class="form-group">
                <input type="password" name="confirm_mot_de_passe" placeholder="Confirmer le mot de passe" required />
            </div>
            <button type="submit">S'inscrire</button>
        </form>
        <p id="signupMessage"></p>
        <div style="text-align: center; margin-top: 15px;">
            <a href="#" id="switchToLogin" style="color: #00d4aa; text-decoration: none; font-size: 0.9rem;">Déjà inscrit ? Se connecter</a>
        </div>
    </div>
</div>
<script>
    // Ouvrir la modale au clic sur le bouton connexion
    document.getElementById('openLogin').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('loginModal').style.display = 'flex';
        document.getElementById('signupModal').style.display = 'none';
    });

    document.getElementById('openSignup').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('signupModal').style.display = 'flex';
        document.getElementById('loginModal').style.display = 'none';
    });
    
    // Basculer entre les modales
    document.getElementById('switchToSignup').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('loginModal').style.display = 'none';
        document.getElementById('signupModal').style.display = 'flex';
        document.getElementById('loginMessage').textContent = '';
        document.getElementById('loginForm').reset();
    });
    
    document.getElementById('closeLogin').addEventListener('click', function() {
        document.getElementById('loginModal').style.display = 'none';
        document.getElementById('loginMessage').textContent = '';
        document.getElementById('loginForm').reset();
    });

    document.getElementById('closeSignup').addEventListener('click', function() {
        document.getElementById('signupModal').style.display = 'none';
        document.getElementById('signupMessage').textContent = '';
        document.getElementById('signupForm').reset();
    });
    
    document.getElementById('switchToLogin').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('signupModal').style.display = 'none';
        document.getElementById('loginModal').style.display = 'flex';
        document.getElementById('signupMessage').textContent = '';
        document.getElementById('signupForm').reset();
    });
    
    // Fermer les modales en cliquant en dehors
    window.addEventListener('click', function(e) {
        const loginModal = document.getElementById('loginModal');
        const signupModal = document.getElementById('signupModal');
        
        if (e.target === loginModal) {
            loginModal.style.display = 'none';
            document.getElementById('loginMessage').textContent = '';
            document.getElementById('loginForm').reset();
        }
        
        if (e.target === signupModal) {
            signupModal.style.display = 'none';
            document.getElementById('signupMessage').textContent = '';
            document.getElementById('signupForm').reset();
        }
    });

    // Soumission formulaire login en AJAX
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.email.value.trim();
        const password = this.password.value;
        let msg = '';
        if (!email) {
            msg = "Le champ Email est obligatoire.";
        } else if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
            msg = "Email invalide.";
        } else if (!password) {
            msg = "Le champ Mot de passe est obligatoire.";
        } else if (password.length < 6) {
            msg = "Le mot de passe doit contenir au moins 6 caractères.";
        }
        if (msg) {
            document.getElementById('loginMessage').className = 'error-message';
            document.getElementById('loginMessage').textContent = msg;
            return;
        }
        const formData = new FormData(this);
        // Afficher un message de chargement
        document.getElementById('loginMessage').textContent = 'Connexion en cours...';
        document.getElementById('loginMessage').className = '';
        
        fetch('/PulsePlay/controller/UtilisateurController.php?action=login', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById('loginMessage').className = 'success-message';
                document.getElementById('loginMessage').textContent = 'Connexion réussie!';
                window.location.href = data.redirect;
            } else {
                document.getElementById('loginMessage').className = 'error-message';
                document.getElementById('loginMessage').textContent = data.message;
            }
        })
        .catch(() => {
            document.getElementById('loginMessage').className = 'error-message';
            document.getElementById('loginMessage').textContent = "Erreur réseau.";
        });
    });

    // Soumission formulaire signup en AJAX (à adapter côté serveur)
    document.getElementById('signupForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const nom = this.nom.value.trim();
        const prenom = this.prenom.value.trim();
        const email = this.email.value.trim();
        const role = this.role.value;
        const mot_de_passe = this.mot_de_passe.value;
        const confirm = this.confirm_mot_de_passe.value;
        let msg = '';
        if (!nom) {
            msg = "Le champ Nom est obligatoire.";
        } else if (!prenom) {
            msg = "Le champ Prénom est obligatoire.";
        } else if (!email) {
            msg = "Le champ Email est obligatoire.";
        } else if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
            msg = "Email invalide.";
        } else if (!role) {
            msg = "Veuillez choisir un rôle.";
        } else if (!mot_de_passe) {
            msg = "Le champ Mot de passe est obligatoire.";
        } else if (mot_de_passe.length < 6) {
            msg = "Le mot de passe doit contenir au moins 6 caractères.";
        } else if (!confirm) {
            msg = "Veuillez confirmer le mot de passe.";
        } else if (mot_de_passe !== confirm) {
            msg = "Les mots de passe ne correspondent pas.";
        }
        if (msg) {
            document.getElementById('signupMessage').className = 'error-message';
            document.getElementById('signupMessage').textContent = msg;
            return;
        }
        const formData = new FormData(this);
        // Afficher un message de chargement
        document.getElementById('signupMessage').textContent = 'Inscription en cours...';
        document.getElementById('signupMessage').className = '';
        
        fetch('/PulsePlay/controller/UtilisateurController.php?action=signup', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById('signupMessage').className = 'success-message';
                document.getElementById('signupMessage').textContent = data.message || 'Inscription réussie !';
                setTimeout(() => {
                    document.getElementById('signupModal').style.display = 'none';
                    document.getElementById('signupForm').reset();
                    document.getElementById('signupMessage').textContent = '';
                }, 1200);
            } else {
                document.getElementById('signupMessage').className = 'error-message';
                document.getElementById('signupMessage').textContent = data.message;
            }
        })
        .catch(() => {
            document.getElementById('signupMessage').className = 'error-message';
            document.getElementById('signupMessage').textContent = "Erreur réseau.";
        });
    });

    // Fermer la modale si clic en dehors du contenu (login ou signup)
    window.addEventListener('click', function(e) {
        const loginModal = document.getElementById('loginModal');
        const signupModal = document.getElementById('signupModal');
        if(e.target === loginModal) {
            loginModal.style.display = 'none';
            document.getElementById('loginMessage').textContent = '';
            document.getElementById('loginForm').reset();
        }
        if(e.target === signupModal) {
            signupModal.style.display = 'none';
            document.getElementById('signupMessage').textContent = '';
            document.getElementById('signupForm').reset();
        }
    });
</script>

</body>
</html>
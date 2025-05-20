// Animate elements when they come into view
document.addEventListener('DOMContentLoaded', function() {
    const fadeElements = document.querySelectorAll('.fade-in');
    
    // Initial animation for elements that are already in view
    animateElementsInView();
    
    // Animation on scroll
    window.addEventListener('scroll', animateElementsInView);
    
    function animateElementsInView() {
        fadeElements.forEach(function(element) {
            const elementTop = element.getBoundingClientRect().top;
            const elementVisible = 150;
            
            if (elementTop < window.innerHeight - elementVisible) {
                // Add delay based on data attribute
                const delay = element.getAttribute('data-delay')  0;
                setTimeout(function() {
                    element.classList.add('active');
                }, delay);
            }
        });
    }
    
    // Carousel for feature cards on mobile (optional)
    let currentFeatureIndex = 0;
    const featureCards = document.querySelectorAll('.feature-card');
    
    function rotateFeatures() {
        if (window.innerWidth <= 768) {
            featureCards.forEach((card, index) => {
                if (index === currentFeatureIndex) {
                    card.classList.add('active');
                } else {
                    card.classList.remove('active');
                }
            });
            
            currentFeatureIndex = (currentFeatureIndex + 1) % featureCards.length;
        }
    }
    
    // Auto-rotate features every 3 seconds on mobile
    if (window.innerWidth <= 768) {
        setInterval(rotateFeatures, 3000);
    }
});


// JavaScript pour contrôler et améliorer les animations

document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour vérifier si un élément est visible dans la fenêtre
    function isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight  document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    // Fonction pour démarrer les animations quand la section est visible
    function handleScrollAnimations() {
        const featuresSection = document.querySelector('.features-section');
        
        if (featuresSection && isElementInViewport(featuresSection)) {
            // Ajouter une classe pour déclencher les animations CSS
            featuresSection.classList.add('animate');
            
            // Retirer l'écouteur d'événement une fois que les animations ont commencé
            window.removeEventListener('scroll', handleScrollAnimations);
        }
    }

    // Écouter l'événement de défilement pour déclencher les animations
    window.addEventListener('scroll', handleScrollAnimations);
    
    // Vérifier au chargement initial
    handleScrollAnimations();

    // Animation interactive pour les cartes
    const featureCards = document.querySelectorAll('.feature-card');
    
    featureCards.forEach(card => {
        // Rotation 3D au survol
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left; // Position X de la souris à l'intérieur de la carte
            const y = e.clientY - rect.top;  // Position Y de la souris à l'intérieur de la carte
            
            // Calculer la rotation en fonction de la position de la souris
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            // Limitez la rotation à un petit angle (5 degrés max)
            const rotateY = ((x - centerX) / centerX) * 5;
            const rotateX = ((centerY - y) / centerY) * 5;
            
            // Appliquer la transformation
            this.style.transform = perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px);
        });
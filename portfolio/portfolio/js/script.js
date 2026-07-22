document.addEventListener('DOMContentLoaded', () => {

    // --- Loading Animation ---
    setTimeout(() => {
        const loader = document.getElementById('loader');
        if (loader) {
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
                // Initialize AOS after loader is gone to prevent layout bugs
                AOS.init({
                    duration: 1000,
                    once: true,
                    offset: 100
                });
            }, 500);
        }
    }, 1000);

    // --- Dark/Light Mode Toggle ---
    const themeToggleBtn = document.getElementById('theme-toggle');
    const body = document.documentElement; // using root for data-theme

    // Check for saved theme
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        body.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);
    } else {
        // Default to dark mode if preferred
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            body.setAttribute('data-theme', 'dark');
            updateThemeIcon('dark');
        }
    }

    themeToggleBtn.addEventListener('click', () => {
        let currentTheme = body.getAttribute('data-theme');
        let newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        body.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    });

    function updateThemeIcon(theme) {
        if (theme === 'dark') {
            themeToggleBtn.classList.remove('fa-moon');
            themeToggleBtn.classList.add('fa-sun');
        } else {
            themeToggleBtn.classList.remove('fa-sun');
            themeToggleBtn.classList.add('fa-moon');
        }
    }

    // --- Generate Floating Particles ---
    const particlesContainer = document.querySelector('.particles');
    if (particlesContainer) {
        for (let i = 0; i < 20; i++) {
            let particle = document.createElement('div');
            particle.classList.add('particle');
            
            // Random properties
            let size = Math.random() * 50 + 10;
            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            particle.style.left = `${Math.random() * 100}%`;
            particle.style.top = `${Math.random() * 100}%`;
            particle.style.animationDuration = `${Math.random() * 20 + 10}s`;
            particle.style.animationDelay = `${Math.random() * 5}s`;
            
            particlesContainer.appendChild(particle);
        }
    }

    // --- Fetch GitHub Stats ---
    const githubUsername = 'octocat'; // Placeholder, user can change this
    
    fetch(`https://api.github.com/users/${githubUsername}`)
        .then(response => response.json())
        .then(data => {
            if (data && !data.message) {
                document.getElementById('repo-count').textContent = data.public_repos;
                document.getElementById('followers-count').textContent = data.followers;
            }
        })
        .catch(err => console.error('Error fetching GitHub data:', err));

    // --- Contact Form Submission (Mock) ---
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            alert("Thanks for reaching out! This is a mock form submission for the static portfolio.");
            contactForm.reset();
        });
    }

    // --- Smooth Scrolling for Anchor Links ---
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

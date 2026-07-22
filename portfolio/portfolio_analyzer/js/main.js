document.addEventListener('DOMContentLoaded', function() {
    
    // --- Theme Toggle Logic ---
    const themeToggleBtn = document.getElementById('theme-toggle');
    const body = document.documentElement; 

    if (themeToggleBtn) {
        // Check for saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            body.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            body.setAttribute('data-theme', 'dark');
            updateThemeIcon('dark');
        }

        themeToggleBtn.addEventListener('click', () => {
            let currentTheme = body.getAttribute('data-theme');
            let newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
    }

    function updateThemeIcon(theme) {
        if (!themeToggleBtn) return;
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
        for (let i = 0; i < 15; i++) {
            let particle = document.createElement('div');
            particle.classList.add('particle');
            let size = Math.random() * 40 + 10;
            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            particle.style.left = `${Math.random() * 100}%`;
            particle.style.top = `${Math.random() * 100}%`;
            particle.style.animationDuration = `${Math.random() * 20 + 10}s`;
            particle.style.animationDelay = `${Math.random() * 5}s`;
            particlesContainer.appendChild(particle);
        }
    }

    // --- Initialize AOS ---
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true,
            offset: 50
        });
    }

    // --- Dynamic Form Fields ---
    const addProjectBtn = document.getElementById('addProjectBtn');
    const projectsContainer = document.getElementById('projects-container');

    if (projectsContainer) {
        // Event delegation to handle removing projects (both existing and new ones)
        projectsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-project')) {
                const projectEntry = e.target.closest('.project-entry');
                if (projectEntry) {
                    projectsContainer.removeChild(projectEntry);
                }
            }
        });
    }

    if (addProjectBtn && projectsContainer) {
        addProjectBtn.addEventListener('click', function() {
            const projectEntry = document.createElement('div');
            projectEntry.className = 'project-entry border rounded p-3 mb-3 position-relative';
            projectEntry.style.borderColor = 'var(--card-border)';
            projectEntry.innerHTML = `
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2 remove-project" aria-label="Close"></button>
                <input type="text" class="form-control bg-transparent mb-2" name="project_title[]" required placeholder="Project Title">
                <input type="text" class="form-control bg-transparent mb-2" name="project_tech[]" placeholder="Technologies Used (e.g. React, Node.js)">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="url" class="form-control bg-transparent" name="project_github[]" placeholder="GitHub Link">
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="url" class="form-control bg-transparent" name="project_demo[]" placeholder="Live Demo Link">
                    </div>
                </div>
                <textarea class="form-control bg-transparent" name="project_desc[]" rows="2" required placeholder="Project Description"></textarea>
            `;
            projectsContainer.appendChild(projectEntry);
        });
    }

    // --- Bootstrap Form Validation ---
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});

// Utility function to get URL parameters
function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

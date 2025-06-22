// VeggieDelight Frontend - Main JavaScript
// Handles navigation, menu filtering, and interactive features
// Integrated with PHP Backend

// --- CRITICAL: Check if the site is being loaded from a web server ---
if (window.location.protocol === 'file:') {
    document.body.innerHTML = `
        <div style="font-family: sans-serif; padding: 2rem; text-align: center; background-color: #ffebee; color: #c62828; height: 100vh; display: flex; justify-content: center; align-items: center;">
            <div>
                <h1 style="font-size: 2.5rem;">Error: You Must Use a Web Server</h1>
                <p style="font-size: 1.2rem;">You are trying to open this file directly. This will not work.</p>
                <p style="font-size: 1.2rem;">You need to access your project through the XAMPP web server.</p>
                <p style="font-size: 1.2rem; margin-top: 2rem;"><strong>Click the link below to open the project correctly:</strong></p>
                <a href="http://localhost/e-com/" style="display: inline-block; background-color: #28a745; color: white; padding: 15px 30px; font-size: 1.5rem; border-radius: 5px; text-decoration: none; font-weight: bold;">
                    Start Project Correctly
                </a>
            </div>
        </div>
    `;
    // Stop all other script execution
    throw new Error("Execution stopped: Project must be run on a web server.");
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initNavigation();
    initScrollEffects();
    initContactForm();
    loadMenuItems();
    initAnimations();
    loadCartCount();
});

// Navigation functionality
function initNavigation() {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('nav-menu');
    const navLinks = document.querySelectorAll('.nav-link');
    const cartLink = document.querySelector('.nav-cart');

    if (cartLink) {
        cartLink.addEventListener('click', function(e) {
            e.preventDefault();
            fetch('../backend/api/check_auth.php')
                .then(response => response.json())
                .then(data => {
                    if (data.logged_in) {
                        window.location.href = 'cart.html';
                    } else {
                        showToast('Please log in to view your cart', 'warning');
                        setTimeout(() => {
                            window.location.href = 'login.html';
                        }, 1500);
                    }
                })
                .catch(error => {
                    console.error('Auth check failed', error);
                    showToast('Please log in to view your cart', 'warning');
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 1500);
                });
        });
    }

    // Mobile menu toggle
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
    }

    // Close mobile menu when clicking on a link
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navMenu.classList.remove('active');
            hamburger.classList.remove('active');
        });
    });

    // Smooth scrolling for anchor links
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

    // Header scroll effect
    window.addEventListener('scroll', function() {
        const header = document.querySelector('.header');
        if (window.scrollY > 100) {
            header.style.background = 'rgba(255, 255, 255, 0.95)';
            header.style.backdropFilter = 'blur(10px)';
        } else {
            header.style.background = 'var(--white)';
            header.style.backdropFilter = 'none';
        }
    });

    // Check authentication status and update nav menu
    fetch('../backend/api/check_auth.php', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.logged_in) {
            // Remove Login and Register buttons
            const navMenu = document.getElementById('nav-menu');
            if (navMenu) {
                const loginLink = navMenu.querySelector('a[href="login.html"]');
                const registerLink = navMenu.querySelector('a[href="register.html"]');
                if (loginLink) loginLink.parentElement.remove();
                if (registerLink) registerLink.parentElement.remove();
            }
            
            // Add profile icon to nav-actions
            addProfileIcon(data.user_name, data.user_email);
        }
    })
    .catch(error => console.error('Error checking auth status:', error));
}

// Add profile icon to navigation
function addProfileIcon(userName, userEmail) {
    const navActions = document.querySelector('.nav-actions');
    if (!navActions) return;

    // Create profile icon container
    const profileContainer = document.createElement('div');
    profileContainer.className = 'profile-container';
    
    // Create profile icon
    const profileIcon = document.createElement('div');
    profileIcon.className = 'profile-icon';
    profileIcon.innerHTML = '<i class="fas fa-user"></i>';
    
    // Create profile dropdown
    const profileDropdown = document.createElement('div');
    profileDropdown.className = 'profile-dropdown';
    profileDropdown.innerHTML = `
        <div class="profile-header">
            <div class="profile-name">${userName || 'User'}</div>
            <div class="profile-email">${userEmail || 'user@example.com'}</div>
        </div>
        <ul class="profile-menu">
            <li class="profile-menu-item">
                <a href="profile.html" class="profile-menu-link">
                    <i class="fas fa-user-circle"></i>
                    My Profile
                </a>
            </li>
            <li class="profile-menu-item">
                <a href="orders.html" class="profile-menu-link">
                    <i class="fas fa-shopping-bag"></i>
                    My Orders
                </a>
            </li>
            <li class="profile-menu-item">
                <a href="favorites.html" class="profile-menu-link">
                    <i class="fas fa-heart"></i>
                    Favorites
                </a>
            </li>
            <li class="profile-menu-item">
                <a href="settings.html" class="profile-menu-link">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
            </li>
            <li class="profile-menu-divider"></li>
            <li class="profile-menu-item">
                <a href="../backend/logout.php" class="profile-menu-link logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
    `;
    
    // Add click event to toggle dropdown
    profileIcon.addEventListener('click', function(e) {
        e.stopPropagation();
        profileDropdown.classList.toggle('show');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!profileContainer.contains(e.target)) {
            profileDropdown.classList.remove('show');
        }
    });
    
    // Close dropdown on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            profileDropdown.classList.remove('show');
        }
    });
    
    // Add elements to container
    profileContainer.appendChild(profileIcon);
    profileContainer.appendChild(profileDropdown);
    
    // Insert before hamburger menu
    const hamburger = navActions.querySelector('.hamburger');
    if (hamburger) {
        navActions.insertBefore(profileContainer, hamburger);
    } else {
        navActions.appendChild(profileContainer);
    }
}

// Menu filtering functionality
function initMenuFiltering() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const menuGrid = document.getElementById('menu-grid');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');

            const category = this.getAttribute('data-category');
            filterMenuItems(category);
        });
    });
}

// Filter menu items based on category
function filterMenuItems(category) {
    const menuItems = document.querySelectorAll('.menu-item');
    
    menuItems.forEach(item => {
        const itemCategory = item.getAttribute('data-category');
        
        if (category === 'all' || itemCategory === category) {
            item.style.display = 'block';
            item.classList.add('fade-in-up');
        } else {
            item.style.display = 'none';
            item.classList.remove('fade-in-up');
        }
    });
}

// Load menu items from PHP backend
function loadMenuItems() {
    const menuGrid = document.getElementById('menu-grid');
    // Only run if the menu grid exists on the page
    if (!menuGrid) {
        return;
    }
    
    // Show loading state
    menuGrid.innerHTML = '<div style="text-align: center; padding: 2rem;"><div class="loading"></div><p>Loading featured menu...</p></div>';
    
    // Fetch menu data from PHP backend
    fetch('../backend/api/get_menu.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                menuGrid.innerHTML = '';
                // Show only first 6 items as featured
                const featuredItems = data.menu.slice(0, 6);
                featuredItems.forEach(item => {
                    const menuItem = createMenuItem(item);
                    menuGrid.appendChild(menuItem);
                });
            } else {
                menuGrid.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--error);">Failed to load menu items</div>';
            }
        })
        .catch(error => {
            console.error('Error loading menu:', error);
            // Fallback to sample data if API fails
            loadSampleMenuItems();
        });
}

// Fallback to sample menu data
function loadSampleMenuItems() {
    const menuGrid = document.getElementById('menu-grid');
    
    const menuData = [
        {
            id: 1,
            title: 'Special Gujarati Thali',
            description: 'A grand platter featuring 3 sabzis, dal, kadhi, rotis, rice, farsan, a sweet, and buttermilk.',
            price: 15.99,
            category: 'Gujarati Thali',
            image_path: 'gujarati-thali.jpg'
        },
        {
            id: 2,
            title: 'Paneer Butter Masala',
            description: 'Soft paneer cubes cooked in a rich and creamy tomato-based gravy.',
            price: 11.99,
            category: 'Curries & Sabzis',
            image_path: 'paneer-butter-masala.jpg'
        },
        {
            id: 3,
            title: 'Thepla (5 pcs)',
            description: 'Soft and flavorful fenugreek flatbread, a staple in Gujarati households.',
            price: 5.99,
            category: 'Breads',
            image_path: 'thepla.jpg'
        },
        {
            id: 4,
            title: 'Pani Puri',
            description: 'Crispy hollow puris filled with spicy and tangy mint-flavored water.',
            price: 4.99,
            category: 'Street Food',
            image_path: 'pani-puri.jpg'
        },
        {
            id: 5,
            title: 'Mohanthal',
            description: 'A rich and fudgy sweet made from gram flour, ghee, sugar, and nuts.',
            price: 7.99,
            category: 'Sweets',
            image_path: 'mohanthal.jpg'
        },
        {
            id: 6,
            title: 'Masala Chaas',
            description: 'Spiced buttermilk, a refreshing and digestive drink.',
            price: 2.99,
            category: 'Beverages',
            image_path: 'masala-chaas.jpg'
        }
    ];

    menuGrid.innerHTML = '';
    menuData.forEach(item => {
        const menuItem = createMenuItem(item);
        menuGrid.appendChild(menuItem);
    });
}

// Create menu item element
function createMenuItem(item) {
    const menuItem = document.createElement('div');
    menuItem.className = 'menu-item';
    menuItem.setAttribute('data-category', item.category);
    
    const imagePath = item.image_path ? `/e-com/mmm-main/backend/uploads/${item.image_path}` : '/e-com/mmm-main/frontend/images/placeholder.jpg';
    
    // Conversion rate
    const usdToInrRate = 86.6;
    const priceInInr = parseFloat(item.price) * usdToInrRate;

    menuItem.innerHTML = `
        <a href="dish.html?id=${item.id}" class="menu-item-link">
            <div class="menu-item-image">
                <img src="${imagePath}" alt="${item.title}" onerror="this.onerror=null; this.src='/e-com/mmm-main/frontend/images/placeholder.jpg';">
            </div>
        </a>
        <div class="menu-item-content">
            <a href="dish.html?id=${item.id}" class="menu-item-link">
                <h3 class="menu-item-title">${item.title}</h3>
            </a>
            <p class="menu-item-description">${item.description}</p>
            <div class="menu-item-footer">
                <div class="menu-item-price">₹${priceInInr.toFixed(2)}</div>
                <button class="add-to-cart-btn" onclick="addToCart(${item.id})">
                    <i class="fas fa-plus"></i> Add to Cart
                </button>
            </div>
        </div>
    `;
    
    return menuItem;
}

// Scroll effects
function initScrollEffects() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.step, .menu-item, .stat-item').forEach(el => {
        observer.observe(el);
    });
}

// Contact form functionality
function initContactForm() {
    const contactForm = document.getElementById('contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Simple validation
            if (!data.name || !data.email || !data.subject || !data.message) {
                showToast('Please fill in all fields', 'error');
                return;
            }
            
            if (!isValidEmail(data.email)) {
                showToast('Please enter a valid email address', 'error');
                return;
            }
            
            // Submit to PHP backend
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<span class="loading"></span> Sending...';
            submitBtn.disabled = true;
            
            // Send to PHP contact handler
            fetch('../backend/api/contact.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Message sent successfully! We\'ll get back to you soon.', 'success');
                    this.reset();
                } else {
                    showToast(data.message || 'Failed to send message', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network error. Please try again.', 'error');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Initialize animations
function initAnimations() {
    // Animate stats on scroll
    const stats = document.querySelectorAll('.stat-item');
    const statsObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
            }
        });
    }, { threshold: 0.5 });

    stats.forEach(stat => statsObserver.observe(stat));
}

// Animate counter
function animateCounter(element) {
    const counter = element.querySelector('h3');
    const target = parseInt(counter.textContent);
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;

    const timer = setInterval(() => {
        current += step;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        counter.textContent = Math.floor(current) + (counter.textContent.includes('+') ? '+' : '');
    }, 16);
}

// Show toast notification
function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast ${type}`;
    toast.classList.add('show');
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Add to cart function - integrated with PHP backend
function addToCart(itemId) {
    const button = event.target.closest('.add-to-cart-btn');
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<span class="loading"></span> Adding...';
    button.disabled = true;
    
    // Send to PHP backend
    const formData = new FormData();
    formData.append('food_id', itemId);
    
    fetch('../backend/cart_add.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count in header
            updateCartCount(data.cart_count);
            
            // Show success message
            showToast('Item added to cart!', 'success');
            
            // Update button text temporarily
            button.innerHTML = '<i class="fas fa-check"></i> Added!';
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 2000);
        } else {
            showToast(data.message || 'Failed to add item to cart', 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error. Please try again.', 'error');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Load cart count from PHP backend
function loadCartCount() {
    fetch('../backend/api/cart_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartCount(data.count);
            }
        })
        .catch(error => {
            console.error('Error loading cart count:', error);
        });
}

// Update cart count in header
function updateCartCount(count) {
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        if (count > 0) {
            cartCount.textContent = count;
            cartCount.style.display = 'flex';
        } else {
            cartCount.textContent = '0';
            cartCount.style.display = 'flex';
        }
    }
}

// Utility function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR'
    }).format(amount);
}

// Utility function to debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Handle window resize
window.addEventListener('resize', debounce(function() {
    // Handle responsive adjustments
    const navMenu = document.getElementById('nav-menu');
    const hamburger = document.getElementById('hamburger');
    
    if (window.innerWidth > 768) {
        if (navMenu) {
            navMenu.classList.remove('active');
        }
        if (hamburger) {
            hamburger.classList.remove('active');
        }
    }
}, 250));

// Export functions for use in other scripts
window.VeggieDelight = {
    showToast,
    addToCart,
    formatCurrency,
    updateCartCount
}; 
// Dish Detail Page JavaScript
// Handles all interactive functionality for the product page

// Global variables
let currentDish = null;
let currentQuantity = 1;
let currentPrice = 0;
let isFavorite = false;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the page
    initDishDetailPage();
    initTabs();
    initStarRating();
    initQuantitySelector();
    initStickyCartBar();
    loadCartCount();
});

// Initialize dish detail page
function initDishDetailPage() {
    const urlParams = new URLSearchParams(window.location.search);
    const foodId = urlParams.get('id');
    
    if (!foodId) {
        showError('No dish selected. <a href="menu.html">Go back to menu</a>.');
        return;
    }
    
    // Load dish details
    loadDishDetails(foodId);
    
    // Load reviews
    loadReviews(foodId);
    
    // Load similar items
    loadSimilarItems(foodId);
    
    // Check authentication for review form
    checkAuthForReviews();
}

// Load dish details from backend
function loadDishDetails(foodId) {
    const container = document.getElementById('dish-details-container');
    
    fetch(`../backend/api/get_dish.php?id=${foodId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.food) {
                currentDish = data.food;
                currentPrice = parseFloat(data.food.price);
                displayDishDetails(data.food);
                loadIngredients(data.food);
                loadNutritionData(data.food);
                updateStickyCart();
            } else {
                showError(data.message || 'Could not find dish.');
            }
        })
        .catch(error => {
            console.error('Error fetching dish:', error);
            showError('Error loading dish details. Please try again.');
        });
}

// Display dish details in the main section
function displayDishDetails(food) {
    const container = document.getElementById('dish-details-container');
    const imagePath = food.image_path ? `/e-com/mmm-main/backend/uploads/${food.image_path}` : '/e-com/mmm-main/frontend/images/placeholder.jpg';
    
    // Update page title and breadcrumb
    document.title = `${food.title} - VeggieDelight`;
    document.getElementById('breadcrumb-current').textContent = food.title;
    
    // Generate tags based on dish properties
    const tags = generateTags(food);
    
    // INR Conversion
    const usdToInrRate = 86.6;
    const priceInInr = parseFloat(food.price) * usdToInrRate;

    container.innerHTML = `
        <div class="dish-image-container">
            <img src="${imagePath}" alt="${food.title}" class="dish-image" onerror="this.onerror=null; this.src='/e-com/mmm-main/frontend/images/placeholder.jpg';">
            <div class="dish-image-overlay">
                <button class="favorite-btn ${isFavorite ? 'active' : ''}" onclick="toggleFavorite()">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
        </div>
        
        <div class="dish-content">
            <span class="dish-category">${food.category_name || 'Vegetarian'}</span>
            <h1 class="dish-title">${food.title}</h1>
            
            <div class="dish-rating">
                <div class="rating-stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="far fa-star"></i>
                </div>
                <span class="rating-text">4.2 (24 reviews)</span>
            </div>
            
            <div class="dish-tags">
                ${tags.map(tag => `<span class="dish-tag ${tag.class}">${tag.text}</span>`).join('')}
            </div>
            
            <p class="dish-description">${food.description}</p>
            
            <div class="dish-price-section">
                <div class="dish-price">₹${priceInInr.toFixed(2)}</div>
                <div class="quantity-selector">
                    <button class="quantity-btn minus" onclick="updateQuantity(-1)">
                        <i class="fas fa-minus"></i>
                    </button>
                    <span class="quantity-value" id="quantity-value">1</span>
                    <button class="quantity-btn plus" onclick="updateQuantity(1)">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            
            <div class="dish-actions">
                <button class="add-to-cart-btn" onclick="addToCart()">
                    <i class="fas fa-shopping-cart"></i>
                    Add to Cart
                </button>
            </div>
        </div>
    `;
}

// Generate tags based on dish properties
function generateTags(food) {
    const tags = [];
    
    // Always add vegetarian tag
    tags.push({ text: '🌿 Vegetarian', class: 'vegetarian' });
    
    // Add other tags based on dish properties
    if (food.title.toLowerCase().includes('spicy') || food.title.toLowerCase().includes('hot')) {
        tags.push({ text: '🔥 Spicy', class: 'spicy' });
    }
    
    if (food.title.toLowerCase().includes('popular') || food.title.toLowerCase().includes('best')) {
        tags.push({ text: '⭐ Popular', class: 'popular' });
    }
    
    // Add new tag for recently added items (you can customize this logic)
    if (food.id <= 3) {
        tags.push({ text: '🆕 New', class: 'new' });
    }
    
    return tags;
}

// Load ingredients data
function loadIngredients(food) {
    const ingredientsList = document.getElementById('ingredients-list');
    
    // Sample ingredients based on dish category
    const ingredients = getIngredientsByCategory(food.category_name || 'Vegetarian');
    
    ingredientsList.innerHTML = ingredients.map(ingredient => `
        <div class="ingredient-item">
            <i class="fas fa-check-circle"></i>
            <span>${ingredient}</span>
        </div>
    `).join('');
}

// Get ingredients based on category
function getIngredientsByCategory(category) {
    const ingredientsMap = {
        'Gujarati Thali': ['Rice', 'Dal', 'Roti', 'Sabzi', 'Kadhi', 'Farsan', 'Sweet', 'Buttermilk'],
        'Curries & Sabzis': ['Fresh Vegetables', 'Spices', 'Oil', 'Onions', 'Tomatoes', 'Ginger', 'Garlic'],
        'Breads': ['Wheat Flour', 'Oil', 'Salt', 'Water', 'Spices'],
        'Street Food': ['Potatoes', 'Spices', 'Oil', 'Chutney', 'Sev', 'Onions'],
        'Sweets': ['Sugar', 'Ghee', 'Flour', 'Nuts', 'Cardamom'],
        'Beverages': ['Milk', 'Yogurt', 'Spices', 'Sugar', 'Water']
    };
    
    return ingredientsMap[category] || ['Fresh Ingredients', 'Natural Spices', 'Pure Oil', 'Salt', 'Water'];
}

// Load nutrition data
function loadNutritionData(food) {
    // Sample nutrition data (in real app, this would come from database)
    const nutritionData = {
        calories: Math.floor(Math.random() * 300) + 200,
        protein: Math.floor(Math.random() * 15) + 5,
        carbs: Math.floor(Math.random() * 40) + 20,
        fat: Math.floor(Math.random() * 15) + 5,
        fiber: Math.floor(Math.random() * 8) + 2,
        sodium: Math.floor(Math.random() * 500) + 200
    };
    
    document.getElementById('calories').textContent = nutritionData.calories + ' cal';
    document.getElementById('protein').textContent = nutritionData.protein + 'g';
    document.getElementById('carbs').textContent = nutritionData.carbs + 'g';
    document.getElementById('fat').textContent = nutritionData.fat + 'g';
    document.getElementById('fiber').textContent = nutritionData.fiber + 'g';
    document.getElementById('sodium').textContent = nutritionData.sodium + 'mg';
}

// Initialize tabs functionality
function initTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and panes
            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked button and corresponding pane
            this.classList.add('active');
            document.getElementById(`${targetTab}-tab`).classList.add('active');
        });
    });
}

// Initialize star rating
function initStarRating() {
    const stars = document.querySelectorAll('.stars .fa-star');
    
    stars.forEach(star => {
        star.addEventListener('mouseover', function() {
            const value = this.dataset.value;
            stars.forEach(s => {
                s.classList.toggle('fas', s.dataset.value <= value);
                s.classList.toggle('far', s.dataset.value > value);
            });
        });
        
        star.addEventListener('click', function() {
            document.getElementById('rating-value').value = this.dataset.value;
        });
    });
    
    // Reset stars when mouse leaves the container
    document.querySelector('.stars').addEventListener('mouseleave', function() {
        const selectedRating = document.getElementById('rating-value').value;
        stars.forEach(s => {
            s.classList.toggle('fas', s.dataset.value <= selectedRating);
            s.classList.toggle('far', s.dataset.value > selectedRating);
        });
    });
}

// Initialize quantity selector
function initQuantitySelector() {
    // Quantity functionality is handled by updateQuantity function
}

// Update quantity
function updateQuantity(change) {
    const newQuantity = currentQuantity + change;
    if (newQuantity >= 1 && newQuantity <= 10) {
        currentQuantity = newQuantity;
        document.getElementById('quantity-value').textContent = currentQuantity;
        updateStickyCart();
    }
}

// Initialize sticky cart bar
function initStickyCartBar() {
    // Show sticky bar on mobile
    if (window.innerWidth <= 768) {
        document.getElementById('sticky-cart-bar').style.display = 'block';
    }
    
    // Update sticky bar on scroll
    window.addEventListener('scroll', function() {
        const stickyBar = document.getElementById('sticky-cart-bar');
        if (window.innerWidth <= 768) {
            stickyBar.style.display = 'block';
        }
    });
}

// Update sticky cart bar
function updateStickyCart() {
    if (currentPrice && currentQuantity) {
        // INR Conversion
        const usdToInrRate = 86.6;
        const totalPriceInr = (currentPrice * currentQuantity) * usdToInrRate;
        document.getElementById('sticky-price').textContent = `₹${totalPriceInr.toFixed(2)}`;
        document.getElementById('sticky-quantity').textContent = currentQuantity;
    }
}

// Add to cart functionality
function addToCart() {
    if (!currentDish) return;
    
    const button = document.querySelector('.add-to-cart-btn');
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<span class="loading"></span> Adding...';
    button.disabled = true;
    
    // Send to PHP backend
    const formData = new FormData();
    formData.append('food_id', currentDish.id);
    formData.append('quantity', currentQuantity);
    
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

// Add to cart from sticky bar
function addToCartFromSticky() {
    addToCart();
}

// Toggle favorite
function toggleFavorite() {
    isFavorite = !isFavorite;
    const favoriteBtn = document.querySelector('.favorite-btn');
    favoriteBtn.classList.toggle('active', isFavorite);
    
    showToast(isFavorite ? 'Added to favorites!' : 'Removed from favorites', 'success');
}

// Load reviews
function loadReviews(foodId) {
    fetch(`../backend/api/get_reviews.php?food_id=${foodId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayReviews(data.reviews);
                displayReviewsSummary(data.reviews);
            }
        })
        .catch(error => {
            console.error('Error loading reviews:', error);
        });
}

// Display reviews
function displayReviews(reviews) {
    const reviewsList = document.getElementById('reviews-list');
    
    if (reviews.length === 0) {
        reviewsList.innerHTML = '<p style="text-align: center; color: var(--text-secondary);">Be the first to leave a review for this dish!</p>';
        return;
    }
    
    reviewsList.innerHTML = reviews.map(review => `
        <div class="review-card">
            <div class="review-header">
                <strong class="review-user">${review.user_name}</strong>
                <span class="review-date">${new Date(review.created_at).toLocaleDateString()}</span>
            </div>
            <div class="review-rating">${'★'.repeat(review.rating)}${'☆'.repeat(5 - review.rating)}</div>
            <p class="review-comment">${review.comment}</p>
        </div>
    `).join('');
}

// Display reviews summary
function displayReviewsSummary(reviews) {
    const reviewsSummary = document.getElementById('reviews-summary');
    
    if (reviews.length === 0) {
        reviewsSummary.innerHTML = '<span class="rating-text">No reviews yet</span>';
        return;
    }
    
    const avgRating = reviews.reduce((sum, review) => sum + review.rating, 0) / reviews.length;
    
    reviewsSummary.innerHTML = `
        <div class="overall-rating">
            <div class="stars">
                ${'★'.repeat(Math.floor(avgRating))}${'☆'.repeat(5 - Math.floor(avgRating))}
            </div>
            <span class="rating-text">${avgRating.toFixed(1)} (${reviews.length} reviews)</span>
        </div>
    `;
}

// Load similar items
function loadSimilarItems(foodId) {
    fetch('../backend/api/get_menu.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Get 4 random items (excluding current dish)
                const similarItems = data.menu
                    .filter(item => item.id != foodId)
                    .sort(() => 0.5 - Math.random())
                    .slice(0, 4);
                
                displaySimilarItems(similarItems);
            }
        })
        .catch(error => {
            console.error('Error loading similar items:', error);
        });
}

// Display similar items
function displaySimilarItems(items) {
    const similarGrid = document.getElementById('similar-items-grid');
    const usdToInrRate = 86.6;

    similarGrid.innerHTML = items.map(item => {
        const imagePath = item.image_path ? `/e-com/mmm-main/backend/uploads/${item.image_path}` : '/e-com/mmm-main/frontend/images/placeholder.jpg';
        const priceInInr = parseFloat(item.price) * usdToInrRate;
        return `
            <div class="similar-item" onclick="window.location.href='dish.html?id=${item.id}'">
                <div class="similar-item-image">
                    <img src="${imagePath}" alt="${item.title}" onerror="this.onerror=null; this.src='/e-com/mmm-main/frontend/images/placeholder.jpg';">
                </div>
                <div class="similar-item-content">
                    <h3 class="similar-item-title">${item.title}</h3>
                    <div class="similar-item-price">₹${priceInInr.toFixed(2)}</div>
                </div>
            </div>
        `;
    }).join('');
}

// Check authentication for reviews
function checkAuthForReviews() {
    fetch('../backend/api/check_auth.php')
        .then(res => res.json())
        .then(data => {
            if (data.logged_in) {
                document.getElementById('review-form-container').style.display = 'block';
                document.getElementById('review-login-prompt').style.display = 'none';
            } else {
                document.getElementById('review-form-container').style.display = 'none';
                document.getElementById('review-login-prompt').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error checking auth:', error);
        });
}

// Handle review submission
document.addEventListener('DOMContentLoaded', function() {
    const reviewForm = document.getElementById('review-form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const urlParams = new URLSearchParams(window.location.search);
            const foodId = urlParams.get('id');
            
            const formData = new FormData(this);
            formData.append('food_id', foodId);
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<span class="loading"></span> Submitting...';
            submitBtn.disabled = true;
            
            fetch('../backend/api/add_review.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Review submitted successfully!', 'success');
                    this.reset();
                    loadReviews(foodId);
                } else {
                    showToast(data.message || 'Failed to submit review', 'error');
                }
            })
            .catch(() => {
                showToast('An error occurred. Please try again.', 'error');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});

// Load cart count
function loadCartCount() {
    fetch('../backend/api/cart_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartCount(data.count);
            }
        })
        .catch(error => console.error('Error loading cart count:', error));
}

// Update cart count display
function updateCartCount(count) {
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        cartCount.textContent = count;
        cartCount.style.display = count > 0 ? 'flex' : 'flex';
    }
}

// Show error message
function showError(message) {
    const container = document.getElementById('dish-details-container');
    container.innerHTML = `<div class="error-message">${message}</div>`;
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

// Handle window resize
window.addEventListener('resize', function() {
    const stickyBar = document.getElementById('sticky-cart-bar');
    if (window.innerWidth <= 768) {
        stickyBar.style.display = 'block';
    } else {
        stickyBar.style.display = 'none';
    }
}); 
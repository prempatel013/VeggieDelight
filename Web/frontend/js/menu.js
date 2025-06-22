// VeggieDelight Menu Page JavaScript
// Handles full menu display, filtering, search, and pagination

document.addEventListener('DOMContentLoaded', function() {
    // Initialize menu page components
    initMenuPage();
    initSearch();
    initMenuFiltering();
    loadCartCount();
});

// Global variables for menu management
let allMenuItems = [];
let filteredItems = [];
let currentPage = 1;
const itemsPerPage = 12;
let currentCategory = 'all';
let currentSearch = '';

// Initialize menu page
function initMenuPage() {
    loadAllMenuItems();
}

// Load all menu items from backend
function loadAllMenuItems() {
    const menuGrid = document.getElementById('menu-grid');
    
    // Show loading state
    menuGrid.innerHTML = '<div style="text-align: center; padding: 2rem;"><div class="loading"></div><p>Loading menu...</p></div>';
    
    // Fetch all menu data from PHP backend
    fetch('../backend/api/get_menu.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allMenuItems = data.menu;
                filteredItems = [...allMenuItems];
                displayMenuItems();
            } else {
                menuGrid.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--error);">Failed to load menu items</div>';
            }
        })
        .catch(error => {
            console.error('Error loading menu:', error);
            // Fallback to sample data
            loadSampleMenuItems();
        });
}

// Fallback to sample menu data
function loadSampleMenuItems() {
    allMenuItems = [
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
            title: 'Kathiyawadi Thali',
            description: 'A spicy and rustic thali with sev tameta, lasaniya bateta, bajra no rotlo, rice, and jaggery.',
            price: 16.99,
            category: 'Gujarati Thali',
            image_path: 'kathiyawadi-thali.jpg'
        },
        {
            id: 3,
            title: 'Undhiyu',
            description: 'A classic Gujarati mixed vegetable dish, slow-cooked to perfection with a blend of spices.',
            price: 12.99,
            category: 'Curries & Sabzis',
            image_path: 'undhiyu.jpg'
        },
        {
            id: 4,
            title: 'Paneer Butter Masala',
            description: 'Soft paneer cubes cooked in a rich and creamy tomato-based gravy.',
            price: 11.99,
            category: 'Curries & Sabzis',
            image_path: 'paneer-butter-masala.jpg'
        },
        {
            id: 5,
            title: 'Thepla (5 pcs)',
            description: 'Soft and flavorful fenugreek flatbread, a staple in Gujarati households.',
            price: 5.99,
            category: 'Breads',
            image_path: 'thepla.jpg'
        },
        {
            id: 6,
            title: 'Puran Poli',
            description: 'Sweet flatbread stuffed with a delicious mixture of chana dal, jaggery, and cardamom.',
            price: 6.99,
            category: 'Breads',
            image_path: 'puran-poli.jpg'
        },
        {
            id: 7,
            title: 'Pani Puri',
            description: 'Crispy hollow puris filled with spicy and tangy mint-flavored water.',
            price: 4.99,
            category: 'Street Food',
            image_path: 'pani-puri.jpg'
        },
        {
            id: 8,
            title: 'Dabeli',
            description: 'A sweet and spicy potato mixture stuffed in a pav (bun), garnished with pomegranate and roasted peanuts.',
            price: 3.99,
            category: 'Street Food',
            image_path: 'dabeli.jpg'
        },
        {
            id: 9,
            title: 'Mohanthal',
            description: 'A rich and fudgy sweet made from gram flour, ghee, sugar, and nuts.',
            price: 7.99,
            category: 'Sweets',
            image_path: 'mohanthal.jpg'
        },
        {
            id: 10,
            title: 'Gulab Jamun (2 pcs)',
            description: 'Soft, spongy balls made of milk solids, deep-fried and soaked in a light sugar syrup.',
            price: 4.49,
            category: 'Sweets',
            image_path: 'gulab-jamun.jpg'
        },
        {
            id: 11,
            title: 'Masala Chaas',
            description: 'Spiced buttermilk, a refreshing and digestive drink.',
            price: 2.99,
            category: 'Beverages',
            image_path: 'masala-chaas.jpg'
        },
        {
            id: 12,
            title: 'Mango Lassi',
            description: 'A creamy and delicious yogurt-based drink, blended with sweet mango pulp.',
            price: 3.99,
            category: 'Beverages',
            image_path: 'mango-lassi.jpg'
        }
    ];
    
    filteredItems = [...allMenuItems];
    displayMenuItems();
}

// Display menu items with pagination
function displayMenuItems() {
    const menuGrid = document.getElementById('menu-grid');
    const loadMoreContainer = document.getElementById('load-more-container');
    
    // Calculate pagination
    const startIndex = 0;
    const endIndex = currentPage * itemsPerPage;
    const itemsToShow = filteredItems.slice(startIndex, endIndex);
    
    // Clear existing items
    menuGrid.innerHTML = '';
    
    if (itemsToShow.length === 0) {
        menuGrid.innerHTML = `
            <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <h3>No items found</h3>
                <p>Try adjusting your search or filter criteria</p>
            </div>
        `;
        loadMoreContainer.style.display = 'none';
        return;
    }
    
    // Display items
    itemsToShow.forEach(item => {
        const menuItem = createMenuItem(item);
        menuGrid.appendChild(menuItem);
    });
    
    // Show/hide load more button
    if (endIndex < filteredItems.length) {
        loadMoreContainer.style.display = 'block';
    } else {
        loadMoreContainer.style.display = 'none';
    }
    
    // Update results count
    updateResultsCount();
}

// Initialize search functionality
function initSearch() {
    const searchInput = document.getElementById('menu-search');
    
    searchInput.addEventListener('input', debounce(function() {
        currentSearch = this.value.toLowerCase().trim();
        filterItems();
    }, 300));
}

// Initialize menu filtering
function initMenuFiltering() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            currentCategory = this.getAttribute('data-category');
            currentPage = 1; // Reset to first page
            filterItems();
        });
    });
    
    // Initialize load more button
    const loadMoreBtn = document.getElementById('load-more-btn');
    loadMoreBtn.addEventListener('click', function() {
        currentPage++;
        displayMenuItems();
    });
}

// Filter items based on category and search
function filterItems() {
    filteredItems = allMenuItems.filter(item => {
        const matchesCategory = currentCategory === 'all' || item.category === currentCategory;
        const matchesSearch = currentSearch === '' || 
            item.title.toLowerCase().includes(currentSearch) ||
            item.description.toLowerCase().includes(currentSearch) ||
            item.category.toLowerCase().includes(currentSearch);
        
        return matchesCategory && matchesSearch;
    });
    
    currentPage = 1; // Reset to first page
    displayMenuItems();
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

// Update results count display
function updateResultsCount() {
    const totalItems = filteredItems.length;
    const displayedItems = Math.min(currentPage * itemsPerPage, totalItems);
    
    // You can add a results counter element if needed
    // For now, we'll just log it
    console.log(`Showing ${displayedItems} of ${totalItems} items`);
}

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
        cartCount.style.display = count > 0 ? 'block' : 'none';
    }
}

// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
} 
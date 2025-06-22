document.addEventListener('DOMContentLoaded', function() {
    loadCart();
});

// Load cart from PHP backend
function loadCart() {
    const cartLoading = document.getElementById('cart-loading');
    const emptyCart = document.getElementById('empty-cart');
    const cartContentWrapper = document.getElementById('cart-content-wrapper');

    // Show loading state first
    cartLoading.style.display = 'block';
    emptyCart.style.display = 'none';
    cartContentWrapper.style.display = 'none';
    
    fetch('../backend/api/get_cart.php')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            cartLoading.style.display = 'none'; // Hide loading state
            if (data.success && data.cart && data.cart.length > 0) {
                cartContentWrapper.style.display = 'flex';
                renderCart(data.cart);
                updateCartSummary(data.cart, data.total);
                initCartEventListeners(data.cart);
            } else {
                emptyCart.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading cart:', error);
            cartLoading.style.display = 'none';
            emptyCart.style.display = 'block';
            showToast('Could not load your cart. Please try again.', 'error');
        });
}

function renderCart(cartItems) {
    const cartContainer = document.getElementById('cart-items');
    if (!cartContainer) return;

    const usdToInrRate = 86.6;
    cartContainer.innerHTML = cartItems.map(item => {
        const itemPriceInr = parseFloat(item.price) * usdToInrRate;
        const itemTotalInr = itemPriceInr * item.quantity;
        return `
        <div class="cart-item" data-id="${item.id}">
            <div class="cart-item-image">
                <img src="/e-com/mmm-main/backend/uploads/${item.image_path}" alt="${item.title}" onerror="this.onerror=null; this.src='/e-com/mmm-main/frontend/images/placeholder.jpg';">
            </div>
            <div class="cart-item-details">
                <h3 class="cart-item-title">${item.title}</h3>
                <p class="cart-item-price">₹${itemPriceInr.toFixed(2)}</p>
            </div>
            <div class="cart-item-quantity">
                <button class="quantity-btn minus" data-id="${item.id}" data-change="-1">-</button>
                <span class="quantity-value">${item.quantity}</span>
                <button class="quantity-btn plus" data-id="${item.id}" data-change="1">+</button>
            </div>
            <div class="cart-item-total">
                ₹${itemTotalInr.toFixed(2)}
            </div>
            <button class="remove-item-btn" data-id="${item.id}">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        `;
    }).join('');
}

function updateCartSummary(cartItems, cartTotal) {
    const subtotalElement = document.getElementById('cart-subtotal');
    const taxElement = document.getElementById('cart-tax');
    const totalElement = document.getElementById('cart-total');
    
    const usdToInrRate = 86.6;
    const subtotal = cartTotal;
    const tax = subtotal * 0.08;
    const total = subtotal + tax;
    
    const subtotalInr = subtotal * usdToInrRate;
    const taxInr = tax * usdToInrRate;
    const totalInr = total * usdToInrRate;

    if (subtotalElement) subtotalElement.textContent = `₹${subtotalInr.toFixed(2)}`;
    if (taxElement) taxElement.textContent = `₹${taxInr.toFixed(2)}`;
    if (totalElement) totalElement.textContent = `₹${totalInr.toFixed(2)}`;
}

function initCartEventListeners(cart) {
    document.getElementById('checkout-btn').addEventListener('click', () => {
        if (cart.length === 0) {
            showToast('Your cart is empty!', 'error');
            return;
        }
        window.location.href = 'checkout.html';
    });

    document.getElementById('clear-cart-btn').addEventListener('click', clearCart);

    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const foodId = e.currentTarget.dataset.id;
            const change = parseInt(e.currentTarget.dataset.change);
            updateQuantity(foodId, change, cart);
        });
    });

    document.querySelectorAll('.remove-item-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const foodId = e.currentTarget.dataset.id;
            removeFromCart(foodId);
        });
    });
}

function updateQuantity(foodId, change, cart) {
    const item = cart.find(i => i.id == foodId);
    if (!item) return;

    const newQuantity = item.quantity + change;
    if (newQuantity <= 0) {
        removeFromCart(foodId);
        return;
    }

    const formData = new FormData();
    formData.append('food_id', foodId);
    formData.append('quantity', newQuantity);

    fetch('../backend/api/update_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadCart();
        } else {
            showToast(data.message || 'Failed to update quantity', 'error');
        }
    })
    .catch(err => {
        showToast('An error occurred.', 'error');
        console.error(err);
    });
}

function removeFromCart(foodId) {
    const formData = new FormData();
    formData.append('food_id', foodId);

    fetch('../backend/api/remove_from_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadCart();
            showToast('Item removed from cart.', 'success');
        } else {
            showToast(data.message || 'Failed to remove item.', 'error');
        }
    })
    .catch(err => {
        showToast('An error occurred.', 'error');
        console.error(err);
    });
}

function clearCart() {
    if (!confirm('Are you sure you want to clear your entire cart?')) {
        return;
    }

    fetch('../backend/api/clear_cart.php', {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadCart();
            showToast('Cart has been cleared.', 'success');
        } else {
            showToast(data.message || 'Failed to clear cart.', 'error');
        }
    })
    .catch(err => {
        showToast('An error occurred.', 'error');
        console.error(err);
    });
}

/**
 * POS System JavaScript
 */

$(document).ready(function() {
    // Setup CSRF token for AJAX requests
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const csrfParam = $('meta[name="csrf-param"]').attr('content');
    
    // Add CSRF token to all AJAX requests
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                xhr.setRequestHeader('X-CSRF-Token', csrfToken);
                
                // If data is sent as FormData, add CSRF token to it
                if (settings.data instanceof FormData) {
                    settings.data.append(csrfParam, csrfToken);
                } 
                // If data is a string, add CSRF token
                else if (typeof settings.data === 'string') {
                    settings.data = settings.data + '&' + csrfParam + '=' + csrfToken;
                }
                // If data is an object, add CSRF token
                else if (settings.data && typeof settings.data === 'object' && !(settings.data instanceof FormData)) {
                    settings.data[csrfParam] = csrfToken;
                }
                // If no data sent, create an object with CSRF token
                else {
                    settings.data = {};
                    settings.data[csrfParam] = csrfToken;
                }
            }
        }
    });
    
    // Variables
    let cart = [];
    let currentCategoryId = '';
    let currentPage = 1;
    let pageSize = 20;
    let totalProducts = 0;
    let totalPages = 0;
    let selectedCustomer = null;
    let currentDiscount = 0;
    let currentDiscountType = 'amount'; // 'amount' or 'percent'
    let holdOrderId = null;
    
    // Initialize
    updateClock();
    loadProducts();
    
    // Update clock
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        const dateString = now.toLocaleDateString('vi-VN');
        
        $('#current-time').text(timeString);
        $('#current-date').text(dateString);
        
        setTimeout(updateClock, 1000);
    }
    
    // Load products
    function loadProducts(category = null) {
        showLoading();
        
        $.ajax({
            url: baseUrl + '/pos/get-products',
            type: 'GET',
            data: {
                category_id: category,
                page: currentPage,
                page_size: pageSize,
                sort: $('#productSort').val() || 'name'
            },
            dataType: 'json',
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    renderProducts(response.products);
                    totalProducts = response.total;
                    totalPages = response.total_pages;
                    currentPage = response.page;
                    
                    $('#showing-products').text(response.products.length);
                    $('#total-products').text(response.total);
                    
                    renderPagination();
                } else {
                    alert(response.message || 'Có lỗi xảy ra khi tải sản phẩm.');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                console.error('AJAX Error:', status, error);
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    }
    
    // Render products
    function renderProducts(products) {
        const productGrid = $('#product-grid');
        productGrid.empty();
        
        if (products.length === 0) {
            productGrid.html('<div class="col-12 text-center py-5"><p>Không có sản phẩm nào.</p></div>');
            return;
        }
        
        const template = $('#product-item-template').html();
        let productHTML = '';
        
        $.each(products, function(index, product) {
            const stockClass = product.quantity <= 0 ? 'text-danger' : '';
            const outOfStock = product.quantity <= 0;
            const isNew = product.is_new || false;
            const isDiscount = product.discount_percent > 0 || false;
            
            // Replace template variables with product data
            let productCard = template
                .replace(/\${id}/g, product.id)
                .replace(/\${image}/g, product.image || baseUrl + '/images/no-image.png')
                .replace(/\${name}/g, product.name)
                .replace(/\${code}/g, product.code)
                .replace(/\${formatted_price}/g, product.formatted_price || formatCurrency(product.selling_price))
                .replace(/\${quantity}/g, product.quantity)
                .replace(/\${unit}/g, product.unit)
                .replace(/\${stockClass}/g, stockClass)
                .replace(/\${outOfStock}/g, outOfStock)
                .replace(/\${isNew}/g, isNew)
                .replace(/\${isDiscount}/g, isDiscount);
            
            productHTML += productCard;
        });
        
        productGrid.html(productHTML);
    }
    
    // Render pagination
    function renderPagination() {
        const pagination = $('#product-pagination');
        pagination.empty();
        
        if (totalPages <= 1) {
            return;
        }
        
        // Previous button
        pagination.append(`
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">«</a>
            </li>
        `);
        
        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, startPage + 4);
        
        for (let i = startPage; i <= endPage; i++) {
            pagination.append(`
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }
        
        // Next button
        pagination.append(`
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">»</a>
            </li>
        `);
        
        // Add click event to pagination
        $('.page-link').on('click', function(e) {
            e.preventDefault();
            
            const page = $(this).data('page');
            
            if (page < 1 || page > totalPages || page === currentPage) {
                return;
            }
            
            currentPage = page;
            loadProducts(currentCategoryId);
        });
    }
    
    // Add to cart
    function addToCart(product) {
        // Check if product already in cart
        const existingItemIndex = cart.findIndex(item => item.product_id === product.id);
        
        if (existingItemIndex !== -1) {
            // Check if adding more would exceed available stock
            const newQuantity = cart[existingItemIndex].quantity + 1;
            
            if (newQuantity > product.quantity) {
                alert('Không đủ số lượng tồn kho!');
                return;
            }
            
            // Update quantity
            cart[existingItemIndex].quantity = newQuantity;
            cart[existingItemIndex].total = calculateItemTotal(cart[existingItemIndex]);
        } else {
            // Add new item to cart
            const cartItem = {
                product_id: product.id,
                code: product.code,
                name: product.name,
                quantity: 1,
                price: product.selling_price,
                formatted_price: product.formatted_price || formatCurrency(product.selling_price),
                discount_percent: 0,
                discount_amount: 0,
                tax_percent: 10, // Default tax rate
                tax_amount: product.selling_price * 0.1,
                total: product.selling_price + (product.selling_price * 0.1),
                formatted_total: formatCurrency(product.selling_price + (product.selling_price * 0.1)),
                unit: product.unit,
                image: product.image || baseUrl + '/images/no-image.png'
            };
            
            cart.push(cartItem);
        }
        
        renderCart();
        updateOrderSummary();
    }
    
    // Calculate item total
    function calculateItemTotal(item) {
        const subtotal = item.quantity * item.price;
        const discount = item.discount_amount || (subtotal * (item.discount_percent / 100));
        const taxableAmount = subtotal - discount;
        const tax = taxableAmount * (item.tax_percent / 100);
        
        return taxableAmount + tax;
    }
    
    // Render cart
    function renderCart() {
        const cartItems = $('#cartItems');
        cartItems.empty();
        
        if (cart.length === 0) {
            cartItems.html(`
                <tr class="empty-cart">
                    <td colspan="4" class="text-center py-4">
                        <div class="empty-cart-icon">
                            <i class="fas fa-shopping-cart fa-3x text-muted"></i>
                        </div>
                        <p class="mt-2">Giỏ hàng trống</p>
                        <p class="text-muted small">Vui lòng chọn sản phẩm để thêm vào giỏ hàng</p>
                    </td>
                </tr>
            `);
            return;
        }
        
        const template = $('#cart-item-template').html();
        
        $.each(cart, function(index, item) {
            const cartItemHTML = template
                .replace(/\${product_id}/g, item.product_id)
                .replace(/\${image}/g, item.image)
                .replace(/\${name}/g, item.name)
                .replace(/\${formatted_price}/g, item.formatted_price)
                .replace(/\${quantity}/g, item.quantity)
                .replace(/\${formatted_total}/g, formatCurrency(item.total));
            
            cartItems.append(cartItemHTML);
        });
        
        // Highlight the last added item
        $('.cart-item:last-child').addClass('highlight-row');
        
        // Add event listeners
        $('.decrease-qty').on('click', function() {
            const productId = $(this).data('id');
            decreaseQuantity(productId);
        });
        
        $('.increase-qty').on('click', function() {
            const productId = $(this).data('id');
            increaseQuantity(productId);
        });
        
        $('.item-qty').on('change', function() {
            const productId = $(this).data('id');
            const newQuantity = parseInt($(this).val());
            
            if (isNaN(newQuantity) || newQuantity < 1) {
                $(this).val(1);
                updateItemQuantity(productId, 1);
                return;
            }
            
            updateItemQuantity(productId, newQuantity);
        });
        
        $('.remove-item').on('click', function() {
            const productId = $(this).data('id');
            removeItem(productId);
        });
    }
    
    // Decrease quantity
    function decreaseQuantity(productId) {
        const itemIndex = cart.findIndex(item => item.product_id === productId);
        
        if (itemIndex === -1) {
            return;
        }
        
        if (cart[itemIndex].quantity > 1) {
            cart[itemIndex].quantity--;
            cart[itemIndex].total = calculateItemTotal(cart[itemIndex]);
            
            renderCart();
            updateOrderSummary();
        } else {
            removeItem(productId);
        }
    }
    
    // Increase quantity
    function increaseQuantity(productId) {
        const itemIndex = cart.findIndex(item => item.product_id === productId);
        
        if (itemIndex === -1) {
            return;
        }
        
        // Get product data to check stock
        $.ajax({
            url: baseUrl + '/pos/check-stock',
            type: 'GET',
            data: {
                product_id: productId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const availableStock = response.quantity;
                    
                    if (cart[itemIndex].quantity < availableStock) {
                        cart[itemIndex].quantity++;
                        cart[itemIndex].total = calculateItemTotal(cart[itemIndex]);
                        
                        renderCart();
                        updateOrderSummary();
                    } else {
                        alert('Không đủ số lượng tồn kho!');
                    }
                } else {
                    alert(response.message || 'Không thể kiểm tra tồn kho. Vui lòng thử lại sau.');
                }
            },
            error: function() {
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    }
    
    // Update item quantity
    function updateItemQuantity(productId, newQuantity) {
        const itemIndex = cart.findIndex(item => item.product_id === productId);
        
        if (itemIndex === -1) {
            return;
        }
        
        // Get product data to check stock
        $.ajax({
            url: baseUrl + '/pos/check-stock',
            type: 'GET',
            data: {
                product_id: productId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const availableStock = response.quantity;
                    
                    if (newQuantity <= availableStock) {
                        cart[itemIndex].quantity = newQuantity;
                        cart[itemIndex].total = calculateItemTotal(cart[itemIndex]);
                        
                        renderCart();
                        updateOrderSummary();
                    } else {
                        alert('Không đủ số lượng tồn kho! Tối đa: ' + availableStock);
                        cart[itemIndex].quantity = availableStock;
                        cart[itemIndex].total = calculateItemTotal(cart[itemIndex]);
                        
                        renderCart();
                        updateOrderSummary();
                    }
                } else {
                    alert(response.message || 'Không thể kiểm tra tồn kho. Vui lòng thử lại sau.');
                }
            },
            error: function() {
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    }
    
    // Remove item from cart
    function removeItem(productId) {
        cart = cart.filter(item => item.product_id !== productId);
        
        renderCart();
        updateOrderSummary();
    }
    
    // Update order summary
    function updateOrderSummary() {
        let subtotal = 0;
        let totalQuantity = 0;
        
        cart.forEach(item => {
            subtotal += item.price * item.quantity;
            totalQuantity += item.quantity;
        });
        
        const discount = calculateDiscount(subtotal);
        const taxableAmount = subtotal - discount;
        const tax = taxableAmount * 0.1; // 10% tax
        const total = taxableAmount + tax;
        
        $('#subtotal').text(formatCurrency(subtotal));
        $('#discount').text(formatCurrency(discount));
        $('#tax').text(formatCurrency(tax));
        $('#total').text(formatCurrency(total));
        
        // Update paid amount and change amount
        const amountTendered = parseFloat($('#amountTendered').val()) || 0;
        const changeAmount = amountTendered - total;
        
        $('#changeAmount').val(formatCurrency(changeAmount >= 0 ? changeAmount : 0));
    }
    
    // Calculate discount
    function calculateDiscount(subtotal) {
        if (currentDiscountType === 'percent') {
            return subtotal * (currentDiscount / 100);
        } else {
            return currentDiscount;
        }
    }
    
    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    }
    
    // Show loading
    function showLoading() {
        $('.loading-overlay').removeClass('d-none');
    }
    
    // Hide loading
    function hideLoading() {
        $('.loading-overlay').addClass('d-none');
    }
    
    // Event Listeners
    
    // Product sort change
    $('#productSort').on('change', function() {
        currentPage = 1;
        loadProducts(currentCategoryId);
    });
    
    // Category selection
    $('.category-list .list-group-item').on('click', function(e) {
        e.preventDefault();
        
        $('.category-list .list-group-item').removeClass('active');
        $(this).addClass('active');
        
        currentCategoryId = $(this).data('category') || '';
        currentPage = 1;
        loadProducts(currentCategoryId);
    });
    
    // Product search
    $('#productSearch').on('keypress', function(e) {
        if (e.which === 13) {
            const searchTerm = $(this).val().trim();
            
            if (searchTerm) {
                searchProducts(searchTerm);
            } else {
                loadProducts(currentCategoryId);
            }
        }
    });
    
    // Barcode button click
    $('#barcodeBtn').on('click', function() {
        const searchTerm = $('#productSearch').val().trim();
        if (searchTerm) {
            searchProducts(searchTerm);
        } else {
            // Focus on input if empty
            $('#productSearch').focus();
        }
    });
    
    // Search products
    function searchProducts(term) {
        showLoading();
        
        $.ajax({
            url: baseUrl + '/pos/search-products',
            type: 'GET',
            data: {
                term: term
            },
            dataType: 'json',
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    renderProducts(response.products);
                    
                    $('#showing-products').text(response.products.length);
                    $('#total-products').text(response.products.length);
                    
                    // Clear pagination since search results don't have pages
                    $('#product-pagination').empty();
                    
                    // If only one product is found and has barcode, add to cart
                    if (response.products.length === 1 && term.length >= 8 && /^\d+$/.test(term)) {
                        addToCart(response.products[0]);
                    }
                } else {
                    alert(response.message || 'Có lỗi xảy ra khi tìm kiếm sản phẩm.');
                }
            },
            error: function() {
                hideLoading();
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    }
    
    // Add to cart button click (event delegation)
    $(document).on('click', '.add-to-cart-btn', function() {
        const productCard = $(this).closest('.product-card');
        const productId = productCard.data('id');
        
        // Get product data
        $.ajax({
            url: baseUrl + '/pos/get-product',
            type: 'GET',
            data: {
                id: productId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    addToCart(response.product);
                } else {
                    alert(response.message || 'Có lỗi xảy ra khi lấy thông tin sản phẩm.');
                }
            },
            error: function() {
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    });
    
    // Customer search
    $('#customerSearch').on('keypress', function(e) {
        if (e.which === 13) {
            const searchTerm = $(this).val().trim();
            
            if (searchTerm) {
                searchCustomers(searchTerm);
            }
        }
    });
    
    // Customer search button click
    $('#customerSearchBtn').on('click', function() {
        const searchTerm = $('#customerSearch').val().trim();
        
        if (searchTerm) {
            searchCustomers(searchTerm);
        }
    });
    
    // Search customers
    function searchCustomers(term) {
        showLoading();
        
        $.ajax({
            url: baseUrl + '/pos/search-customers',
            type: 'GET',
            data: {
                term: term
            },
            dataType: 'json',
            success: function(response) {
                hideLoading();
                
                if (response.success && response.customers.length > 0) {
                    // If only one customer found, select it
                    if (response.customers.length === 1) {
                        selectCustomer(response.customers[0]);
                    } else {
                        // Show customer selection modal
                        showCustomerSelectionModal(response.customers);
                    }
                } else {
                    alert('Không tìm thấy khách hàng. Vui lòng thử lại hoặc thêm khách hàng mới.');
                }
            },
            error: function() {
                hideLoading();
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    }
    
    // Show customer selection modal
    function showCustomerSelectionModal(customers) {
        // Create modal if it doesn't exist
        if ($('#customerSelectionModal').length === 0) {
            $('body').append(`
                <div class="modal fade" id="customerSelectionModal" tabindex="-1" role="dialog" aria-labelledby="customerSelectionModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="customerSelectionModalLabel">Chọn khách hàng</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="list-group" id="customerList"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }
        
        // Populate customer list
        const customerList = $('#customerList');
        customerList.empty();
        
        $.each(customers, function(index, customer) {
            customerList.append(`
                <a href="#" class="list-group-item list-group-item-action select-customer" data-customer='${JSON.stringify(customer)}'>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">${customer.name}</h6>
                            <small>${customer.phone || ''} ${customer.email ? '| ' + customer.email : ''}</small>
                        </div>
                        <span class="badge badge-primary">${customer.code}</span>
                    </div>
                </a>
            `);
        });
        
        // Add click event to select customer
        $('.select-customer').on('click', function() {
            const customer = $(this).data('customer');
            selectCustomer(customer);
            $('#customerSelectionModal').modal('hide');
        });
        
        // Show modal
        $('#customerSelectionModal').modal('show');
    }
    
    // Select customer
    function selectCustomer(customer) {
        selectedCustomer = customer;
        
        // Update customer info
        $('#customerName').text(customer.name);
        $('#customerPhone').text(customer.phone);
        $('#customerPoints').text(customer.points || 0);
        $('#customerDebt').text(formatCurrency(customer.debt || 0));
        
        // Show customer info
        $('#customerInfo').removeClass('d-none');
        
        // Clear search input
        $('#customerSearch').val('');
    }
    
    // Add customer button
    $('#addCustomerBtn').on('click', function() {
        $('#addCustomerModal').modal('show');
    });
    
    // Save customer button
    $('#saveCustomerBtn').on('click', function() {
        const name = $('#customerNameInput').val().trim();
        const phone = $('#customerPhoneInput').val().trim();
        const email = $('#customerEmailInput').val().trim();
        const address = $('#customerAddressInput').val().trim();
        
        if (!name || !phone) {
            alert('Vui lòng nhập đầy đủ thông tin bắt buộc (Tên và SĐT).');
            return;
        }
        
        showLoading();
        
        $.ajax({
            url: baseUrl + '/pos/add-customer',
            type: 'POST',
            data: {
                name: name,
                phone: phone,
                email: email,
                address: address
            },
            dataType: 'json',
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    selectCustomer(response.customer);
                    $('#addCustomerModal').modal('hide');
                    
                    // Clear form
                    $('#customerNameInput').val('');
                    $('#customerPhoneInput').val('');
                    $('#customerEmailInput').val('');
                    $('#customerAddressInput').val('');
                } else {
                    alert(response.message || 'Có lỗi xảy ra khi thêm khách hàng.');
                }
            },
            error: function() {
                hideLoading();
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    });
    
    // Remove customer button
    $('#removeCustomerBtn').on('click', function() {
        selectedCustomer = null;
        $('#customerInfo').addClass('d-none');
    });
    
    // Discount code
    $('#applyDiscountBtn').on('click', function() {
        const discountCode = $('#discountCode').val().trim();
        
        if (!discountCode) {
            alert('Vui lòng nhập mã giảm giá.');
            return;
        }
        
        showLoading();
        
        $.ajax({
            url: baseUrl + '/pos/check-discount',
            type: 'GET',
            data: {
                code: discountCode
            },
            dataType: 'json',
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    if (response.discount.discount_type === 1) { // percentage
                        currentDiscount = response.discount.value;
                        currentDiscountType = 'percent';
                        $('#discountPercent').val(currentDiscount);
                    } else { // amount
                        currentDiscount = response.discount.value;
                        currentDiscountType = 'amount';
                        $('#discountPercent').val(0);
                    }
                    
                    updateOrderSummary();
                    alert('Áp dụng mã giảm giá thành công!');
                } else {
                    alert(response.message || 'Mã giảm giá không hợp lệ hoặc đã hết hạn.');
                }
            },
            error: function() {
                hideLoading();
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    });
    
    // Discount percent
    $('#discountPercent').on('change', function() {
        const percent = parseFloat($(this).val()) || 0;
        
        if (percent < 0 || percent > 100) {
            $(this).val(0);
            currentDiscount = 0;
        } else {
            currentDiscount = percent;
        }
        
        currentDiscountType = 'percent';
        updateOrderSummary();
    });
    
    // Amount tendered
    $('#amountTendered').on('input', function() {
        updateOrderSummary();
    });
    
    // Payment method tabs
    $('#paymentTab a').on('click', function(e) {
        e.preventDefault();
        $(this).tab('show');
        
        // Update selected payment method
        const paymentMethodId = $(this).attr('id').replace('payment-tab-', '');
        $('.payment-method-input').prop('checked', false);
        $(`#payment-content-${paymentMethodId} .payment-method-input`).prop('checked', true);
    });
    
    // Cancel order button
    $('#cancelOrderBtn').on('click', function() {
        if (cart.length === 0) {
            return;
        }
        
        if (confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) {
            cart = [];
            selectedCustomer = null;
            currentDiscount = 0;
            currentDiscountType = 'amount';
            
            $('#customerInfo').addClass('d-none');
            $('#discountCode').val('');
            $('#discountPercent').val(0);
            $('#amountTendered').val(0);
            
            renderCart();
            updateOrderSummary();
        }
    });
    
    // Complete order button
    $('#completeOrderBtn').on('click', function() {
        if (cart.length === 0) {
            alert('Giỏ hàng trống. Vui lòng thêm sản phẩm vào giỏ hàng.');
            return;
        }
        
        // Validate payment
        const activeTabId = $('#paymentTab .nav-link.active').attr('id');
        const paymentMethodId = activeTabId.replace('payment-tab-', '');
        
        if (!paymentMethodId) {
            alert('Vui lòng chọn phương thức thanh toán.');
            return;
        }
        
        const total = calculateOrderTotal();
        let paidAmount = 0;
        let changeAmount = 0;
        let transactionReference = '';
        
        if (paymentMethodId === '1') { // Assuming ID 1 is cash
            paidAmount = parseFloat($('#amountTendered').val()) || 0;
            
            if (paidAmount < total) {
                alert('Số tiền khách đưa không đủ.');
                return;
            }
            
            changeAmount = paidAmount - total;
        } else {
            paidAmount = total;
            
            // Validate transaction reference for non-cash payment
            transactionReference = $(`#transactionReference${paymentMethodId}`).val().trim();
            
            if (!transactionReference) {
                alert('Vui lòng nhập mã giao dịch.');
                return;
            }
        }
        
        // Prepare order data
        const orderData = {
            customer_id: selectedCustomer ? selectedCustomer.id : null,
            cart_items: cart,
            total_quantity: calculateTotalQuantity(),
            subtotal: calculateSubtotal(),
            discount_amount: calculateDiscount(calculateSubtotal()),
            tax_amount: calculateTaxAmount(),
            total_amount: total,
            paid_amount: paidAmount,
            change_amount: changeAmount,
            payment_method_id: paymentMethodId,
            transaction_reference: transactionReference
        };
        
        createOrder(orderData);
    });
    
    // Calculate total quantity
    function calculateTotalQuantity() {
        let totalQuantity = 0;
        
        cart.forEach(item => {
            totalQuantity += item.quantity;
        });
        
        return totalQuantity;
    }
    
    // Calculate subtotal
    function calculateSubtotal() {
        let subtotal = 0;
        
        cart.forEach(item => {
            subtotal += item.price * item.quantity;
        });
        
        return subtotal;
    }
    
    // Calculate tax amount
    function calculateTaxAmount() {
        const subtotal = calculateSubtotal();
        const discount = calculateDiscount(subtotal);
        const taxableAmount = subtotal - discount;
        
        return taxableAmount * 0.1; // 10% tax
    }
    
    // Calculate order total
    function calculateOrderTotal() {
        const subtotal = calculateSubtotal();
        const discount = calculateDiscount(subtotal);
        const taxableAmount = subtotal - discount;
        const tax = taxableAmount * 0.1; // 10% tax
        
        return taxableAmount + tax;
    }
    
    // Create order
    function createOrder(orderData) {
        showLoading();
        
        $.ajax({
            url: baseUrl + '/pos/create-order',
            type: 'POST',
            data: orderData,
            dataType: 'json',
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    // Show success modal
                    $('#completedOrderCode').text(response.order.code);
                    $('#completedOrderTotal').text(formatCurrency(response.order.total_amount));
                    $('#orderCompleteModal').modal('show');
                    
                    // Clear cart
                    cart = [];
                    selectedCustomer = null;
                    currentDiscount = 0;
                    currentDiscountType = 'amount';
                    
                    $('#customerInfo').addClass('d-none');
                    $('#discountCode').val('');
                    $('#discountPercent').val(0);
                    $('#amountTendered').val(0);
                    
                    renderCart();
                    updateOrderSummary();
                } else {
                    alert(response.message || 'Có lỗi xảy ra khi tạo đơn hàng.');
                }
            },
            error: function() {
                hideLoading();
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    }
    
    // End shift button
    $('#endShiftBtn').on('click', function() {
        // Get shift details
        getShiftDetails();
    });
    
    // Get shift details
    function getShiftDetails() {
        showLoading();
        
        $.ajax({
            url: baseUrl + '/pos/get-shift-details',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    // Update shift details in modal
                    $('#shiftTotalSales').text(formatCurrency(response.shift.total_sales));
                    $('#shiftTotalReturns').text(formatCurrency(response.shift.total_returns));
                    $('#shiftExpectedAmount').text(formatCurrency(response.shift.expected_amount));
                    
                    // Update payment method details
                    const shiftPaymentDetails = $('#shiftPaymentDetails');
                    shiftPaymentDetails.empty();
                    
                    $.each(response.payment_details, function(index, detail) {
                        shiftPaymentDetails.append(`
                            <tr>
                                <td class="text-muted">${detail.name}:</td>
                                <td class="font-weight-medium">${formatCurrency(detail.amount)}</td>
                            </tr>
                        `);
                    });
                    
                    // Set actual amount to expected amount
                    $('#actualAmount').val(response.shift.expected_amount);
                    $('#amountDifference').val(formatCurrency(0));
                    
                    // Show modal
                    $('#endShiftModal').modal('show');
                } else {
                    alert(response.message || 'Có lỗi xảy ra khi tải thông tin ca làm việc.');
                }
            },
            error: function() {
                hideLoading();
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    }
    
    // Actual amount change
    $('#actualAmount').on('input', function() {
        const actualAmount = parseFloat($(this).val()) || 0;
        const expectedAmountText = $('#shiftExpectedAmount').text();
        const expectedAmount = parseFloat(expectedAmountText.replace(/[^\d.-]/g, '')) || 0;
        const difference = actualAmount - expectedAmount;
        
        $('#amountDifference').val(formatCurrency(difference));
    });
    
    // Confirm end shift button
    $('#confirmEndShiftBtn').on('click', function() {
        const actualAmount = parseFloat($('#actualAmount').val()) || 0;
        const explanation = $('#shiftExplanation').val().trim();
        
        endShift(actualAmount, explanation);
    });
    
    // End shift
    function endShift(actualAmount, explanation) {
        showLoading();
        
        $.ajax({
            url: baseUrl + '/pos/end-shift',
            type: 'POST',
            data: {
                actual_amount: actualAmount,
                explanation: explanation
            },
            dataType: 'json',
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    alert('Ca làm việc đã được kết thúc thành công.');
                    $('#endShiftModal').modal('hide');
                    
                    // Redirect to dashboard or refresh page
                    window.location.href = baseUrl + '/dashboard';
                } else {
                    alert(response.message || 'Có lỗi xảy ra khi kết thúc ca làm việc.');
                }
            },
            error: function() {
                hideLoading();
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    }
    
    // Recent orders button
    $('#recentOrdersBtn').on('click', function() {
        getRecentOrders();
    });
    
    // Get recent orders
    function getRecentOrders() {
        showLoading();
        
        $.ajax({
            url: baseUrl + '/pos/get-recent-orders',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    // Update recent orders list
                    const recentOrdersList = $('#recentOrdersList');
                    recentOrdersList.empty();
                    
                    if (response.orders.length === 0) {
                        recentOrdersList.html('<tr><td colspan="6" class="text-center">Không có đơn hàng nào.</td></tr>');
                    } else {
                        $.each(response.orders, function(index, order) {
                            recentOrdersList.append(`
                                <tr>
                                    <td>${order.code}</td>
                                    <td>${order.order_date}</td>
                                    <td>${order.customer_name || 'Khách lẻ'}</td>
                                    <td class="text-right">${formatCurrency(order.total_amount)}</td>
                                    <td><span class="badge badge-${getBadgeClass(order.status)}">${order.status_text}</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary view-order" data-id="${order.id}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary print-order" data-id="${order.id}">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                    }
                    
                    // Add click event to view order
                    $('.view-order').on('click', function() {
                        const orderId = $(this).data('id');
                        loadOrder(orderId);
                        $('#recentOrdersModal').modal('hide');
                    });
                    
                    // Add click event to print order
                    $('.print-order').on('click', function() {
                        const orderId = $(this).data('id');
                        printOrder(orderId);
                    });
                    
                    // Show modal
                    $('#recentOrdersModal').modal('show');
                } else {
                    alert(response.message || 'Có lỗi xảy ra khi tải đơn hàng gần đây.');
                }
            },
            error: function() {
                hideLoading();
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    }
    
    // Get badge class for order status
    function getBadgeClass(status) {
        switch (parseInt(status)) {
            case 0: return 'secondary'; // draft
            case 1: return 'info'; // confirmed
            case 2: return 'primary'; // paid
            case 3: return 'warning'; // shipped
            case 4: return 'success'; // completed
            case 5: return 'danger'; // canceled
            default: return 'secondary';
        }
    }
    
    // Hold orders button
    $('#holdOrdersBtn').on('click', function() {
        getHoldOrders();
    });
    
    // Get hold orders
    function getHoldOrders() {
        showLoading();
        
        $.ajax({
            url: baseUrl + '/pos/get-hold-orders',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    // Update hold orders list
                    const holdOrdersList = $('#holdOrdersList');
                    holdOrdersList.empty();
                    
                    if (response.orders.length === 0) {
                        holdOrdersList.html('<tr><td colspan="6" class="text-center">Không có đơn hàng tạm giữ nào.</td></tr>');
                    } else {
                        $.each(response.orders, function(index, order) {
                            holdOrdersList.append(`
                                <tr>
                                    <td>${order.code}</td>
                                    <td>${order.order_date}</td>
                                    <td>${order.customer_name || 'Khách lẻ'}</td>
                                    <td class="text-right">${formatCurrency(order.total_amount)}</td>
                                    <td>${order.note || ''}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary load-hold-order" data-id="${order.id}">
                                            <i class="fas fa-shopping-cart"></i> Tải
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-hold-order" data-id="${order.id}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                    }
                    
                    // Add click event to load hold order
                    $('.load-hold-order').on('click', function() {
                        const orderId = $(this).data('id');
                        loadOrder(orderId);
                        $('#holdOrdersModal').modal('hide');
                    });
                    
                    // Add click event to delete hold order
                    $('.delete-hold-order').on('click', function() {
                        const orderId = $(this).data('id');
                        
                        if (confirm('Bạn có chắc chắn muốn xóa đơn hàng tạm giữ này?')) {
                            deleteHoldOrder(orderId);
                        }
                    });
                    
                    // Show modal
                    $('#holdOrdersModal').modal('show');
                } else {
                    alert(response.message || 'Có lỗi xảy ra khi tải đơn hàng tạm giữ.');
                }
            },
            error: function() {
                hideLoading();
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    }
    
    // Delete hold order
    function deleteHoldOrder(orderId) {
        showLoading();
        
        $.ajax({
            url: baseUrl + '/pos/delete-hold-order',
            type: 'POST',
            data: {
                id: orderId
            },
            dataType: 'json',
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    getHoldOrders(); // Refresh hold orders list
                } else {
                    alert(response.message || 'Có lỗi xảy ra khi xóa đơn hàng tạm giữ.');
                }
            },
            error: function() {
                hideLoading();
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    }
    
    // Hold order button
    $('#holdOrderBtn').on('click', function() {
        if (cart.length === 0) {
            alert('Giỏ hàng trống. Vui lòng thêm sản phẩm vào giỏ hàng.');
            return;
        }
        
        // Show prompt for note
        const note = prompt('Nhập ghi chú cho đơn hàng tạm giữ:', '');
        
        if (note !== null) {
            holdOrder(note);
        }
    });
    
    // Hold order
    function holdOrder(note) {
        showLoading();
        
        const orderData = {
            customer_id: selectedCustomer ? selectedCustomer.id : null,
            cart_items: cart,
            total_quantity: calculateTotalQuantity(),
            subtotal: calculateSubtotal(),
            discount_amount: calculateDiscount(calculateSubtotal()),
            tax_amount: calculateTaxAmount(),
            total_amount: calculateOrderTotal(),
            note: note
        };
        
        $.ajax({
            url: baseUrl + '/pos/hold-order',
            type: 'POST',
            data: orderData,
            dataType: 'json',
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    alert('Đơn hàng đã được lưu tạm thành công!');
                    
                    // Clear cart
                    cart = [];
                    selectedCustomer = null;
                    currentDiscount = 0;
                    currentDiscountType = 'amount';
                    
                    $('#customerInfo').addClass('d-none');
                    $('#discountCode').val('');
                    $('#discountPercent').val(0);
                    $('#amountTendered').val(0);
                    
                    renderCart();
                    updateOrderSummary();
                } else {
                    alert(response.message || 'Có lỗi xảy ra khi lưu đơn hàng tạm.');
                }
            },
            error: function() {
                hideLoading();
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    }
    
    // Load order
    function loadOrder(orderId) {
        showLoading();
        
        $.ajax({
            url: baseUrl + '/pos/load-order',
            type: 'GET',
            data: {
                id: orderId
            },
            dataType: 'json',
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    // Clear current cart
                    cart = [];
                    
                    // Set order items to cart
                    cart = response.order_items;
                    
                    // Set customer if available
                    if (response.customer) {
                        selectedCustomer = response.customer;
                        
                        // Update customer info
                        $('#customerName').text(selectedCustomer.name);
                        $('#customerPhone').text(selectedCustomer.phone);
                        $('#customerPoints').text(selectedCustomer.points || 0);
                        $('#customerDebt').text(formatCurrency(selectedCustomer.debt || 0));
                        
                        // Show customer info
                        $('#customerInfo').removeClass('d-none');
                    } else {
                        selectedCustomer = null;
                        $('#customerInfo').addClass('d-none');
                    }
                    
                    // Set discount
                    if (response.order.discount_amount > 0) {
                        currentDiscount = response.order.discount_amount;
                        currentDiscountType = 'amount';
                    }
                    
                    // Set hold order ID if loading a hold order
                    if (response.order.status === 0) {
                        holdOrderId = response.order.id;
                    } else {
                        holdOrderId = null;
                    }
                    
                    // Render cart and update order summary
                    renderCart();
                    updateOrderSummary();
                } else {
                    alert(response.message || 'Có lỗi xảy ra khi tải đơn hàng.');
                }
            },
            error: function() {
                hideLoading();
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    }
    
    // Print order
    function printOrder(orderId) {
        window.open(baseUrl + '/pos/print-order?id=' + orderId, '_blank');
    }
    
    // Print receipt button
    $('#printReceiptBtn').on('click', function() {
        const orderCode = $('#completedOrderCode').text();
        
        if (orderCode) {
            $.ajax({
                url: baseUrl + '/pos/get-order-by-code',
                type: 'GET',
                data: {
                    code: orderCode
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        printOrder(response.order.id);
                    } else {
                        alert(response.message || 'Không thể tìm thấy đơn hàng.');
                    }
                },
                error: function() {
                    alert('Không thể kết nối đến máy chủ.');
                }
            });
        }
    });
    
    // Print warranty button
    $('#printWarrantyBtn').on('click', function() {
        const orderCode = $('#completedOrderCode').text();
        
        if (orderCode) {
            $.ajax({
                url: baseUrl + '/pos/get-order-by-code',
                type: 'GET',
                data: {
                    code: orderCode
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.open(baseUrl + '/pos/print-warranty?id=' + response.order.id, '_blank');
                    } else {
                        alert(response.message || 'Không thể tìm thấy đơn hàng.');
                    }
                },
                error: function() {
                    alert('Không thể kết nối đến máy chủ.');
                }
            });
        }
    });
    
    // Other function buttons
    $('#openCashDrawerBtn').on('click', function() {
        $.ajax({
            url: baseUrl + '/pos/open-cash-drawer',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Cash drawer opened successfully
                } else {
                    alert(response.message || 'Không thể mở ngăn kéo đựng tiền.');
                }
            },
            error: function() {
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    });
    
    $('#quickProductBtn').on('click', function() {
        // Implement quick product feature
        alert('Tính năng đang được phát triển.');
    });
    
    $('#helpBtn').on('click', function() {
        // Show help modal
        alert('Tính năng đang được phát triển.');
    });
});
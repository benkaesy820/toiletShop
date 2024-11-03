// This file contains the JavaScript code for the admin page. admin.php is the main file for the admin page.admin.js
$(document).ready(function() {
    // Initialize DataTable
    $('#productsTable').DataTable({
        order: [[1, 'asc']]
    });

    // Edit Product
    $('.edit-product').click(function() {
        const productId = $(this).data('id');
        
        // Fetch product data
        $.get('php/product_management.php', {
            action: 'get_product',
            id: productId
        }, function(product) {
            $('#edit_id').val(product.id);
            $('#edit_name').val(product.name);
            $('#edit_description').val(product.description);
            $('#edit_price').val(product.price);
            $('#edit_category_id').val(product.category_id);
            $('#editProductModal').modal('show');
        });
    });

    // Save Changes
    $('#saveChanges').click(function() {
        const formData = new FormData($('#editProductForm')[0]);
        formData.append('action', 'update_product');

        $.ajax({
            url: 'php/product_management.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error updating product');
                }
            }
        });
    });

    // Delete Product
    $('.delete-product').click(function() {
        if (confirm('Are you sure you want to delete this product?')) {
            const productId = $(this).data('id');
            
            $.post('php/product_management.php', {
                action: 'delete_product',
                id: productId
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error deleting product');
                }
            });
        }
    });
});
// This file is responsible for handling the search functionality on the website .main.js
$(document).ready(function () {
    $('#searchInput').on('input', function () {
        let query = $(this).val();
        if (query.length > 2) {
            $.ajax({
                url: 'php/searchProducts.php',
                type: 'GET',
                data: { query: query },
                dataType: 'json', // Expect a JSON response
                success: function (data) {
                    let dropdown = $('#searchResults');
                    dropdown.empty().show();
                    
                    if (Array.isArray(data) && data.length > 0) { // Check if data is an array
                        data.forEach(function (product) {
                            dropdown.append(`<a href="product.php?id=${product.id}" class="dropdown-item">${product.name}</a>`);
                        });
                    } else {
                        dropdown.append('<span class="dropdown-item text-muted">No results found</span>');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error("Error occurred: " + textStatus, errorThrown);
                    $('#searchResults').hide(); // Hide results if there's an error
                }
            });
        } else {
            $('#searchResults').hide(); // Hide results if query is too short
        }
    });
    
    $(document).click(function (e) {
        if (!$(e.target).closest('#searchInput, #searchResults').length) {
            $('#searchResults').hide(); // Hide dropdown if click is outside of input and results
        }
    });
});
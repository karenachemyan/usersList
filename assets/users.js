jQuery(document).ready(function($) {
    function loadUsers(page = 1, role = '', sort = 'username') {
        $.ajax({
            url: UserTable.ajax_url,
            method: 'GET',
            data: {
                action: 'get_users',
                page: page,
                role: role,
                sort: sort
            },
            success: function(response) {
                if (response.success) {
                    $('#user-table tbody').html(response.data.rows);
                    $('#pagination').html(response.data.pagination);
                }
            }
        });
    }

    $(document).on('change', '#filter', function() {
        loadUsers(1, $(this).val());
    });

    $(document).on('click', 'th a', function(e) {
        e.preventDefault();
        loadUsers(1, $('#filter').val(), $(this).data('sort'));
    });

    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        loadUsers($(this).data('page'), $('#filter').val());
    });

    loadUsers();
});
/**
 * Custom JavaScript
 * ระบบงานเอกสารสำนักงานทนายความ
 */

$(document).ready(function() {
    // Initialize DataTables
    if ($.fn.DataTable) {
        $('.datatable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
            },
            pageLength: 20,
            responsive: true,
            order: [
                [0, 'asc']
            ]
        });
    }

    // Initialize Select2
    if ($.fn.select2) {
        $('.select2').select2({
            width: '100%',
            placeholder: '-- เลือก --',
            allowClear: true
        });
    }

    // Delete confirmation
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        var name = $(this).data('name') || 'รายการนี้';

        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: 'คุณต้องการลบ "' + name + '" ใช่หรือไม่?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });

    // Status change confirmation
    $(document).on('click', '.btn-status', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        var status = $(this).data('status');
        var statusText = {
            'processing': 'อนุมัติ',
            'paid': 'จ่ายแล้ว',
            'rejected': 'ปฏิเสธ'
        };

        Swal.fire({
            title: 'ยืนยันการเปลี่ยนสถานะ?',
            text: 'เปลี่ยนสถานะเป็น "' + (statusText[status] || status) + '"?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1cc88a',
            cancelButtonColor: '#858796',
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });

    // Show flash message with SweetAlert
    var flashMessage = $('#flash-message');
    if (flashMessage.length && flashMessage.data('message')) {
        var type = flashMessage.data('type');
        var message = flashMessage.data('message');
        var icon = type === 'success' ? 'success' : (type === 'danger' ? 'error' : type);

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }

    // Mobile sidebar toggle
    $('#sidebarToggle').on('click', function() {
        $('.sidebar').toggleClass('show');
    });

    // Close sidebar on outside click (mobile)
    $(document).on('click', function(e) {
        if ($(window).width() <= 768) {
            if (!$(e.target).closest('.sidebar').length && !$(e.target).closest('#sidebarToggle').length) {
                $('.sidebar').removeClass('show');
            }
        }
    });

    // Auto-submit on select change
    $('.auto-submit').on('change', function() {
        $(this).closest('form').submit();
    });

    // Number formatting
    $('.number-format').on('blur', function() {
        var value = parseFloat($(this).val().replace(/,/g, '')) || 0;
        $(this).val(value.toLocaleString('th-TH', { minimumFractionDigits: 2 }));
    });

    // Date picker default (if needed)
    $('input[type="date"]').on('focus', function() {
        if (!$(this).val()) {
            $(this).val(new Date().toISOString().split('T')[0]);
        }
    });

    // Print button
    $(document).on('click', '.btn-print', function(e) {
        e.preventDefault();
        window.print();
    });

    // Tooltip initialization
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Popover initialization
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Form validation
    (function() {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
});

// Utility functions
function formatNumber(num, decimals = 2) {
    return parseFloat(num).toLocaleString('th-TH', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

function formatDate(date) {
    var d = new Date(date);
    return d.toLocaleDateString('th-TH', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

function showLoading() {
    $('body').append('<div class="loading-overlay"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
}

function hideLoading() {
    $('.loading-overlay').remove();
}

function showAlert(type, message) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}
<?php
/**
 * Footer Template
 * ระบบงานเอกสารสำนักงานทนายความ
 */
?>
        </div><!-- End Content Area -->
    </div><!-- End Main Content -->
    <?php if (isLoggedIn()): ?>
    <?php endif; ?>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Base URL for AJAX
        const BASE_URL = '<?php echo BASE_URL; ?>';
        
        $(document).ready(function() {
            // Initialize DataTables
            if ($('.datatable').length) {
                $('.datatable').DataTable({
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
                    },
                    pageLength: 20,
                    responsive: true
                });
            }
            
            // Initialize Select2
            if ($('.select2').length) {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    allowClear: true,
                    placeholder: 'เลือก...'
                });
            }
            
            // Sidebar toggle for mobile
            $('#sidebarToggle').on('click', function() {
                $('#sidebar').toggleClass('show');
            });
            
            // Close sidebar when clicking outside
            $(document).on('click', function(e) {
                if ($(window).width() <= 992) {
                    if (!$(e.target).closest('#sidebar, #sidebarToggle').length) {
                        $('#sidebar').removeClass('show');
                    }
                }
            });
            
            // Confirm delete
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const name = $(this).data('name') || 'รายการนี้';
                
                Swal.fire({
                    title: 'ยืนยันการลบ?',
                    text: 'คุณต้องการลบ ' + name + ' ใช่หรือไม่?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'ลบ',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
        
        // Format number helper
        function formatNumber(num, decimals = 2) {
            return parseFloat(num).toLocaleString('en-US', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        }
        
        // Show loading
        function showLoading() {
            Swal.fire({
                title: 'กำลังโหลด...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        // Hide loading
        function hideLoading() {
            Swal.close();
        }
        
        // Show toast notification
        function showToast(icon, title) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            Toast.fire({ icon: icon, title: title });
        }
    </script>
    
    <?php if (isset($additionalJS)): ?>
    <?php echo $additionalJS; ?>
    <?php endif; ?>
</body>
</html>

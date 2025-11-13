    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-building"></i> Sistema Clinfec</h5>
                    <p class="text-muted">Sistema de Gestão de Empresas, Contratos, Projetos e Atividades</p>
                </div>
                <div class="col-md-3">
                    <h6>Links Rápidos</h6>
                    <ul class="list-unstyled">
                        <li><a href="/" class="text-white-50">Dashboard</a></li>
                        <li><a href="?page=empresas-tomadoras" class="text-white-50">Empresas Tomadoras</a></li>
                        <li><a href="?page=empresas-prestadoras" class="text-white-50">Empresas Prestadoras</a></li>
                        <li><a href="?page=contratos" class="text-white-50">Contratos</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Suporte</h6>
                    <ul class="list-unstyled">
                        <li><a href="?page=ajuda" class="text-white-50">Ajuda</a></li>
                        <li><a href="?page=documentacao" class="text-white-50">Documentação</a></li>
                        <li><a href="?page=contato" class="text-white-50">Contato</a></li>
                    </ul>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">© <?= date('Y') ?> Clinfec. Todos os direitos reservados.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-muted">
                        Versão <?= $config['version'] ?? '1.0.0' ?> | 
                        <a href="?page=changelog" class="text-white-50">Changelog</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Inputmask -->
    <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/jquery.inputmask.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/public/js/app.js"></script>
    <script src="/public/js/masks.js"></script>
    <script src="/public/js/validations.js"></script>
    
    <?php if (isset($customJS)): ?>
        <?php foreach ($customJS as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Global Scripts -->
    <script>
        // Configurar CSRF Token para AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>'
            }
        });
        
        // Inicializar Select2
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Selecione...',
                allowClear: true
            });
            
            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Inicializar popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
            
            // Auto-hide alerts após 5 segundos
            setTimeout(function() {
                $('.alert:not(.alert-permanent)').fadeOut('slow');
            }, 5000);
        });
        
        // Função para formatar moeda brasileira
        function formatMoney(value) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(value);
        }
        
        // Função para formatar data brasileira
        function formatDate(date) {
            return new Date(date).toLocaleDateString('pt-BR');
        }
        
        // Função para confirmação de exclusão
        function confirmDelete(message = 'Tem certeza que deseja excluir este registro?') {
            return Swal.fire({
                title: 'Confirmação',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            });
        }
        
        // Função para mostrar loading
        function showLoading(message = 'Carregando...') {
            Swal.fire({
                title: message,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        // Função para esconder loading
        function hideLoading() {
            Swal.close();
        }
        
        // Função para mostrar toast
        function showToast(message, type = 'success') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            Toast.fire({
                icon: type,
                title: message
            });
        }
    </script>
    
    <!-- Inline Scripts -->
    <?php if (isset($inlineJS)): ?>
        <script>
            <?= $inlineJS ?>
        </script>
    <?php endif; ?>
</body>
</html>

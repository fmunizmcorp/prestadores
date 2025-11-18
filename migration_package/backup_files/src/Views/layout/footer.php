    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>
                &copy; <?= date('Y') ?> Clinfec - Sistema de Gestão de Prestadores. Todos os direitos reservados.
                <?php 
                $versionConfig = require __DIR__ . '/../../../config/version.php';
                ?>
                <span class="version">v<?= $versionConfig['version'] ?></span>
            </p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="<?= asset('js/main.js') ?>"></script>
    
    <?php if (isset($extraJs)): ?>
        <?= $extraJs ?>
    <?php endif; ?>
</body>
</html>

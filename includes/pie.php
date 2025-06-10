    <footer class="pie-pagina">
        <div class="info-sistema">
            <div>
                <h3>Sistema Aprisco</h3>
                <p>Gestión integral de hatos caprinos</p>
            </div>
            <div>
                <h3>Contacto</h3>
                <p><i class="fas fa-envelope"></i> soporte@aprisco.com</p>
                <p><i class="fas fa-phone"></i> +1 234 567 890</p>
            </div>
        </div>
        <div class="derechos">
            <p>&copy; <?= date('Y') ?> Sistema Aprisco. Todos los derechos reservados.</p>
            <p>Versión 1.0.0</p>
        </div>
    </footer>
    
    <!-- Scripts adicionales -->
    <script>
        // Funcionalidad básica para cerrar mensajes
        document.querySelectorAll('.mensaje-exito button, .mensaje-error button').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
        
        // Confirmación para acciones críticas
        document.querySelectorAll('form.accion-critica').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('¿Está seguro de realizar esta acción? No se puede deshacer.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
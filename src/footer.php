<?php $is_auth_page = strpos($_SERVER['PHP_SELF'], 'login.php') !== false || strpos($_SERVER['PHP_SELF'], 'cadastro.php') !== false; ?>

<?php if (isset($_SESSION['usuario_id']) && !$is_auth_page): ?>
    </main> <!-- End of .main-content -->
<?php endif; ?>

</body>
</html>

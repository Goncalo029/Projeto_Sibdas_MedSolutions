    </div><!-- /.mhs-main-content -->

<script src="../assets/bootstrap/bootstrap.bundle.min.js"></script>
<script>
    // Toggle sidebar em mobile
    document.querySelectorAll('.mhs-nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) {
                document.body.classList.remove('mhs-sidebar-open');
            }
        });
    });
</script>

</body>
</html>

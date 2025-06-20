</div> <!-- End .container -->
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            
            function toggleSidebar() {
                sidebar.classList.toggle('active');
            }
            
            if (menuToggle) {
                menuToggle.addEventListener('click', toggleSidebar);
            }
            
            function handleResize() {
                if (window.innerWidth < 768) {
                    if (menuToggle) menuToggle.style.display = 'block';
                } else {
                    if (menuToggle) menuToggle.style.display = 'none';
                    sidebar.classList.add('active');
                }
            }
            
            window.addEventListener('resize', handleResize);
            handleResize();
        });
    </script>
</body>
</html>
    </main>
    <?php if (isLoggedIn()): ?>
            </div>
        </div>
    <?php endif; ?>
    <footer class="footer">
        <p>☕ &copy; 2024 KapengINAMO Coffee Shop. All rights reserved.</p>
    </footer>
    <script>
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.closest('.profile-dropdown')) {
                const dropdown = document.getElementById('profileDropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        }
    </script>
</body>
</html>

    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- About -->
                <div class="footer-col">
                    <h4><?php echo getSetting('site_logo', '🛒'); ?> <?php echo SITE_NAME; ?></h4>
                    <p><?php echo getSetting('site_description', ''); ?></p>
                    <div class="footer-social">
                        <a href="#" title="اینستاگرام">📷</a>
                        <a href="#" title="تلگرام">📱</a>
                        <a href="#" title="واتساپ">💬</a>
                        <a href="#" title="توییتر">🐦</a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-col">
                    <h4>دسترسی سریع</h4>
                    <ul>
                        <li><a href="/">خانه</a></li>
                        <li><a href="/products">محصولات</a></li>
                        <li><a href="/discounts">تخفیف‌ها</a></li>
                        <li><a href="/about">درباره ما</a></li>
                        <li><a href="/contact">تماس با ما</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div class="footer-col">
                    <h4>خدمات مشتریان</h4>
                    <ul>
                        <li><a href="/faq">سوالات متداول</a></li>
                        <li><a href="/terms">قوانین و مقررات</a></li>
                        <li><a href="/privacy">حریم خصوصی</a></li>
                        <li><a href="/return-policy">شرایط بازگشت</a></li>
                        <li><a href="/shipping">روش‌های ارسال</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="footer-col">
                    <h4>ارتباط با ما</h4>
                    <ul class="contact-info">
                        <li>📞 <?php echo getSetting('contact_phone', ''); ?></li>
                        <li>📧 <?php echo getSetting('contact_email', ''); ?></li>
                        <li>📍 <?php echo getSetting('contact_address', ''); ?></li>
                    </ul>
                </div>

                <!-- Trust Symbols -->
                <div class="footer-col">
                    <h4>نمادهای اعتماد</h4>
                    <div class="trust-symbols">
                        <div class="trust-item">🔒 پرداخت امن</div>
                        <div class="trust-item">✅ ضمانت اصالت</div>
                        <div class="trust-item">🚚 تحویل سریع</div>
                        <div class="trust-item">🔄 ۷ روز بازگشت</div>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="footer-bottom">
                <p><?php echo getSetting('footer_text', 'تمامی حقوق محفوظ است'); ?> © <?php echo date('Y'); ?> <?php echo SITE_NAME; ?></p>
            </div>
        </div>
    </footer>

    <!-- Back to Top -->
    <button class="back-to-top" id="backToTop" title="بازگشت به بالا">⬆️</button>

    <!-- Scripts -->
    <script src="<?php echo ASSETS_URL; ?>js/main.js"></script>
    <?php if(isset($extraScripts)): ?>
        <?php echo $extraScripts; ?>
    <?php endif; ?>

    <script>
        // Theme Toggle
        document.getElementById('themeToggle').addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            this.textContent = isDark ? '☀️' : '🌙';
            fetch('/ajax/toggle-theme.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'theme=' + (isDark ? 'dark' : 'light')
            });
        });

        // Mobile Menu
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.add('open');
        });
        document.getElementById('closeMobileMenu').addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.remove('open');
        });

        // Back to Top
        window.addEventListener('scroll', function() {
            document.getElementById('backToTop').style.display = window.scrollY > 500 ? 'block' : 'none';
        });
        document.getElementById('backToTop').addEventListener('click', function() {
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    </script>
</body>
</html>
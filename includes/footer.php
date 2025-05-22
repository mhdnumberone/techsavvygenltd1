    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 pt-5">
        <div class="container">
            <div class="row">
                <!-- Company Info -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5 class="text-uppercase mb-4"><?php echo $currentLang === LANGUAGE_ARABIC ? SITE_NAME_AR : SITE_NAME_EN; ?></h5>
                    <p><?php echo translate('footer_about_text'); ?></p>
                    <div class="mt-4">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5 class="text-uppercase mb-4"><?php echo translate('quick_links'); ?></h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>" class="text-white text-decoration-none"><?php echo translate('home'); ?></a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/about.php" class="text-white text-decoration-none"><?php echo translate('about_us'); ?></a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/services.php" class="text-white text-decoration-none"><?php echo translate('services'); ?></a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/products.php" class="text-white text-decoration-none"><?php echo translate('products'); ?></a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/achievements.php" class="text-white text-decoration-none"><?php echo translate('achievements'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contact.php" class="text-white text-decoration-none"><?php echo translate('contact_us'); ?></a></li>
                    </ul>
                </div>
                
                <!-- Services -->
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-uppercase mb-4"><?php echo translate('services'); ?></h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/services.php?category=web-development" class="text-white text-decoration-none"><?php echo translate('web_development'); ?></a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/services.php?category=android-applications" class="text-white text-decoration-none"><?php echo translate('android_applications'); ?></a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/services.php?category=python-scripts" class="text-white text-decoration-none"><?php echo translate('python_scripts'); ?></a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/services.php?category=digital-products" class="text-white text-decoration-none"><?php echo translate('digital_products'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/services.php?category=training-courses" class="text-white text-decoration-none"><?php echo translate('training_courses'); ?></a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-uppercase mb-4"><?php echo translate('contact_us'); ?></h5>
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <i class="fas fa-map-marker-alt me-2"></i> 123 Tech Street, Silicon Valley, CA 94043
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-phone me-2"></i> +1 (234) 567-890
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-envelope me-2"></i> <a href="mailto:<?php echo MAIL_FROM_ADDRESS; ?>" class="text-white text-decoration-none"><?php echo MAIL_FROM_ADDRESS; ?></a>
                        </li>
                        <li>
                            <i class="fas fa-clock me-2"></i> <?php echo translate('working_hours'); ?>: 9AM - 5PM
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="bg-darker py-3 mt-5">
            <div class="container text-center">
                <p class="mb-0">
                    &copy; <?php echo date('Y'); ?> <?php echo $currentLang === LANGUAGE_ARABIC ? SITE_NAME_AR : SITE_NAME_EN; ?>. <?php echo translate('all_rights_reserved'); ?>
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>

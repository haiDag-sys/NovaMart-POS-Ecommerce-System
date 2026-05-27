<footer class="bg-white border-top pt-5 pb-4 mt-5" id="footer" style="font-size: 0.85rem;">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <h6 class="text-uppercase fw-bold mb-3 text-dark">Chăm sóc khách hàng</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><a href="#!" class="text-muted text-decoration-none footer-link">Trung tâm trợ giúp</a></li>
                    <li class="mb-2"><a href="#!" class="text-muted text-decoration-none footer-link">Hướng dẫn mua hàng</a></li>
                    <li class="mb-2"><a href="#!" class="text-muted text-decoration-none footer-link">Chính sách vận chuyển</a></li>
                    <li class="mb-2"><a href="#!" class="text-muted text-decoration-none footer-link">Trả hàng & Hoàn tiền</a></li>
                    <li class="mb-2"><a href="#!" class="text-muted text-decoration-none footer-link">Hotline: +84 123 456 789</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">    
                <h6 class="text-uppercase fw-bold mb-3 text-dark">Về NovaMart</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><a href="#!" class="text-muted text-decoration-none footer-link">Giới thiệu</a></li>
                    <li class="mb-2"><a href="#!" class="text-muted text-decoration-none footer-link">Tuyển dụng</a></li>
                    <li class="mb-2"><a href="#!" class="text-muted text-decoration-none footer-link">Điều khoản sử dụng</a></li>
                    <li class="mb-2"><a href="#!" class="text-muted text-decoration-none footer-link">Chính sách bảo mật</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <h6 class="text-uppercase fw-bold mb-3 text-dark">Thông tin liên hệ</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2 text-muted"><i class="fas fa-school fa-fw me-2"></i>Đại học Cần Thơ</li>
                    <li class="mb-2 text-muted"><i class="fas fa-map-marker-alt fa-fw me-2"></i>Khu II, Đ. 3 Tháng 2, Ninh Kiều, CT</li>
                    <li class="mb-2 text-muted"><i class="fas fa-building fa-fw me-2"></i>Trường CNTT-TT</li>
                    <li class="mb-2"><a href="mailto:thelightshop@gmail.com" class="text-muted text-decoration-none footer-link"><i class="fas fa-envelope fa-fw me-2"></i>thelightshop@gmail.com</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <h6 class="text-uppercase fw-bold mb-3 text-dark">Theo dõi chúng tôi</h6>
                <ul class="list-unstyled mb-0 d-flex flex-column">
                    <li class="mb-2">
                        <a href="#!" class="text-muted text-decoration-none footer-link d-flex align-items-center">
                            <i class="fab fa-facebook fa-lg me-2" style="color: #3b5998;"></i> Facebook
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#!" class="text-muted text-decoration-none footer-link d-flex align-items-center">
                            <i class="fab fa-instagram fa-lg me-2" style="color: #c13584;"></i> Instagram
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#!" class="text-muted text-decoration-none footer-link d-flex align-items-center">
                            <i class="fab fa-youtube fa-lg me-2" style="color: #ff0000;"></i> YouTube
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="my-4" style="border-color: #e0e0e0;">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="text-muted mb-3 mb-md-0">
                © 2026 Bản quyền thuộc về <span class="fw-bold brand-text">CT201</span>.
            </div>
            <div>
                <i class="fab fa-cc-visa fa-2x text-muted me-2"></i>
                <i class="fab fa-cc-mastercard fa-2x text-muted me-2"></i>
                <i class="fab fa-cc-paypal fa-2x text-muted"></i>
            </div>
        </div>
    </div>

    <a href="#top" class="scroll-top-btn shadow" id="top-button">
        <i class="fas fa-arrow-up"></i>
    </a>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
<?php if (!empty($pageScripts) && is_array($pageScripts)): ?>
    <?php foreach ($pageScripts as $scriptPath): ?>
        <script src="<?php echo htmlspecialchars($scriptPath, ENT_QUOTES, 'UTF-8'); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>

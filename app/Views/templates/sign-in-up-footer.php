        </section>
        <script src="<?=site_url("public/assets/js/jquery-3.3.1.min.js")?>"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Sweet Alerts js -->
        <script src="<?=site_url("public/assets/libs/sweetalert2/sweetalert2.min.js")?>"></script>
        <!-- <script type="text/javascript" src="<?=site_url("public/assets/js/pages/login.js?v=".uniqid())?>"></script> -->
        <script src="https://accounts.google.com/gsi/client" async defer></script>
        <?php
        if (!empty($morejs)) {
            foreach ($morejs as $value) {
        ?>
                <script src="<?php echo $value; ?>"></script>
        <?php
            }
        }
        ?>
        <script type="text/javascript" src="<?=site_url("public/assets/js/main.js?v=".uniqid())?>"></script>
    </body>
</html>

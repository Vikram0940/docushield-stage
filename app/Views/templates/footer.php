                </div>
            </div>
        </div>
        <!-- Sidebar Starts Here -->

        <?php
        echo view("projects/project-modal");
        echo view("templates/upload-modal");
        echo view("templates/rename-file-modal");
        echo view("templates/empty-modal");
        /*echo view("projects/edit-project-modal");
        echo view("projects/archive-project-modal");
        echo view("projects/share-project-modal");*/
        ?>

        <!-- Right Side Starts Here -->
        <!-- Top Header Search Section -->
        <!-- Right Main Content -->

        <!-- Sub Footer -->
        <!-- Global Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="<?=site_url("public/assets/js/jquery-3.3.1.min.js")?>"></script>
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
        <!-- Sweet Alerts js -->
        <script src="<?=site_url("public/assets/libs/sweetalert2/sweetalert2.min.js")?>"></script>
        <script src="<?=site_url("public/assets/js/loadingoverlay.min.js?v=".uniqid())?>"></script>
        <script src="<?=site_url("public/assets/js/app.js?v=".uniqid())?>"></script>
        <script type="module" src="<?=site_url("public/assets/js/search.js?v=".uniqid())?>"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <!-- toastr JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <!-- FilePond -->
        <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
        <script src="<?=site_url("public/assets/js/pages/filepond-custom.js?v=".uniqid())?>"></script>

        <script src="<?=site_url("public/assets/js/main.js?v=".uniqid())?>"></script>
        <script src="<?=site_url("public/assets/js/pages/upload.js?v=".uniqid())?>"></script>
        <script src="<?=site_url("public/assets/js/pages/library.js?v=".uniqid())?>"></script>
        <!-- <script src="<?=site_url("public/assets/js/pages/upload-file.js?v=".uniqid())?>"></script> -->
        <!-- Including any custom scripts here if required, page specific -->
        <?php
        if (!empty($morejs)) {
            foreach ($morejs as $value) {
        ?>
                <script src="<?php echo $value; ?>"></script>
        <?php
            }
        }
        ?>
        <script>
            const profileBtn = document.getElementById('profileBtn');
            const profileMenu = document.getElementById('profileMenu');

            // Toggle dropdown on click
            profileBtn.addEventListener('click', () => {
              profileMenu.style.display = profileMenu.style.display === 'block' ? 'none' : 'block';
            });

            // Hide menu if clicked outside
            document.addEventListener('click', (e) => {
              if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.style.display = 'none';
              }
            });
          </script>
    </body>
</html>
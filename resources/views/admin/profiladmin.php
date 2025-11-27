<?php
include '../layout/header.php';
include '../layout/sidebar.php';
include '../dbconfig.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: loginAdmin.php");
    exit();
}

$admin = $_SESSION['admin'];
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php include '../layout/topbar.php'; ?>
        
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Profil Admin</h1>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Informasi Profil</h6>
                        </div>
                        <div class="card-body">
                            <form method="post" action="updateProfile.php">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Username</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" readonly>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Email</label>
                                    <div class="col-sm-9">
                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Nama Lengkap</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($admin['fullname'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-9 offset-sm-3">
                                        <button type="submit" class="btn btn-primary">Update Profil</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Foto Profil</h6>
                        </div>
                        <div class="card-body text-center">
                            <img class="img-profile rounded-circle mb-3" src="../uploads/<?= $_SESSION['admin']['profile_photo'] ?? 'img/profile.png' ?>" width="120" alt="Profile">
                            <div class="small font-italic text-muted mb-4">JPG atau PNG tidak lebih dari 5MB</div>
                            <form method="post" enctype="multipart/form-data" action="updateFoto.php">
                                <div class="form-group">
                                    <input type="file" name="profile_photo" class="form-control-file">
                                </div>
                                <button type="submit" class="btn btn-primary">Unggah Foto</button>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Ubah Password</h6>
                        </div>
                        <div class="card-body">
                            <form method="post" action="changePassword.php">
                                <div class="form-group">
                                    <label>Password Saat Ini</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Password Baru</label>
                                    <input type="password" name="new_password" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Konfirmasi Password Baru</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Ubah Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../layout/footer.php'; ?>
</div>
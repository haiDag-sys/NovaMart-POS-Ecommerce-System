<?php
namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\EmployeeModel;

class AdminAuthController
{
    private $adminModel;
    private $employeeModel;

    public function __construct()
    {
        $this->adminModel = new AdminModel();
        $this->employeeModel = new EmployeeModel();
    }

    public function showLogin($error = '')
    {
        view('admin/auth/login', ['error' => $error, 'portal' => trim($_GET['portal'] ?? '')]);
    }

    public function handle()
    {
        $portal = trim($_GET['portal'] ?? '');
        $isStaffPortal = ($portal === 'staff');

        if (isset($_SESSION['user_id'], $_SESSION['role'])) {
            if ($_SESSION['role'] === 'nhan_vien') {
                redirect('../staff/index.php');
            }

            if (!$isStaffPortal) {
                redirect('index.php');
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (!$isStaffPortal) {
                $admin = $this->adminModel->findByUsername($username);
                if ($admin) {
                    if ($password === $admin['ad_matkhau']) {
                        $_SESSION['user_id'] = $admin['ad_id'];
                        $_SESSION['user_name'] = $admin['ad_taikhoan'];
                        $_SESSION['hoten'] = $admin['ad_hoten'] ?: ('Quản lý ' . $admin['ad_taikhoan']);
                        $_SESSION['role'] = 'admin';
                        redirect('index.php');
                    }

                    return $this->showLogin('Sai mật khẩu!');
                }
            }

            $employee = $this->employeeModel->findByUsername($username);
            if ($employee) {
                if ($password === $employee['nv_matkhau']) {
                    $_SESSION['user_id'] = $employee['nv_id'];
                    $_SESSION['user_name'] = $employee['nv_taikhoan'];
                    $_SESSION['hoten'] = $employee['nv_hoten'];
                    $_SESSION['role'] = 'nhan_vien';
                    redirect('../staff/index.php');
                }

                return $this->showLogin('Sai mật khẩu!');
            }

            return $this->showLogin($isStaffPortal ? 'Tài khoản nhân viên không tồn tại!' : 'Tài khoản không tồn tại!');
        }

        $this->showLogin();
    }
}
?>

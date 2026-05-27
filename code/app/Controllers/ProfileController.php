<?php
namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\NotificationModel;
use App\Models\OrderModel;

class ProfileController
{
    private $customerModel;
    private $orderModel;
    private $notificationModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->orderModel = new OrderModel();
        $this->notificationModel = new NotificationModel();
    }

    public function index()
    {
        require_customer();

        $khId = (int) $_SESSION['kh_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_notifications_read'])) {
            $this->notificationModel->markAllAsRead($khId);
            redirect('profile.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
            $this->handleCancelOrder($khId, 'profile.php#don-hang');
        }

        if (isset($_FILES['avatar_upload']) && isset($_FILES['avatar_upload']['error']) && $_FILES['avatar_upload']['error'] === 0) {
            $thuMucLuu = 'assets/uploads/avatars/';
            if (!is_dir(base_path($thuMucLuu))) {
                @mkdir(base_path($thuMucLuu), 0777, true);
            }

            $tenFileMoi = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['avatar_upload']['name']));
            $duongDanFile = $thuMucLuu . $tenFileMoi;

            if (move_uploaded_file($_FILES['avatar_upload']['tmp_name'], base_path($duongDanFile))) {
                if ($this->customerModel->updateAvatar($khId, $duongDanFile)) {
                    $_SESSION['kh_avatar'] = $duongDanFile;
                    set_flash('success', 'Cập nhật ảnh đại diện thành công.');
                    redirect('profile.php');
                }
            }

            set_flash('error', 'Không thể cập nhật ảnh đại diện.');
            redirect('profile.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
            $fullName = trim($_POST['kh_hoten'] ?? '');
            $address = trim($_POST['kh_diachi'] ?? '');

            if ($fullName === '') {
                set_flash('error', 'Vui lòng nhập họ và tên.');
                redirect('profile.php#thong-tin');
            }

            if ($this->customerModel->updateProfile($khId, $fullName, $address)) {
                $_SESSION['kh_hoten'] = $fullName;
                set_flash('success', 'Cập nhật hồ sơ thành công.');
            } else {
                set_flash('error', 'Không thể cập nhật hồ sơ. Vui lòng thử lại.');
            }

            redirect('profile.php#thong-tin');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
            $currentPassword = trim($_POST['current_password'] ?? '');
            $newPassword = trim($_POST['new_password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');

            if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                set_flash('error', 'Vui lòng nhập đầy đủ thông tin đổi mật khẩu.');
                redirect('profile.php#mat-khau');
            }

            if (!$this->customerModel->verifyPassword($khId, $currentPassword)) {
                set_flash('error', 'Mật khẩu hiện tại không chính xác.');
                redirect('profile.php#mat-khau');
            }

            if (strlen($newPassword) < 6 || !preg_match('/^(?=.*[A-Za-z])(?=.*\d).{6,}$/', $newPassword)) {
                set_flash('error', 'Mật khẩu mới phải có ít nhất 6 ký tự và gồm cả chữ lẫn số.');
                redirect('profile.php#mat-khau');
            }

            if ($newPassword !== $confirmPassword) {
                set_flash('error', 'Mật khẩu xác nhận không khớp.');
                redirect('profile.php#mat-khau');
            }

            if ($this->customerModel->updatePassword($khId, $newPassword)) {
                set_flash('success', 'Đổi mật khẩu thành công.');
            } else {
                set_flash('error', 'Không thể đổi mật khẩu. Vui lòng thử lại.');
            }

            redirect('profile.php#mat-khau');
        }

        $customer = $this->customerModel->getById($khId);
        $orders = $this->orderModel->getCustomerOrders($khId);

        if (!empty($customer['kh_avatar'])) {
            $_SESSION['kh_avatar'] = $customer['kh_avatar'];
        }

        view('customer/profile/index', [
            'customer' => $customer,
            'orders' => $orders,
            'flashSuccess' => get_flash('success'),
            'flashError' => get_flash('error'),
            'orderModel' => $this->orderModel,
        ]);
    }

    public function showOrder()
    {
        require_customer();

        $khId = (int) $_SESSION['kh_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
            $hdId = (int) ($_POST['hd_id'] ?? 0);
            $this->handleCancelOrder($khId, 'order_detail.php?id=' . $hdId);
        }

        $hdId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($hdId <= 0) {
            die('Không tìm thấy mã hóa đơn!');
        }

        $order = $this->orderModel->getCustomerOrderById($hdId, $khId);
        if (!$order) {
            echo "<script>alert('Hóa đơn không tồn tại hoặc bạn không có quyền xem!'); window.location='profile.php';</script>";
            exit();
        }

        $items = $this->orderModel->getOrderItems($hdId);

        view('customer/orders/show', [
            'order' => $order,
            'items' => $items,
            'flashSuccess' => get_flash('success'),
            'flashError' => get_flash('error'),
            'orderModel' => $this->orderModel,
        ]);
    }

    private function handleCancelOrder(int $khId, string $redirectUrl): void
    {
        $hdId = (int) ($_POST['hd_id'] ?? 0);
        $reason = trim((string) ($_POST['cancel_reason'] ?? ''));

        try {
            $this->orderModel->cancelCustomerOrder($hdId, $khId, $reason);
            $this->notificationModel->createCustomerCancelNotification($khId, $hdId, $reason);
            set_flash('success', 'Hủy đơn hàng thành công.');
        } catch (\Exception $e) {
            set_flash('error', $e->getMessage());
        }

        redirect($redirectUrl);
    }
}
?>

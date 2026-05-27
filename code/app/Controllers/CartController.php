<?php
namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\OrderModel;
use Exception;

class CartController
{
    private $orderModel;
    private $customerModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->customerModel = new CustomerModel();
    }

    public function index()
    {
        require_customer();

        if (!isset($_SESSION['client_cart']) || !is_array($_SESSION['client_cart'])) {
            $_SESSION['client_cart'] = [];
        }

        $khId = (int) $_SESSION['kh_id'];
        $customer = $this->customerModel->getById($khId);

        if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'remove') {
            $removeId = (int) $_GET['id'];
            unset($_SESSION['client_cart'][$removeId]);
            redirect('cart.php');
        }

        if (isset($_GET['action']) && $_GET['action'] === 'clear') {
            $_SESSION['client_cart'] = [];
            redirect('cart.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
            try {
                if (empty($_SESSION['client_cart'])) {
                    throw new Exception('Giỏ hàng đang trống.');
                }

                $note = trim($_POST['ghi_chu'] ?? '');
                $deliveryAddress = trim($_POST['dia_chi_nhan'] ?? '');

                if ($deliveryAddress === '') {
                    throw new Exception('Vui lòng nhập địa chỉ nhận hàng.');
                }

                $hdId = $this->orderModel->createOrderAndDeductStock(
                    $_SESSION['client_cart'],
                    'Tiền mặt',
                    $khId,
                    null,
                    'dang_xu_ly',
                    $note,
                    $deliveryAddress
                );

                $_SESSION['client_cart'] = [];
                set_flash('success', 'Đặt hàng thành công. Mã đơn của bạn là #' . $hdId . '.');
                redirect('profile.php#don-hang');
            } catch (Exception $e) {
                set_flash('error', 'Lỗi thanh toán: ' . $e->getMessage());
                redirect('cart.php');
            }
        }

        $cartItems = $_SESSION['client_cart'];
        $total = 0;
        foreach ($cartItems as $item) {
            $total += ((float) $item['gia']) * ((int) $item['sl']);
        }

        view('customer/cart/index', [
            'cartItems' => $cartItems,
            'tong_tien' => $total,
            'flashSuccess' => get_flash('success'),
            'flashError' => get_flash('error'),
            'customer' => $customer,
        ]);
    }
}
?>
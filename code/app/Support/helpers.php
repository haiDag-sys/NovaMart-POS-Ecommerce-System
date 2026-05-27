<?php
use App\Config\Database;

if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        return BASE_PATH . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('app_path')) {
    function app_path($path = '')
    {
        return base_path('app' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('db')) {
    function db()
    {
        return Database::getConnection();
    }
}

if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect($url)
    {
        header('Location: ' . $url);
        exit();
    }
}

if (!function_exists('view')) {
    function view($view, array $data = [])
    {
        extract($data);
        require app_path('Views/' . ltrim($view, '/') . '.php');
    }
}

if (!function_exists('set_flash')) {
    function set_flash($key, $message)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['_flash'][$key] = $message;
    }
}

if (!function_exists('get_flash')) {
    function get_flash($key)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION['_flash'][$key])) {
            return null;
        }

        $value = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);

        return $value;
    }
}

if (!function_exists('require_admin')) {
    function require_admin()
    {
        if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
            redirect('login.php');
        }

        if ($_SESSION['role'] !== 'admin') {
            redirect('../staff/index.php');
        }
    }
}


if (!function_exists('require_staff')) {
    function require_staff()
    {
        if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
            redirect('../admin/login.php?portal=staff');
        }

        if ($_SESSION['role'] !== 'nhan_vien') {
            redirect('../admin/index.php');
        }
    }
}

if (!function_exists('require_customer')) {
    function require_customer()
    {
        if (!isset($_SESSION['kh_id'])) {
            redirect('login_member.php');
        }
    }
}

if (!function_exists('order_status_options')) {
    function order_status_options()
    {
        return [
            'dang_xu_ly' => 'Đang xử lý',
            'da_xac_nhan' => 'Đã xác nhận',
            'dang_giao' => 'Đang giao',
            'hoan_thanh' => 'Hoàn thành',
            'da_huy' => 'Đã hủy',
        ];
    }
}

if (!function_exists('order_status_alias_map')) {
    function order_status_alias_map()
    {
        return [
            'dang_xu_ly' => 'dang_xu_ly',
            'da_xac_nhan' => 'da_xac_nhan',
            'da_thanh_toan' => 'da_xac_nhan',
            'dang_giao' => 'dang_giao',
            'hoan_thanh' => 'hoan_thanh',
            'hoan_tat' => 'hoan_thanh',
            'da_huy' => 'da_huy',
        ];
    }
}

if (!function_exists('order_status_normalize')) {
    function order_status_normalize($status)
    {
        $status = trim((string) $status);
        $aliases = order_status_alias_map();
        return $aliases[$status] ?? 'dang_xu_ly';
    }
}

if (!function_exists('order_status_label')) {
    function order_status_label($status)
    {
        $normalized = order_status_normalize($status);
        $options = order_status_options();
        return $options[$normalized] ?? 'Đang xử lý';
    }
}

if (!function_exists('order_status_badge')) {
    function order_status_badge($status)
    {
        $status = order_status_normalize($status);
        $map = [
            'dang_xu_ly' => 'bg-warning text-dark',
            'da_xac_nhan' => 'bg-info text-dark',
            'dang_giao' => 'bg-primary',
            'hoan_thanh' => 'bg-success',
            'da_huy' => 'bg-danger',
        ];
        return isset($map[$status]) ? $map[$status] : 'bg-warning text-dark';
    }
}


if (!function_exists('category_image_url')) {
    function category_image_url($categoryId, $prefix = '')
    {
        $categoryId = (int) $categoryId;
        if ($categoryId <= 0) {
            return null;
        }

        $pattern = base_path('assets/uploads/categories/cat_' . $categoryId . '.*');
        $files = glob($pattern);
        if (!$files || empty($files[0])) {
            return null;
        }

        return ($prefix !== '' ? rtrim($prefix, '/') . '/' : '') . 'assets/uploads/categories/' . basename($files[0]);
    }
}

if (!function_exists('delete_category_image')) {
    function delete_category_image($categoryId)
    {
        $categoryId = (int) $categoryId;
        if ($categoryId <= 0) {
            return;
        }

        $pattern = base_path('assets/uploads/categories/cat_' . $categoryId . '.*');
        $files = glob($pattern);
        if (!$files) {
            return;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}



if (!function_exists("format_quantity")) {
    function format_quantity($number, $decimals = 2)
    {
        if ($number === null || $number === "") {
            return "0";
        }

        $formatted = number_format((float) $number, $decimals, ".", "");
        return rtrim(rtrim($formatted, "0"), ".");
    }
}

?>
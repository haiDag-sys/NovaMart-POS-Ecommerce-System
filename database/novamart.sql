CREATE DATABASE taphoa_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE taphoa_db;

CREATE TABLE admin (
    ad_id INT AUTO_INCREMENT PRIMARY KEY,
    ad_taikhoan VARCHAR(50) NOT NULL,
    ad_matkhau VARCHAR(255) NOT NULL,
    ad_hoten VARCHAR(100) NOT NULL,
    UNIQUE KEY uq_admin_taikhoan (ad_taikhoan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE khach_hang (
    kh_id INT AUTO_INCREMENT PRIMARY KEY,
    kh_hoten VARCHAR(100) NOT NULL,
    kh_sdt VARCHAR(10) NOT NULL,
    kh_matkhau VARCHAR(255) NOT NULL,
    kh_diachi VARCHAR(255) NULL,
    kh_avatar VARCHAR(255) NULL,
    UNIQUE KEY uq_khach_hang_sdt (kh_sdt),
    CONSTRAINT chk_kh_sdt CHECK (CHAR_LENGTH(kh_sdt) = 10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE nhan_vien (
    nv_id INT AUTO_INCREMENT PRIMARY KEY,
    nv_hoten VARCHAR(100) NOT NULL,
    nv_taikhoan VARCHAR(50) NOT NULL,
    nv_matkhau VARCHAR(255) NOT NULL,
    UNIQUE KEY uq_nhan_vien_taikhoan (nv_taikhoan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE loai_san_pham (
    lsp_id INT AUTO_INCREMENT PRIMARY KEY,
    lsp_ten VARCHAR(100) NOT NULL,
    lsp_hinhanh VARCHAR(255) NULL,
    UNIQUE KEY uq_loai_san_pham_ten (lsp_ten)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE san_pham (
    sp_id INT AUTO_INCREMENT PRIMARY KEY,
    sp_ten VARCHAR(150) NOT NULL,
    sp_tonkho DECIMAL(10,2) NOT NULL DEFAULT 0,
    sp_giaban DECIMAL(12,2) NOT NULL,
    sp_hinhanh VARCHAR(255) NULL,
    sp_donvi VARCHAR(50) NULL,
    sp_daban DECIMAL(10,2) NOT NULL DEFAULT 0,
    sp_mota TEXT NULL,
    lsp_id INT NOT NULL,
    CONSTRAINT fk_san_pham_loai FOREIGN KEY (lsp_id)
        REFERENCES loai_san_pham(lsp_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT chk_sp_tonkho CHECK (sp_tonkho >= 0),
    CONSTRAINT chk_sp_giaban CHECK (sp_giaban >= 0),
    CONSTRAINT chk_sp_daban CHECK (sp_daban >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE nha_cung_cap (
    ncc_id INT AUTO_INCREMENT PRIMARY KEY,
    ncc_ten VARCHAR(100) NOT NULL,
    ncc_diachi VARCHAR(255) NULL,
    ncc_sdt VARCHAR(10) NULL,
    UNIQUE KEY uq_nha_cung_cap_ten (ncc_ten),
    CONSTRAINT chk_ncc_sdt CHECK (ncc_sdt IS NULL OR CHAR_LENGTH(ncc_sdt) = 10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE hoa_don (
    hd_id INT AUTO_INCREMENT PRIMARY KEY,
    hd_ngaylap DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    hd_tongtien DECIMAL(12,2) NOT NULL DEFAULT 0,
    hd_hinhthuctt VARCHAR(50) NULL,
    hd_trangthai VARCHAR(30) NOT NULL DEFAULT 'dang_xu_ly',
    hd_ghichu TEXT NULL,
    hd_diachinhan VARCHAR(255) NULL,
    kh_id INT NULL,
    nv_id INT NULL,
    CONSTRAINT fk_hoa_don_khach_hang FOREIGN KEY (kh_id)
        REFERENCES khach_hang(kh_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    CONSTRAINT fk_hoa_don_nhan_vien FOREIGN KEY (nv_id)
        REFERENCES nhan_vien(nv_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    CONSTRAINT chk_hd_tongtien CHECK (hd_tongtien >= 0),
    CONSTRAINT chk_hd_trangthai CHECK (hd_trangthai IN ('dang_xu_ly','da_xac_nhan','dang_giao','hoan_thanh','da_huy')),
    CONSTRAINT chk_hd_nguon CHECK (kh_id IS NOT NULL OR nv_id IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ct_hoa_don (
    hd_id INT NOT NULL,
    sp_id INT NOT NULL,
    cthd_soluong DECIMAL(10,2) NOT NULL,
    cthd_dongia DECIMAL(12,2) NOT NULL,
    cthd_thanhtien DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (hd_id, sp_id),
    CONSTRAINT fk_ct_hoa_don_hoa_don FOREIGN KEY (hd_id)
        REFERENCES hoa_don(hd_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_ct_hoa_don_san_pham FOREIGN KEY (sp_id)
        REFERENCES san_pham(sp_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT chk_cthd_soluong CHECK (cthd_soluong > 0),
    CONSTRAINT chk_cthd_dongia CHECK (cthd_dongia >= 0),
    CONSTRAINT chk_cthd_thanhtien CHECK (cthd_thanhtien >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE danh_gia (
    dg_id INT AUTO_INCREMENT PRIMARY KEY,
    dg_sao INT NOT NULL,
    dg_noidung TEXT NULL,
    dg_thoigian DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    kh_id INT NOT NULL,
    sp_id INT NOT NULL,
    CONSTRAINT fk_danh_gia_khach_hang FOREIGN KEY (kh_id)
        REFERENCES khach_hang(kh_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_danh_gia_san_pham FOREIGN KEY (sp_id)
        REFERENCES san_pham(sp_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT chk_dg_sao CHECK (dg_sao BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE phieu_nhap_kho (
    pnk_id INT AUTO_INCREMENT PRIMARY KEY,
    pnk_ngaylap DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    pnk_tongtien DECIMAL(12,2) NOT NULL DEFAULT 0,
    pnk_hinhthuctt VARCHAR(50) NULL,
    ad_id INT NOT NULL,
    ncc_id INT NOT NULL,
    CONSTRAINT fk_phieu_nhap_admin FOREIGN KEY (ad_id)
        REFERENCES admin(ad_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_phieu_nhap_ncc FOREIGN KEY (ncc_id)
        REFERENCES nha_cung_cap(ncc_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT chk_pnk_tongtien CHECK (pnk_tongtien >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ct_phieu_nhap (
    ctpn_id INT AUTO_INCREMENT PRIMARY KEY,
    pnk_id INT NOT NULL,
    sp_id INT NOT NULL,
    ctpn_soluong DECIMAL(10,2) NOT NULL,
    ctpn_soluongton DECIMAL(10,2) NOT NULL DEFAULT 0,
    ctpn_dongia DECIMAL(12,2) NOT NULL,
    ctpn_thanhtien DECIMAL(12,2) NOT NULL,
    ctpn_hansudung DATE NULL,
    CONSTRAINT fk_ct_phieu_nhap_pnk FOREIGN KEY (pnk_id)
        REFERENCES phieu_nhap_kho(pnk_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_ct_phieu_nhap_san_pham FOREIGN KEY (sp_id)
        REFERENCES san_pham(sp_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT chk_ctpn_soluong CHECK (ctpn_soluong > 0),
    CONSTRAINT chk_ctpn_soluongton CHECK (ctpn_soluongton >= 0),
    CONSTRAINT chk_ctpn_dongia CHECK (ctpn_dongia >= 0),
    CONSTRAINT chk_ctpn_thanhtien CHECK (ctpn_thanhtien >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE thong_bao (
    tb_id INT AUTO_INCREMENT PRIMARY KEY,
    tb_loai VARCHAR(50) NULL,
    tb_noidung TEXT NOT NULL,
    tb_dadoc TINYINT(1) NOT NULL DEFAULT 0,
    tb_thoigian DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    kh_id INT NOT NULL,
    hd_id INT NULL,
    CONSTRAINT fk_thong_bao_khach_hang FOREIGN KEY (kh_id)
        REFERENCES khach_hang(kh_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_thong_bao_hoa_don FOREIGN KEY (hd_id)
        REFERENCES hoa_don(hd_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    CONSTRAINT chk_tb_dadoc CHECK (tb_dadoc IN (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_san_pham_lsp_id ON san_pham(lsp_id);
CREATE INDEX idx_hoa_don_kh_id ON hoa_don(kh_id);
CREATE INDEX idx_hoa_don_nv_id ON hoa_don(nv_id);
CREATE INDEX idx_hoa_don_trangthai ON hoa_don(hd_trangthai);
CREATE INDEX idx_ct_hoa_don_sp_id ON ct_hoa_don(sp_id);
CREATE INDEX idx_danh_gia_kh_id ON danh_gia(kh_id);
CREATE INDEX idx_danh_gia_sp_id ON danh_gia(sp_id);
CREATE INDEX idx_pnk_ad_id ON phieu_nhap_kho(ad_id);
CREATE INDEX idx_pnk_ncc_id ON phieu_nhap_kho(ncc_id);
CREATE INDEX idx_ctpn_pnk_id ON ct_phieu_nhap(pnk_id);
CREATE INDEX idx_ctpn_sp_id ON ct_phieu_nhap(sp_id);
CREATE INDEX idx_ctpn_hansudung ON ct_phieu_nhap(ctpn_hansudung);
CREATE INDEX idx_thong_bao_kh_id ON thong_bao(kh_id);
CREATE INDEX idx_thong_bao_hd_id ON thong_bao(hd_id);

INSERT INTO admin (ad_taikhoan, ad_matkhau, ad_hoten) VALUES
('admin', '123', 'Quản trị viên');

INSERT INTO nhan_vien (nv_hoten, nv_taikhoan, nv_matkhau) VALUES
('Nhân viên NovaMart', 'nhanvien', '123');

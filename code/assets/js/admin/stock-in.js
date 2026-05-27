document.addEventListener('DOMContentLoaded', function () {
    const btnAddRow = document.getElementById('btnAddRow');
    const tableBody = document.getElementById('chiTietBody');
    const optionSource = document.getElementById('stock-in-options');

    if (!btnAddRow || !tableBody || !optionSource) {
        return;
    }

    const productOptions = optionSource.innerHTML.trim();

    btnAddRow.addEventListener('click', function () {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td><select name="sp_id[]" class="form-select" required>${productOptions}</select></td>
            <td><input type="number" name="soluong[]" class="form-control text-center" required min="0.01" step="0.01" value="1"></td>
            <td><input type="number" name="dongia[]" class="form-control text-end" required min="0"></td>
            <td><input type="date" name="hansudung[]" class="form-control" required></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove">Xóa</button></td>
        `;
        tableBody.appendChild(newRow);
    });

    tableBody.addEventListener('click', function (event) {
        const removeButton = event.target.closest('.btn-remove');
        if (!removeButton) {
            return;
        }

        const row = removeButton.closest('tr');
        if (tableBody.children.length > 1) {
            row.remove();
        } else {
            alert('Phiếu nhập phải có ít nhất 1 sản phẩm!');
        }
    });
});

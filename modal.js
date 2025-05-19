document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const modal = document.getElementById('product-modal');
    const addProductBtn = document.getElementById('add-product-btn');
    const closeModalBtn = document.getElementById('close-modal');
    const cancelBtn = document.getElementById('cancel-btn');
    const productForm = document.getElementById('product-form');
    const submitText = document.getElementById('submit-text');
    const loadingSpinner = document.getElementById('loading-spinner');

    // Show modal function
    function showModal(productData = null) {
        if (productData) {
            // Edit mode
            document.getElementById('product-id').value = productData.id;
            document.getElementById('kode_produk').value = productData.kode_produk;
            document.getElementById('nama_produk').value = productData.nama_produk;
            document.getElementById('kategori').value = productData.kategori;
            document.getElementById('harga').value = productData.harga;
            document.getElementById('stok').value = productData.stok;
            document.getElementById('gambar').value = productData.gambar || '';
            document.getElementById('modal-title').textContent = 'Edit Produk';
        } else {
            // Add mode
            productForm.reset();
            document.getElementById('modal-title').textContent = 'Tambah Produk Baru';
        }
        modal.classList.add('active');
    }

    // Hide modal function
    function hideModal() {
        modal.classList.remove('active');
    }

    // Event listeners
    addProductBtn.addEventListener('click', () => showModal());
    
    closeModalBtn.addEventListener('click', hideModal);
    
    cancelBtn.addEventListener('click', hideModal);
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            hideModal();
        }
    });

    // Edit button handlers (berbasis class, opsional kalau masih mau pakai)
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            
            fetch(`../includes/get_product.php?id=${productId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showModal(data.product);
                    } else {
                        alert(data.message || 'Gagal memuat data produk');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat data produk');
                });
        });
    });

    // GLOBAL FUNCTIONS untuk tombol onclick di HTML
    window.editItem = function(id) {
        if (!id) return;
        fetch(`../includes/get_product.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showModal(data.product);
                } else {
                    alert(data.message || 'Gagal memuat data produk');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memuat data produk');
            });
    }

    window.deleteItem = function(id) {
        if (confirm("Apakah yakin ingin menghapus item ini?")) {
            fetch('../includes/delete_product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus produk');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus produk');
            });
        }
    }

    // Form submission
    productForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        submitText.textContent = 'Menyimpan...';
        loadingSpinner.classList.remove('hidden');
        
        const formData = new FormData(this);
        const isEdit = formData.get('id') !== '';
        
        fetch(isEdit ? '../includes/update_product.php' : '../includes/add_product.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                hideModal();
                window.location.reload();
            } else {
                alert(data.message || 'Terjadi kesalahan saat menyimpan data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan data');
        })
        .finally(() => {
            submitText.textContent = 'Simpan';
            loadingSpinner.classList.add('hidden');
        });  
    });
});

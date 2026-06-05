/**
 * ==============================================================================
 * FILE: pages/cart.js
 * TUJUAN: Halaman Keranjang Belanja
 * DESKRIPSI: Logika cart management termasuk selection, quantity update,
 *            summary calculation, dan delete item dengan modal confirmation.
 * ==============================================================================
 */

import { getCsrfToken, getPageConfig } from '../utils/helpers';

/**
 * Handler untuk manajemen cart: selection, quantity update, delete
 */
export function cartHandler() {
    const config = getPageConfig('cart-config');

    return {
        selectedCount: 0,
        totalPrice: 0,
        showDeleteModal: false,
        cartIdToDelete: null,
        items: config.items ?? [],
        updateUrl: config.updateUrl ?? '',

        init() {
            this.updateSummary();

            window.addEventListener('update-cart', (e) => {
                this.updateQty(e.detail.id, e.detail.qty);
            });
        },

        /**
         * Update summary berdasarkan item yang dipilih (checked)
         */
        updateSummary() {
            const checkboxes = document.querySelectorAll('input[name="cart_ids[]"]:checked');
            this.selectedCount = checkboxes.length;

            let total = 0;
            checkboxes.forEach((cb) => {
                const item = this.items.find((i) => i.id == cb.value);

                if (item) {
                    total += item.price * item.qty;
                }
            });
            this.totalPrice = total;
        },

        /**
         * Update jumlah item di cart dan sync ke server
         */
        async updateQty(cartId, newQty) {
            const item = this.items.find((i) => i.id == cartId);

            if (! item || newQty < 1 || newQty > item.stok) {
                return;
            }

            item.qty = newQty;
            this.updateSummary();

            try {
                const response = await fetch(`${this.updateUrl}/${cartId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({ jumlah: newQty }),
                });

                const data = await response.json();

                if (! data.success) {
                    alert(data.message);
                    location.reload();
                }
            } catch (error) {
                console.error('Error updating quantity:', error);
            }
        },

        /**
         * Buka modal konfirmasi delete item
         */
        removeItem(cartId) {
            this.cartIdToDelete = cartId;
            this.showDeleteModal = true;
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        },
    };
}

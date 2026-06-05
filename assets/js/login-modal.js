document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById('buy-again-modal');

    if (!modal) {
        return;
    }

    const closeModal = async () => {

        await fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'buy_again_close_modal',
            }),
        });

        modal.remove();
    };

    document
        .querySelectorAll('[data-close-buy-again-modal]')
        .forEach(button => {
            button.addEventListener('click', closeModal);
        });
});
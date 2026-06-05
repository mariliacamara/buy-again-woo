document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById('buy-again-modal');

    if (!modal) {
        return;
    }

    const closeModal = () => {

        modal.remove();

        document.cookie =
            'buy_again_show_modal=0; path=/; max-age=86400';
    };

    document
        .querySelectorAll('[data-close-buy-again-modal]')
        .forEach(button => {
            button.addEventListener('click', closeModal);
        });
});
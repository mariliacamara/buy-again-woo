<?php

if (!defined('ABSPATH')) {
    exit;
}

class Buy_Again_Login_Modal
{
    public function __construct()
    {
        add_action(
            'wp_enqueue_scripts',
            [$this, 'enqueue_assets']
        );

        // add_action(
        //     'wp_login',
        //     [$this, 'mark_login'],
        //     10,
        //     2
        // );

        add_action(
            'wp_footer',
            [$this, 'render_modal']
        );
    }

    // public function mark_login($user_login, $user)
    // {
    //     update_user_meta(
    //         $user->ID,
    //         '_buy_again_show_modal',
    //         1
    //     );
    // }

    public function enqueue_assets()
    {
        if (!is_user_logged_in()) {
            return;
        }

        wp_enqueue_style(
            'buy-again-login-modal',
            plugin_dir_url(__FILE__) . '../assets/css/login-modal.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'buy-again-login-modal',
            plugin_dir_url(__FILE__) . '../assets/js/login-modal.js',
            [],
            '1.0.0',
            true
        );
    }

    public function render_modal()
    {
        if (!is_user_logged_in()) {
            return;
        }

        $show_modal = get_user_meta(
            get_current_user_id(),
            '_buy_again_show_modal',
            true
        );

        if (!$show_modal) {
            return;
        }

        delete_user_meta(
            get_current_user_id(),
            '_buy_again_show_modal'
        );

        $user = wp_get_current_user();

        ?>
        <div id="buy-again-modal" class="buy-again-modal">

            <div
                class="buy-again-modal__overlay"
                data-close-buy-again-modal
            ></div>

            <div class="buy-again-modal__content">

                <button
                    type="button"
                    class="buy-again-modal__close"
                    data-close-buy-again-modal
                >
                    ×
                </button>

                <h3>
                    Olá, <?php echo esc_html($user->display_name); ?>
                </h3>

                <p>
                    Deseja aceder seu histórico de encomenda
                    e refazer uma compra?
                </p>

                <div class="buy-again-modal__actions">

                    <a
                        href="<?php echo esc_url(
                            wc_get_account_endpoint_url('orders')
                        ); ?>"
                        class="button buy-again-modal__history"
                    >
                        Histórico de Encomenda
                    </a>

                    <a
                        href="<?php echo esc_url(home_url('/')); ?>"
                        class="button buy-again-modal__continue"
                    >
                        Continuar a Compra
                    </a>

                </div>

            </div>

        </div>
        <?php
    }
}
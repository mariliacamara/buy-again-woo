<?php

if (!defined('ABSPATH')) {
    exit;
}

class Buy_Again_Login_Modal
{
    public function __construct()
    {
        add_action(
            'wp_login',
            [$this, 'mark_login'],
            10,
            2
        );

        add_action(
            'wp_footer',
            [$this, 'render_modal']
        );
    }

    public function mark_login($user_login, $user)
    {
        if (headers_sent()) {
            return;
        }

        setcookie(
            'buy_again_show_modal',
            '1',
            time() + 300,
            COOKIEPATH ?: '/'
        );
    }

    public function render_modal()
    {
        if (!is_user_logged_in()) {
            return;
        }

        if (
            empty($_COOKIE['buy_again_show_modal']) ||
            $_COOKIE['buy_again_show_modal'] !== '1'
        ) {
            return;
        }

        setcookie(
            'buy_again_show_modal',
            '',
            time() - 3600,
            COOKIEPATH ?: '/'
        );

        $user = wp_get_current_user();

        ?>
        <div id="buy-again-modal">
            <div
                style="
                    position:fixed;
                    top:50%;
                    left:50%;
                    transform:translate(-50%,-50%);
                    background:#fff;
                    padding:30px;
                    border:1px solid #ccc;
                    z-index:99999;
                "
            >

                <button
                    type="button"
                    data-close-buy-again-modal
                >
                    X
                </button>

                <h3>
                    Olá, <?php echo esc_html($user->display_name); ?>
                </h3>

                <p>
                    Deseja aceder seu histórico de encomenda
                    e refazer uma compra?
                </p>

                <p>
                    <a
                        href="<?php echo esc_url(
                            wc_get_account_endpoint_url('orders')
                        ); ?>"
                        class="button"
                    >
                        Histórico de Encomenda
                    </a>

                    <a
                        href="<?php echo esc_url(home_url('/')); ?>"
                        class="button"
                    >
                        Continuar a Compra
                    </a>
                </p>
            </div>
        </div>
        <?php
    }
}
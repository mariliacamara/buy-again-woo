<?php
/**
 * Página de definições do Buy Again, no menu Camorim Tech.
 *
 * @package BuyAgainWoo
 */

declare(strict_types=1);

namespace Marilia\BuyAgainWoo\Admin;

use Marilia\BuyAgainWoo\Assets;
use Marilia\BuyAgainWoo\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Regista e mostra a página Camorim Tech → Buy Again.
 */
class SettingsPage {

	/**
	 * Hook da página, para carregar o CSS e o JS só nela.
	 *
	 * @var string
	 */
	private $hook = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'option_page_capability_buy_again_woo_settings', array( $this, 'capability' ) );
	}

	/**
	 * Permissão para gravar em options.php: a mesma da página (o padrão seria manage_options).
	 *
	 * @return string
	 */
	public function capability(): string {
		return 'manage_woocommerce';
	}

	/**
	 * Acrescenta a página ao menu Camorim Tech.
	 */
	public function register_menu(): void {
		$hook       = add_submenu_page(
			'camorim-tech',
			'Buy Again',
			'Buy Again',
			'manage_woocommerce',
			'buy-again-woo',
			array( $this, 'render_page' )
		);
		$this->hook = $hook ? (string) $hook : '';
	}

	/**
	 * Regista a opção com a validação de Settings.
	 */
	public function register_settings(): void {
		register_setting(
			'buy_again_woo_settings',
			Settings::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( Settings::class, 'sanitize' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * CSS, seletor de cor e JS da pré-visualização, só nesta página.
	 *
	 * @param string $current Hook da página atual.
	 */
	public function enqueue_assets( $current ): void {
		if ( '' === $this->hook || $current !== $this->hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'buy-again-woo-admin', BUY_AGAIN_WOO_URL . 'assets/admin/admin.css', array(), BUY_AGAIN_WOO_VERSION );
		wp_add_inline_style( 'buy-again-woo-admin', str_replace( ':root', '#ba-preview', Assets::css_variables( Settings::get() ) ) );
		wp_enqueue_script( 'buy-again-woo-admin', BUY_AGAIN_WOO_URL . 'assets/admin/admin.js', array( 'jquery', 'wp-color-picker' ), BUY_AGAIN_WOO_VERSION, true );
	}

	/**
	 * Nome do campo no formulário.
	 *
	 * @param string $key Chave da definição.
	 * @return string
	 */
	private function name( string $key ): string {
		return Settings::OPTION . '[' . $key . ']';
	}

	/**
	 * Campo de texto (ou área de texto).
	 *
	 * @param array<string, mixed> $s     Definições.
	 * @param string               $key   Chave.
	 * @param string               $label Rótulo.
	 * @param string               $help  Ajuda (opcional).
	 * @param bool                 $area  Área de texto.
	 */
	private function text( array $s, string $key, string $label, string $help = '', bool $area = false ): void {
		$id = 'ba_' . $key;
		echo '<div class="ba-field"><label class="ba-label" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
		if ( $area ) {
			echo '<textarea class="large-text" rows="2" id="' . esc_attr( $id ) . '" name="' . esc_attr( $this->name( $key ) ) . '" data-ba-text="' . esc_attr( $key ) . '">' . esc_textarea( (string) $s[ $key ] ) . '</textarea>';
		} else {
			echo '<input type="text" class="regular-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $this->name( $key ) ) . '" value="' . esc_attr( (string) $s[ $key ] ) . '" data-ba-text="' . esc_attr( $key ) . '">';
		}
		if ( '' !== $help ) {
			echo '<span class="ba-help">' . esc_html( $help ) . '</span>';
		}
		echo '</div>';
	}

	/**
	 * Interruptor.
	 *
	 * @param array<string, mixed> $s     Definições.
	 * @param string               $key   Chave.
	 * @param string               $label Rótulo.
	 */
	private function toggle( array $s, string $key, string $label ): void {
		echo '<label class="ba-switch"><input type="checkbox" role="switch" name="' . esc_attr( $this->name( $key ) ) . '" value="1" data-ba-switch="' . esc_attr( $key ) . '"' . checked( (bool) $s[ $key ], true, false ) . '><span>' . esc_html( $label ) . '</span></label>';
	}

	/**
	 * Escolha de destino: início, página ou URL.
	 *
	 * @param array<string, mixed> $s      Definições.
	 * @param string               $prefix modal_secondary ou cart.
	 * @param string               $label  Rótulo.
	 */
	private function destination( array $s, string $prefix, string $label ): void {
		$type  = (string) $s[ $prefix . '_type' ];
		$types = array(
			'home' => array( 'Página inicial', 'dashicons-admin-home' ),
			'page' => array( 'Página', 'dashicons-admin-page' ),
			'url'  => array( 'URL', 'dashicons-admin-links' ),
		);
		echo '<div class="ba-field" data-ba-destination="' . esc_attr( $prefix ) . '"><span class="ba-label">' . esc_html( $label ) . '</span>';
		echo '<div class="ba-segmented" role="radiogroup" aria-label="' . esc_attr( $label ) . '">';
		foreach ( $types as $value => $info ) {
			echo '<label class="ba-segment"><input type="radio" name="' . esc_attr( $this->name( $prefix . '_type' ) ) . '" value="' . esc_attr( $value ) . '"' . checked( $type, $value, false ) . '><span><span class="dashicons ' . esc_attr( $info[1] ) . '" aria-hidden="true"></span>' . esc_html( $info[0] ) . '</span></label>';
		}
		echo '</div>';
		echo '<div class="ba-sub" data-ba-show="page"><label class="screen-reader-text" for="ba_' . esc_attr( $prefix ) . '_page">Página</label>';
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_dropdown_pages escapa o que imprime.
		wp_dropdown_pages(
			array(
				'name'              => $this->name( $prefix . '_page' ),
				'id'                => 'ba_' . $prefix . '_page',
				'selected'          => (int) $s[ $prefix . '_page' ],
				'show_option_none'  => 'Selecionar página',
				'option_none_value' => '0',
			)
		);
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
		echo '<div class="ba-sub" data-ba-show="url"><label class="screen-reader-text" for="ba_' . esc_attr( $prefix ) . '_url">URL</label><input type="url" class="regular-text" id="ba_' . esc_attr( $prefix ) . '_url" name="' . esc_attr( $this->name( $prefix . '_url' ) ) . '" placeholder="https://" value="' . esc_attr( (string) $s[ $prefix . '_url' ] ) . '"></div>';
		echo '<span class="ba-help">Uma página apagada ou um URL vazio levam à página inicial.</span>';
		echo '</div>';
	}

	/**
	 * Mostra a página.
	 */
	public function render_page(): void {
		$s        = Settings::get();
		$defaults = Settings::defaults();
		$colors   = array(
			'color_primary'       => 'Botão principal',
			'color_primary_hover' => 'Botão principal ao passar o rato',
			'color_secondary'     => 'Segundo botão e botão do carrinho',
			'color_text'          => 'Título e texto da janela',
		);
		?>
		<div class="wrap ba-admin">
			<h1 class="ba-title"><span class="dashicons dashicons-update" aria-hidden="true"></span> Buy Again</h1>
			<p class="ba-lead">Deixa o cliente repetir uma encomenda antiga com um clique e lembra-o disso depois do login.</p>

			<?php settings_errors(); ?>

			<form method="post" action="options.php" class="ba-layout">
				<?php settings_fields( 'buy_again_woo_settings' ); ?>

				<div class="ba-main">
					<section class="ba-card">
						<h2>Botão nas encomendas</h2>
						<p class="ba-muted">Aparece em cada encomenda, em A minha conta → Encomendas.</p>
						<?php $this->text( $s, 'order_button_text', 'Texto do botão' ); ?>
					</section>

					<section class="ba-card">
						<div class="ba-card-head"><h2>Janela depois do login</h2><?php $this->toggle( $s, 'modal_enabled', 'Ligada' ); ?></div>
						<p class="ba-muted">Abre uma vez, a seguir a cada login.</p>
						<div class="ba-field"><?php $this->toggle( $s, 'modal_only_with_orders', 'Mostrar só a clientes que já têm encomendas' ); ?></div>
						<?php
						$this->text( $s, 'modal_title', 'Título', '{nome} é trocado pelo nome do cliente.' );
						$this->text( $s, 'modal_message', 'Mensagem', '', true );
						?>
						<div class="ba-two">
							<?php
							$this->text( $s, 'modal_primary_text', 'Botão principal', 'Leva sempre às encomendas.' );
							$this->text( $s, 'modal_secondary_text', 'Segundo botão' );
							?>
						</div>
						<?php $this->destination( $s, 'modal_secondary', 'Destino do segundo botão' ); ?>
					</section>

					<section class="ba-card">
						<div class="ba-card-head"><h2>Botão no carrinho</h2><?php $this->toggle( $s, 'cart_enabled', 'Ligado' ); ?></div>
						<p class="ba-muted">Ao lado de «Atualizar carrinho».</p>
						<?php
						$this->text( $s, 'cart_text', 'Texto' );
						$this->destination( $s, 'cart', 'Destino' );
						?>
					</section>

					<section class="ba-card">
						<h2>Cores</h2>
						<div class="ba-colors">
							<?php foreach ( $colors as $key => $label ) : ?>
								<div class="ba-color">
									<span class="ba-label"><?php echo esc_html( $label ); ?></span>
									<input type="text" class="ba-color-input" data-ba-color="<?php echo esc_attr( $key ); ?>"
										data-default-color="<?php echo esc_attr( $defaults[ $key ] ); ?>"
										name="<?php echo esc_attr( $this->name( $key ) ); ?>"
										value="<?php echo esc_attr( (string) $s[ $key ] ); ?>">
								</div>
							<?php endforeach; ?>
						</div>
					</section>

					<?php submit_button( 'Guardar alterações', 'primary large' ); ?>
				</div>

				<aside class="ba-side">
					<div class="ba-card" id="ba-preview">
						<h2>Pré-visualização</h2>

						<span class="ba-preview-caption">Janela depois do login</span>
						<div class="ba-preview-stage" data-ba-when="modal_enabled">
							<div class="ba-preview-modal">
								<span class="ba-preview-close" aria-hidden="true">×</span>
								<strong class="ba-preview-title" data-ba-preview="modal_title"></strong>
								<p class="ba-preview-message" data-ba-preview="modal_message"></p>
								<span class="ba-preview-btn ba-preview-btn--primary" data-ba-preview="modal_primary_text"></span>
								<span class="ba-preview-btn ba-preview-btn--secondary" data-ba-preview="modal_secondary_text"></span>
							</div>
						</div>
						<p class="ba-preview-off" data-ba-when-off="modal_enabled">Janela desligada.</p>

						<span class="ba-preview-caption">Nas encomendas</span>
						<div class="ba-preview-row"><span>#1043 · 12 nov · 38,40 €</span><span class="ba-preview-btn ba-preview-btn--primary ba-preview-btn--small" data-ba-preview="order_button_text"></span></div>

						<span class="ba-preview-caption">No carrinho</span>
						<div class="ba-preview-row ba-preview-row--end" data-ba-when="cart_enabled"><span class="ba-preview-btn ba-preview-btn--secondary ba-preview-btn--small" data-ba-preview="cart_text"></span></div>
						<p class="ba-preview-off" data-ba-when-off="cart_enabled">Botão desligado.</p>

						<p class="ba-help">Atualiza enquanto mexe nos campos. Na loja, os botões usam o tipo de letra do tema.</p>
					</div>
				</aside>
			</form>

			<footer class="ba-footer">
				Developed by <strong>Camorim Tech</strong> &middot;
				<a href="https://camorim.dev.br" target="_blank" rel="noopener">camorim.dev.br</a>
			</footer>
		</div>
		<?php
	}
}

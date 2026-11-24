<?php
/**
 * Definições do Buy Again: valores de partida, leitura e validação.
 *
 * @package BuyAgainWoo
 */

namespace Marilia\BuyAgainWoo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Guarda todas as definições numa só opção.
 */
final class Settings {

	/**
	 * Nome da opção.
	 */
	const OPTION = 'buy_again_woo_settings';

	/**
	 * Campos de texto e o tipo de limpeza de cada um.
	 */
	const TEXT_FIELDS = array(
		'order_button_text'    => 'text',
		'modal_title'          => 'text',
		'modal_message'        => 'textarea',
		'modal_primary_text'   => 'text',
		'modal_secondary_text' => 'text',
		'cart_text'            => 'text',
	);

	/**
	 * Interruptores.
	 */
	const SWITCHES = array( 'modal_enabled', 'modal_only_with_orders', 'cart_enabled' );

	/**
	 * Cores.
	 */
	const COLORS = array( 'color_primary', 'color_primary_hover', 'color_secondary', 'color_text' );

	/**
	 * Destinos: prefixo de cada grupo tipo/página/URL.
	 */
	const DESTINATIONS = array(
		'modal_secondary' => array( 'modal_secondary_type', 'modal_secondary_page', 'modal_secondary_url' ),
		'cart'            => array( 'cart_type', 'cart_page', 'cart_url' ),
	);

	/**
	 * Tipos de destino aceites.
	 */
	const DESTINATION_TYPES = array( 'home', 'page', 'url' );

	/**
	 * Valores de partida (os textos e as cores da versão 1.1.0).
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		return array(
			'order_button_text'      => __( 'Comprar Novamente', 'buy-again-woo' ),
			'modal_enabled'          => true,
			'modal_only_with_orders' => true,
			/* translators: {nome} é trocado pelo nome do cliente. */
			'modal_title'            => __( 'Olá, {nome}', 'buy-again-woo' ),
			'modal_message'          => __( 'Deseja aceder ao seu histórico de encomendas e refazer uma compra?', 'buy-again-woo' ),
			'modal_primary_text'     => __( 'Histórico de encomendas', 'buy-again-woo' ),
			'modal_secondary_text'   => __( 'Continuar a compra', 'buy-again-woo' ),
			'modal_secondary_type'   => 'home',
			'modal_secondary_page'   => 0,
			'modal_secondary_url'    => '',
			'cart_enabled'           => true,
			'cart_text'              => __( 'Continuar a Comprar', 'buy-again-woo' ),
			'cart_type'              => 'home',
			'cart_page'              => 0,
			'cart_url'               => '',
			'color_primary'          => '#37bdd6',
			'color_primary_hover'    => '#2aa8c0',
			'color_secondary'        => '#081d7a',
			'color_text'             => '#081d7a',
		);
	}

	/**
	 * Definições gravadas, completadas com os valores de partida.
	 *
	 * @return array<string, mixed>
	 */
	public static function get(): array {
		$defaults = self::defaults();
		$saved    = get_option( self::OPTION, array() );
		if ( ! is_array( $saved ) ) {
			return $defaults;
		}
		return array_merge( $defaults, array_intersect_key( $saved, $defaults ) );
	}

	/**
	 * Valida o formulário (callback do register_setting).
	 *
	 * Um interruptor que não vem no formulário fica desligado.
	 *
	 * @param mixed $input Valores enviados.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $input ): array {
		$input    = is_array( $input ) ? $input : array();
		$defaults = self::defaults();
		$out      = array();

		foreach ( self::TEXT_FIELDS as $key => $type ) {
			$value       = isset( $input[ $key ] ) ? (string) $input[ $key ] : '';
			$value       = 'textarea' === $type ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
			$out[ $key ] = '' === trim( $value ) ? $defaults[ $key ] : $value;
		}

		foreach ( self::SWITCHES as $key ) {
			$out[ $key ] = ! empty( $input[ $key ] );
		}

		foreach ( self::COLORS as $key ) {
			$color       = isset( $input[ $key ] ) ? self::normalize_color( (string) $input[ $key ] ) : null;
			$out[ $key ] = null === $color ? $defaults[ $key ] : $color;
		}

		foreach ( self::DESTINATIONS as list( $type_key, $page_key, $url_key ) ) {
			$type             = isset( $input[ $type_key ] ) ? sanitize_key( (string) $input[ $type_key ] ) : 'home';
			$out[ $type_key ] = in_array( $type, self::DESTINATION_TYPES, true ) ? $type : 'home';
			$out[ $page_key ] = isset( $input[ $page_key ] ) ? absint( $input[ $page_key ] ) : 0;
			$url              = isset( $input[ $url_key ] ) ? trim( (string) $input[ $url_key ] ) : '';
			$out[ $url_key ]  = '' === $url ? '' : esc_url_raw( $url, array( 'http', 'https' ) );
		}

		return $out;
	}

	/**
	 * URL de um destino. Página que não está publicada ou URL vazio levam ao início.
	 *
	 * @param string $type Tipo: home, page ou url.
	 * @param int    $page ID da página.
	 * @param string $url  URL.
	 * @return string
	 */
	public static function destination( string $type, int $page, string $url ): string {
		if ( 'page' === $type && $page && 'publish' === get_post_status( $page ) ) {
			$link = get_permalink( $page );
			if ( $link ) {
				return $link;
			}
		}
		if ( 'url' === $type && '' !== $url ) {
			return $url;
		}
		return home_url( '/' );
	}

	/**
	 * Normaliza uma cor para #rrggbb, ou null se não for válida.
	 *
	 * @param string $color Cor enviada.
	 * @return string|null
	 */
	private static function normalize_color( string $color ): ?string {
		$hex = strtolower( ltrim( trim( $color ), '#' ) );
		if ( preg_match( '/^[0-9a-f]{3}$/', $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		return preg_match( '/^[0-9a-f]{6}$/', $hex ) ? '#' . $hex : null;
	}
}

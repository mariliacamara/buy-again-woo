<?php
/**
 * Cores do Buy Again como variáveis CSS.
 *
 * @package BuyAgainWoo
 */

namespace Marilia\BuyAgainWoo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gera o CSS inline com as cores das definições.
 */
final class Assets {

	/**
	 * Variáveis CSS a partir das definições.
	 *
	 * As cores já vêm validadas (#rrggbb) de Settings; voltam a ser verificadas
	 * aqui para nunca pôr nada além de uma cor no CSS.
	 *
	 * @param array<string, mixed> $s Definições (Settings::get()).
	 * @return string
	 */
	public static function css_variables( array $s ): string {
		$map = array(
			'--ba-cor-principal'       => 'color_primary',
			'--ba-cor-principal-hover' => 'color_primary_hover',
			'--ba-cor-secundaria'      => 'color_secondary',
			'--ba-cor-texto'           => 'color_text',
		);
		$defaults = Settings::defaults();
		$vars     = '';
		foreach ( $map as $var => $key ) {
			$color = isset( $s[ $key ] ) ? (string) $s[ $key ] : '';
			if ( ! preg_match( '/^#[0-9a-f]{6}$/', $color ) ) {
				$color = $defaults[ $key ];
			}
			$vars .= $var . ':' . $color . ';';
		}
		return ':root{' . rtrim( $vars, ';' ) . '}';
	}
}

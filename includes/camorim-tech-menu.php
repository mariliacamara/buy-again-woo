<?php
/**
 * Menu «Camorim Tech» partilhado pelos plugins da Camorim Tech.
 *
 * Este ficheiro é copiado igual para cada plugin. A cópia que carregar primeiro
 * define as funções e regista os hooks; as outras não fazem nada. Por isso, ao
 * mudar este ficheiro, muda-se em todos os plugins ao mesmo tempo.
 *
 * Cada plugin acrescenta a sua página com:
 *   add_submenu_page( 'camorim-tech', ..., 'o-meu-slug', ... );
 * em admin_menu com a prioridade normal (10).
 *
 * @package CamorimTech
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'CAMORIM_TECH_MENU_VERSION' ) ) {
	define( 'CAMORIM_TECH_MENU_VERSION', '1.0.0' );
}

if ( ! function_exists( 'camorim_tech_menu_register' ) ) {
	/**
	 * Regista o menu de topo «Camorim Tech» (uma só vez).
	 *
	 * @return void
	 */
	function camorim_tech_menu_register() {
		global $menu;
		foreach ( (array) $menu as $item ) {
			if ( isset( $item[2] ) && 'camorim-tech' === $item[2] ) {
				return;
			}
		}
		add_menu_page( 'Camorim Tech', 'Camorim Tech', 'read', 'camorim-tech', '__return_null', 'dashicons-admin-generic', 56 );
	}
}

if ( ! function_exists( 'camorim_tech_menu_finalize' ) ) {
	/**
	 * Arruma o menu depois de todos os plugins acrescentarem as suas páginas.
	 *
	 * Tira a entrada repetida que o WordPress cria para o próprio topo e, se o
	 * utilizador não tiver acesso a nenhuma página, tira o menu inteiro. O link
	 * do topo passa a ser, pelo próprio WordPress, a primeira página acessível.
	 *
	 * @return void
	 */
	function camorim_tech_menu_finalize() {
		global $submenu;
		if ( isset( $submenu['camorim-tech'] ) ) {
			foreach ( $submenu['camorim-tech'] as $i => $item ) {
				if ( isset( $item[2] ) && 'camorim-tech' === $item[2] ) {
					unset( $submenu['camorim-tech'][ $i ] );
				}
			}
			$submenu['camorim-tech'] = array_values( $submenu['camorim-tech'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- arrumar o próprio menu.
		}

		$accessible = array_filter(
			isset( $submenu['camorim-tech'] ) ? $submenu['camorim-tech'] : array(),
			function ( $item ) {
				return isset( $item[1] ) && current_user_can( $item[1] );
			}
		);
		if ( ! $accessible ) {
			remove_menu_page( 'camorim-tech' );
		}
	}
}

if ( ! has_action( 'admin_menu', 'camorim_tech_menu_register' ) ) {
	add_action( 'admin_menu', 'camorim_tech_menu_register', 9 );
}
if ( ! has_action( 'admin_menu', 'camorim_tech_menu_finalize' ) ) {
	add_action( 'admin_menu', 'camorim_tech_menu_finalize', 999 );
}

<?php
/**
 * Menu Camorim Tech, página de definições e versão.
 * wp eval-file .../tests/test-admin.php <loja>
 */

use Marilia\BuyAgainWoo\Admin\SettingsPage;
use Marilia\BuyAgainWoo\Settings;

require __DIR__ . '/bootstrap.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/user.php';

$original = get_option(Settings::OPTION, null);
$tmp = array();
cl_cleanup(function () use ($original, &$tmp) {
    if ($original === null) delete_option(Settings::OPTION); else update_option(Settings::OPTION, $original);
    foreach ($tmp as $u) wp_delete_user($u);
    @unlink(sys_get_temp_dir() . '/ba-menu-copia/camorim-tech-menu.php');
    @rmdir(sys_get_temp_dir() . '/ba-menu-copia');
});

$admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
$admin = (int) $admins[0];

function ba_menu_limpo() {
    $GLOBALS['menu'] = array();
    $GLOBALS['submenu'] = array();
    $GLOBALS['_wp_submenu_nopriv'] = array();
    $GLOBALS['_registered_pages'] = array();
    $GLOBALS['admin_page_hooks'] = array();
}

function ba_topos() {
    return array_values(array_filter($GLOBALS['menu'], function ($m) { return isset($m[2]) && $m[2] === 'camorim-tech'; }));
}

/* Ficheiro do menu partilhado, carregado por dois plugins */
cl_assert(function_exists('camorim_tech_menu_register') && function_exists('camorim_tech_menu_finalize'), 'funções do menu Camorim Tech existem');
wp_mkdir_p(sys_get_temp_dir() . '/ba-menu-copia');
copy(BUY_AGAIN_WOO_PATH . 'includes/camorim-tech-menu.php', sys_get_temp_dir() . '/ba-menu-copia/camorim-tech-menu.php');
require sys_get_temp_dir() . '/ba-menu-copia/camorim-tech-menu.php';
cl_assert(true, 'segunda cópia do ficheiro do menu carrega sem erro');
cl_assert(has_action('admin_menu', 'camorim_tech_menu_register') === 9 && has_action('admin_menu', 'camorim_tech_menu_finalize') === 999, 'hooks do menu com prioridades 9 e 999');

/* Administrador */
wp_set_current_user($admin);
ba_menu_limpo();
camorim_tech_menu_register();
camorim_tech_menu_register();
cl_assert(count(ba_topos()) === 1, 'menu de topo registado uma só vez');
(new SettingsPage())->register_menu();
camorim_tech_menu_finalize();
$sub = isset($GLOBALS['submenu']['camorim-tech']) ? array_column($GLOBALS['submenu']['camorim-tech'], 2) : array();
cl_assert($sub && $sub[0] === 'buy-again-woo' && !in_array('camorim-tech', $sub, true), 'submenu: Buy Again primeiro, sem a entrada repetida do topo (' . implode(',', $sub) . ')');
cl_assert(count(ba_topos()) === 1, 'administrador vê o menu Camorim Tech');

/* Assinante */
$assinante = wp_insert_user(array('user_login' => 'ba_teste_sub_' . wp_rand(1000, 9999), 'user_pass' => wp_generate_password(), 'role' => 'subscriber'));
$tmp[] = $assinante;
wp_set_current_user($assinante);
ba_menu_limpo();
camorim_tech_menu_register();
(new SettingsPage())->register_menu();
camorim_tech_menu_finalize();
cl_assert(count(ba_topos()) === 0, 'assinante não vê o menu Camorim Tech');
wp_set_current_user($admin);

/* Página */
update_option(Settings::OPTION, array_merge(Settings::defaults(), array('modal_title' => 'Bem-vinda, {nome}')));
ob_start(); (new SettingsPage())->render_page(); $h = ob_get_clean();
cl_assert(strpos($h, 'name="buy_again_woo_settings[modal_title]"') !== false && strpos($h, 'value="Bem-vinda, {nome}"') !== false, 'página: campo do título com o valor gravado');
foreach (array_keys(Settings::defaults()) as $k) {
    cl_assert(strpos($h, 'buy_again_woo_settings[' . $k . ']') !== false, "página: campo $k");
}
cl_assert(strpos($h, 'Developed by <strong>Camorim Tech</strong>') !== false, 'página: rodapé da Camorim Tech');
cl_assert(strpos($h, 'id="ba-preview"') !== false, 'página: pré-visualização');
cl_assert(stripos($h, 'coming soon') === false && strpos($h, 'buy_again_woo_custom_css') === false, 'página: sem «coming soon» nem o CSS antigo');
foreach (array('Salvar', 'Buscar', 'Selecione', 'General', 'Styles') as $w) {
    cl_assert(strpos($h, '>' . $w) === false, "página sem «$w»");
}

/* Opções registadas */
(new SettingsPage())->register_settings();
$reg = get_registered_settings();
cl_assert(isset($reg[Settings::OPTION]) && !isset($reg['buy_again_woo_custom_css']), 'opção registada; CSS antigo não');
cl_assert(isset($reg[Settings::OPTION]['sanitize_callback']) && is_callable($reg[Settings::OPTION]['sanitize_callback']), 'opção com validação');

cl_assert(BUY_AGAIN_WOO_VERSION === '1.2.0', 'versão 1.2.0 (' . BUY_AGAIN_WOO_VERSION . ')');

cl_test_finish();

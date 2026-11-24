<?php
/**
 * Loja: botão nas encomendas, janela depois do login, botão no carrinho, cores e repor encomenda.
 * wp eval-file .../tests/test-loja.php <loja>
 */

use Marilia\BuyAgainWoo\Assets;
use Marilia\BuyAgainWoo\BuyAgain;
use Marilia\BuyAgainWoo\BuyAgainLoginModal;
use Marilia\BuyAgainWoo\Settings;

require __DIR__ . '/bootstrap.php';
require_once ABSPATH . 'wp-admin/includes/user.php';
add_filter('pre_wp_mail', '__return_false'); // Nenhum email sai dos testes, mesmo fora do guarda do dev.

// Restos de uma corrida interrompida: clientes ba_teste_* e as encomendas deles.
foreach (get_users(array('search' => 'ba_teste_*', 'search_columns' => array('user_login'), 'fields' => 'ID')) as $velho) {
    foreach (wc_get_orders(array('customer_id' => $velho, 'limit' => -1, 'return' => 'ids', 'status' => array_keys(wc_get_order_statuses()))) as $o) wc_get_order($o)->delete(true);
    wp_delete_user($velho);
}
$original = get_option(Settings::OPTION, null);
$limpar = array('users' => array(), 'orders' => array());
cl_cleanup(function () use ($original, &$limpar) {
    if ($original === null) delete_option(Settings::OPTION); else update_option(Settings::OPTION, $original);
    foreach ($limpar['orders'] as $o) { $x = wc_get_order($o); if ($x) $x->delete(true); }
    foreach ($limpar['users'] as $u) wp_delete_user($u);
});

function ba_grava($valores) {
    update_option(Settings::OPTION, array_merge(Settings::defaults(), $valores));
}

// Produto simples comprável.
$produto = null;
foreach (wc_get_products(array('type' => 'simple', 'status' => 'publish', 'stock_status' => 'instock', 'limit' => 30)) as $p) {
    if ($p->is_purchasable() && $p->get_price() > 0) { $produto = $p; break; }
}
cl_assert($produto !== null, 'há um produto simples comprável');

$a = wp_insert_user(array('user_login' => 'ba_teste_a_' . wp_rand(1000, 9999), 'user_pass' => wp_generate_password(), 'role' => 'customer', 'display_name' => '<b>Ana</b>'));
$b = wp_insert_user(array('user_login' => 'ba_teste_b_' . wp_rand(1000, 9999), 'user_pass' => wp_generate_password(), 'role' => 'customer', 'display_name' => 'Bruno'));
$limpar['users'] = array($a, $b);
$encomenda = wc_create_order(array('customer_id' => $a));
$encomenda->add_product($produto, 2);
$encomenda->calculate_totals();
$encomenda->set_status('completed');
$encomenda->save();
$limpar['orders'][] = $encomenda->get_id();

/* Botão nas encomendas */
ba_grava(array('order_button_text' => 'Repetir'));
$acoes = (new BuyAgain())->add_button(array(), $encomenda);
cl_assert($acoes['buy_again']['name'] === 'Repetir', 'botão nas encomendas usa o texto gravado');
cl_assert(strpos($acoes['buy_again']['url'], '_wpnonce=') !== false && strpos($acoes['buy_again']['url'], 'buy_again=' . $encomenda->get_id()) !== false, 'botão nas encomendas mantém o nonce e o id');

/* Quem vê a janela */
$m = new BuyAgainLoginModal();
ba_grava(array('modal_enabled' => false));
cl_assert($m->should_show($a) === false, 'janela desligada: não aparece');
ba_grava(array());
cl_assert($m->should_show($a) === true && $m->should_show($b) === false, 'só com encomendas: A vê, B não');
ba_grava(array('modal_only_with_orders' => false));
cl_assert($m->should_show($b) === true, 'para todos: B também vê');

/* Conteúdo da janela */
ba_grava(array('modal_secondary_type' => 'url', 'modal_secondary_url' => 'https://exemplo.pt/x', 'modal_title' => 'Olá, {nome}', 'modal_primary_text' => 'Ver encomendas'));
// wp_insert_user limpa o HTML do nome; gravar direto simula um nome perigoso vindo de outro sítio.
$GLOBALS['wpdb']->update($GLOBALS['wpdb']->users, array('display_name' => '<b>Ana</b>'), array('ID' => $a));
clean_user_cache($a);
wp_set_current_user($a);
update_user_meta($a, '_buy_again_show_modal', 1);
ob_start(); $m->render_modal(); $h = ob_get_clean();
cl_assert(strpos($h, 'Olá, &lt;b&gt;Ana&lt;/b&gt;') !== false && strpos($h, '<b>Ana') === false, 'título com {nome} escapado' . (strpos($h, '&lt;b&gt;') === false ? ' — h3: ' . (preg_match('~<h3[^>]*>(.*?)</h3>~s', $h, $hm) ? $hm[1] : substr($h, 0, 200)) : ''));
cl_assert(strpos($h, 'href="https://exemplo.pt/x"') !== false, 'segundo botão vai para o destino gravado');
cl_assert(strpos($h, 'Ver encomendas') !== false && strpos($h, esc_url(wc_get_account_endpoint_url('orders'))) !== false, 'botão principal vai para as encomendas');
cl_assert(strpos($h, '<br') === false, 'mensagem sem <br>');
update_user_meta($b, '_buy_again_show_modal', 1);
ba_grava(array());
wp_set_current_user($b);
ob_start(); $m->render_modal(); $h = ob_get_clean();
cl_assert(trim($h) === '', 'cliente sem encomendas não vê a janela');
delete_user_meta($b, '_buy_again_show_modal');

ba_grava(array('modal_enabled' => false));
delete_user_meta($a, '_buy_again_show_modal');
$m->mark_login('x', get_userdata($a));
cl_assert(!get_user_meta($a, '_buy_again_show_modal', true), 'janela desligada: o login não marca o cliente');
ba_grava(array());
$m->mark_login('x', get_userdata($a));
cl_assert((bool) get_user_meta($a, '_buy_again_show_modal', true), 'janela ligada: o login marca o cliente');
delete_user_meta($a, '_buy_again_show_modal');

/* Botão no carrinho */
$ba = new BuyAgain();
ba_grava(array('cart_enabled' => false));
ob_start(); $ba->render_continue_shopping_button(); $h = ob_get_clean();
cl_assert(trim($h) === '', 'botão do carrinho desligado: não aparece');
ba_grava(array('cart_text' => 'Voltar', 'cart_type' => 'url', 'cart_url' => 'https://exemplo.pt/loja'));
ob_start(); $ba->render_continue_shopping_button(); $h = ob_get_clean();
cl_assert(strpos($h, 'Voltar') !== false && strpos($h, 'href="https://exemplo.pt/loja"') !== false, 'botão do carrinho com texto e destino gravados');

/* Cores */
$css = Assets::css_variables(Settings::defaults());
cl_assert(strpos($css, '--ba-cor-principal:#37bdd6') !== false && strpos($css, '--ba-cor-principal-hover:#2aa8c0') !== false && strpos($css, '--ba-cor-secundaria:#081d7a') !== false && strpos($css, '--ba-cor-texto:#081d7a') !== false, 'variáveis CSS com as cores');
cl_assert(strpos($css, '--ba-cor-secundaria-hover:#061762') !== false, 'hover do segundo botão calculado no servidor (#081d7a 80% → #061762)');
cl_assert(strpos(Assets::css_variables(array_merge(Settings::defaults(), array('color_secondary' => '#297131'))), '--ba-cor-secundaria-hover:#215a27') !== false, 'hover do segundo botão segue a cor gravada');
cl_assert(strpos(file_get_contents(BUY_AGAIN_WOO_PATH . 'assets/css/login-modal.css'), 'color-mix') === false, 'CSS da loja sem color-mix (navegadores antigos)');
$fixas = array();
foreach (array('buy-again.css', 'login-modal.css') as $f) {
    $src = file_get_contents(BUY_AGAIN_WOO_PATH . 'assets/css/' . $f);
    $sem_var = preg_replace('~var\([^)]*\)~', '', $src);
    preg_match_all('~#[0-9a-f]{3,6}\b~i', $sem_var, $mm);
    foreach ($mm[0] as $c) if (!in_array(strtolower($c), array('#fff', '#ffffff', '#d9d9d9', '#000'), true)) $fixas[] = "$f $c";
}
cl_assert(!$fixas, 'CSS sem cores fixas da Zincomed' . ($fixas ? ': ' . implode(', ', $fixas) : ''));

$css_loja = file_get_contents(BUY_AGAIN_WOO_PATH . 'assets/css/buy-again.css');
cl_assert(strpos($css_loja, '.shop_table tr') === false, 'CSS não mexe nas tabelas do carrinho e dos totais (só na das encomendas)');

/* Repor encomenda */
wc_load_cart();
WC()->cart->empty_cart();
wc_clear_notices();
wp_set_current_user($b);
$_GET = array('buy_again' => $encomenda->get_id(), '_wpnonce' => wp_create_nonce('buy_again_' . $encomenda->get_id()));
$ba->process_reorder();
$erros = wc_get_notices('error');
cl_assert(WC()->cart->is_empty() && $erros && strpos(wp_json_encode($erros), 'permiss') !== false, 'encomenda de outro cliente: recusada');
wc_clear_notices();
wp_set_current_user($a);
$_GET = array('buy_again' => $encomenda->get_id(), '_wpnonce' => wp_create_nonce('buy_again_' . $encomenda->get_id()));
$redir = function ($l) { throw new Exception('redirect'); };
add_filter('wp_redirect', $redir);
try { $ba->process_reorder(); } catch (Exception $e) {}
remove_filter('wp_redirect', $redir);
$qtd = 0; foreach (WC()->cart->get_cart() as $item) if ((int) $item['product_id'] === $produto->get_id()) $qtd += $item['quantity'];
cl_assert($qtd === 2, "a própria encomenda volta ao carrinho ($qtd)");
WC()->cart->empty_cart();
$_GET = array();

cl_test_finish();

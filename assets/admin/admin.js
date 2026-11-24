/* Buy Again: pré-visualização ao vivo, destinos e seletores de cor da página de definições. */
jQuery(function ($) {
  var $form = $('.ba-admin form');
  var preview = document.getElementById('ba-preview');
  var exemploNome = 'Ana';
  var cssVar = {
    color_primary: '--ba-cor-principal',
    color_primary_hover: '--ba-cor-principal-hover',
    color_secondary: '--ba-cor-secundaria',
    color_text: '--ba-cor-texto'
  };

  function textoDe(key) {
    var el = $form.find('[data-ba-text="' + key + '"]').get(0);
    var valor = el ? $.trim(el.value) : '';
    if (!valor && el) valor = el.defaultValue;
    return key === 'modal_title' ? valor.split('{nome}').join(exemploNome) : valor;
  }

  function atualizarTextos() {
    $('[data-ba-preview]').each(function () {
      this.textContent = textoDe(this.getAttribute('data-ba-preview'));
    });
  }

  function atualizarInterruptores() {
    $form.find('[data-ba-switch]').each(function () {
      var key = this.getAttribute('data-ba-switch');
      $('[data-ba-when="' + key + '"]').prop('hidden', !this.checked);
      $('[data-ba-when-off="' + key + '"]').prop('hidden', this.checked);
    });
  }

  function atualizarDestinos() {
    $form.find('[data-ba-destination]').each(function () {
      var tipo = $(this).find('input[type="radio"]:checked').val() || 'home';
      $(this).find('[data-ba-show]').each(function () {
        $(this).toggle(this.getAttribute('data-ba-show') === tipo);
      });
    });
  }

  function pintar(key, cor) {
    if (preview && cssVar[key] && cor) preview.style.setProperty(cssVar[key], cor);
  }

  $('.ba-color-input').each(function () {
    var key = $(this).data('ba-color');
    $(this).wpColorPicker({
      change: function (event, ui) { pintar(key, ui.color.toString()); },
      clear: function () { pintar(key, $(this).closest('.wp-picker-container').find('.ba-color-input').data('default-color')); }
    });
  });

  $form.on('input change', '[data-ba-text]', atualizarTextos);
  $form.on('change', '[data-ba-switch]', atualizarInterruptores);
  $form.on('change', '[data-ba-destination] input[type="radio"]', atualizarDestinos);

  atualizarTextos();
  atualizarInterruptores();
  atualizarDestinos();
});

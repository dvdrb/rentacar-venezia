(function () {
  document.documentElement.classList.add('js');

  var toggle = document.querySelector('[data-menu-toggle]');
  var navigation = document.querySelector('[data-primary-navigation]');

  if (!toggle || !navigation) {
    return;
  }

  function closeMenu() {
    toggle.setAttribute('aria-expanded', 'false');
    navigation.classList.remove('is-open');
  }

  toggle.addEventListener('click', function () {
    var isOpen = toggle.getAttribute('aria-expanded') === 'true';
    toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    navigation.classList.toggle('is-open', !isOpen);
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && navigation.classList.contains('is-open')) {
      closeMenu();
      toggle.focus();
    }
  });
}());

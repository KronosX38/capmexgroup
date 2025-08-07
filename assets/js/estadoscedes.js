
  document.addEventListener('DOMContentLoaded', function () {
    const estadoscedes = document.querySelectorAll('[data-bs-toggle="popover"]');
    estadoscedes.forEach(el => {
      const contentId = el.getAttribute('data-popover-content');
      const contentElement = document.querySelector(contentId);
      if (contentElement) {
        new bootstrap.Popover(el, {
          html: true,
          content: contentElement.innerHTML,
          customClass: 'mipopover',
          sanitize: false
        });
      }
    });

    // Botón cerrar
    document.body.addEventListener('click', function (e) {
      if (e.target.classList.contains('cerrar-popover')) {
        document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
          const pop = bootstrap.Popover.getInstance(el);
          if (pop) pop.hide();
        });
      }
    });
  });
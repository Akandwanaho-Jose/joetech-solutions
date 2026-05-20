document.documentElement.classList.add('js');

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (!prefersReducedMotion) {
  const revealItems = document.querySelectorAll('[data-reveal]');

  if (revealItems.length && 'IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            revealObserver.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.14 }
    );

    revealItems.forEach((item, index) => {
      item.style.setProperty('--reveal-delay', `${Math.min(index * 45, 260)}ms`);
      revealObserver.observe(item);
    });
  } else {
    revealItems.forEach((item) => item.classList.add('is-visible'));
  }
} else {
  document.querySelectorAll('[data-reveal]').forEach((item) => item.classList.add('is-visible'));
}

document.querySelectorAll('[data-slider]').forEach((slider) => {
  const slides = Array.from(slider.querySelectorAll('.testimonial-slide'));
  const dots = Array.from(slider.querySelectorAll('[data-slide-to]'));

  if (!slides.length || !dots.length) {
    return;
  }

  let activeIndex = 0;
  let intervalId = null;

  const showSlide = (index) => {
    activeIndex = (index + slides.length) % slides.length;

    slides.forEach((slide, slideIndex) => {
      slide.classList.toggle('is-active', slideIndex === activeIndex);
    });

    dots.forEach((dot, dotIndex) => {
      dot.classList.toggle('is-active', dotIndex === activeIndex);
      dot.setAttribute('aria-pressed', dotIndex === activeIndex ? 'true' : 'false');
    });
  };

  const start = () => {
    if (prefersReducedMotion || slides.length < 2) {
      return;
    }

    stop();
    intervalId = window.setInterval(() => {
      showSlide(activeIndex + 1);
    }, 4800);
  };

  const stop = () => {
    if (intervalId !== null) {
      window.clearInterval(intervalId);
      intervalId = null;
    }
  };

  dots.forEach((dot) => {
    dot.addEventListener('click', () => {
      const next = Number(dot.getAttribute('data-slide-to'));
      showSlide(next);
      start();
    });
  });

  slider.addEventListener('mouseenter', stop);
  slider.addEventListener('mouseleave', start);
  slider.addEventListener('focusin', stop);
  slider.addEventListener('focusout', start);

  showSlide(0);
  start();
});

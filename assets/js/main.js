
(function() {
  "use strict";

  /**
   * Easy selector helper function
   */
  const select = (el, all = false) => {
    el = el.trim()
    if (all) {
      return [...document.querySelectorAll(el)]
    } else {
      return document.querySelector(el)
    }
  }

  /**
   * Easy event listener function
   */
  const on = (type, el, listener, all = false) => {
    let selectEl = select(el, all)
    if (selectEl) {
      if (all) {
        selectEl.forEach(e => e.addEventListener(type, listener))
      } else {
        selectEl.addEventListener(type, listener)
      }
    }
  }

  /**
   * Easy on scroll event listener 
   */
  const onscroll = (el, listener) => {
    el.addEventListener('scroll', listener)
  }

  /**
   * Back to top button
   */
  let backtotop = select('.back-to-top')
  if (backtotop) {
    const toggleBacktotop = () => {
      if (window.scrollY > 100) {
        backtotop.classList.add('active')
      } else {
        backtotop.classList.remove('active')
      }
    }
    window.addEventListener('load', toggleBacktotop)
    onscroll(document, toggleBacktotop)
  }

  /**
   * Mobile nav toggle
   */
  on('click', '.mobile-nav-toggle', function(e) {
    select('#navbar').classList.toggle('navbar-mobile')
    this.classList.toggle('bx-list-ul')
    this.classList.toggle('bx-x')
  })

/**
 * Mobile nav dropdowns activate
 */
on('click', '.navbar .dropdown > a', function(e) {

  if (!select('#navbar').classList.contains('navbar-mobile')) return;

  // Always prevent default on mobile for dropdown links
  e.preventDefault();
  e.stopPropagation();

  var next    = this.nextElementSibling; // <ul> or <div.aim_courses_panel>
  var chevron = this.querySelector('i[class*="chevron"]');

  if (!next) return;

  var isOpen = next.classList.contains('dropdown-active');

  // Close ALL open dropdowns first
  select('#navbar .dropdown-active', true).forEach(function(el) {
    el.classList.remove('dropdown-active');
  });
  select('#navbar i[class*="chevron"]', true).forEach(function(ic) {
    ic.style.transform = '';
  });

  // If it was closed, open it now
  if (!isOpen) {
    next.classList.add('dropdown-active');
    if (chevron) chevron.style.transform = 'rotate(180deg)';
  }

}, true)

  /**
   * Preloader
   */
  let preloader = select('#preloader');
  if (preloader) {
    window.addEventListener('load', () => {
      preloader.remove()
    });
  }

  /**
   * Testimonials slider
   */
  new Swiper('.testimonials-slider', {
    speed: 600,
    loop: true,
    autoplay: {
      delay: 2000,
      disableOnInteraction: false
    },
    slidesPerView: 'auto',
    pagination: {
      el: '.swiper-pagination',
      type: 'bullets',
      clickable: true
    },
    breakpoints: {
      320: {
        slidesPerView: 1,
        spaceBetween: 20
      },

      1200: {
        slidesPerView: 2,
        spaceBetween: 20
      }
    }
  });

  

  /**
   * Animation on scroll
   */
  // window.addEventListener('load', () => {
  //   AOS.init({
  //     duration: 100,
  //     easing: 'ease-in-out',
  //     once: true,
  //     mirror: false
  //   })
  // });

  /**
   * Initiate Pure Counter 
   */
  // new PureCounter();

})()


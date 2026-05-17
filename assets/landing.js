/* KyrosCounsel landing — animations + interactivity (graceful degradation) */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {

        // ============= Navbar shrink on scroll =============
        var nav = document.getElementById('kn-nav');
        if (nav) {
            var onScroll = function () {
                if (window.scrollY > 20) nav.classList.add('scrolled');
                else nav.classList.remove('scrolled');
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        }

        // ============= Auto-marcar elementos con data-anim como kn-reveal =============
        // El sistema de visibilidad NO depende de GSAP. Si GSAP carga, mejora la animación;
        // si no, IntersectionObserver hace fade-up. Si tampoco hay IO, todo se ve.
        document.querySelectorAll('[data-anim="fade-up"]').forEach(function (el) {
            el.classList.add('kn-reveal');
        });
        document.querySelectorAll('[data-anim="stagger"]').forEach(function (container) {
            Array.prototype.forEach.call(container.children, function (child, idx) {
                child.classList.add('kn-reveal');
                child.style.transitionDelay = (idx * 70) + 'ms';
            });
        });

        // ============= Reveal con IntersectionObserver =============
        var reveals = document.querySelectorAll('.kn-reveal');
        if (reveals.length && 'IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) {
                    if (e.isIntersecting) {
                        e.target.classList.add('is-visible');
                        io.unobserve(e.target);
                    }
                });
            }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
            reveals.forEach(function (el) { io.observe(el); });
        } else {
            // Sin IO: mostrar todo
            reveals.forEach(function (el) { el.classList.add('is-visible'); });
        }

        // ============= Bento card mouse-tracking glow =============
        document.querySelectorAll('.kn-bento-card').forEach(function (card) {
            card.addEventListener('mousemove', function (e) {
                var r = card.getBoundingClientRect();
                card.style.setProperty('--mx', ((e.clientX - r.left) / r.width * 100) + '%');
                card.style.setProperty('--my', ((e.clientY - r.top) / r.height * 100) + '%');
            });
        });

        // ============= Demo tabs =============
        document.querySelectorAll('[data-tabs]').forEach(function (group) {
            var tabs = group.querySelectorAll('[data-tab]');
            var panels = group.querySelectorAll('[data-panel]');
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var name = tab.getAttribute('data-tab');
                    tabs.forEach(function (t) { t.classList.toggle('active', t === tab); });
                    panels.forEach(function (p) {
                        p.classList.toggle('hidden', p.getAttribute('data-panel') !== name);
                    });
                });
            });
        });

        // ============= Pricing toggle (mensual/anual) =============
        var toggle = document.querySelector('[data-pricing-toggle]');
        if (toggle) {
            var btnMonthly = toggle.querySelector('[data-period="monthly"]');
            var btnYearly  = toggle.querySelector('[data-period="yearly"]');
            var pill       = toggle.querySelector('.kn-toggle-pill');

            function setPeriod(period) {
                var isYearly = period === 'yearly';
                btnMonthly.classList.toggle('active', !isYearly);
                btnYearly.classList.toggle('active', isYearly);
                var target = isYearly ? btnYearly : btnMonthly;
                pill.style.left   = target.offsetLeft + 'px';
                pill.style.width  = target.offsetWidth + 'px';

                document.querySelectorAll('[data-price]').forEach(function (el) {
                    var monthly = parseFloat(el.getAttribute('data-price'));
                    var shown   = isYearly ? Math.round(monthly * 0.8) : monthly;
                    el.textContent = '$' + shown;
                });
                document.querySelectorAll('[data-period-label]').forEach(function (el) {
                    el.textContent = isYearly ? '/mes (anual)' : '/mes';
                });
                document.querySelectorAll('[data-savings]').forEach(function (el) {
                    el.classList.toggle('hidden', !isYearly);
                });
            }
            btnMonthly.addEventListener('click', function () { setPeriod('monthly'); });
            btnYearly.addEventListener('click', function () { setPeriod('yearly'); });
            requestAnimationFrame(function () { setPeriod('monthly'); });
        }

        // ============= Number counter =============
        function animateCounter(el) {
            var target = parseFloat(el.getAttribute('data-counter'));
            var suffix = el.getAttribute('data-suffix') || '';
            var prefix = el.getAttribute('data-prefix') || '';
            var dur    = parseInt(el.getAttribute('data-duration') || '1600', 10);
            var start  = performance.now();
            var ease   = function (t) { return 1 - Math.pow(1 - t, 3); };

            function tick(now) {
                var p = Math.min(1, (now - start) / dur);
                var v = target * ease(p);
                var formatted = (target % 1 === 0) ? Math.round(v).toLocaleString('en-US') : v.toFixed(1);
                el.textContent = prefix + formatted + suffix;
                if (p < 1) requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        }
        var counters = document.querySelectorAll('[data-counter]');
        if (counters.length && 'IntersectionObserver' in window) {
            var cio = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) {
                    if (e.isIntersecting) {
                        animateCounter(e.target);
                        cio.unobserve(e.target);
                    }
                });
            }, { threshold: 0.5 });
            counters.forEach(function (el) { cio.observe(el); });
        }

        // ============= GSAP enhancements (opcional) =============
        function initGSAP(retries) {
            retries = retries || 0;
            if (!window.gsap) {
                if (retries < 30) return setTimeout(function () { initGSAP(retries + 1); }, 100);
                return; // sin GSAP, todo sigue funcionando con CSS+IO
            }
            var hasST = !!window.ScrollTrigger;
            if (hasST) gsap.registerPlugin(ScrollTrigger);

            // Workflow line scrubeada (solo con ScrollTrigger)
            if (hasST) {
                var workflowLine = document.querySelector('[data-workflow-line]');
                if (workflowLine) {
                    gsap.fromTo(workflowLine,
                        { scaleY: 0, transformOrigin: 'top' },
                        {
                            scaleY: 1, ease: 'none',
                            scrollTrigger: {
                                trigger: '[data-workflow]',
                                start: 'top 60%',
                                end: 'bottom 70%',
                                scrub: true
                            }
                        }
                    );
                }

                // Parallax muy sutil en mockup hero
                var mockup = document.querySelector('[data-hero-mockup]');
                if (mockup) {
                    gsap.to(mockup, {
                        y: -40, ease: 'none',
                        scrollTrigger: { trigger: mockup, start: 'top bottom', end: 'bottom top', scrub: true }
                    });
                }
            }
        }
        initGSAP();
    });
})();

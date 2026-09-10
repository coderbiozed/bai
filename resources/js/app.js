import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const coarsePointer = window.matchMedia('(pointer: coarse)').matches;

function initCrazyCursor() {
    if (reduceMotion || coarsePointer) return;

    const dot = document.createElement('div');
    const ring = document.createElement('div');
    dot.className = 'crazy-cursor';
    ring.className = 'crazy-cursor-ring';
    Object.assign(dot.style, {
        position: 'fixed',
        width: '12px',
        height: '12px',
        borderRadius: '9999px',
        background: '#c8f542',
        pointerEvents: 'none',
        zIndex: '9999',
        transform: 'translate(-50%, -50%)',
        mixBlendMode: 'difference',
        transition: 'width 0.15s ease, height 0.15s ease, background 0.15s ease',
    });
    Object.assign(ring.style, {
        position: 'fixed',
        width: '42px',
        height: '42px',
        borderRadius: '9999px',
        border: '2px solid #2de2e6',
        pointerEvents: 'none',
        zIndex: '9998',
        transform: 'translate(-50%, -50%)',
        transition: 'width 0.2s ease, height 0.2s ease, border-color 0.2s ease',
    });
    document.body.append(dot, ring);

    let x = window.innerWidth / 2;
    let y = window.innerHeight / 2;
    let rx = x;
    let ry = y;

    window.addEventListener('mousemove', (event) => {
        x = event.clientX;
        y = event.clientY;
        dot.style.left = `${x}px`;
        dot.style.top = `${y}px`;
    });

    const tick = () => {
        rx += (x - rx) * 0.18;
        ry += (y - ry) * 0.18;
        ring.style.left = `${rx}px`;
        ring.style.top = `${ry}px`;
        requestAnimationFrame(tick);
    };
    tick();

    document.querySelectorAll('a, button, .tilt-card, .interactive-row').forEach((el) => {
        el.addEventListener('mouseenter', () => {
            dot.style.width = '22px';
            dot.style.height = '22px';
            dot.style.background = '#ff2ebd';
            ring.style.width = '70px';
            ring.style.height = '70px';
            ring.style.borderColor = '#ffd23f';
        });
        el.addEventListener('mouseleave', () => {
            dot.style.width = '12px';
            dot.style.height = '12px';
            dot.style.background = '#c8f542';
            ring.style.width = '42px';
            ring.style.height = '42px';
            ring.style.borderColor = '#2de2e6';
        });
    });
}

function initTiltCards() {
    if (reduceMotion || coarsePointer) return;

    document.querySelectorAll('.tilt-card').forEach((card) => {
        card.addEventListener('mousemove', (event) => {
            const rect = card.getBoundingClientRect();
            const px = (event.clientX - rect.left) / rect.width;
            const py = (event.clientY - rect.top) / rect.height;
            const rotateY = (px - 0.5) * 14;
            const rotateX = (0.5 - py) * 14;
            card.style.transform = `perspective(900px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.03)`;
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(900px) rotateX(0deg) rotateY(0deg) scale(1)';
        });
    });
}

function initMagneticButtons() {
    if (reduceMotion || coarsePointer) return;

    document.querySelectorAll('.btn-primary, .btn-secondary, .btn-ghost, .btn, .btn-pop').forEach((btn) => {
        btn.addEventListener('mousemove', (event) => {
            const rect = btn.getBoundingClientRect();
            const dx = event.clientX - (rect.left + rect.width / 2);
            const dy = event.clientY - (rect.top + rect.height / 2);
            btn.style.transform = `translate(${dx * 0.18}px, ${dy * 0.18}px) scale(1.06)`;
        });
        btn.addEventListener('mouseleave', () => {
            btn.style.transform = '';
        });
    });
}

function initRevealOnScroll() {
    const nodes = document.querySelectorAll('[data-reveal]');
    if (!nodes.length) return;

    if (reduceMotion || !('IntersectionObserver' in window)) {
        nodes.forEach((node) => node.classList.add('is-revealed'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15 }
    );

    nodes.forEach((node) => observer.observe(node));
}

document.addEventListener('DOMContentLoaded', () => {
    initCrazyCursor();
    initTiltCards();
    initMagneticButtons();
    initRevealOnScroll();
});

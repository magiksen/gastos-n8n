const CLAVE_TEMA = 'tema';
const consultaOscuro = window.matchMedia('(prefers-color-scheme: dark)');

const preferenciaGuardada = () => localStorage.getItem(CLAVE_TEMA) ?? 'sistema';

const forzarRecalculoDeEstilos = (elemento) => window.getComputedStyle(elemento).opacity;

const aplicarTema = () => {
    const preferencia = preferenciaGuardada();
    const oscuro = preferencia === 'oscuro' || (preferencia === 'sistema' && consultaOscuro.matches);
    const raiz = document.documentElement;

    raiz.classList.add('cambiando-tema');
    raiz.classList.toggle('dark', oscuro);
    forzarRecalculoDeEstilos(raiz);
    requestAnimationFrame(() => raiz.classList.remove('cambiando-tema'));
};

export const registrarTema = (Alpine) => {
    consultaOscuro.addEventListener('change', aplicarTema);
    document.addEventListener('turbo:render', aplicarTema);

    Alpine.store('tema', {
        preferencia: preferenciaGuardada(),

        elegir(preferencia) {
            this.preferencia = preferencia;

            if (preferencia === 'sistema') {
                localStorage.removeItem(CLAVE_TEMA);
            } else {
                localStorage.setItem(CLAVE_TEMA, preferencia);
            }

            aplicarTema();
        },

        alternar() {
            this.elegir(document.documentElement.classList.contains('dark') ? 'claro' : 'oscuro');
        },
    });
};

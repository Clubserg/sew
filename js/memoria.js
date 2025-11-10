class Memoria {
    constructor() {
        
        this.tablero_bloqueado = true;
        this.primera_carta = null;
        this.segunda_carta = null;

        this.barajarCartas();
        this.tablero_bloqueado = false;
        this.cronometro = new Cronometro();
        this.cronometro.arrancar();
    }

    voltearCarta(carta) { 
        if (
            carta.getAttribute("data-state") === "revelada" || 
            carta.getAttribute("data-state") === "flip" ||    
            this.tablero_bloqueado === true
        ) {
            return; // No hacemos nada si alguna condición se cumple
        }

        carta.setAttribute("data-state", "flip");

        if (!this.primera_carta) {
            // Es la primera carta del turno
            this.primera_carta = carta;
            return;
        }

        // Es la segunda carta del turno
        this.segunda_carta = carta;
        this.tablero_bloqueado = true;
        this.comprobarPareja();
        
        
    }

    barajarCartas() {
        const contenedor = document.querySelector("main");

        const cartas = Array.from(contenedor.querySelectorAll("article"));

        // Algoritmo Fisher-Yates para barajar
        for (let i = cartas.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [cartas[i], cartas[j]] = [cartas[j], cartas[i]];
        }

        cartas.forEach(carta => contenedor.appendChild(carta));
    }

    reiniciarAtributos() {
        this.tablero_bloqueado = false; // ← antes estaba en true
        this.primera_carta = null;
        this.segunda_carta = null;
    }
    

    deshabilitarCartas() {
        if (this.primera_carta && this.segunda_carta) {
            this.primera_carta.setAttribute("data-state", "revelada");
            this.segunda_carta.setAttribute("data-state", "revelada");

            this.reiniciarAtributos();
            this.comprobarJuego();
        }
    }

    comprobarJuego() {
        const contenedor = document.querySelector("main");

        const cartas = Array.from(contenedor.querySelectorAll("article"));

        let todasReveladas = true;
        for (let carta of cartas) {
            if (carta.getAttribute("data-state") !== "revelada") {
                todasReveladas = false;
                break;
            }
        }
        if(todasReveladas)
            this.cronometro.parar();

    }

    cubrirCartas() {
        this.tablero_bloqueado = true;
        
        setTimeout(() => {
            if (this.primera_carta && this.segunda_carta) {
                this.primera_carta.removeAttribute("data-state");
                this.segunda_carta.removeAttribute("data-state");
            }

            // 3. Reiniciar atributos del turno
            this.reiniciarAtributos();
        }, 1500);


    }

    comprobarPareja() {
        const imagen1 = this.primera_carta.children[1]; 
        const imagen2 = this.segunda_carta.children[1];


        const sonIguales = imagen1.getAttribute("src") === imagen2.getAttribute("src");

        sonIguales ? this.deshabilitarCartas() : this.cubrirCartas();
        

    }
}

const juegoMemoria = new Memoria();

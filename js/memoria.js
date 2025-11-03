class Memoria {
    constructor() {
        // Constructor sin parámetros
        // Aquí se podrían inicializar propiedades del juego más adelante
        console.log("Clase Memoria inicializada correctamente.");
    }

    voltearCarta(carta) {
        // Añade el atributo data-estado="volteada" a la carta
        carta.setAttribute("data-state", "flip");
        console.log("Carta volteada:", carta);
    }
}

const juegoMemoria = new Memoria();
class Cronometro {
    constructor() {
        this.tiempo = 0.0;
        this.inicio = null;
        this.corriendo = null;
    }

    arrancar() {
        if(this.corriendo == null) {
            try{
                this.inicio = Temporal.Now.instant();
            } 
            catch {
                // Si Temporal no está disponible, usamos Date como alternativa
                this.inicio = new Date();
            }
    
            this.corriendo = setInterval(this.actualizar.bind(this), 100)
        }
        
    }

    actualizar() {
        let ahora;
        let diferencia;
    
        try {
            ahora = Temporal.Now.instant();
            diferencia = ahora.since(this.inicio, { largestUnit: "milliseconds" }).milliseconds;
        } catch {
            ahora = new Date();
            diferencia = ahora.getTime() - this.inicio.getTime();
        }
    
        this.tiempo = diferencia;
        this.mostrar(); // <- Asegúrate de mostrar el tiempo actualizado
    }
    

    mostrar() {
        let minutos = parseInt(this.tiempo / 60000);             // 1 min = 60000 ms
        let segundos = parseInt((this.tiempo % 60000) / 1000);   // resto en segundos
        let decimas = parseInt((this.tiempo % 1000) / 100);      // décimas (1 dígito)
    
        let minutosTexto = String(minutos).padStart(2, "0");
        let segundosTexto = String(segundos).padStart(2, "0");

        let tiempoFormateado = `${minutosTexto}:${segundosTexto}.${decimas}`;
        let parrafo = document.querySelector("main p")

        parrafo.textContent = tiempoFormateado;
    }

    parar() {
        if (this.corriendo) {
          clearInterval(this.corriendo);
          this.corriendo = null;
        }
      }

    reiniciar() {
        this.parar();
        this.tiempo = 0;
        this.inicio = null;
        this.mostrar();
    }
    
}
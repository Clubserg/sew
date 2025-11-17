class Carrusel {
    constructor(busqueda) {
        this.busqueda = busqueda;
        this.actual = 0;
        this.maximo = 4; // aunque pedimos 5 fotos, mostramos 4 en carrusel
        this.fotos = [];
        this.intervalo = null; // para guardar el temporizador
    }

    getFotografias() {
        const apiKey = "TU_API_KEY"; // Sustituye por tu clave de Flickr
        const url = "https://api.flickr.com/services/rest/";

        return $.ajax({
            url: url,
            method: "GET",
            dataType: "json",
            data: {
                method: "flickr.photos.search",
                api_key: apiKey,
                text: this.busqueda,
                per_page: 5,
                format: "json",
                nojsoncallback: 1,
                extras: "url_z"
            }
        });
    }

    procesarJSONFotografias(json) {
        if (!json || !json.photos || !json.photos.photo) {
            console.error("JSON inválido");
            return;
        }

        const fotosArray = json.photos.photo;
        this.fotos = [];

        for (let i = 0; i < fotosArray.length && i < 5; i++) {
            const foto = fotosArray[i];
            this.fotos.push({
                titulo: foto.title || "Sin título",
                url: foto.url_z || ""
            });
        }
    }

    /**
     * Devuelve el HTML inicial con la primera imagen y activa el temporizador.
     */
    mostrarFotografias() {
        if (this.fotos.length === 0) {
            return "<p>No hay fotos para mostrar</p>";
        }

        const primeraFoto = this.fotos[0];

        // HTML inicial con contenedor para la imagen
        let html = `
            <article id="carrusel">
                <h2>Imágenes del circuito de ${this.busqueda}</h2>
                ${primeraFoto.url}
            </article>
        `;

        // Activar temporizador para cambiar imagen cada 3 segundos
        this.intervalo = setInterval(this.cambiarFotografia.bind(this), 3000);

        return html;
    }

    /**
     * Cambia la imagen mostrada en el carrusel cada 3 segundos.
     */
    cambiarFotografia() {
        if (this.fotos.length === 0) return;

        // Avanzar al siguiente índice
        this.actual = (this.actual + 1) % this.fotos.length;

        const fotoActual = this.fotos[this.actual];

        // Actualizar la imagen en el DOM usando jQuery
        $("#foto-carrusel")
            .attr("src", fotoActual.url)
            .attr("alt", fotoActual.titulo);
    }
}
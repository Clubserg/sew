/**
 * Clase Noticias
 * Consume el servicio web de noticias para obtener información sobre MotoGP
 */
class Noticias {

    constructor() {
      this.apiKey = "NSItDBhTQkZk9TmAzhOGKHwqjm6frvzeVSDs5hyy";
      this.busqueda = "motoGP";
      
      this.url = "https://api.thenewsapi.com/v1/news/all?api_token=" + this.apiKey + "&search=" + this.busqueda + "&language=en&limit=3"

      this.noticias = [];
    }
  
    /**
     * TAREA 3: Obtener las noticias sobre MotoGP
     * Realiza una llamada al servicio web de noticias utilizando fetch()
     * @returns {Promise} Promesa que devuelve los datos en formato JSON
     */
    buscar() {
      return fetch(this.url)
        .then(response => {
          if (!response.ok) {
            throw new Error("Error en la petición: " + response.status);
          }
          return response.json();
        })
        .catch(error => {
          console.error("Error al buscar noticias:", error);
          throw error;
        });
    }
  
    /**
     * TAREA 4: Procesar la información del objeto JSON
     * Extrae las noticias del objeto JSON obtenido
     * @param {Object} data - Objeto JSON con las noticias
     */
    procesarInformacion(data) {
      if (data && data.data && Array.isArray(data.data)) {
        this.noticias = data.data.map(article => ({
          titular: article.title || "Sin título",
          entradilla: article.description || "Sin descripción",
          enlace: article.url || "#",
          fuente: article.source || "Fuente desconocida",
          imagen: article.image_url || null,
          fecha: article.published_at || null,
          autor: article.author || "Autor desconocido"
        }));
      } else {
        console.error("Formato de datos inesperado");
        this.noticias =    this.noticias = [];
      }
    }
  
    /**
     * TAREA 5: Crear elementos HTML con las noticias
     * Genera una sección con todas las noticias procesadas
     * @returns {jQuery} Elemento section con las noticias
     */
    mostrarNoticias() {
      const $section = $("<section></section>");
      $section.append("<h2>Noticias sobre MotoGP</h2>");
  
      if (this.noticias.length === 0) {
        $section.append("<p>No se encontraron noticias.</p>");
        return $section;
      }
  
      for (let i = 0; i < this.noticias.length; i++) {
        const noticia = this.noticias[i];
        const $article = $("<article></article>");
  
        // Titular
        const $titular = $("<h3></h3>").text(noticia.titular);
        $article.append($titular);
  
        // Fuente y fecha
        let metaInfo = "Fuente: " + noticia.fuente;
        if (noticia.fecha) {
          const fecha = new Date(noticia.fecha);
          const fechaFormateada = fecha.toLocaleDateString("es-ES", {
            year: "numeric",
            month: "long",
            day: "numeric"
          });
          metaInfo += " | " + fechaFormateada;
        }
        const $meta = $("<p></p>").html("<em>" + metaInfo + "</em>");
        $article.append($meta);
  
        // Entradilla
        const $entradilla = $("<p></p>").text(noticia.entradilla);
        $article.append($entradilla);
  
        // Enlace
        const $enlace = $("<p></p>");
        const $link = $("<a></a>")
          .attr("href", noticia.enlace)
          .attr("target", "_blank")
          .attr("rel", "noopener noreferrer")
          .text("Leer noticia completa");
        $enlace.append($link);
        $article.append($enlace);
  
        $section.append($article);
      }
  
      return $section;
    }
  
  
}
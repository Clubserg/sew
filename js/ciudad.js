
/**
 * Clase Ciudad
 * Representa: nombre, país, gentilicio, población y coordenadas (latitud, longitud)
 */
class Ciudad {

  constructor(nombre, pais, gentilicio) {
    this.nombre = String(nombre);
    this.pais = String(pais);
    this.gentilicio = String(gentilicio);

    // Atributos que se completan después
    this.poblacion = null; // número entero o null
    this.coordenadas = { lat: null, lon: null }; // {lat: number, lon: number}
  }

  establecerSecundarios(poblacion, lat, lon) {
    // Validaciones sencillas
    const pob = Number(poblacion);
    const la = Number(lat);
    const lo = Number(lon);

    this.poblacion = Number.isFinite(pob) && pob >= 0 ? Math.trunc(pob) : null;

    this.coordenadas = {
      lat: Number.isFinite(la) ? la : null,
      lon: Number.isFinite(lo) ? lo : null,
    };
  }

  /** Devuelve, en forma de texto, el nombre de la ciudad. */
  getNombre() {
    return this.nombre;
  }

  /** Devuelve, en forma de texto, el nombre del país. */
  getPais() {
    return this.pais;
  }

  /**
   * Devuelve la información secundaria (gentilicio y población)
   * con la estructura de una lista no ordenada HTML5 dentro de una cadena.
   */
  infoSecundariaHTML() {
    const pobTxt =
      this.poblacion !== null ? this.poblacion.toLocaleString("es-ES") : "—";
    const gentTxt = this.gentilicio || "—";

    return `
        <ul>
        <li><strong>Gentilicio:</strong> ${gentTxt}</li>
        <li><strong>Población:</strong> ${pobTxt}</li>
        </ul>`.trim();
  }

  /**
   * Escribe en el documento la información de las coordenadas
   * del punto elegido utilizando document.write()
   * (Se recomienda invocar este método durante el parseo del documento).
   */
  escribirCoordenadas() {
    const { lat, lon } = this.coordenadas;
    let contenido;

    if (lat === null || lon === null) {
      contenido = `<p><em>Coordenadas no disponibles.</em></p>`;
    } else {
      // Muestra las coordenadas con 4 decimales
      const latTxt = lat.toFixed(4);
      const lonTxt = lon.toFixed(4);
      contenido = `<p><strong>Coordenadas del centro:</strong> ${latTxt}°, ${lonTxt}°</p>`;
    }

    // Requisito: uso de document.write()
    document.body.insertAdjacentHTML("beforeend", contenido);
  }
}
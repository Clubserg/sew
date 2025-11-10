
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

  getNombre() {
    return this.nombre;
  }

  getPais() {
    return this.pais;
  }


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

    document.body.insertAdjacentHTML("beforeend", contenido);
  }
}
import xml.etree.ElementTree as ET
import os

# Archivos de entrada/salida
XML_CANDIDATES = ["circuitoEsquema.xml", "circuito.xml"]
SVG_OUT = "altimetria.svg"

# Namespace del XML
NS = {"ns": "http://www.uniovi.es"}

def find_existing_xml():
    """Busca el archivo XML del circuito"""
    for fname in XML_CANDIDATES:
        if os.path.exists(fname):
            return fname
    raise FileNotFoundError("No se encontró 'circuitoEsquema.xml' ni 'circuito.xml'")

def read_altimetry_data(xml_path):
    """
    Lee las distancias y altitudes del archivo XML usando XPath.
    Retorna una lista de tuplas (distancia_acumulada, altitud)
    """
    tree = ET.parse(xml_path)
    root = tree.getroot()
    
    points = []
    
    # Punto de origen del circuito
    po = root.find("ns:punto_origen", NS)
    if po is not None:
        altitud_elem = po.find("ns:altitud_punto", NS)
        if altitud_elem is not None:
            altitud = float(altitud_elem.text)
            points.append((0.0, altitud))
    
    # Recorrer todos los tramos
    tramos = root.find("ns:tramos", NS)
    if tramos is not None:
        for tramo in tramos.findall("ns:tramo", NS):
            # Obtener la distancia acumulada del tramo (el valor en <distancia>)
            distancia_elem = tramo.find("ns:distancia", NS)
            if distancia_elem is not None:
                distancia_acumulada = float(distancia_elem.text)
            else:
                continue
            
            # Obtener la altitud del punto de origen del tramo
            p = tramo.find("ns:punto_origen", NS)
            if p is not None:
                altitud_elem = p.find("ns:altitud_punto", NS)
                if altitud_elem is not None:
                    altitud = float(altitud_elem.text)
                    points.append((distancia_acumulada, altitud))
    
    return points

class Svg:
    """Clase para generar archivos SVG"""
    
    def __init__(self, width, height):
        self.width = width
        self.height = height
        self.content = []
    
    def add_header(self):
        """Genera el encabezado del archivo SVG"""
        self.content.append('<?xml version="1.0" encoding="UTF-8"?>')
        self.content.append(f'<svg xmlns="http://www.w3.org/2000/svg" width="{self.width}" height="{self.height}" viewBox="0 0 {self.width} {self.height}">')
        self.content.append('  <title>Altimetría del Circuito</title>')
        self.content.append('  <desc>Perfil altimétrico del circuito MotoGP</desc>')
    
    def add_polyline(self, points, stroke="blue", stroke_width=2, fill="lightblue"):
        """Añade una polilínea al SVG"""
        points_str = " ".join([f"{x},{y}" for x, y in points])
        self.content.append(f'  <polyline points="{points_str}" ')
        self.content.append(f'            stroke="{stroke}" stroke-width="{stroke_width}" fill="{fill}"/>')
    
    def add_line(self, x1, y1, x2, y2, stroke="black", stroke_width=1):
        """Añade una línea al SVG"""
        self.content.append(f'  <line x1="{x1}" y1="{y1}" x2="{x2}" y2="{y2}" ')
        self.content.append(f'        stroke="{stroke}" stroke-width="{stroke_width}"/>')
    
    def add_text(self, x, y, text, font_size=12, anchor="middle", transform=None):
        """Añade texto al SVG"""
        transform_attr = f' transform="{transform}"' if transform else ''
        self.content.append(f'  <text x="{x}" y="{y}" font-size="{font_size}" text-anchor="{anchor}"{transform_attr}>')
        self.content.append(f'    {text}')
        self.content.append('  </text>')
    
    def add_footer(self):
        """Genera el cierre del archivo SVG"""
        self.content.append('</svg>')
    
    def write_to_file(self, filename):
        """Escribe el contenido SVG a un archivo"""
        with open(filename, 'w', encoding='utf-8') as f:
            f.write('\n'.join(self.content))

def create_altimetry_svg(points, output_path):
    """
    Crea el archivo SVG con el perfil altimétrico
    """
    if len(points) < 2:
        raise ValueError("Se necesitan al menos 2 puntos para crear el perfil")
    
    # Dimensiones del SVG
    svg_width = 1200
    svg_height = 400
    margin = 40
    
    # Área de dibujo
    draw_width = svg_width - 2 * margin
    draw_height = svg_height - 2 * margin
    
    # Encontrar rangos de datos
    distancias = [p[0] for p in points]
    altitudes = [p[1] for p in points]
    
    dist_min, dist_max = min(distancias), max(distancias)
    alt_min, alt_max = min(altitudes), max(altitudes)
    
    # Manejar casos donde todos los valores son iguales
    if dist_max == dist_min:
        dist_min -= 1.0
        dist_max += 1.0
    
    if alt_max == alt_min:
        alt_min -= 10.0
        alt_max += 10.0
    
    # Añadir margen vertical para mejor visualización
    alt_range = alt_max - alt_min
    alt_min -= alt_range * 0.1
    alt_max += alt_range * 0.1
    
    # Crear objeto SVG
    svg = Svg(svg_width, svg_height)
    svg.add_header()
    
    # Función para escalar coordenadas
    def scale_point(dist, alt):
        x = margin + (dist - dist_min) / (dist_max - dist_min) * draw_width
        y = svg_height - margin - (alt - alt_min) / (alt_max - alt_min) * draw_height
        return (x, y)
    
    # Convertir puntos a coordenadas SVG
    svg_points = [scale_point(d, a) for d, a in points]
    
    # Dibujar polilínea roja
    svg.add_polyline(svg_points, stroke="red", stroke_width=2, fill="none")
    
    # Dibujar solo los ejes básicos
    # Eje Y (izquierda)
    svg.add_line(margin, margin, margin, svg_height - margin, 
                 stroke="black", stroke_width=2)
    # Eje X (abajo)
    svg.add_line(margin, svg_height - margin, 
                 svg_width - margin, svg_height - margin, 
                 stroke="black", stroke_width=2)
    
    svg.add_footer()
    svg.write_to_file(output_path)

if __name__ == "__main__":
    try:
        # Leer archivo XML
        xml_path = find_existing_xml()
        print(f"Leyendo archivo: {xml_path}")
        
        # Extraer datos de altimetría usando XPath
        points = read_altimetry_data(xml_path)
        print(f"Puntos extraídos: {len(points)}")
        
        if len(points) < 2:
            raise ValueError("No hay suficientes puntos para crear el perfil altimétrico")
        
        # Generar archivo SVG
        create_altimetry_svg(points, SVG_OUT)
        print(f"✓ SVG generado: {SVG_OUT}")
        
    except Exception as e:
        print(f"Error: {e}")
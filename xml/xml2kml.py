import xml.etree.ElementTree as ET
import os

# Archivos de entrada/salida
XML_CANDIDATES = ["circuitoEsquema.xml", "circuito.xml"]
KML_OUT = "circuito.kml"

# Namespace del XML
NS = {"ns": "http://www.uniovi.es"}

def find_existing_xml():
    for fname in XML_CANDIDATES:
        if os.path.exists(fname):
            return fname
    raise FileNotFoundError("No se encontró 'circuitoEsquema.xml' ni 'circuito.xml'")

def read_points_from_xml(xml_path):
    tree = ET.parse(xml_path)
    root = tree.getroot()
    points = []

    # punto_origen del circuito
    po = root.find("ns:punto_origen", NS)
    if po is not None:
        lon = po.find("ns:longitud_punto", NS).text
        lat = po.find("ns:latitud_punto", NS).text
        points.append((lon, lat))

    # puntos origen de cada tramo
    tramos = root.find("ns:tramos", NS)
    if tramos is not None:
        for tramo in tramos.findall("ns:tramo", NS):
            p = tramo.find("ns:punto_origen", NS)
            if p is not None:
                lon = p.find("ns:longitud_punto", NS).text
                lat = p.find("ns:latitud_punto", NS).text
                points.append((lon, lat))

    return points

def write_kml(points, kml_path):
    # Prólogo
    kml_content = """<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
  <Document>
    <name>Circuito MotoGP</name>
    <Placemark>
      <name>Trazado del Circuito</name>
      <LineString>
        <extrude>1</extrude>
        <tessellate>1</tessellate>
        <coordinates>
"""
    
    # Coordenadas
    for lon, lat in points:
        kml_content += f"          {lon},{lat},0\n"
    
    # Epílogo
    kml_content += """        </coordinates>
      </LineString>
    </Placemark>
  </Document>
</kml>
"""
    
    with open(kml_path, "w", encoding="utf-8") as f:
        f.write(kml_content)

if __name__ == "__main__":
    xml_path = find_existing_xml()
    points = read_points_from_xml(xml_path)
    write_kml(points, KML_OUT)
    print(f"✓ KML generado: {KML_OUT}")
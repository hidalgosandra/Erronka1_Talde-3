import os
import zipfile
from datetime import datetime, timezone
from xml.sax.saxutils import escape

out_path = r"C:\Users\Ander\Documents\GitHub\Erronka1_Talde-3\Erronka1_Talde-3_Informe_Eskolak.docx"
sections = [
    ("Title", "Informe académico: Eskolak"),
    ("Heading1", "Resumen"),
    ("Normal", "Eskolak es una plataforma digital orientada a la educación y al aprendizaje continuo, diseñada para ofrecer un entorno accesible, moderno y funcional donde los usuarios puedan formarse, explorar contenidos educativos y desarrollar nuevas competencias. La empresa nace con la intención de transformar la experiencia de aprendizaje mediante la integración de tecnologías digitales, un diseño intuitivo y una propuesta educativa centrada en la participación, la accesibilidad y la mejora continua."),
    ("Heading1", "1. Introducción"),
    ("Normal", "Desde una perspectiva académica, Eskolak puede entenderse como una iniciativa de innovación educativa vinculada al uso de herramientas digitales para facilitar el aprendizaje en distintos contextos. La plataforma permite a los usuarios acceder a cursos, inscribirse en actividades formativas, gestionar su perfil y avanzar en su proceso de aprendizaje de manera autónoma y organizada. Este enfoque responde a la creciente demanda de soluciones educativas flexibles que se adapten a las necesidades de una sociedad cada vez más digitalizada."),
    ("Heading1", "2. Descripción de la empresa"),
    ("Normal", "La propuesta de Eskolak se fundamenta en la idea de que el aprendizaje debe ser un proceso dinámico, inclusivo y motivador. Para ello, la empresa combina contenido formativo, diseño centrado en el usuario y tecnología con el fin de generar una experiencia educativa atractiva y útil. La plataforma no solo ofrece recursos de aprendizaje, sino que también favorece la participación activa, la curiosidad intelectual y la adquisición de conocimientos en un entorno estructurado y accesible."),
    ("Heading1", "3. Objetivos y valor propuesto"),
    ("Normal", "Entre sus objetivos principales destacan la democratización del conocimiento, la mejora de la accesibilidad a la formación y la promoción de una comunidad de aprendizaje activa. Eskolak busca ofrecer un espacio en el que las personas puedan desarrollar habilidades, ampliar su formación y adquirir competencias relevantes para su crecimiento personal y profesional. Asimismo, la empresa apuesta por una educación más cercana, adaptable y eficiente, en consonancia con las tendencias actuales del sector educativo."),
    ("Heading1", "4. Modelo de negocio y contexto educativo"),
    ("Normal", "Desde un punto de vista empresarial, Eskolak representa una solución con un alto potencial en el ámbito de la educación digital. Su modelo combina innovación, tecnología y pedagogía para crear un servicio que responde a las exigencias del entorno actual y a la necesidad de herramientas de aprendizaje cada vez más interactivas y personalizadas. La plataforma tiene capacidad para adaptarse a distintos perfiles de usuarios, ofrecer experiencias de formación de calidad y contribuir al desarrollo de competencias clave en un contexto académico y profesional cambiante."),
    ("Heading1", "Conclusión"),
    ("Normal", "En conclusión, Eskolak puede definirse como una empresa enfocada en la innovación educativa y en la digitalización del aprendizaje. Su valor radica en la creación de un espacio formativo accesible, atractivo y funcional, capaz de acompañar a los usuarios en su proceso de aprendizaje y de contribuir al desarrollo de una sociedad más preparada, conectada y competente. La empresa representa una propuesta sólida dentro del ámbito de la educación digital, con una clara orientación hacia la mejora de la formación y la accesibilidad al conocimiento."),
]

paragraphs = []
for style, content in sections:
    text = content.strip()
    if not text:
        continue
    p_style = ''
    if style == 'Title':
        p_style = '<w:pPr><w:pStyle w:val="Title"/></w:pPr>'
    elif style == 'Heading1':
        p_style = '<w:pPr><w:pStyle w:val="Heading1"/></w:pPr>'
    else:
        p_style = '<w:pPr><w:pStyle w:val="Normal"/></w:pPr>'
    paragraphs.append(
        f'<w:p>{p_style}<w:r><w:t xml:space="preserve">{escape(text)}</w:t></w:r></w:p>'
    )

body_xml = ''.join(paragraphs)
document_xml = f'''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <w:body>
    {body_xml}
    <w:sectPr>
      <w:pgSz w:w="12240" w:h="15840"/>
      <w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/>
    </w:sectPr>
  </w:body>
</w:document>
'''

styles_xml = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:style w:type="paragraph" w:default="1" w:styleId="Normal">
    <w:name w:val="Normal"/>
    <w:qFormat/>
    <w:rPr>
      <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
      <w:sz w:val="22"/>
    </w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Title">
    <w:name w:val="Title"/>
    <w:basedOn w:val="Normal"/>
    <w:qFormat/>
    <w:rPr>
      <w:b/>
      <w:sz w:val="32"/>
      <w:szCs w:val="32"/>
    </w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading1">
    <w:name w:val="heading 1"/>
    <w:basedOn w:val="Normal"/>
    <w:qFormat/>
    <w:rPr>
      <w:b/>
      <w:sz w:val="28"/>
      <w:szCs w:val="28"/>
    </w:rPr>
  </w:style>
</w:styles>
'''

content_types = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
'''

rels = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
'''

created = datetime.now(timezone.utc).strftime('%Y-%m-%dT%H:%M:%SZ')
core = f'''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:title>Erronka1_Talde-3_Informe_Eskolak</dc:title>
  <dc:creator>GitHub Copilot</dc:creator>
  <cp:lastModifiedBy>GitHub Copilot</cp:lastModifiedBy>
  <dcterms:created xsi:type="dcterms:W3CDTF">{created}</dcterms:created>
  <dcterms:modified xsi:type="dcterms:W3CDTF">{created}</dcterms:modified>
</cp:coreProperties>
'''

app = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>Microsoft Office Word</Application>
</Properties>
'''

os.makedirs(os.path.dirname(out_path), exist_ok=True)
with zipfile.ZipFile(out_path, 'w', zipfile.ZIP_DEFLATED) as zf:
    zf.writestr('[Content_Types].xml', content_types)
    zf.writestr('_rels/.rels', rels)
    zf.writestr('docProps/core.xml', core)
    zf.writestr('docProps/app.xml', app)
    zf.writestr('word/document.xml', document_xml)
    zf.writestr('word/styles.xml', styles_xml)

print(f"Generated: {out_path}")
print(f"Size: {os.path.getsize(out_path)} bytes")

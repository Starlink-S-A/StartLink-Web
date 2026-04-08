import zipfile
import xml.etree.ElementTree as ET

def get_docx_text(path):
    try:
        with zipfile.ZipFile(path) as docx:
            tree = ET.XML(docx.read('word/document.xml'))
            text = []
            for p in tree.iter():
                if p.tag == '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}t':
                    if p.text:
                        text.append(p.text)
            return '\n'.join(text) # use newline
    except Exception as e:
        return str(e)

if __name__ == '__main__':
    content = get_docx_text(r"c:\xampp\htdocs\StartLink-Web\StartLink-Historias de Usuario.docx")
    with open(r"c:\xampp\htdocs\StartLink-Web\docx_content.md", 'w', encoding='utf-8') as f:
        f.write(content)

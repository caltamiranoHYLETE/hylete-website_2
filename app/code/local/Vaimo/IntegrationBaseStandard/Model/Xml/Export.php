<?php
class Vaimo_IntegrationBaseStandard_Model_Xml_Export
{
    private $_level;
    private $_xml;
    
    public function __construct()
    {
        $this->_level = -1;
        $this->_xml = '';
        $this->AddXMLBlockStart('?xml version="1.0" encoding="UTF-8"?');
    }

    private function AddIndentation()
    {
        for ($i=0; $i<$this->_level; $i++) {
            $this->_xml = $this->_xml . "\t";
        }
    }
    
    public function AddXMLBlockStart($tag, $attr = NULL,$value = NULL)
    {
        $this->AddIndentation();
        $this->_xml = $this->_xml . '<' . $tag;
        if ($attr && $value) {
            if ($value!="") {
                $this->_xml = $this->_xml . ' ' . $attr . '="' . $value . '"';
            }
        }
        $this->_xml = $this->_xml . '>' . "\r\n";
        $this->_level++;
    }
    
    public function AddXMLBlockValue($attr,$value)
    {
        $t = $value;
        $t = str_replace("&","&amp;",$t);
        $t = str_replace("<","&lt;",$t);
        $t = str_replace(">","&gt;",$t);
        $t = str_replace("'","&apos;",$t);
        $t = str_replace('"',"&quot;",$t);
        $this->AddIndentation();
        $this->_xml = $this->_xml . '<' . $attr . '>' . $t . '</' . $attr . '>' . "\r\n";
    }

    // Could have been done with recursion, but some data is cyclic, so rather have these two levels, which is quite enough for now
    public function AddXMLBlockDataExpand($data,$include_zero,$include_blank)
    {
        foreach ($data as $option_label => $product_option) {
            $this->AddXMLBlockStart($option_label);
            $this->AddXMLBlockData($product_option,$include_zero,$include_blank);
            foreach ($product_option as $option_label_two => $product_option_two) {
                if (is_array($product_option_two)) {
                    if (is_int($option_label_two)) $option_label_two = $option_label . '_' . $option_label_two;
                    $this->AddXMLBlockStart($option_label_two);
                    $this->AddXMLBlockData($product_option_two,$include_zero,$include_blank);
                    $this->AddXMLBlockEnd($option_label_two);
                }
            }
            $this->AddXMLBlockEnd($option_label);
        }
    }

    public function AddXMLBlockData($data, $include_zeros, $include_blank)
    {
        foreach ($data as $field => $val) {
            $export = false;
            if ($val) $export = true;
            if ($val==="0.0000" && !$include_zeros) $export = false;
            if ($val==="0") $export = true;
            if ($val==="" && $include_blank) $export = true;
            if ($val===null && $include_blank) {
                $val = 'NULL';
                $export = true;
            }
            if (is_array($val) || is_object($val)) $export = false;
            if ($export) {
                $this->AddXMLBlockValue($field, $val);
            }
        }
    }
    
    public function AddXMLBlockEnd($tag)
    {
        $this->_level--;
        $this->AddIndentation();
        $this->_xml = $this->_xml . '</' . $tag . '>' . "\r\n";
    }
    
    public function startElement($parser, $name, $attrs)
    {
        $this->xml_line++;
        $this->xmlTag = $name;
        $this->xmlCurrentAttributes = $attrs;
    }

    public function characterData($parser, $data)
    {
        switch ($this->xmlTag) {
            case 'VAL': $this->xml_answer[$this->xmlCurrentAttributes['N']] = $data; break;
        }
    }
    
    public function endElement($parser, $name)
    {
        $this->xmlTag = "";
        $this->xmlCurrentAttributes = "";
    }
    
    public function exportXml($path,$filename)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        if (substr($path,-1,1)!='/') $path .= '/';
		$f = fopen($path . $filename, "w" );
		fwrite($f, $this->_xml);
		fclose($f);
    }
    
}

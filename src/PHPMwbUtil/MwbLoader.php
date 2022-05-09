<?php

namespace PHPMwbUtil;

class MwbLoader
{
    static private $classMap = [
        'workbench.physical.Model' => MwbModel::class,
        'db.mysql.Catalog' => MwbCatalog::class,
        'db.mysql.Schema' => MwbSchema::class,
        'db.mysql.Table' => MwbTable::class,
        'db.mysql.Column' => MwbColumn::class,
        'db.mysql.Index' => MwbIndex::class,
        'db.mysql.IndexColumn' => MwbIndexColumn::class,
        'db.mysql.ForeignKey' => MwbForeignKey::class,
    ];

    public $document;
    public $data;

    public function load(string $filename)
    {
        $zipArchive = new \ZipArchive();
        $zipArchive->open($filename);

        // load xml from mwb file
        $stream = $zipArchive->getStream('document.mwb.xml');
        if (!$stream) {
            throw new \RuntimeException('Invalid mwb file: document.mwb.xml not found');
        }
        $xml = stream_get_contents($stream);
        fclose($stream);

        // build MwbModel from xml
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($xml);
        $path = new \DOMXPath($domDocument);
        $entries = $path->query('//data/value');
        if ($entries->length != 1) {
            throw new \RuntimeException('Invalid mwb file');
        }
        $this->document = new MwbDocument();
        $this->mapObject($entries[0], $this->document);

        // load table data
        $stream = $zipArchive->getStream('@db/data.db');
        if (!$stream) {
            throw new \RuntimeException('Invalid mwb file: @db/data.db not found');
        }
        $this->data = new MwbData();
        $this->data->load(stream_get_contents($stream));
        fclose($stream);

        $zipArchive->close();

        // check mwb file is simple
        if (count($this->document->physicalModels) != 1) {
            throw new \RuntimeException('Unsupported mwb file: file must contains only one model');
        }
        if (!isset($this->document->physicalModels[0]->catalog)) {
            throw new \RuntimeException('Unsupported mwb file: catalog not found');
        }
        if (count($this->document->physicalModels[0]->catalog->schemata) != 1) {
            throw new \RuntimeException('Unsupported mwb file: file must contains only one schema');
        }
    }

    static private function nodeAttr(\DOMElement $node, string $attributeName)
    {
        if (!$node->hasAttribute($attributeName)) {
            throw new \RuntimeException('mwb error: value.' . $attributeName . ': ' . $node->getLineNo());
        }
        return $node->getAttribute($attributeName);
    }

    static private function deserializeNode(\DOMElement $node)
    {
        if ($node->nodeName == 'value') {
            $type = MwbLoader::nodeAttr($node, 'type');
            if ($type == 'list') {
                return MwbLoader::deserializeList($node);
            } else if ($type == 'object') {
                return MwbLoader::deserializeObject($node);
            } else {
                return $node->textContent;
            }
        } else if ($node->nodeName == 'link') {
            return $node->textContent;
        } else {
            return null;
        }
    }

    static private function deserializeList(\DOMElement $node)
    {
        $obj = [];
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof \DOMElement) {
                if ($childNode->nodeName == 'value' || $childNode->nodeName == 'link') {
                    if (($childObj = MwbLoader::deserializeNode($childNode)) !== null) {
                        $obj[] = $childObj;
                    }
                }
            }
        }
        return $obj;
    }

    static private function deserializeObject(\DOMElement $node)
    {
        $structName = MwbLoader::nodeAttr($node, 'struct-name');
        if (isset(MwbLoader::$classMap[$structName])) {
            $obj = new MwbLoader::$classMap[$structName];
            MwbLoader::mapObject($node, $obj);
            return $obj;
        } else {
            return new \stdClass();
        }
    }

    static private function mapObject(\DOMElement $node, object $obj)
    {
        $class = new \ReflectionClass(get_class($obj));
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof \DOMElement) {
                $key = MwbLoader::nodeAttr($childNode, 'key');
                if ($class->hasProperty($key)) {
                    if (($childObj = MwbLoader::deserializeNode($childNode)) !== null) {
                        $class->getProperty($key)->setValue($obj, $childObj);
                    }
                }
            }
        }
        if ($node->hasAttribute('id')) {
            $id = $node->getAttribute('id');
            if ($class->hasProperty('id')) {
                $obj->id = $id;
            }
        }
        return $obj;
    }
}
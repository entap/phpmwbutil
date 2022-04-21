<?php

namespace PHPMwbUtil;

class MwbDocument
{
    /**
     * struct-nameからクラスへのマップ
     */
    private $classMap = [
        'workbench.physical.Model' => MwbModel::class,
        'db.mysql.Catalog' => MwbCatalog::class,
        'db.mysql.Schema' => MwbSchema::class,
        'db.mysql.Table' => MwbTable::class,
        'db.mysql.Column' => MwbColumn::class,
        'db.mysql.Index' => MwbIndex::class,
        'db.mysql.IndexColumn' => MwbIndexColumn::class,
        'db.mysql.ForeignKey' => MwbForeignKey::class,
    ];

    /**
     * idからオブジェクトへのマップ
     */
    private $idMap = [];

    public $physicalModels;
    public $data;

    /**
     * mwbファイルを読み込む
     *
     * @param $filename
     */
    public function loadFile(string $filename)
    {
        $zipArchive = new \ZipArchive();
        $zipArchive->open($filename);

        // mwbファイルからXMLを読み込む
        $stream = $zipArchive->getStream('document.mwb.xml');
        if (!$stream) {
            throw new \RuntimeException('Invalid mwb file');
        }
        $xml = stream_get_contents($stream);
        fclose($stream);

        // XMLからMwbModelモデルを構築する
        $document = new \DOMDocument();
        $document->loadXML($xml);
        $this->loadXML($document);

        // 各テーブルの初期値データを読み込む
        $stream = $zipArchive->getStream('@db/data.db');
        if (!$stream) {
            throw new \RuntimeException('Invalid mwb file');
        }
        $this->data = new MwbData();
        $this->data->load(stream_get_contents($stream));
        fclose($stream);

        $zipArchive->close();
    }

    /**
     * mwbファイルのDOMDocumentを読み込む
     *
     * @param \DOMDocument $document
     * @return void
     */
    public function loadXML(\DOMDocument $document)
    {
        $path = new \DOMXPath($document);
        $entries = $path->query('//data/value');
        if ($entries->length != 1) {
            throw new \RuntimeException('Invalid mwb file');
        }
        $this->mapObject($entries[0], $this);
        //$this->resolveLinks($this);
    }

    /**
     * IDを指定してオブジェクトを取得する
     *
     * @param $id
     * @return mixed
     */
    public function getById(string $id)
    {
        return isset($this->idMap[$id]) ? $this->idMap[$id] : $id;
    }

    /**
     * 指定したDOM要素の属性値を取得する。属性が存在しなければ例外を発生させる。
     *
     * @param \DOMElement $node
     * @param $attributeName
     * @return string
     */
    private function nodeAttr(\DOMElement $node, string $attributeName)
    {
        if (!$node->hasAttribute($attributeName)) {
            throw new \RuntimeException('mwb error: value.' . $attributeName . ': ' . $node->getLineNo());
        }
        return $node->getAttribute($attributeName);
    }

    /**
     * ノードを読み込んで、適切な値を返す
     *
     * @param \DOMElement $node
     * @return mixed
     */
    private function deserializeNode(\DOMElement $node)
    {
        if ($node->nodeName == 'value') {
            $type = $this->nodeAttr($node, 'type');
            if ($type == 'list') {
                return $this->deserializeList($node);
            } else if ($type == 'object') {
                return $this->deserializeObject($node);
            } else {
                return $node->textContent;
            }
        } else if ($node->nodeName == 'link') {
            return new MwbLink($this, $node->textContent);
        } else {
            return null;
        }
    }

    /**
     * <value type="list">ノードを読み込んで、配列を返す
     *
     * @param \DOMElement $node
     * @return array
     */
    private function deserializeList(\DOMElement $node)
    {
        $obj = [];
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof \DOMElement) {
                if ($childNode->nodeName == 'value') {
                    if (($childObj = $this->deserializeNode($childNode)) !== null) {
                        $obj[] = $childObj;
                    }
                }
            }
        }
        return $obj;
    }

    /**
     * <value type="object">ノードを読み込んで、オブジェクトを返す
     *
     * @param \DOMElement $node
     * @return object
     */
    private function deserializeObject(\DOMElement $node)
    {
        $structName = $this->nodeAttr($node, 'struct-name');
        if (isset($this->classMap[$structName])) {
            $obj = new $this->classMap[$structName];
            $this->mapObject($node, $obj);
            return $obj;
        } else {
            return new \stdClass();
        }
    }

    /**
     * <value type="object">ノードを、指定されたオブジェクトに読み込む
     *
     * @param \DOMElement $node
     * @param object $obj
     * @return object
     */
    private function mapObject(\DOMElement $node, object $obj)
    {
        $class = new \ReflectionClass(get_class($obj));
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof \DOMElement) {
                $key = $this->nodeAttr($childNode, 'key');
                if ($class->hasProperty($key)) {
                    if (($childObj = $this->deserializeNode($childNode)) !== null) {
                        $class->getProperty($key)->setValue($obj, $childObj);
                    }
                }
            }
        }
        if ($node->hasAttribute('id')) {
            $id = $node->getAttribute('id');
            $this->idMap[$id] = $obj;
            if ($class->hasProperty('id')) {
                $obj->id = $id;
            }
        }
        return $obj;
    }

    /**
     * リンクされているオブジェクトの参照を解決し、実体への参照に置き換える。
     */
    /*
    private function resolveLinks($obj)
    {
        $class = new \ReflectionClass(get_class($obj));
        foreach ($class->getProperties() as $prop) {
            if ($prop->isPublic()) {
                assert($prop instanceof \ReflectionProperty);
                $value = $prop->getValue($obj);
                if ($value instanceof MwbLink) {
                    $prop->setValue($obj, $value->get());
                } else if (is_object($value)) {
                    $this->resolveLinks($value);
                } else if (is_array($value)) {
                    foreach ($value as $i => $x) {
                        $value[$i] = $this->resolveLinks($x);
                    }
                }
            }
        }
    }*/
}
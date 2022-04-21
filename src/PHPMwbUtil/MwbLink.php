<?php

namespace PHPMwbUtil;

class MwbLink
{
    private $document;
    private $id;

    public function __construct(MwbDocument $document, $id)
    {
        $this->document = $document;
        $this->id = $id;
    }

    /**
     * <link>タグの参照先を取得する
     *
     * @return mixed
     */
    public function get()
    {
        $document = $this->document;
        assert($document instanceof MwbDocument);
        return $document->getById($this->id);
    }
}
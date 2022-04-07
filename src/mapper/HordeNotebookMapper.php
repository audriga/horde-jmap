<?php

namespace OpenXPort\Mapper;

use OpenXPort\Jmap\Note\Notebook;

class HordeNotebookMapper extends AbstractMapper
{
    public function mapFromJmap($jmapData, $adapter)
    {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter)
    {
        $list = [];

        foreach ($data as $notebookUid => $notebook) {
            $adapter->setNotebook($notebook);

            $jmapNotebook = new Notebook();
            $jmapNotebook->setId($adapter->getId());
            $jmapNotebook->setName($adapter->getName());
            $jmapNotebook->setDescription($adapter->getDescription());
            $jmapNotebook->setShareWith($adapter->getShareWith());

            array_push($list, $jmapNotebook);
        }

        return $list;
    }
}

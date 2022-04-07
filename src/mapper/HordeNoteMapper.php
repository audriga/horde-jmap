<?php

namespace OpenXPort\Mapper;

use OpenXPort\Jmap\Note\Note;

class HordeNoteMapper extends AbstractMapper
{
    public function mapFromJmap($jmapData, $adapter)
    {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter)
    {
        $list = [];

        foreach ($data as $notebookId => $vNotes) {
            foreach ($vNotes as $uid => $vNote) {
                $adapter->setNote($vNote);

                $jmapNote = new Note();
                $jmapNote->setId($adapter->getId());
                $jmapNote->setNotebookId($notebookId);
                $jmapNote->setBody($adapter->getBody());
                $jmapNote->setName($adapter->getName());
                $jmapNote->setKeywords($adapter->getKeywords());
                $jmapNote->setCreated($adapter->getCreated());
                $jmapNote->setUpdated($adapter->getUpdated());

                array_push($list, $jmapNote);
            }
        }
        return $list;
    }
}

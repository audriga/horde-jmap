<?php

namespace OpenXPort\DataAccess;

class HordeNoteDataAccess extends AbstractDataAccess
{
    public function getAll($accountId = null)
    {
        // Use registry for access to internal API which provides data access
        global $registry;

        $noteIds = [];
        $result = [];

        // Only look at editable notebooks
        $notepads = $registry->call('notes/listNotepads', array(true, \Horde_Perms::EDIT));

        foreach ($notepads as $notepad) {
            $notepadId = $notepad->data['share_name'];

            $notepadNotes = [];

            // Take all IDs of Notes
            $noteIds = $registry->call('notes/listUids', array($notepadId));

            // Iterate through all IDs and export for each ID a vNote
            foreach ($noteIds as $noteId) {
                $vNote = $registry->call(
                    'notes/export',
                    array($noteId, 'text/x-vnote')
                );

                $notepadNotes[$noteId] = $vNote;
            }

            $result[$notepadId] = $notepadNotes;
        }

        return $result;
    }

    public function get($ids, $accountId = null)
    {
        // TODO: Implement me
    }

    public function create($notesToCreate, $accountId = null)
    {
        // TODO: Implement me
    }

    public function destroy($ids, $accountId = null)
    {
        // TODO: Implement me
    }

    public function query($accountId, $filter = null)
    {
        // TODO: Implement me
    }
}

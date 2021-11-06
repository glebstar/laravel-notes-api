<?php

namespace App\Services;

use App\Note;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\Types\Integer;

class NoteService
{
    /**
     * Add File
     *
     * @param Note $note
     * @param UploadedFile $attache
     * @param int $id
     * @return Note
     */
    public function addfile(Note $note, UploadedFile $attache, int $id)
    {
        $fileName = $id . '.' . $attache->getClientOriginalExtension ();

        if ($note->file && Storage::exists($note->file)) {
            Storage::delete($note->file);
        }

        Storage::put($fileName, file_get_contents($attache));
        unlink ($attache->getPathname ());

        $note->file = $fileName;
        $note->save ();

        return $note;
    }
}

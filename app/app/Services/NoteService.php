<?php

namespace App\Services;

use App\Note;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class NoteService
{
    /**
     * List Notes
     *
     * @param int $page
     * @return Collection
     */
    public function listNotes(int $page)
    {
        if ($page < 1) {
            $page = 1;
        }

        $limit  = 10;
        $offset = ( $page - 1 ) * $limit;

        return Note::where ('user_id', \JWTAuth::parseToken()->authenticate()->id)
            ->skip ($offset)->take ($limit)
            ->get ();
    }

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
